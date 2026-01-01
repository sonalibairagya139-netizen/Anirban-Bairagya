<?php
require_once '../includes/config.php';
adminAuth();

// Get issued books per month
$issuedCounts = array_fill(1, 12, 0);
$issuedQuery = $pdo->query("SELECT MONTH(issue_date) AS month, COUNT(*) AS count FROM issues GROUP BY MONTH(issue_date)");
while ($row = $issuedQuery->fetch()) {
    $issuedCounts[(int)$row['month']] = (int)$row['count'];
}

// Get returned books per month
$returnedCounts = array_fill(1, 12, 0);
$returnedQuery = $pdo->query("SELECT MONTH(return_date) AS month, COUNT(*) AS count FROM issues WHERE return_date IS NOT NULL GROUP BY MONTH(return_date)");
while ($row = $returnedQuery->fetch()) {
    $returnedCounts[(int)$row['month']] = (int)$row['count'];
}

// Convert PHP arrays to JavaScript arrays
$js_issued = json_encode(array_values($issuedCounts));
$js_returned = json_encode(array_values($returnedCounts));

$pageTitle = "Reports";
require_once '../includes/header.php';
?>

<div class="admin-section">
    <div class="section-header">
        <h1><i class="fas fa-chart-bar"></i> Monthly Reports</h1>
        <p>Visual overview of books issued and returned this year</p>
    </div>

    <div class="card">
        <div class="card-body">
            <canvas id="issueChart" height="100"></canvas>
        </div>
    </div>
</div>

<!-- Chart.js and Data Injection -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const issuedData = <?php echo $js_issued; ?>;
    const returnedData = <?php echo $js_returned; ?>;

    const ctx = document.getElementById('issueChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [
                "January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"
            ],
            datasets: [
                {
                    label: 'Books Issued',
                    data: issuedData,
                    backgroundColor: 'rgba(52, 152, 219, 0.6)',
                    borderColor: 'rgba(52, 152, 219, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Books Returned',
                    data: returnedData,
                    backgroundColor: 'rgba(46, 204, 113, 0.6)',
                    borderColor: 'rgba(46, 204, 113, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: '#2c3e50',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: '#34495e',
                    titleColor: '#fff',
                    bodyColor: '#ecf0f1',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#2c3e50'
                    }
                },
                x: {
                    ticks: {
                        color: '#2c3e50'
                    }
                }
            }
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>
