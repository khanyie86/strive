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

// Include header file at the beginning
require 'includes/header.php';

// Retrieve application number from query parameter
$application_no = isset($_GET['application_no']) ? htmlspecialchars($_GET['application_no']) : 'N/A';
?>

<div class="container-fluid">
    <div class="row">
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            <div class="chart-placeholder">
                <br>
                <div class="container">
                    <hr>
                    <h4>Application Submitted</h4>
                    <p>The child profile has been successfully created.</p>
                    <p>Application Number: <?php echo $application_no; ?></p>
                    <a href="/admin/add_learner.php" class="btn btn-primary">Add Another Child</a>
                    <a href="dashboard.php" class="btn btn-secondary">Go to Dashboard</a>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
