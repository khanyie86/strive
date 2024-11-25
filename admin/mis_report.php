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
<div class="container-fluid">
    <div class="row">
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            <div class="chart-placeholder">
                <br>
                <div class="container">
                    <h2>MIS Reports</h2>
                    <div class="container mt-3">
                        <div class="center-buttons">
                            <a href="/reports" class="btn btn-primary">
                                <i class="fas fa-file-alt"></i> 
                                Daily MIS Report
                            </a>
                            <a href="/reports/mis_daily_report" class="btn btn-primary">
                                <i class="fas fa-file-alt"></i> 
                                Daily Approved MIS Report
                            </a>
                            <a href="/reports/mis_weekly_report" class="btn btn-primary">
                                <i class="fas fa-file-alt"></i> 
                                Weekly MIS Report
                            </a>
                            <a href="/reports/other_mis_report" class="btn btn-primary">
                                <i class="fas fa-file-alt"></i> 
                                Other MIS Report
                            </a>
                            <!-- <a href="#" class="btn btn-primary" onclick="showReport('report2')"><i class="fas fa-chart-line"></i> Report 2</a> -->
                            <!-- <a href="#" class="btn btn-primary" onclick="showReport('report3')"><i class="fas fa-database"></i> Report 3</a> -->
                        </div>
                    </div>
                    <div class="report-container mt-4" id="report-container">
                        <!-- Reports will be dynamically loaded here -->
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>



<script>
    function showReport(reportId) {
        let reportContent = '';
        const xhr = new XMLHttpRequest();
        xhr.open('GET', `fetch_report.php?report=${reportId}`, true);
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                const response = JSON.parse(xhr.responseText);
                if (response.error) {
                    reportContent = `<h3>Error</h3><p>${response.error}</p>`;
                } else {
                    switch (reportId) {
                        case 'report1':
                            reportContent = '<h3>Report 1: Learner Surname</h3>';
                            reportContent += '<table class="table table-bordered"><thead><tr><th>ID</th><th>Name</th><th>Surname</th><th>Age</th></tr></thead><tbody>';
                            response.forEach(item => {
                                reportContent += `<tr><td>${item.learner_id}</td><td>${item.learner_name}</td><td>${item.learner_surname}</td><td>${item.age}</td></tr>`;
                            });
                            reportContent += '</tbody></table>';
                            break;
                        case 'report2':
                            reportContent = '<h3>Report 2: Learners by DOB</h3>';
                            reportContent += '<table class="table table-bordered"><thead><tr><th>Age</th><th>Count</th></tr></thead><tbody>';
                            response.forEach(item => {
                                reportContent += `<tr><td>${item.age}</td><td>${item.count}</td></tr>`;
                            });
                            reportContent += '</tbody></table>';
                            break;
                        case 'additional_mis_report':
                            reportContent = '<h3>Report 3: Learners by Age</h3>';
                            reportContent += '<table class="table table-bordered"><thead><tr><th>Surname</th><th>Count</th></tr></thead><tbody>';
                            response.forEach(item => {
                                reportContent += `<tr><td>${item.learner_surname}</td><td>${item.count}</td></tr>`;
                            });
                            reportContent += '</tbody></table>';
                            break;
                        default:
                            reportContent = '<h3>Unknown Report</h3>' +
                                '<p>No content available for this report.</p>';
                            break;
                    }
                }
                document.getElementById('report-container').innerHTML = reportContent;
            } else {
                document.getElementById('report-container').innerHTML = '<h3>Error</h3><p>Failed to fetch report data.</p>';
            }
        };
        xhr.send();
    }

</script>
<?php require 'includes/footer.php'; ?>