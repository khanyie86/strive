<?php

// Start the session if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['parent_id'])) {
    header("Location: index.php");
    exit();
}

$booking_id = $_GET['app_no'];

require 'db.php';

$getInfo = "SELECT learner_id FROM bookings WHERE booking_id = ?";
$statement = $db->prepare($getInfo);

if ($statement->execute([$booking_id])) {

    $info = $statement->fetch();

    if ($info) {
        if (sendmail($info['learner_id'], $db)) {
            $sql = "DELETE FROM bookings WHERE booking_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$booking_id]);

            header('location: /parent/waiting_list.php');
        }
        else
        {
            echo "Error sending email, please try again.<br><a href=''>Refresh Page</a>";
        }
    }
} else {
    echo "No record found!";
}


function sendmail($learner, $db)
{
    $sql = "SELECT parents.name AS parent_name, email, learners.name, learners.surname FROM learners
    INNER JOIN parents ON learners.parent_id = parents.parent_id
    WHERE learners.learner_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$learner]);

    $row = $stmt->fetch();
    $to = $row['email'];
    $subject = 'Application Cancelled';
    $message = "Hi {$row['parent_name']},\n\n";
    $message .= "Application for {$row['name']} {$row['surname']} has been cancelled successfully.";

    if (mail($to, $subject, $message)) {
        return true;
    }

    return false;
}
