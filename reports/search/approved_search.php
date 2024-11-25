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

    $approvedSearch = $data['search'];
    $approvedQuery = "SELECT *, bookings.booking_status AS status FROM bookings
INNER JOIN learners ON bookings.learner_id = learners.learner_id 
WHERE (bookings.learner_id LIKE ? || bookings.booking_id LIKE ? || learners.name LIKE ?) AND bookings.booking_status = ?";
    $stmt = $conn->prepare($approvedQuery);
    $searchTerm = "%" . $approvedSearch . "%";
    $status = "APPROVED";
    $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $status);
    $stmt->execute();
    $approvedResult = $stmt->get_result();

    ?>

    <table id="approved_search" style="disply: block">
        <tr>
            <th>App No</th>
            <th>Learner ID</th>
            <th>Learner Name</th>
            <th>Learner Phone</th>
            <th>Parent ID</th>
            <th>Status</th>
        </tr>
        <?php
        if ($approvedResult->num_rows > 0) {
            while ($row = $approvedResult->fetch_assoc()) {
                $status = $row["booking_status"];
                echo "<tr>
                            <td>" . $row["booking_id"] . "</td>
                            <td>" . $row["learner_id"] . "</td>
                            <td>" . $row["name"] . "</td>
                            <td>" . $row["contact_number"] . "</td>
                            <td>" . $row["parent_id"] . "</td>
                            <td>" . str_replace("'", '', $status) . "</td>
                        </tr>";
            }
        } else {
            echo "<tr><td colspan='6' class='no-data'>No learners on the approved list.</td></tr>";
        }
        ?>
    </table>

<?php endif ?>