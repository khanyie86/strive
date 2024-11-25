<?php
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

$data = json_decode(file_get_contents('php://input'), true);
?>

<?php if (isset($data['name'])): ?>
    <?php

$waitingListSearch = isset($_GET['waiting_list_search']) ? $_GET['waiting_list_search'] : '';
$waitingListQuery = "SELECT * FROM bookings 
INNER JOIN learners ON learners.learner_id = bookings.learner_id
WHERE (bookings.learner_id LIKE ? OR learners.name LIKE ? OR learners.surname LIKE ?) 
AND bookings.booking_status = ?";
$stmt = $conn->prepare($waitingListQuery);
$searchTerm = "%" . $waitingListSearch . "%";
$pending = "WAITING_LIST";
$stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $pending);
$stmt->execute();
$waitingListResult = $stmt->get_result();

    ?>

    <table>
        <tr>
            <th>App No</th>
            <th>Learner ID</th>
            <th>Learner Name</th>
            <th>Learner Surname</th>
            <th>Learner Phone</th>
            <th>Status</th>
        </tr>
        <?php

if ($waitingListResult->num_rows > 0) {
    while ($row = $waitingListResult->fetch_assoc()) {
        echo "<tr>
            <td>" . $row["booking_id"] . "</td>
            <td>" . $row["learner_id"] . "</td>
            <td>" . $row["name"] . "</td>
            <td>" . $row["surname"] . "</td>
            <td>" . $row["contact_number"] . "</td>
            <td>PENDING</td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='6' class='no-data'>No learners on the waiting list.</td></tr>";
}
        ?>
    </table>

<?php endif ?>