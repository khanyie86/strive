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
                <div class="container">
                    <h3 class="mt-5">Bus Notifications</h3>
                    <table class="table table-striped mt-3">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Bus ID</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Date Sent</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        // Database credentials
                        $host = getenv('DB_HOST') ?: 'localhost';
                        $dbname = getenv('DB_NAME') ?: 'bus_reg_system';
                        $username = getenv('DB_USER') ?: 'root';
                        $password = getenv('DB_PASS') ?: '';

                        // Data Source Name (DSN)
                        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

                        try {
                            // Create a PDO instance (connect to the database)
                            $db = new PDO($dsn, $username, $password);

                            // Set PDO attributes to throw exceptions on errors
                            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                            // Query to retrieve data from the bus_notifications table
                            $sql = "SELECT id, bus_id, message, status, date_sent FROM bus_notifications";
                            $stmt = $db->query($sql);

                            // Check if there are results
                            if ($stmt->rowCount() > 0) {
                                // Output data of each row
                                while ($row = $stmt->fetch()) {
                                    echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['bus_id']}</td>
                                <td>{$row['message']}</td>
                                <td>{$row['status']}</td>
                                <td>{$row['date_sent']}</td>
                              </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No notifications found</td></tr>";
                            }
                        } catch (PDOException $e) {
                            // Handle connection error
                            echo "<tr><td colspan='5'>Connection failed: " . $e->getMessage() . "</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>


                </div>
            </div>
        </main>
    </div>
</div>

<?php require 'includes/footer.php'; ?>

<?php
// End output buffering and send output to browser
ob_end_flush();
?>

