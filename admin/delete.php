<?php

// Start the session if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$learner_id = $_GET['id'];

require 'db.php';

$sql = "DELETE FROM learners WHERE learner_id = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$learner_id]);

header('location: /admin/dashboard.php');