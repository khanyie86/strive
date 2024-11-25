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
// Fetch parents for dropdown
$parents = [];
try {
    $stmt = $db->query("SELECT * FROM parents");
    $parents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p>Error fetching parents: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Fetch bus routes for dropdown
$busses = [];
try {
    $stmt = $db->query("SELECT * FROM bus 
    INNER JOIN routes ON bus.route_id = routes.route_id
    ");
    $busses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p>Error fetching busses: " . htmlspecialchars($e->getMessage()) . "</p>";
}

$error = '';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_child'])) {
    // Retrieve and sanitize form inputs
    $parent_id = htmlspecialchars(trim($_POST['parent_id']));
    $name = htmlspecialchars(trim($_POST['learner_name']));
    $surname = htmlspecialchars(trim($_POST['learner_surname']));
    $phone = htmlspecialchars(trim($_POST['learner_phone']));
    $grade = htmlspecialchars(trim($_POST['learner_grade']));
    $date_of_birth = htmlspecialchars(trim($_POST['learner_dob']));
    $address = htmlspecialchars(trim($_POST['learner_home_address']));
    $preg_str = '/^[a-zA-Z]+( [a-zA-Z]+)*$/';
    $preg_num = "/^[0-9]{10}+$/";

    if(!preg_match($preg_str, $name))
    {
        $error = '<div class="alert alert-danger text-center" role="alert">Invalid name!</div>';
    }
    elseif(!preg_match($preg_str, $surname))
    {
        $error = '<div class="alert alert-danger text-center" role="alert">Invalid surname!</div>';
    }
    elseif(!preg_match($preg_num, $phone))
    {
        $error = '<div class="alert alert-danger text-center" role="alert">Invalid cellphone number!</div>';
    }
    else
    {
        try {
            // Start a transaction
            $db->beginTransaction();
    
            $learnerQuery = "SELECT * FROM learners WHERE `name` = ? AND surname = ?";
            $getLearner = $db->prepare($learnerQuery);
            $getLearner->execute([$name, $surname]);
            $res = $getLearner->fetchAll();
            
            $learner2Query = "SELECT * FROM learners WHERE contact_number = ?";
            $getLearner2 = $db->prepare($learner2Query);
            $getLearner2->execute([$phone]);
            $res2 = $getLearner2->fetchAll();
            
            if (count($res) > 0) {
                if (count($res) > 0) {
                    $error = '<div class="alert alert-danger text-center" role="alert">Learner has already been registered!</div>';
                }
                else
                if (count($res2) > 0) {
                    $error = '<div class="alert alert-danger text-center" role="alert">cellphone number has already been registered!</div>';
                }
            } else {
                // Insert into learner table
                $sql = "
                INSERT INTO learners (name, surname, contact_number, grade, date_of_birth, address, parent_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$name, $surname, $phone, $grade, $date_of_birth, $address, $parent_id]);
    
                // Get the last inserted learner_id
                $learner_id = $db->lastInsertId();
    
                // Fetch parent email
                $sql = "SELECT email, `name` FROM parents WHERE parent_id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$parent_id]);
                $parent = $stmt->fetch(PDO::FETCH_ASSOC);
                $parent_email = $parent['email'];
    
                // Commit the transaction
                $db->commit();
    
                try {
    
                    $to = $parent['email'];
                    $subject = 'Child Application Confirmation';
                    $message = "Hi {$parent['name']},\n\n";
                    $message .= "Your child has been successfully added.\n\n";
                    $message .= "Thank you.";
    
                    mail($to, $subject, $message);
    
                } catch (Exception $e) {
                    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
    
                // Redirect to the success page with the application number
                header("Location: success.php?application_no=" . urlencode($application_no));
                exit();
            }
    
        } catch (PDOException $e) {
            // Rollback the transaction on error
            $db->rollBack();
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
                    <!-- HTML Form for adding learners -->
                    <form method="POST" action="add_learner.php" class="container mt-4">
                        <div class="row">
                            <!-- Parent ID Dropdown -->
                            <div class="col-md-6 mb-3">
                                <label for="parent_id" class="form-label">Parent</label>
                                <select id="parent_id" name="parent_id" class="form-select" required>
                                    <option value="">Select Parent</option>
                                    <?php foreach ($parents as $parent): ?>
                                        <option value="<?php echo htmlspecialchars($parent['parent_id']); ?>">
                                            <?php echo htmlspecialchars($parent['name'] . ' ' . $parent['surname']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Bus ID Dropdown -->
                            <div class="col-md-6 mb-3">
                                <label for="bus_id" class="form-label">Bus Route</label>
                                <select id="bus_id" name="bus_id" class="form-select" required>
                                    <option value="">Select Route</option>
                                    <?php foreach ($busses as $bus): ?>
                                        <option value="<?php echo htmlspecialchars($bus['bus_id']); ?>">
                                            Bus <?= htmlspecialchars($bus['bus_number']); ?>
                                            <?= htmlspecialchars($bus['route_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Admin ID (hidden field) -->
                            <input type="hidden" name="admin_id"
                                value="<?php echo htmlspecialchars($_SESSION['admin_id']); ?>">

                            <!-- Child's Name -->
                            <div class="col-md-6 mb-3">
                                <label for="learner_name" class="form-label">Child's Name</label>
                                <input type="text" id="learner_name" name="learner_name" class="form-control"
                                    placeholder="Child's Name" required>
                            </div>

                            <!-- Surname -->
                            <div class="col-md-6 mb-3">
                                <label for="learner_surname" class="form-label">Surname</label>
                                <input type="text" id="learner_surname" name="learner_surname" class="form-control"
                                    placeholder="Surname" required>
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

                            <!-- Phone -->
                            <div class="col-md-6 mb-3">
                                <label for="learner_phone" class="form-label">Phone</label>
                                <input type="tel" id="learner_phone" name="learner_phone" maxlength="10"
                                    class="form-control" placeholder="Phone" required>
                            </div>

                            <!-- Grade -->
                            <div class="col-md-6 mb-3">
                                <label for="learner_grade" class="form-label">Grade</label>
                                <select id="learner_grade" name="learner_grade" class="form-select" required>
                                    <option value="">Select Grade</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                    <option value="11">11</option>
                                    <option value="12">12</option>
                                </select>
                            </div>
                            <?= $error ?>
                            <!-- Submit Button -->
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