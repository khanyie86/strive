<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bus_reg_system";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to fetch the data
$TransportQuery = "
    SELECT learner_id, learner_name, learner_surname,
           FLOOR(DATEDIFF(CURDATE(), learner_dob) / 365.25) AS age
    FROM learner;
";
$TransportResult = $conn->query($TransportQuery);

// Check if query was successful
if (!$TransportResult) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Other MIS Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1, h2 {
            color: #333;
        }
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Other MIS Report</h1>

        <h2>Age of Each Learner Based on Date of Birth</h2>
        <table>
            <tr>
                <th>Learner ID</th>
                <th>Learner Name</th>
                <th>Learner Surname</th>
                <th>Age</th>
            </tr>
            <?php
            if ($TransportResult->num_rows > 0) {
                while ($row = $TransportResult->fetch_assoc()) {
                    echo "<tr>
                            <td>" . $row["learner_id"] . "</td>
                            <td>" . $row["learner_name"] . "</td>
                            <td>" . $row["learner_surname"] . "</td>
                            <td>" . $row["age"] . "</td>
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
</body>
</html>
