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

// Search parameters
$weekNumber = isset($_GET['week_number']) ? $_GET['week_number'] : '';
$session = isset($_GET['session']) ? $_GET['session'] : '';

// SQL query to fetch the weekly transport data
$weeklyTransportQuery = "
 SELECT WEEK(b.application_date, 1) AS week_number, 
 YEAR(b.application_date) AS year, 
 COUNT(DISTINCT l.learner_id) AS total_learners, 
CASE 
    WHEN TIME(b.application_date) < '12:00:00' THEN 'Morning'
    ELSE 'Afternoon'
END AS bus_session
FROM learners l
JOIN bookings b ON l.learner_id = b.learner_id
WHERE YEARWEEK(b.application_date, 1) = YEARWEEK(CURDATE(), 1)
";

// Append search criteria to the query
if ($weekNumber) {
    $weeklyTransportQuery .= " AND WEEK(b.application_date, 1) = '$weekNumber'";
}
if ($session) {
    $weeklyTransportQuery .= " AND (CASE WHEN TIME(b.application_date) < '12:00:00' THEN 'Morning' ELSE 'Afternoon' END) = '$session'";
}

$weeklyTransportQuery .= " GROUP BY week_number, year, bus_session";

$weeklyTransportResult = $conn->query($weeklyTransportQuery);
?>

<title>Weekly MIS Report</title>
<?php require '../admin/includes/header.php'; ?>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
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
        .search-form {
            margin-bottom: 20px;
        }
        .search-form input, .search-form select {
            padding: 8px;
            width: 200px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .search-form button {
            padding: 8px 12px;
            border: none;
            background-color: #333;
            color: #fff;
            border-radius: 4px;
            cursor: pointer;
        }
        .search-form button:hover {
            background-color: #555;
        }
    </style>

<div class="container-fluid">
    <div class="row">

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
        <div class="container">
        <h1>Weekly MIS Report</h1>

        <h2>Total Learners Using Bus Transport This Week</h2>
        <form method="GET" class="search-form">
            <input type="text" name="week_number" placeholder="Enter Week Number" value="<?php echo htmlspecialchars($weekNumber); ?>">
            <select name="session">
                <option value="">Select Session</option>
                <option value="Morning" <?php if ($session == 'Morning') echo 'selected'; ?>>Morning</option>
                <option value="Afternoon" <?php if ($session == 'Afternoon') echo 'selected'; ?>>Afternoon</option>
            </select>
            <button type="submit">Search</button>
        </form>
        <table>
            <tr>
                <th>Week Number</th>
                <th>Year</th>
                <th>Total Learners</th>
                <th>Bus Session</th>
            </tr>
            <?php
            if ($weeklyTransportResult->num_rows > 0) {
                while($row = $weeklyTransportResult->fetch_assoc()) {
                    echo "<tr>
                            <td>" . $row["week_number"] . "</td>
                            <td>" . $row["year"] . "</td>
                            <td>" . $row["total_learners"] . "</td>
                            <td>" . $row["bus_session"] . "</td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='4' class='no-data'>No data available for this week.</td></tr>";
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
