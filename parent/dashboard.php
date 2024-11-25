<?php

// Start the session if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


// Check if the user is logged in
if (!isset($_SESSION['parent_id'])) {
    header("Location: index.php");
    exit();
}
?>

<?php require 'includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">


        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            <div class="chart-placeholder">
                <br>
                <div class="container">


                    <?php
                    require 'db.php'; // Database connection

                    // Fetch parent's data
                    $parent_id = $_SESSION['parent_id'];
                    try {
                        $stmt = $db->prepare("SELECT * FROM parents WHERE parent_id = ?");
                        $stmt->execute([$parent_id]);
                        $parent = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($parent) {
                            $parent_email = $parent['email'];
                            $parent_name = $parent['name'];
                        } else {
                            echo "Parent not found.";
                            exit;
                        }
                    } catch (PDOException $e) {
                        echo "Error fetching parent's data: " . $e->getMessage();
                        exit; // Stop execution if there's an error
                    }

                    // Fetch learners for the logged-in parent
                    try {
                        $stmt = $db->prepare("SELECT * FROM learners WHERE parent_id = ?");
                        $stmt->execute([$parent_id]);
                        $learners = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                        echo "Error fetching learners: " . $e->getMessage();
                        exit; // Stop execution if there's an error
                    }
                    ?>

                    <h3>Your Parent Portal</h3><a href="add_child.php">Add Child</a>

                    <?php
                    if (empty($learners)) {
                        echo "<p>No children found for this parent.</p>";
                    } else {
                        echo "<table class='table table-striped'>";
                        echo "<thead>
            <tr>
                <th>Learner ID</th>
                <th>Name</th>
                <th>Surname</th>
                <th>Phone</th>
                <th>Grade</th>
                <th>Age</th>
                <th>Actions</th>
            </tr>
          </thead>";
                        echo "<tbody>";
                        foreach ($learners as $learner) {
                            $age = date("Y") - substr($learner['date_of_birth'], 0, 4);
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($learner['learner_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($learner['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($learner['surname']) . "</td>";
                            echo "<td>" . htmlspecialchars($learner['contact_number']) . "</td>";
                            echo "<td>" . htmlspecialchars($learner['grade']) . "</td>";
                            echo "<td>" . htmlspecialchars($age) . "</td>";
                            echo "<td>
            <a href='view.php?id=" . $learner['learner_id'] . "'>View</a> | 
            <a href='edit.php?id=" . $learner['learner_id'] . "'>Edit</a> | 
            <a href='delete.php?id=" . $learner['learner_id'] . "' onclick=\"return confirmDelete('" . htmlspecialchars($learner['name']) . "')\">Delete</a>
            </td>";
                            echo "</tr>";
                        }
                        echo "</tbody>";
                        echo "</table>";
                    }
                    ?>

                    <script>
                        function confirmDelete(name) {
                            return confirm('Are you sure you want to delete ' + name + '?');
                        }
                    </script>

                </div>
            </div>
        </main>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
