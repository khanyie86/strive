<?php
session_start();

// Database configuration
require 'db.php';

// Initialize error message
$error_msg = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate input
    if (empty($email) || empty($password)) {
        $error_msg = "Email or Password cannot be empty";
    } else {
        // Prepare and bind
        $stmt = $db->prepare("SELECT * FROM parents WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        if($row)
        {
            // Check if email exists
            if ($row['email'] === $email) {

                if (password_verify($password, $row['password'])) {
                    // Password is correct, start session
                    $_SESSION['parent_id'] = $row['parent_id'];
                    $_SESSION['firstname'] = $row['name'];
                    $_SESSION['lastname'] = $row['surname'];
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error_msg = "Invalid Password";
                }
            } else {
                $error_msg = "No account found with that email";
            }
        } else {
            $error_msg = "No account found with that email";
        }        

    }
}

?>

<!doctype html>
<html lang="en">
<head>
    <!-- Meta tags for character set and viewport settings -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Title of the page -->
    <title>Parent Login</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Custom CSS for centering the div -->
    <style>
        .centered-div {
            width: 50%; /* Adjust the width as needed */
            margin: auto; /* This centers the div horizontally */
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="centered-div">

            <!-- Logo -->
            <div class="text-center my-4">
                <img src="image/logo.png" alt="Logo" class="img-fluid" style="max-width: 200px;">
                <h3>OBR Parent Login</h3>
            </div>
            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error_msg); ?>
                </div>
            <?php endif; ?>
            <!-- Login form -->
            <form action="" method="post">
                <div class="form-group mb-3">
                    <label for="parent_email">Parent Email</label>
                    <input type="email" placeholder="Enter Email" name="email" class="form-control" id="parent_email">
                </div>
                <div class="form-group mb-3">
                    <label for="parent_password">Parent Password</label>
                    <input type="password" placeholder="Enter Password" name="password" class="form-control" id="parent_password">
                </div>
                <div class="form-btn">
                    <input type="submit" value="Login" name="login" class="btn btn-primary">
                </div>
            </form>
            <!-- Registration link -->
            <div>
                <p>Not registered yet? <a href="registration.php">Register Here</a></p>
            </div>

        </div>
    </div>
</div>

<!-- Bootstrap JavaScript bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
