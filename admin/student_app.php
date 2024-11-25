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

// Include header file
require 'includes/header.php';

// Database configuration
$servername = "localhost";
$username = "root"; // Change to your database username
$password = ""; // Change to your database password
$dbname = "strivehighsecondaryschool";

// Create connection
$conn = new PDO("mysql:host={$servername};dbname={$dbname}", $username, $password);

// Fetch student applications
$sql = "SELECT b.booking_id, 
        l.learner_id AS learner_id, 
        l.name AS learner_name, 
        l.surname AS learner_surname, 
        p.name AS parent_name, 
        p.surname AS parent_surname, 
        b.booking_status, 
        b.application_date,
        bus.bus_id
        FROM bookings b
        INNER JOIN bus ON b.bus_id = bus.bus_id
        INNER JOIN learners l ON b.learner_id = l.learner_id
        INNER JOIN parents p ON l.parent_id = p.parent_id";
$result = $conn->prepare($sql);
$result->execute();

?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOMt23cez/3paNdF+Z4pb9fq6hLQ9D3T+GOZ4pR" crossorigin="anonymous">

<div class="container-fluid">
    <div class="row">
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            <div class="chart-placeholder">
                <div class="container mt-5">
                    <br><br>
                    <h2>Student Applications</h2>
                    <hr>
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Application No</th>
                            <th>Child's Name</th>
                            <th>Parent's Name</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($result->rowCount() > 0): ?>
                            <?php foreach($result->fetchAll() as $row): ?>

                                <?php
                                    $status = $row['booking_status'];
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['booking_id']); ?></td>
                                    <td><?= htmlspecialchars($row['learner_name'] . ' ' . $row['learner_surname']); ?></td>
                                    <td><?= htmlspecialchars($row['parent_name'] . ' ' . $row['parent_surname']); ?></td>
                                    <td>
                                        <select class="form-select" onchange="updateStatus(<?= $row['booking_id']; ?>, this.value, <?= $row['learner_id']; ?>, <?= $row['bus_id']; ?>)">
                                            <option value="WAITING_LIST" <?= ($status == 'WAITING_LIST') ? 'selected' : ''; ?>>Waiting List</option>
                                            <option value="APPROVED" <?= ($status == 'APPROVED') ? 'selected' : ''; ?>>Approved</option>
                                            <option value="CANCELLED" <?= ($status == 'CANCELLED') ? 'selected' : ''; ?>>Cancel</option>
                                        </select>
                                    </td>

                                    <td>
                                        <a href="edit_application.php?id=<?= $row['booking_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">No applications found</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
                <script>
                    function updateStatus(booking_id, status, learner_id, bus_id) {
                        // Make an AJAX call to update application status

                        fetch('/admin/update_application_status.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ booking_id: booking_id, status: status, learner_id: learner_id, bus_id: bus_id })
                        })
                            .then(response => response.json())
                            .then(data => {
                                
                                if (data.app_status === 'success') {
                                    alert('Status updated successfully');
                                } else {
                                    alert('Failed to update status');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                            });
                    }
                </script>
            </div>
        </main>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
