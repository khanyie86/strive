<?php require 'includes/header.php'; ?>


<?php


redirect_if_parent_not_logged_in('login.php'); // Redirect to login if not logged in

// Fetch parent's data
$parent_id = $_SESSION['parent_id'];
try {
    $stmt = $db->prepare("SELECT * FROM parents WHERE id = ?");
    $stmt->execute([$parent_id]);
    $parent = $stmt->fetch(PDO::FETCH_ASSOC);
    $parent_email = $parent['email'];
    $parent_id = $parent['id'];
    $parent_name = $parent['firstname'];
} catch (PDOException $e) {
    echo "Error fetching parent's data: " . $e->getMessage();
    exit; // Stop execution if there's an error
}
// Fetch registrations for the logged-in parent
try {
    $stmt = $db->prepare("SELECT * FROM registrations WHERE parent_id = ?");
    $stmt->execute([$parent_id]);
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching registrations: " . $e->getMessage();
    exit; // Stop execution if there's an error
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Parent Dashboard</title>
</head>
<body>


<h3>Your Registrations:</h3>
<?php
try {
    // Fetch registrations with child, route, and stop details in a single query
    $stmt = $db->prepare("
        SELECT 
            registrations.*, 
            children.name AS child_name, 
            routes.route_name, 
            stops.stop_name
        FROM 
            registrations
        JOIN 
            children ON registrations.child_id = children.id
        JOIN 
            routes ON registrations.route_id = routes.id
        JOIN 
            stops ON registrations.stop_id = stops.id
        WHERE 
            children.parent_id = ?
    ");
    $stmt->execute([$parent_id]);
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching registrations: " . $e->getMessage();
    exit; // Stop execution if there's an error
}

if (empty($registrations)) {
    echo "<p>No registrations found for this parent.</p>";
} else {
    echo "<table class='table table-striped'>";
    echo "<thead><tr><th>Child Name</th><th>Route Name</th><th>Stop Name</th><th>Status</th><th>Registration Date</th><th>Payment</th></tr></thead>";
    echo "<tbody>";
    foreach ($registrations as $registration) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($registration['child_name']) . "</td>";
        echo "<td>" . htmlspecialchars($registration['route_name']) . "</td>";
        echo "<td>" . htmlspecialchars($registration['stop_name']) . "</td>";
        echo "<td>" . htmlspecialchars($registration['status']) . "</td>";
        echo "<td>" . htmlspecialchars($registration['registration_date']) . "</td>";
        echo "<td>";
        if ($registration['status'] === 'Approved') {
            echo "<a href='process_payment.php' target='_blank'>pay</a>";
        } else {
            echo "Pending";
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
}
?>

<?php require 'includes/footer.php'; ?>
