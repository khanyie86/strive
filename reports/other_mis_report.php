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

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "strivehighsecondaryschool";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to fetch the data
$TransportQuery = "SELECT * FROM learners";
$TransportResult = $conn->query($TransportQuery);

// Check if query was successful
if (!$TransportResult) {
    die("Query failed: " . $conn->error);
}
?>

<title>Other MIS Report</title>
<style>
    h1,
    h2 {
        color: #333;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    table,
    th,
    td {
        border: 1px solid #ddd;
    }

    th,
    td {
        padding: 12px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
        color: #333;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .no-data {
        text-align: center;
        font-style: italic;
        color: #999;
    }
</style>
<?php require '../admin/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            <div class="container">
                <h1>Other MIS Report</h1>

                <h2>Grade of Each Learner</h2>
                <table>
                    <tr>
                        <th>Learner ID</th>
                        <th>Learner Name</th>
                        <th>Learner Surname</th>
                        <th>Grade</th>
                        <th>Date of Birth</th>
                        <th>Age</th>
                        <th>Home Address</th>
                    </tr>
                    <?php
                    if ($TransportResult->num_rows > 0) {
                        while ($row = $TransportResult->fetch_assoc()) {
                            echo "<tr>
                            <td>" . $row["learner_id"] . "</td>
                            <td>" . $row["name"] . "</td>
                            <td>" . $row["surname"] . "</td>
                            <td>" . $row["grade"] . "</td>
                            <td>" . date("d M Y", strtotime($row["date_of_birth"])) . "</td>
                            <td>" . date("Y") - substr($row["date_of_birth"], 0, 4) . "</td>
                            <td>" . $row["address"] . "</td>
                        </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='no-data'>No data available.</td></tr>";
                    }
                    ?>
                </table>
            </div>

            <?php
            // Close connection
            $conn->close();
            ?>
        </main>
    </div>
</div>

<?php require '../admin/includes/footer.php'; ?>