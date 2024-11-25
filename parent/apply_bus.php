<?php
// Start output buffering
ob_start();

// Start the session if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['parent_id'])) {
    header("Location: index.php");
    exit();
}

require 'includes/header.php';
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_bus'])) {
    $learner_id = $_POST['learner_id'];
    $route_id = $_POST['route_id'];
    $stop_id = $_POST['stop_id'];

    // Verify that the learner_id exists in the learner table
    $stmt_check_learner = $db->prepare("SELECT learner_id FROM learner WHERE learner_id = ?");
    $stmt_check_learner->execute([$learner_id]);
    $existing_learner = $stmt_check_learner->fetch(PDO::FETCH_ASSOC);

    if (!$existing_learner) {
        echo "Invalid learner ID";
        exit();
    }

    // Verify bus capacity
    $stmt_bus_capacity = $db->prepare("SELECT capacity FROM busses WHERE route_id = ?");
    $stmt_bus_capacity->execute([$route_id]);
    $bus_capacity = $stmt_bus_capacity->fetchColumn();

    $stmt_registrations_count = $db->prepare("SELECT COUNT(*) FROM registrations WHERE route_id = ?");
    $stmt_registrations_count->execute([$route_id]);
    $registrations_count = $stmt_registrations_count->fetchColumn();

    if ($registrations_count >= $bus_capacity) {
        echo "Sorry, the bus is already full. Please choose another route or try again later.";
        exit();
    } else {
        // Insert the parent_id into the parent_id column
        $parent_id = $_SESSION['parent_id'];
        try {
            $stmt = $db->prepare("INSERT INTO registrations (parent_id, learner_id, route_id ) VALUES (?, ?, ?)");
            $stmt->execute([$parent_id, $learner_id, $route_id]);
            header("Location: dashboard.php");
            exit();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Integrity constraint violation: 1062 Duplicate entry
                echo "<script>alert('Only one application per child is allowed.');</script>";
            } else {
                echo "Error: " . $e->getMessage();
            }
        }
    }
}

// Fetch necessary data
$parent_id = $_SESSION['parent_id'];
$learner = $db->query("SELECT * FROM learner WHERE parent_id = $parent_id")->fetchAll(PDO::FETCH_ASSOC);
$routes = $db->query("SELECT * FROM routes")->fetchAll(PDO::FETCH_ASSOC);
$stops = $db->query("SELECT * FROM stops")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Bus Routes</title>
</head>
<body>
<h1>Apply for Bus Routes</h1>

<form method="POST">
    <select name="learner_id" required>
        <?php foreach ($learner as $learner): ?>
            <option value="<?= $learner['learner_id'] ?>"><?= $learner['learner_name'] ?></option>
        <?php endforeach; ?>
    </select>

    <select name="route_id" required>
        <?php foreach ($routes as $route): ?>
            <option value="<?= $route['route_id'] ?>"><?= $route['route_name'] ?></option>
        <?php endforeach; ?>
    </select>

    <select name="stop_id" required>
        <?php foreach ($stops as $stop): ?>
            <option value="<?= $stop['stop_id'] ?>"><?= $stop['stop_name'] ?></option>
        <?php endforeach; ?>
    </select>


    <button type="submit" name="apply_bus">Apply</button>
</form>

<?php require 'includes/footer.php'; ?>