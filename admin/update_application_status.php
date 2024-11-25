<?php

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['booking_id'])) {
    
    require 'db.php';

    if(!availability($db, $data['bus_id']))
    {
        echo json_encode(['app_status' => 'failed']);
        exit;
    }

    $status = $data['status'];

    $sql = "UPDATE bookings SET booking_status = ? WHERE booking_id = ?";
    $stmt = $db->prepare($sql);

    if ($stmt->execute([$status, $data['booking_id']])) {

        sendmail($data, $db);

        echo json_encode(['app_status' => 'success']);
    } else {
        echo json_encode(['app_status' => 'failed']);
    }
}

function availability($db, $bus)
{
    $sql = "SELECT bus.capacity, COUNT(bookings.bus_id) AS BOOKINGS 
    FROM bus
    LEFT JOIN bookings ON bus.bus_id = bookings.bus_id
    WHERE bus.bus_id = ? AND booking_status != ?;
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([$bus, "CANCELLED"]);

    $row = $stmt->fetchAll();
    
    if(count($row) > 0)
    {    
        if ($row[0]['BOOKINGS'] >= $row[0]['capacity']) {
            return false;
        }
    }

    return true;
}

function sendmail($data, $db)
{
    $sql = "SELECT parents.name AS parent_name, parents.email, learners.name, learners.surname FROM learners
    INNER JOIN parents ON learners.parent_id = parents.parent_id
    WHERE learners.learner_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$data['learner_id']]);

    $status = $data['status'];

    $row = $stmt->fetch();
    $to = $row['email'];
    $subject = 'Application Status Change';
    $message = "Hi {$row['name']},\n\n";
    $message .= "Application status for {$row['name']} {$row['surname']} has now been updated successfully.\n\n";
    $message .= "New status: " . str_replace("_", " ", $status);

    if (mail($to, $subject, $message)) {
        return true;
    }

    return false;
}