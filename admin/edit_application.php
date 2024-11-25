<?php
// Start output buffering
ob_start();

// Start the session if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

require 'includes/header.php';
require 'db.php';

// Fetch data from the database for the form
$app_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($app_id <= 0) {
    echo "Invalid application ID.";
    exit();
}

$stmt = $db->prepare("SELECT * FROM bookings WHERE booking_id = ?");
$stmt->execute([$app_id]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch buses from the database
$stmt = $db->query("SELECT * FROM bus");
$buses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch learners from the database
$stmt = $db->prepare("SELECT * FROM learners
INNER JOIN bookings ON bookings.learner_id = learners.learner_id
WHERE bookings.booking_id = ?");
$stmt->execute([$_GET['id']]);
$learners = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch stops from the database
$stmt = $db->query("SELECT * FROM morning_pickups
    INNER JOIN bus ON bus.bus_id = morning_pickups.bus_id");
$pickups = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->query("SELECT * FROM afternoon_dropoffs
    INNER JOIN bus ON bus.bus_id = afternoon_dropoffs.bus_id");
$dropoffs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Handle form submission
    try {
        $learner_id = $_POST['learner_id'];
        $pickup_id = $_POST['pickup_id'];
        $dropoff_id = $_POST['dropoff_id'];
        $bus_id = $_POST['bus_id'];

        $stmt = $db->prepare("UPDATE bookings SET learner_id = ?, pickup_id = ?, dropoff_id = ? , bus_id = ? WHERE booking_id = ?");
        $stmt->execute([$learner_id, $pickup_id, $dropoff_id, $bus_id, $_GET['id']]);

        $sql = "SELECT * FROM learners
        INNER JOIN parents ON learners.parent_id = parents.parent_id
        WHERE learners.learner_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$learner_id]);

        $row = $stmt->fetch();
        $to = $row['email'];
        $subject = 'Application Updated';
        $message = "Hi {$row['name']},\n\n";
        $message .= "Application status for {$row['name']} {$row['surname']} has now been updated successfully.\n\n";

        if (!mail($to, $subject, $message)) {
            $message = '<div class="alert alert-danger" role="alert">Error sending email</div>';
        } else {
            $message = '<div class="alert alert-success" role="alert">Application updated successfully.</div>';
        }

    } catch (PDOException $e) {
        $message = "Error updating application: " . $e->getMessage();
    }
} else {
    // Fetch the current data for the form
    try {
        $stmt = $db->prepare("SELECT * FROM bookings WHERE booking_id = ?");
        $stmt->execute([$app_id]);
        $application = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$application) {
            $message = "Application not found.";
        } else {
            // Fetch learners, routes, and stops for the dropdowns
            // $stmt = $db->query("SELECT learner_id, name, surname FROM learners");
            // $learners = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // $stmt = $db->query("SELECT * FROM routes");
            // $routes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // $stmt = $db->query("SELECT stop_id, stop_name FROM stops");
            // $stops = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

    } catch (PDOException $e) {
        $message = "Error fetching data: " . $e->getMessage();
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            <div class="chart-placeholder">
                <br>
                <div class="container mt-5">
                    <h2>Edit Application</h2>
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="learner_id" class="form-label">Learner</label>
                                <select id="learner_id" name="learner_id" class="form-select">
                                    <?php foreach ($learners as $learner): ?>
                                        <option value="<?php echo htmlspecialchars($learner['learner_id']); ?>">
                                            <?php echo htmlspecialchars($learner['name'] . ' ' . $learner['surname']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a learner.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="bus_id" class="form-label">Select Bus</label>
                                <select id="bus_id" name="bus_id" class="form-select" required>
                                    <option value="">Select Bus</option>
                                    <?php foreach ($buses as $bus): ?>
                                        <option value="<?php echo htmlspecialchars($bus['bus_id']); ?>">
                                            Bus <?php echo htmlspecialchars($bus['bus_number']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a bus.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="pickup_id" class="form-label">Pickup Point</label>
                                <select id="pickup_id" name="pickup_id" class="form-select" required>
                                    <option value="">Select Pickup</option>
                                    <?php foreach ($pickups as $pickup): ?>
                                        <option value="<?php echo htmlspecialchars($pickup['pickup_id']); ?>">
                                            Bus <?php echo htmlspecialchars($pickup['bus_number']); ?>
                                            <?php echo htmlspecialchars($pickup['pickup_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a pickup point.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="dropoff_id" class="form-label">Dropoff Point</label>
                                <select id="dropoff_id" name="dropoff_id" class="form-select" required>
                                    <option value="">Select Dropoff</option>
                                    <?php foreach ($dropoffs as $dropoff): ?>
                                        <option value="<?php echo htmlspecialchars($dropoff['dropoff_id']); ?>">
                                            Bus <?php echo htmlspecialchars($dropoff['bus_number']); ?>
                                            <?php echo htmlspecialchars($dropoff['dropoff_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a dropoff point.
                                </div>
                            </div>

                        </div>
                        <div>

                            <?php
                            echo $message ?? '';
                            ?>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Application</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    // JavaScript to enable Bootstrap validation feedback
    (function () {
        'use strict';
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
</script>

<?php require 'includes/footer.php'; ?>

<?php
// End output buffering and send output to browser
ob_end_flush();
?>