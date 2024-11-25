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

$success_msg = '';
$error_msg = '';

$learners_data = $db->prepare("SELECT * FROM learners WHERE parent_id = ?");
$learners_data->execute([$_SESSION['parent_id']]);
$learners = $learners_data->fetchAll();

$routes_data = $db->prepare("SELECT * FROM routes");
$routes_data->execute();
$routes = $routes_data->fetchAll();

$morning_pickup = $db->prepare("SELECT * FROM morning_pickups
    INNER JOIN bus ON bus.bus_id = morning_pickups.bus_id");
$morning_pickup->execute();
$pickups = $morning_pickup->fetchAll();

$afternoon_dropoff = $db->prepare("SELECT * FROM afternoon_dropoffs
    INNER JOIN bus ON bus.bus_id = afternoon_dropoffs.bus_id");
$afternoon_dropoff->execute();
$dropoffs = $afternoon_dropoff->fetchAll();

$bus_list = $db->prepare("SELECT * FROM bus");
$bus_list->execute();
$buses = $bus_list->fetchAll();

if (isset($_POST['register'])) {

    $learner = $_POST['learner_id'];
    $bus = $_POST['bus_id'];
    $pickup = $_POST['pickup_id'];
    $dropoff = $_POST['dropoff_id'];
    $created_at = date('Y-m-d H:i:s');
    $date = date('Y-m-d');
    $parent = $_SESSION['parent_id'];

    if (empty($learner)) {
        $error_msg = '<div class="text-danger text-start my-2">Please select a learner.</div>';
    } else
        if (empty($bus)) {
            $error_msg = '<div class="text-danger text-start my-2">Please select a bus.</div>';
        } elseif (empty($pickup)) {
            $error_msg = '<div class="text-danger text-start my-2">Please select a pickup point.</div>';
        } elseif (empty($dropoff)) {
            $error_msg = '<div class="text-danger text-start my-2">Please select a dropoff point.</div>';
        } else {
            $sql = "INSERT INTO bookings (booking_status, learner_id, bus_id, application_date, pickup_id, dropoff_id, created_at) VALUES (?,?,?,?,?,?,?)";
            $stmt = $db->prepare($sql);

            if (registration($db, $learner, $bus) === 0) {

                if (availability($db, $bus)) {

                    if ($stmt->execute(["APPROVED", $learner, $bus, $date, $pickup, $dropoff, $created_at])) {
                        $success_msg = '<div class="text-success text-start my-2">Application is Successful</div>';
                    }

                    if (!sendmail($learner, $db)) {
                        $error_msg = '<div class="text-danger text-start my-2">Application is failed to send email.</div>';
                    }
                } else {
                    if (sendmail($learner, $db)) {
                        $stmt->execute(["WAITING_LIST", $learner, $bus, $date, $pickup, $dropoff, $created_at]);
                        $error_msg = '<div class="text-danger text-start my-2">Bus is not available, and learner has been moved to waiting list.</div>';
                    } else {
                        $error_msg = '<div class="text-danger text-start my-2">Application is failed to send email.</div>';
                    }
                }

            } else {

                $error_msg = '<div class="text-danger text-start my-2">Learner has already been booked.</div>';
            }

        }
}

function sendmail($learner, $db)
{
    $sql = "SELECT 
    learners.name AS learner_name, 
    learners.surname AS learner_surname, 
    parents.name AS parent_name, 
    email
    FROM learners
    INNER JOIN parents ON learners.parent_id = parents.parent_id
    WHERE learners.learner_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$learner]);

    $row = $stmt->fetch();

    $to = $row['email'];
    $subject = 'New Application';
    $message = "Hi {$row['parent_name']},\n\n";
    $message .= "Application for {$row['learner_name']} {$row['learner_surname']} was successful";

    if (mail($to, $subject, $message)) {
        return true;
    }

    return false;
}

function availability($db, $bus)
{
    $sql = "SELECT bus.capacity, COUNT(bookings.bus_id) AS BOOKINGS 
    FROM bus
    LEFT JOIN bookings ON bus.bus_id = bookings.bus_id
    WHERE bus.bus_id = ? || bookings.booking_status != ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$bus, "CANCELLED"]);

    $row = $stmt->fetch();

    if ($row['BOOKINGS'] >= $row['capacity']) {
        return false;
    }

    return true;
}

function registration($db, $learner, $bus)
{
    $sql = "SELECT * FROM bookings WHERE learner_id = ? AND bus_id = ? AND booking_status != ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$learner, $bus, "CANCELLED"]);

    return count($stmt->fetchAll());
}
?>

<div class="container-fluid">
    <div class="row">
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            <div class="chart-placeholder">
                <br>
                <div class="container">
                    <!-- HTML Form for booking learners -->
                    <?php if (!empty($error_msg)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $error_msg; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success_msg)): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo $success_msg; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="container mt-4">
                        <div class="row">
                            <input type="hidden" name="parent_id"
                                value="<?php echo htmlspecialchars($_SESSION['parent_id']); ?>">
                            <input type="hidden" name="bus_id" value="<?php echo htmlspecialchars($bus_id ?? ''); ?>">
                            <input type="hidden" name="admin_id"
                                value="<?php echo htmlspecialchars($admin_id ?? ''); ?>">

                            <div class="col-md-6 mb-3">
                                <label for="learner" class="form-label">Children</label>
                                <select id="learner_id" name="learner_id" class="form-control">
                                    <option value="">Select a child</option>
                                    <?php foreach ($learners as $learner): ?>
                                        <option value="<?= $learner['learner_id'] ?>">
                                            <?= $learner['name'] ?>     <?= $learner['surname'] ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="bus" class="form-label">Bus</label>
                                <select id="bus_id" name="bus_id" class="form-control">
                                    <option value="">Select a Bus</option>
                                    <?php foreach ($buses as $bus): ?>
                                        <option value="<?= $bus['bus_id'] ?>">
                                            Bus <?= $bus['bus_number'] ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="pickups" class="form-label">Pickups</label>
                                <select id="pickup_id" name="pickup_id" class="form-control">
                                    <option value="">Select Pickup Point</option>
                                    <?php foreach ($pickups as $pickup): ?>
                                        <option value="<?= $pickup['pickup_id'] ?>">
                                            Bus <?= $pickup['bus_number'] ?>
                                            <?= $pickup['pickup_name'] ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="dropoffs" class="form-label">Dropoffs</label>
                                <select id="dropoff_id" name="dropoff_id" class="form-control">
                                    <option value="">Select Dropoff Point</option>
                                    <?php foreach ($dropoffs as $dropoff): ?>
                                        <option value="<?= $dropoff['dropoff_id'] ?>">
                                            Bus <?= $dropoff['bus_number'] ?>
                                            <?= $dropoff['dropoff_name'] ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="col-12">
                                <button type="submit" name="register" class="btn btn-primary">Register</button>
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