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
?>

<?php require 'includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">


        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            <div class="chart-placeholder">
                <br>
                <div class="container">


                    <?php
                    require 'db.php';
                    try {
                        // Fetch registrations with child, route, and stop details in a single query
                        $stmt = $db->prepare("
                            SELECT *
                            FROM `parents` 
                            INNER JOIN learners ON learners.parent_id = learners.parent_id
                            INNER JOIN bookings ON learners.learner_id = bookings.learner_id
                            INNER JOIN morning_pickups ON bookings.pickup_id = morning_pickups.pickup_id
                            INNER JOIN afternoon_dropoffs ON bookings.dropoff_id = afternoon_dropoffs.dropoff_id
                            WHERE parents.parent_id = ?
                        ");
                        $stmt->execute([$_SESSION['parent_id']]);
                        $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                        echo "Error fetching registrations: " . $e->getMessage();
                        exit; // Stop execution if there's an error
                    }

                    if (empty($registrations)) {
                        echo "<p class='badge bg-danger p-3'>No registrations found for this parent.</p>";
                        echo '<div class="container">
                            <h1>Make New Application</h1>
                            <a href="create_application.php" class="btn btn-primary">Apply Now</a>
                        </div>';
                    } else {
                        echo "<table class='table table-striped'>";
                        echo "<thead><tr><th>Learner</th><th>Pickup Point</th><th>Dropoff Point</th><th>Status</th><th>Reg Date</th><th>Actions</th></tr></thead>";
                        echo "<tbody>";
                        foreach ($registrations as $registration) {
                            $status = str_replace("_", " ", $registration['booking_status']);

                            if($status === 'WAITING LIST')
                            {
                                $class = 'primary';
                            }
                            elseif($status === 'APPROVED')
                            {
                                $class = 'success';
                            }
                            elseif($status === 'CANCELLED')
                            {
                                $class = 'danger';
                            }


                            echo "<tr>";
                            echo "<td>" . $registration['name'] . " " . $registration['surname'] . "</td>";
                            echo "<td>" . $registration['pickup_name'] . "</td>";
                            echo "<td>" . $registration['dropoff_name'] . "</td>";
                            echo "<td><div class=\"badge bg-{$class}-subtle text-{$class}\">" . $status . "</div></td>";
                            echo "<td>" . $registration['application_date'] . "</td>";
                            echo "<td>
                                <a href='/parent/delete_registration.php?id=" . $registration['booking_id'] . "'>Delete</a>
                            </td>";
                            echo "</tr>";
                        }
                        echo "</tbody></table>";
                    }
                    ?>

                    <script>
                        function confirmDelete(name) {
                            return confirm('Are you sure you want to delete ' + name + '?');
                        }
                    </script>

                </div>
            </div>
        </main>
    </div>
</div>

<?php require 'includes/footer.php'; ?>