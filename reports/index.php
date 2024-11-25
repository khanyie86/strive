<?php

// Start the session if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/index.php");
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

// Function to check bus capacity and place learners on the waiting list if full
function assignLearnerToBusOrWaitingList($learnerId, $busId, $conn)
{
    // Get bus capacity
    $capacityQuery = "SELECT capacity FROM bus WHERE bus_id = ?";
    $stmt = $conn->prepare($capacityQuery);
    $stmt->bind_param("i", $busId);
    $stmt->execute();
    $capacityResult = $stmt->get_result();
    $busCapacity = $capacityResult->fetch_assoc()["capacity"];
    $stmt->close();

    // Count the number of learners already assigned to the bus
    $countQuery = "SELECT COUNT(*) as learner_count FROM bookings WHERE bus_id = ?";
    $stmt = $conn->prepare($countQuery);
    $stmt->bind_param("i", $busId);
    $stmt->execute();
    $countResult = $stmt->get_result();
    $currentCount = $countResult->fetch_assoc()["learner_count"];
    $stmt->close();

    if ($currentCount < $busCapacity) {
        // Assign learner to the bus
        $assignQuery = "UPDATE bookings SET bus_id = ? WHERE learner_id = ?";
        $stmt = $conn->prepare($assignQuery);
        $stmt->bind_param("ii", $busId, $learnerId);
        $stmt->execute();
        $stmt->close();
        return "Learner assigned to bus " . $busId;
    } else {
        // Bus is full, add learner to waiting list
        $waitingListQuery = "INSERT INTO waiting_list (learner_id, waiting_list_group) VALUES (?, ?)";
        $stmt = $conn->prepare($waitingListQuery);
        $waitingListGroup = 1; // or other logic for group assignment
        $stmt->bind_param("ii", $learnerId, $waitingListGroup);
        $stmt->execute();
        $stmt->close();
        return "Bus full. Learner added to waiting list.";
    }
}

// Search functionality for waiting list
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

// Search functionality for daily transport
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
    (l.name LIKE ? OR l.surname LIKE ? OR p.contact_number LIKE ?)
  AND bk.booking_status != ?
";
$stmt = $conn->prepare($dailyTransportQuery);
$searchTerm = "%" . $transportSearch . "%";
$status = "CANCELLED";
$stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $status);
$stmt->execute();
$dailyTransportResult = $stmt->get_result();

// Close connection
$conn->close();

?>
<title>Daily MIS Report</title>
<?php require '../admin/includes/header.php'; ?>
<style>
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    th,
    td {
        padding: 12px;
        text-align: left;
        border: 1px solid #ddd;
    }

    th {
        background-color: #f2f2f2;
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

    .search-form input {
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
                <h1>Daily MIS Report</h1>

                <h2>Learners on the Waiting List</h2>
                <form method="GET" class="search-form">
                    <input type="text" name="waiting_list_search" placeholder="Search by id or name"
                        value="<?= htmlspecialchars($waitingListSearch); ?>" oninput="searchURL(this)">
                    <button type="submit">Search</button>
                </form>
                <table id="waiting_list_search" style="disply: block">
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

                <span id="waiting_list_search_placeholder"></span>

                <h2>Learners Using Bus Transport</h2>
                <form method="GET" class="search-form">
                    <input type="text" name="transport_search" placeholder="Search by id or name"
                        value="<?php echo htmlspecialchars($transportSearch); ?>" oninput="searchURL(this)">
                    <button type="submit">Search</button>
                </form>
                <table id="transport_search">
                    <thead>
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
                    </thead>
                    <tbody>
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
                            <td>" . str_replace("'", "", $row["capacity"]) . "</td>
                            <td>" . $row["application_date"] . "</td>
                        </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='10' class='no-data'>No learners are using bus transport today.</td></tr>";
                    }
                    ?></tbody>
                </table>

                <span id="transport_search_placeholder"></span>

            </div>

            <script>

                function searchURL(event) {
                    var input_name = event.name;
                    var input_value = event.value;
                    document.getElementById(input_name).style.display = 'none';

                    var search = new URLSearchParams(window.location.search);

                    if (search.has(input_name)) {
                        search.set(input_name, input_value);
                        // window[input_name](input_value, input_name)
                        loadData(input_value, input_name)
                    } else {
                        search.append(input_name, input_value);
                    }

                    var updated_url = window.location.pathname + '?' + search.toString();
                    window.history.replaceState(null, '', updated_url);

                }

                function loadData(value, search_name) {
                    fetch('/reports/search/' + search_name + '.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ search: value, name: search_name })
                    })
                        .then(response => response.text())
                        .then(data => {
                            document.getElementById(search_name + '_placeholder').innerHTML = data
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                }
            </script>

        </main>
    </div>
</div>

<?php require '../admin/includes/footer.php'; ?>