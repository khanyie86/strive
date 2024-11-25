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
?>

<?php require 'includes/header.php'; ?>

<?php

require 'db.php';

// Fetch users
$sql = "SELECT parents.*, COUNT(learners.learner_id) AS TOTAL_CHILDREN FROM parents
LEFT JOIN learners ON learners.parent_id = parents.parent_id
GROUP BY parents.parent_id";
$result = $db->prepare($sql);
$result->execute();

?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOMt23cez/3paNdF+Z4pb9fq6hLQ9D3T+GOZ4pR" crossorigin="anonymous">

<div class="container-fluid">
    <div class="row">
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            <div class="chart-placeholder">
                <div class="container">
                    <div class="container mt-5">
                        <br>
                        <br>
                        <h2>Parents</h2>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Parent ID</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Total Children</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->rowCount() > 0): ?>
                                    <?php foreach($result->fetchALl() as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['parent_id']); ?></td>
                                            <td><?= htmlspecialchars($row['name']); ?></td>
                                            <td><?= htmlspecialchars($row['surname']); ?></td>
                                            <td><?= htmlspecialchars($row['email']); ?></td>
                                            <td><?= htmlspecialchars($row['contact_number']); ?></td>
                                            <td><?= htmlspecialchars($row['TOTAL_CHILDREN']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8">No users found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
                        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
                        crossorigin="anonymous"></script>
                    <script>
                        function updateStatus(userId, status) {
                            // Make an AJAX call to update user status
                            fetch('update_status.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({ parent_id: userId, status: status })
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        alert('Status updated successfully');
                                    } else {
                                        alert('Failed to update status');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                });
                        }
                    </script>
                    </body>

                    </html>




                </div>
            </div>
        </main>
    </div>
</div>
<?php require 'includes/footer.php'; ?>