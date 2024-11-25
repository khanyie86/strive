<?php

if (isset($_POST['learner'])) {
    require 'db.php';

    $error_msg = '';
    $success_msg = '';
    $learner = $_POST['learner'];
    $bus = $_POST['bus'];
    $pickup = $_POST['pickup'];
    $dropoff = $_POST['dropoff'];
    $date = date('Y-m-d');
    $created_at = date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO bookings (booking_status, learner_id, bus_id, application_date, pickup_id, dropoff_id, created_at) VALUES (?,?,?,?,?,?,?)";
    $stmt = $db->prepare($sql);

    if (registration($db, $learner, $bus) === 0) {
        
        if (availability($db, $bus)) {

            if ($stmt->execute(["APPROVED", $learner, $bus, $date, $pickup, $dropoff, $created_at])) {
                $success_msg = '<div class="text-success text-start my-2">Application is Successful</div>';
            }

            if (!sendmail($learner, $db)) {
                $error_msg = '<div class="text-danger text-start my-2">Application is failed to send email.</div>';
            }
        } else {
            if (sendmail($learner, $db)) {
                $stmt->execute(["WAITING_LIST", $learner, $bus, $date, $pickup, $dropoff, $created_at]);
                $error_msg = '<div class="text-danger text-start my-2">Bus is not available, and learner has been moved to waiting list.</div>';
            } else {
                $error_msg = '<div class="text-danger text-start my-2">Application is failed to send email.</div>';
            }
        }

    } else {

        $error_msg = '<div class="text-danger text-start my-2">Learner has already been booked.</div>';
    }

}

function sendmail($learner, $db)
{
    $sql = "SELECT 
    learners.name AS learner_name, 
    learners.surname AS learner_surname, 
    parents.name AS parent_name, 
    email
    FROM learners
    INNER JOIN parents ON learners.parent_id = parents.parent_id
    WHERE learners.learner_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$learner]);

    $row = $stmt->fetch();
    
    $to = $row['email'];
    $subject = 'New Application';
    $message = "Hi {$row['parent_name']},\n\n";
    $message .= "Application for {$row['learner_name']} {$row['learner_surname']} was successful";

    if (mail($to, $subject, $message)) {
        return true;
    }

    return false;
}

function availability($db, $bus)
{
    $sql = "SELECT bus.capacity, COUNT(bookings.bus_id) AS BOOKINGS 
    FROM bus
    LEFT JOIN bookings ON bus.bus_id = bookings.bus_id
    WHERE bus.bus_id = ? || bookings.booking_status != ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$bus, "CANCELLED"]);

    $row = $stmt->fetch();
    
    if ($row['BOOKINGS'] >= $row['capacity']) {
        return false;
    }

    return true;
}

function registration($db, $learner, $bus)
{
    $sql = "SELECT * FROM bookings WHERE learner_id = ? AND bus_id = ? AND booking_status != ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$learner, $bus, "CANCELLED"]);

    return count($stmt->fetchAll());
}

echo $error_msg;
echo $success_msg;