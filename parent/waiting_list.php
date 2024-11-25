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
?>

<div class="container-fluid">
    <div class="row">
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            <div class="chart-placeholder">
                <br>
                <div class="container">
                    <h3 class="mt-5">Waiting List</h3>
                    <table class="table table-striped mt-3">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>learner</th>
                                <th>Phone</th>
                                <th>Pickup Point</th>
                                <th>Dropoff Point</th>
                                <th>App Date</th>
                                <th>Cancel</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Database credentials
                            $sql = "SELECT * FROM bookings 
                                INNER JOIN learners ON learners.learner_id = bookings.learner_id
                                INNER JOIN morning_pickups ON morning_pickups.bus_id = bookings.bus_id
                                INNER JOIN afternoon_dropoffs ON afternoon_dropoffs.bus_id = bookings.bus_id
                                WHERE booking_status = ?
                                GROUP BY booking_id";
                            $stmt = $db->prepare($sql);
                            $stmt->execute(["WAITING_LIST"]);

                            // Check if there are results
                            if ($stmt->rowCount() > 0) {
                                // Output data of each row
                                while ($row = $stmt->fetch()) {
                                    echo "<tr>
                                <td>{$row['booking_id']}</td>
                                <td>{$row['name']} {$row['surname']}</td>
                                <td>{$row['contact_number']}</td>
                                <td>{$row['pickup_name']}</td>
                                <td>{$row['dropoff_name']}</td>
                                <td>{$row['created_at']}</td>
                                <td>
                                    <a href=\"/parent/cancel_app?app_no={$row['booking_id']}\" class=\"btn btn-sm btn-danger\"
                                         onclick=\"return confirmCancel('" . htmlspecialchars($row['booking_id']) . "')\">
                                        Cancel
                                    </a>
                                </td>
                              </tr>";
                                }
                            } else {
                                echo "0 results";
                            }
                            ?>


                </div>
            </div>
        </main>
    </div>
</div>

<script>
    function confirmCancel(name) {
        return confirm('Are you sure you want to cancel this application?');
    }
</script>
<?php require 'includes/footer.php'; ?>

<?php
// End output buffering and send output to browser
ob_end_flush();
?>