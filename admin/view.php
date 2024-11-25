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

// Fetch learner details based on learner_id
$learner = [];
if (isset($_GET['id'])) {
    $learner_id = htmlspecialchars(trim($_GET['id']));
    try {
        $stmt = $db->prepare("SELECT * FROM learners WHERE learner_id = ?");
        $stmt->execute([$learner_id]);
        $learner = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<p>Error fetching learner details: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p>Error: Learner ID is not provided.</p>";
    exit();
}
?>

<div class="container-fluid">
    <div class="row">
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            <div class="chart-placeholder">
                <br>
                <div class="container">
                    <!-- Display learner details -->
                    <?php if ($learner): ?>
                        <h3>View Child Details</h3>
                        <p><strong>Name:</strong> <?= htmlspecialchars($learner['name']); ?></p>
                        <p><strong>Surname:</strong> <?= htmlspecialchars($learner['surname']); ?></p>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($learner['contact_number']); ?></p>
                        <p><strong>Grade:</strong> <?= htmlspecialchars($learner['grade']); ?></p>
                        <p><strong>Date of Birth:</strong> <?= htmlspecialchars(date("d M Y",strtotime($learner['date_of_birth']))); ?></p>
                        <p><strong>Age:</strong> <?= htmlspecialchars(date("Y") - substr($learner['date_of_birth'], 0,4)); ?></p>
                        <p><strong>Home Address:</strong> <?= htmlspecialchars($learner['address']); ?></p>
                        <a href="learner_edit.php?id=<?= htmlspecialchars($learner['learner_id']); ?>" class="btn btn-warning">Edit</a>
                    <?php else: ?>
                        <p>Error: Learner not found.</p>
                    <?php endif; ?>
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
