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

// Fetch learner details for editing
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

$error = '';
// Handle the form submission for updating learner details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['learner_edit'])) {

    // Retrieve and sanitize form inputs
    $name = htmlspecialchars(trim($_POST['name']));
    $surname = htmlspecialchars(trim($_POST['surname']));
    $phone = htmlspecialchars(trim($_POST['contact_number']));
    $grade = htmlspecialchars(trim($_POST['grade']));
    $dob = htmlspecialchars(trim($_POST['date_of_birth']));
    $address = htmlspecialchars(trim($_POST['address']));
    $preg_str = '/^[a-zA-Z]+( [a-zA-Z]+)*$/';
    $preg_num = "/^[0-9]{10}+$/";
    $age_min = 12;
    $age_max = 18;

    $age = date("Y") - date("Y", strtotime($dob));

    if (!preg_match($preg_str, $name)) {
        $error = '<div class="alert alert-danger text-center" role="alert">Invalid name!</div>';
    } elseif (!preg_match($preg_str, $surname)) {
        $error = '<div class="alert alert-danger text-center" role="alert">Invalid surname!</div>';
    } elseif (!preg_match($preg_num, $phone)) {
        $error = '<div class="alert alert-danger text-center" role="alert">Invalid cellphone number!</div>';
    } elseif ($age < $age_min || $age > $age_max) {
        $error = '<div class="alert alert-danger text-center" role="alert">Learner age must be between ' . $age_min .' and ' . $age_max . '!</div>';
    } else {
        try {
            // Update the learner details in the database
            $sql = "
                UPDATE learners 
                SET name = ?, surname = ?, contact_number = ?, grade = ?, date_of_birth = ?, address = ?
                WHERE learner_id = ?
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute([$name, $surname, $phone, $grade, $dob, $address, $learner_id]);

            // Redirect to the success page
            header("Location: edit-success.php?message=" . urlencode("Child details updated successfully."));
            exit();
        } catch (PDOException $e) {
            echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }

}

?>

<div class="container-fluid">
    <div class="row">
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            <div class="chart-placeholder">
                <br>
                <div class="container">

                    <?php if ($learner): ?>
                        <!-- Form for editing learner details -->
                        <form method="POST"
                            action="learner_edit.php?id=<?php echo htmlspecialchars($learner['learner_id']); ?>"
                            class="container mt-4">
                            <div class="row">
                                <!-- Child's Name -->
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Child's Name</label>
                                    <input type="text" id="name" name="name" class="form-control"
                                        value="<?php echo htmlspecialchars($learner['name']); ?>" required>
                                </div>

                                <!-- Surname -->
                                <div class="col-md-6 mb-3">
                                    <label for="surname" class="form-label">Surname</label>
                                    <input type="text" id="surname" name="surname" class="form-control"
                                        value="<?php echo htmlspecialchars($learner['surname']); ?>" required>
                                </div>

                                <!-- Phone -->
                                <div class="col-md-6 mb-3">
                                    <label for="contact_number" class="form-label">Phone</label>
                                    <input type="tel" id="contact_number" name="contact_number" class="form-control"
                                        value="<?php echo htmlspecialchars($learner['contact_number']); ?>" required>
                                </div>

                                <!-- Grade -->
                                <div class="col-md-6 mb-3">
                                    <label for="grade" class="form-label">Grade</label>
                                    <select id="grade" name="grade" class="form-select" required>
                                        <option value="7" <?php echo $learner['grade'] == 7 ? 'selected' : ''; ?>>7
                                        </option>
                                        <option value="8" <?php echo $learner['grade'] == 8 ? 'selected' : ''; ?>>8
                                        </option>
                                        <option value="9" <?php echo $learner['grade'] == 9 ? 'selected' : ''; ?>>9
                                        </option>
                                        <option value="10" <?php echo $learner['grade'] == 10 ? 'selected' : ''; ?>>10
                                        </option>
                                        <option value="11" <?php echo $learner['grade'] == 11 ? 'selected' : ''; ?>>11
                                        </option>
                                        <option value="12" <?php echo $learner['grade'] == 12 ? 'selected' : ''; ?>>12
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="date_of_birth" class="form-label">Date Of Birth</label>
                                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-control"
                                        value="<?php echo htmlspecialchars($learner['date_of_birth']); ?>" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="address" class="form-label">Home Address</label>
                                    <input type="tel" id="address" name="address" class="form-control"
                                        value="<?php echo htmlspecialchars($learner['address']); ?>" required>
                                </div>

                                <?php echo $error ?>
                                <!-- Submit Button -->
                                <div class="col-12">
                                    <button type="submit" name="learner_edit" class="btn btn-primary">Update Child</button>
                                </div>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="text-center alert alert-danger">No Record Found</div>
                    <?php endif ?>
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