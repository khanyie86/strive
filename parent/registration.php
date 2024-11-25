<?php
session_start();

// Database configuration
$servername = "localhost";
$username = "root"; // Change to your database username
$password = ""; // Change to your database password
$dbname = "strivehighsecondaryschool";

// Enable MySQLi exceptions
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Create connection
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
} catch (mysqli_sql_exception $e) {
    error_log("Connection failed: " . $e->getMessage()); // Log the error
    die("Sorry, we are experiencing technical difficulties. Please try again later."); // User-friendly message
}

// Initialize error message variables
$error_message = "";
$email_error_message = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $name = htmlspecialchars(trim($_POST['firstname']));
    $surname = htmlspecialchars(trim($_POST['lastname']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone']));
    $password = $_POST['password'];
    $confirm = $_POST['password_confirmation'];

    $preg_str = '/^[a-zA-Z]+( [a-zA-Z]+)*$/';
    $preg_num = "/^[0-9]{10}+$/";

    if (!preg_match($preg_str, $name)) {
        $error_message = 'Invalid name!';
    } elseif (!preg_match($preg_str, $surname)) {
        $error_message = 'Invalid surname!';
    } elseif (!preg_match($preg_num, $phone)) {
        $error_message = 'Invalid cellphone number!';
    } 
    elseif($password !== $confirm)
    {
        $error_message = 'Passwords don\'t match!';   
    }
    else {

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Invalid email format.";
        } else {

            $parent = $conn->prepare("SELECT email FROM parents WHERE email = ?");
            $parent->execute([$email]);

            $row = $parent->fetch();
            
            if(!$row)
            {
                // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Prepare and bind
            try {
                $stmt = $conn->prepare("INSERT INTO parents (`name`, surname, contact_number, email, `password`) VALUES (?, ?, ?, ?,?)");
                if (!$stmt) {
                    throw new mysqli_sql_exception("Prepare statement failed: " . $conn->error);
                }
                
                $stmt->bind_param("sssss", $name, $surname, $phone, $email, $hashed_password);

                if ($stmt->execute()) {

                    try {

                        // Content
                        $from = 'hello@' . $_SERVER['SERVER_NAME'];
                        $to = $email;
                        $subject = 'Registration Confirmation';
                        $message = "Hi {$name},\n\n";
                        $message .= 'Thank you for registering with the Online Bus Registration System. Your registration is successful!';

                        if (mail($to, $subject, $message)) {
                            $success_message = 'Registration successful and email sent';
                        } else {
                            $email_error_message = 'Registration successful but email could not be sent.';
                        }
                        $name = '';
                        $surname =  '';
                        $email = '';
                        $phone = '';
                    } catch (Exception $e) {
                        echo 'Registration successful but email could not be sent.';
                    }
                } else {
                    throw new mysqli_sql_exception("Execute statement failed: " . $stmt->error); // Throw an exception if execution fails
                }
                $stmt->close();
            } catch (mysqli_sql_exception $e) {
                error_log("Database error: " . $e->getMessage()); // Log the error
                if ($e->getCode() == 1062) { // Error code for duplicate entry
                    $error_message = "The email address is already registered.";
                } else {
                    $error_message = "An error occurred during registration. Please try again.";
                }
            }
            }
            else
            {
                $error_message = "User with the same email already exists!";
            }
        }
    }
}
$conn->close();
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Parent Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .centered-div {
            width: 50%;
            margin: auto;
        }

        .error-message,
        .email-error-message {
            color: red;
            font-weight: bold;
        }

        .success-message {
            color: green;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="centered-div">
                <div class="text-center my-4">
                    <img src="image/logo.png" alt="Logo" class="img-fluid" style="max-width: 200px;">
                    <h3>OBR Parent Registration</h3>
                </div>

                <?php if (!empty($success_message)): ?>
                    <div class="success-message">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                    <div class="error-message">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($email_error_message)): ?>
                    <div class="email-error-message">
                        <?php echo htmlspecialchars($email_error_message); ?>
                    </div>
                <?php endif; ?>

                <form action="registration.php" method="post">
                    <div class="row">
                        <div class="col">
                            <div class="form-group mb-3">
                                <label for="firstname">First Name</label>
                                <input type="text" placeholder="Enter First Name" name="firstname" class="form-control"
                                    id="firstname" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group mb-3">
                                <label for="lastname">Last Name</label>
                                <input type="text" placeholder="Enter Last Name" name="lastname" class="form-control"
                                    id="lastname" value="<?php echo htmlspecialchars($surname ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group mb-3">
                                <label for="email">Email</label>
                                <input type="email" placeholder="Enter Email" name="email" class="form-control"
                                    id="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group mb-3">
                                <label for="phone">Phone</label>
                                <input type="text" placeholder="Enter Phone" name="phone" maxlength="10"
                                    class="form-control" id="phone"
                                    value="<?php echo htmlspecialchars($phone ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group mb-3">
                                <label for="password">Password</label>
                                <input type="password" placeholder="Enter Password" name="password" class="form-control"
                                    id="password" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group mb-3">
                                <label for="password_confirmation">Confirm Password</label>
                                <input type="password" placeholder="Confirm Password" name="password_confirmation" class="form-control"
                                    id="password_confirmation" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-btn">
                        <input type="submit" value="Register" name="register" class="btn btn-primary">
                    </div>
                </form>
                <div>
                    <p>Already registered? <a href="index.php">Login Here</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>