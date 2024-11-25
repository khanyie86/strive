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
    $transportSearch = isset($_GET['transport_search']) ? $_GET['transport_search'] : '';
    $dailyTransportQuery = "
    SELECT 
      b.bus_id, b.capacity, bk.application_date,
      l.learner_id, l.name AS learner_name, l.surname AS learner_surname, l.grade,
      p.name AS parent_name, p.surname AS parent_surname, p.contact_number, p.email,
      bk.booking_id
    FROM 
      bus AS b
    JOIN 
      bookings AS bk ON bk.bus_id = b.bus_id
    JOIN 
      learners AS l ON bk.learner_id = l.learner_id
    JOIN 
      parents AS p ON l.parent_id = p.parent_id
    WHERE 
      (l.name LIKE ? OR l.surname LIKE ? OR p.name LIKE ? OR p.surname LIKE ?)
      AND bk.booking_status != ?
    ";
    $stmt = $conn->prepare($dailyTransportQuery);
    $searchTerm = "%" . $transportSearch . "%";
    $status = "CANCELLED";
    $stmt->bind_param("sssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $status);
    $stmt->execute();
    $dailyTransportResult = $stmt->get_result();
?>
<?php endif ?>

<table id="waiting_list_search" style="disply: block">
                        <tr>
                            <th>Bus ID</th>
                            <th>Learner</th>
                            <th>Grade</th>
                            <th>Parent</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Capacity</th>
                            <th>Date</th>
                        </tr>
    <?php

    if ($dailyTransportResult->num_rows > 0) {
        while ($row = $dailyTransportResult->fetch_assoc()) {
            echo "<tr>
                    <td>" . $row["bus_id"] . "</td>
                    <td>" . $row["learner_name"] . " " . $row["learner_surname"] . "</td>
                    <td>" . $row["grade"] . "</td>
                    <td>" . $row["parent_name"] . " " . $row["parent_surname"] . "</td>
                    <td>" . $row["contact_number"] . "</td>
                    <td>" . $row["email"] . "</td>
                    <td>" . $row["capacity"] . "</td>
                    <td>" . $row["application_date"] . "</td>
                </tr>";
        }
    } else {
        echo "<tr><td colspan='6' class='no-data'>No learners on the waiting list.</td></tr>";
    }
    ?>
</table>