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
try {
    // Fetch buses from the database
    $stmt = $db->query("SELECT * FROM bus");
    $buses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch learners from the database
    $stmt = $db->query("SELECT * FROM learners");
    $learners = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch stops from the database
    $stmt = $db->query("SELECT * FROM morning_pickups 
    INNER JOIN bus ON bus.bus_id = morning_pickups.bus_id");
    $pickups = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->query("SELECT * FROM afternoon_dropoffs 
    INNER JOIN bus ON bus.bus_id = afternoon_dropoffs.bus_id");
    $dropoffs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching data: " . $e->getMessage();
    exit();
}
?>

<div class="container-fluid">
    <div class="row">
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            <div class="chart-placeholder">
                <br>
                <div class="container mt-5">
                    <h2>Apply for Bus Registration</h2>
                    <form method="POST" action="/admin/apply.php" class="needs-validation" novalidate>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="learner_id" class="form-label">Learner</label>
                                <select id="learner_id" name="learner_id" class="form-select" required>
                                    <option value="">Select Learner</option>
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
                                <label for="pickup_id" class="form-label">Pickup Point</label>
                                <select id="pickup_id" name="pickup_id" class="form-select" required>
                                    <option value="">Select Pickup</option>
                                    <?php foreach ($pickups as $pickup): ?>
                                        <option value="<?php echo htmlspecialchars($pickup['pickup_id']); ?>">
                                            Bus <?php echo htmlspecialchars($pickup['bus_number']); ?> -
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
                                            Bus <?php echo htmlspecialchars($dropoff['bus_number']); ?> - 
                                            <?php echo htmlspecialchars($dropoff['dropoff_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a dropoff point.
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
                        </div>
                        <span id="response"></span>
                        <button type="submit" class="btn btn-primary">Submit Application</button>
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
                event.preventDefault();

                var learner = document.getElementById('learner_id').value;
                var pickup = document.getElementById('pickup_id').value;
                var dropoff = document.getElementById('dropoff_id').value;
                var bus = document.getElementById('bus_id').value;

                if (!form.checkValidity()) {
                    event.stopPropagation();
                }
                else {
                    var xhttp = new XMLHttpRequest();

                    xhttp.open("POST", form.action, true);
                    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

                    xhttp.onreadystatechange = function () {
                        if (this.readyState == 4 && this.status == 200) {
                            document.getElementById("response").innerHTML = this.responseText;
                        }
                    };

                    xhttp.send("learner=" + learner + "&dropoff=" + dropoff + "&pickup=" + pickup + "&bus=" + bus);
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