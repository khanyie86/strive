<?php
session_start();

// Database configuration
$servername = "localhost";
$username = "root"; // Change to your database username
$password = ""; // Change to your database password
$dbname = "bus_reg_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the posted data
$data = json_decode(file_get_contents('php://input'), true);
$parent_id = $data['parent_id'];
$status = $data['status'];

// Update status
$sql = "UPDATE parent SET status = ? WHERE parent_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $status, $parent_id);
$success = $stmt->execute();
$stmt->close();

$conn->close();

echo json_encode(['success' => $success]);
?>
