<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <title>Admin Dashboard</title>
    <style>
        body {
            padding-top: 0rem; /* Adjust based on navbar height */
            padding-bottom: 3.5rem; /* Adjust based on footer height */
        }

        .sidebar {
            position: fixed;
            top: 2rem; /* Adjust based on your layout */
            bottom: 0;
            left: 0;
            z-index: 100; /* Ensures it's above other content */
            padding: 48px 0; /* Adjust padding as needed */
            background-color: #f8f9fa; /* Background color of sidebar */
        }

        .main-content {
            margin-left: 220px; /* Adjust based on sidebar width */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        th, td {
            font-size: 14px;
        }
        .actions {
            white-space: nowrap;
        }
        .actions a {
            margin-right: 5px;
        }

        .navbar {
            position: relative;
            z-index: 200; /* Higher z-index to ensure it's above other content */
        }
    </style>
</head>
<body>
<nav class="navbar fixed-top navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand ms-3" href="#">
            <img src="../admin/image/logo.png" alt="OBR System Logo" class="logo-img" style="max-height: 40px;">
            <b>OBR System</b>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Logged in as <?php echo htmlspecialchars($_SESSION['surname']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/admin/logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-none d-md-block bg-light sidebar">
            <div class="position-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="/admin/add_learner.php"><i class="fas fa-user"></i> Register Learner</a>
                    </li> -->
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/apply_bus.php"><i class="fas fa-cog"></i> Apply for the Bus</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/student_app.php"><i class="fas fa-cog"></i> Applications</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/user.php"><i class="fa fa-users" aria-hidden="true"></i> Parents</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/mis_report.php"><i class="fa fa-bar-chart" aria-hidden="true"></i> MIS Reports</a>
                    </li>
                </ul>
            </div>
        </nav>