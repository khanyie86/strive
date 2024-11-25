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

$id = $_GET['id'];

require 'db.php';

$sql = "DELETE FROM bookings WHERE booking_id = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$id]);

header('location: /parent/bus_reg.php');