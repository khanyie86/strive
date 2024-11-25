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

// Search functionality for waiting list
$approvedSearch = isset($_GET['approved_search']) ? $_GET['approved_search'] : '';

$approvedQuery = "SELECT *, bookings.booking_status AS status FROM bookings
INNER JOIN learners ON bookings.learner_id = learners.learner_id 
WHERE (bookings.learner_id LIKE ? || bookings.booking_id LIKE ? || learners.name LIKE ?) AND 
bookings.booking_status = ? AND DATE(bookings.application_date) = CURDATE()";
$stmt = $conn->prepare($approvedQuery);
$searchTerm = "%" . $approvedSearch . "%";
$status = "APPROVED";
$stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $status);
$stmt->execute();
$approvedResult = $stmt->get_result();

// Search functionality for daily transport
$transportSearch = isset($_GET['transport_search']) ? $_GET['transport_search'] : '';
$dailyTransportQuery = "
SELECT 
  b.bus_id, b.capacity, bk.application_date, bk.booking_status AS status,
  l.learner_id, l.name AS learner_name, l.surname AS learner_surname, l.grade,
  p.name, p.surname, p.contact_number
FROM 
  bus b
JOIN 
  bookings bk ON bk.bus_id = b.bus_id
JOIN 
  learners l ON bk.learner_id = l.learner_id
JOIN 
  parents p ON l.parent_id = p.parent_id
WHERE 
  (l.name LIKE ? OR l.surname LIKE ? OR p.contact_number LIKE ?)
  AND booking_status = ?";
$stmt = $conn->prepare($dailyTransportQuery);
$searchTerm = "%" . $transportSearch . "%";
$status = "APPROVED";
$stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $status);
$stmt->execute();
$dailyTransportResult = $stmt->get_result();

// Close connection
$conn->close();

?>
<title>Daily Approved MIS Report</title>
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
<?php require '../admin/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
        <div class="container">
    <h1>Daily MIS Report</h1>

    <h2>Learners on the Approved List</h2>
    <form method="GET" class="search-form">
      <input type="text" name="approved_search" placeholder="Search by id or name"
        value="<?= htmlspecialchars($approvedSearch); ?>" oninput="searchURL(this)">
      <button type="submit">Search</button>
    </form>
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
          $status = str_replace("_", " ", $row["status"]);
          echo "<tr>
                            <td>" . $row["booking_id"] . "</td>
                            <td>" . $row["learner_id"] . "</td>
                            <td>" . $row["name"] . " " . $row["surname"] . "</td>
                            <td>" . $row["grade"] . "</td>
                            <td>" . $row["parent_id"] . "</td>
                            <td>" . str_replace("'", '', $status) . "</td>
                        </tr>";
        }
      } else {
        echo "<tr><td colspan='6' class='no-data'>No learners on the approved list.</td></tr>";
      }
      ?>
    </table>

    <span id="approved_search_placeholder"></span>

    <h2>Learners Using Bus Transport</h2>
    <form method="GET" class="search-form">
      <input type="text" name="transport_search" placeholder="Search by id or name"
        value="<?php echo htmlspecialchars($transportSearch); ?>" oninput="searchURL(this)">
      <button type="submit">Search</button>
    </form>
    <table id="transport_search" style="display: block">
      <tr>
        <th>Bus ID</th>
        <th>Learner ID</th>
        <th>Learner Name</th>
        <th>Learner Surname</th>
        <th>Learner Grade</th>
        <th>Parent Name</th>
        <th>Parent Surname</th>
        <th>Parent Cellno</th>
        <th>Spacestatus</th>
        <th>Time</th>
      </tr>
      <?php
      if ($dailyTransportResult->num_rows > 0) {
        while ($row = $dailyTransportResult->fetch_assoc()) {
          echo "<tr>
                            <td>" . $row["bus_id"] . "</td>
                            <td>" . $row["learner_id"] . "</td>
                            <td>" . $row["learner_name"] . "</td>
                            <td>" . $row["learner_surname"] . "</td>
                            <td>" . $row["grade"] . "</td>
                            <td>" . $row["name"] . "</td>
                            <td>" . $row["surname"] . "</td>
                            <td>" . $row["contact_number"] . "</td>
                            <td>" . str_replace("'", "", $row["capacity"]) . "</td>
                            <td>" . $row["application_date"] . "</td>
                        </tr>";
        }
      } else {
        echo "<tr><td colspan='10' class='no-data'>No learners are using bus transport today.</td></tr>";
      }
      ?>
    </table>

    <span id="transport_search_placeholder"></span>

  </div>
        </main>
    </div>
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

<?php require '../admin/includes/footer.php'; ?>
