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

$error_msg = ''; // Initialize error message variable

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_child'])) {
    // Retrieve and sanitize form inputs
    $parent_id = $_SESSION['parent_id'];
    $bus_id = htmlspecialchars(trim($_POST['bus_id']));
    $admin_id = htmlspecialchars(trim($_POST['admin_id']));
    $name = htmlspecialchars(trim($_POST['learner_name']));
    $surname = htmlspecialchars(trim($_POST['learner_surname']));
    $home_address = htmlspecialchars(trim($_POST['learner_home_address']));
    $phone = htmlspecialchars(trim($_POST['learner_phone']));
    $grade = htmlspecialchars(trim($_POST['learner_grade']));
    $date_of_birth = htmlspecialchars(trim($_POST['learner_dob']));
    $address = htmlspecialchars(trim($_POST['learner_home_address']));
    $preg_str = '/^[a-zA-Z]+( [a-zA-Z]+)*$/';
    $preg_num = "/^[0-9]{10}+$/";
    $age_min = 12;
    $age_max = 28;

    $age = date("Y") - date("Y", strtotime($date_of_birth));

    if (!preg_match($preg_str, $name)) {
        $error_msg = 'Invalid name!';
    } elseif (!preg_match($preg_str, $surname)) {
        $error_msg = 'Invalid surname!';
    } elseif (!preg_match($preg_num, $phone)) {
        $error_msg = 'Invalid cellphone number!';
    } elseif ($age < $age_min || $age > $age_max) {
        $error_msg = 'Learner age must be between ' . $age_min . ' and ' . $age_max . '!';
    } else {

        // Check if learner exists
        $get_learner_sql = "SELECT `name`, surname, contact_number FROM learners WHERE `name` = ? AND surname = ? || contact_number = ?";
        $learner_stmt = $db->prepare($get_learner_sql);
        $learner_stmt->execute([$name, $surname, $phone]);

        $learners_results = $learner_stmt->fetch();

        if ($learners_results) {
            
            if ($name === $learners_results['name'] && $surname === $learners_results['surname']) {
                $error_msg = "Learner has laready been registered!";
            } else if ($learners_results['contact_number'] === $phone) {
                $error_msg = "Phone number has already been registered on the system!";
            }

        } else {
            // Proceed if no errors
            if (empty($error_msg)) {
                // Prepare SQL statement
                $sql = "
            INSERT INTO learners (name, surname, contact_number, grade, date_of_birth, address, parent_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($sql);

                try {
                    // Bind parameters and execute the statement
                    $stmt->execute([$name, $surname, $phone, $grade, $date_of_birth, $address, $parent_id]);

                    // Redirect to the success page
                    header("Location: success.php"); // specify the target page here
                    exit();
                } catch (PDOException $e) {
                    // Display error message in a user-friendly manner
                    $error_msg = "Error: " . htmlspecialchars($e->getMessage());
                }
            }
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
                    <form method="POST" action="add_child.php" class="container mt-4">
                        <div class="row">
                            <input type="hidden" name="parent_id"
                                value="<?php echo htmlspecialchars($_SESSION['parent_id']); ?>">
                            <input type="hidden" name="bus_id" value="<?php echo htmlspecialchars($bus_id ?? ''); ?>">
                            <input type="hidden" name="admin_id"
                                value="<?php echo htmlspecialchars($admin_id ?? ''); ?>">

                            <div class="col-md-6 mb-3">
                                <label for="learner_name" class="form-label">Child's Name</label>
                                <input type="text" id="learner_name" name="learner_name" class="form-control"
                                    placeholder="Child's Name" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="learner_surname" class="form-label">Surname</label>
                                <input type="text" id="learner_surname" name="learner_surname" class="form-control"
                                    placeholder="Surname"
                                    value="<?php echo htmlspecialchars($_SESSION['lastname'] ?? ''); ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="learner_phone" class="form-label">Phone</label>
                                <input type="tel" id="learner_phone" name="learner_phone" maxlength="10"
                                    class="form-control" placeholder="Phone" required>
                            </div>

                            <!-- Date of Birth -->
                            <div class="col-md-6 mb-3">
                                <label for="learner_dob" class="form-label">Date of Birth</label>
                                <input type="date" id="learner_dob" name="learner_dob" class="form-control" required>
                            </div>

                            <!-- Home Address -->
                            <div class="col-md-6 mb-3">
                                <label for="learner_home_address" class="form-label">Home Address</label>
                                <input type="text" id="learner_home_address" name="learner_home_address"
                                    class="form-control" placeholder="Home Address" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="learner_grade" class="form-label">Grade</label>
                                <select id="learner_grade" name="learner_grade" class="form-select" required>
                                    <option value="">Select Grade</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                    <option value="11">11</option>
                                    <option value="12">12</option>
                                </select>
                            </div>

                            <!-- HTML Form for adding learners -->
                            <?php if (!empty($error_msg)): ?>
                                <div class="alert alert-danger text-center" role="alert">
                                    <?php echo $error_msg; ?>
                                </div>
                            <?php endif; ?>

                            <div class="col-12">
                                <button type="submit" name="add_child" class="btn btn-primary">Add Child</button>
                            </div>
                        </div>
                    </form>

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