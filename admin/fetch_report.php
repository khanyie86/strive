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

require 'db.php';

// Determine which report data to fetch
$reportType = isset($_GET['report']) ? $_GET['report'] : '';

if ($reportType === 'report1') {
    try {
        // Fetch data for Report 1: Age of each learner
        $stmt = $db->query("SELECT learner_id, learner_name, learner_surname,
                                   FLOOR(DATEDIFF(CURDATE(), learner_dob) / 365.25) AS age
                            FROM learner");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($data);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} elseif ($reportType === 'report2') {
    try {
        // Fetch data for Report 2: Count of learners by age
        $stmt = $db->query("SELECT
                               FLOOR(DATEDIFF(CURDATE(), learner_dob) / 365.25) AS age,
                               COUNT(*) AS count
                            FROM learner
                            GROUP BY age
                            ORDER BY age");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($data);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} elseif ($reportType === 'report3') {
    try {
        // Fetch data for Report 3: Count of learners by surname
        $stmt = $db->query("SELECT learner_surname, COUNT(*) AS count
                            FROM learner
                            GROUP BY learner_surname");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($data);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Invalid report type']);
}
?>
