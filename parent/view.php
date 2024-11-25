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
                    require 'db.php';
                    try {
                        // Fetch learners
                        $stmt = $db->prepare("
                            SELECT * FROM `learners` WHERE learner_id = ?
                        ");
                        $stmt->execute([$_GET['id']]);
                        $learners = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                        echo "Error fetching learners: " . $e->getMessage();
                        exit; // Stop execution if there's an error
                    }

                    if (empty($learners)) {
                        echo "<p>No learners found for this parent.</p>";
                    } else {
                        echo "<table class='table table-striped'>";
                        echo "<thead><tr><th>Name</th><th>Surname</th><th>Cellphone No.</th><th>Grade.</th><th>Age.</th><th>Address.</th></tr></thead>";
                        echo "<tbody>";
                        foreach ($learners as $learner) {
                            $age = date("Y") - substr($learner['date_of_birth'], 0, 4);

                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($learner['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($learner['surname']) . "</td>";
                            echo "<td>" . htmlspecialchars($learner['contact_number']) . "</td>";
                            echo "<td>" . htmlspecialchars($learner['grade']) . "</td>";
                            echo "<td>" . htmlspecialchars($age) . "</td>";
                            echo "<td>" . htmlspecialchars($learner['address']) . "</td>";
                            echo "</tr>";
                        }
                        echo "</tbody></table>";
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
