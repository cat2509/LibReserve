<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION["admin_email"])) {
    header("Location: admin-login.php");
    exit();
}

// Get daily footsteps (reservations per day)
$dailyQuery = "
    SELECT 
        DATE(reservation_time) as date,
        COUNT(*) as total_reservations,
        SUM(chairs_used) as total_chairs
    FROM reservations
    GROUP BY DATE(reservation_time)
    ORDER BY date DESC
    LIMIT 7";

$dailyResult = $conn->query($dailyQuery);
$dailyData = [];
$dailyLabels = [];
$dailyChairs = [];

while ($row = $dailyResult->fetch_assoc()) {
    $dailyLabels[] = date('M d', strtotime($row['date']));
    $dailyData[] = $row['total_reservations'];
    $dailyChairs[] = $row['total_chairs'] ?? 0;
}

// Get peak hours data
$peakHoursQuery = "
    SELECT 
        HOUR(reservation_time) as hour,
        COUNT(*) as total_reservations,
        SUM(chairs_used) as total_chairs
    FROM reservations
    GROUP BY HOUR(reservation_time)
    ORDER BY hour";

$peakResult = $conn->query($peakHoursQuery);
$peakData = [];
$peakLabels = [];
$peakChairs = [];

while ($row = $peakResult->fetch_assoc()) {
    $hour = $row['hour'];
    $formattedHour = ($hour % 12 == 0 ? 12 : $hour % 12) . ($hour < 12 ? ' AM' : ' PM');
    $peakLabels[] = $formattedHour;
    $peakData[] = $row['total_reservations'];
    $peakChairs[] = $row['total_chairs'] ?? 0;
}

// Get current status distribution
$statusQuery = "
    SELECT 
        status,
        COUNT(*) as count,
        SUM(CASE WHEN status = 'reserved' AND reservation_time > NOW() THEN 1 ELSE 0 END) as upcoming_count,
        SUM(chairs_used) as total_chairs
    FROM reservations
    WHERE 
        CASE 
            WHEN status = 'reserved' THEN 
                (reservation_time <= NOW() AND DATE_ADD(reservation_time, INTERVAL 1 HOUR) >= NOW())
                OR reservation_time > NOW()
            ELSE 
                1=1 
        END
    GROUP BY status";

$statusResult = $conn->query($statusQuery);
$statusData = [];
$statusLabels = [];
$upcomingBookings = 0;
$totalChairsUsed = [];

while ($row = $statusResult->fetch_assoc()) {
    $statusLabels[] = ucfirst($row['status']);
    $statusData[] = $row['count'];
    $totalChairsUsed[$row['status']] = $row['total_chairs'] ?? 0;
    if ($row['status'] === 'reserved') {
        $upcomingBookings = $row['upcoming_count'];
    }
}

// Get daily status distribution for last 7 days
$dailyStatusQuery = "
    SELECT 
        DATE(reservation_time) as date,
        status,
        COUNT(*) as count,
        SUM(chairs_used) as chairs_used
    FROM reservations
    WHERE reservation_time >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(reservation_time), status
    ORDER BY date DESC, status";

$dailyStatusResult = $conn->query($dailyStatusQuery);
$dailyStatusData = [];
$dailyStatusDates = [];
$statusTypes = [];
$chairsData = [];

while ($row = $dailyStatusResult->fetch_assoc()) {
    $date = date('M d', strtotime($row['date']));
    if (!in_array($date, $dailyStatusDates)) {
        $dailyStatusDates[] = $date;
    }
    if (!in_array($row['status'], $statusTypes)) {
        $statusTypes[] = $row['status'];
    }
    $dailyStatusData[$date][$row['status']] = $row['count'];
    $chairsData[$date][$row['status']] = $row['chairs_used'] ?? 0;
}

// Calculate current statistics
$currentlyReserved = 0;
$totalExpired = 0;
$totalCancelled = 0;
foreach ($statusData as $index => $count) {
    $status = $statusLabels[$index];
    if ($status === 'Reserved') $currentlyReserved = $count;
    if ($status === 'Expired') $totalExpired = $count;
    if ($status === 'Cancelled') $totalCancelled = $count;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Usage Reports</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .back-btn {
            background: #b91a1a;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .back-btn:hover {
            background: #921515;
        }
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        .full-width {
            grid-column: 1 / -1;
        }
        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }
        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            margin: 0;
            color: #666;
            font-size: 0.9em;
        }
        .stat-card .value {
            font-size: 2em;
            font-weight: bold;
            color: #b91a1a;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Library Usage Reports</h1>
            <a href="admin.php" class="back-btn">Back to Dashboard</a>
        </div>

        <div class="stats-container">
            <div class="stat-card">
                <h3>Currently Reserved</h3>
                <div class="value"><?php echo $currentlyReserved; ?></div>
                <small>Chairs: <?php echo $totalChairsUsed['reserved'] ?? 0; ?></small>
            </div>
            <div class="stat-card">
                <h3>Upcoming Bookings</h3>
                <div class="value"><?php echo $upcomingBookings; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Expired</h3>
                <div class="value"><?php echo $totalExpired; ?></div>
                <small>Chairs: <?php echo $totalChairsUsed['expired'] ?? 0; ?></small>
            </div>
            <div class="stat-card">
                <h3>Total Cancelled</h3>
                <div class="value"><?php echo $totalCancelled; ?></div>
                <small>Chairs: <?php echo $totalChairsUsed['cancelled'] ?? 0; ?></small>
            </div>
        </div>
        
        <div class="charts-grid">
            <div class="chart-container">
                <h2>Daily Footsteps (Last 7 Days)</h2>
                <canvas id="dailyChart"></canvas>
            </div>
            
            <div class="chart-container">
                <h2>Peak Hours Distribution</h2>
                <canvas id="peakHoursChart"></canvas>
            </div>

            <div class="chart-container full-width">
                <h2>Daily Status Distribution (Last 7 Days)</h2>
                <canvas id="dailyStatusChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Daily Footsteps Chart
        new Chart(document.getElementById('dailyChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($dailyLabels) ?>,
                datasets: [{
                    label: 'Number of Reservations',
                    data: <?= json_encode($dailyData) ?>,
                    backgroundColor: '#b91a1a',
                    borderColor: '#921515',
                    borderWidth: 1
                },
                {
                    label: 'Chairs Used',
                    data: <?= json_encode($dailyChairs) ?>,
                    backgroundColor: '#666666',
                    borderColor: '#444444',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });

        // Peak Hours Pie Chart
        new Chart(document.getElementById('peakHoursChart'), {
            type: 'pie',
            data: {
                labels: <?= json_encode($peakLabels) ?>,
                datasets: [{
                    data: <?= json_encode($peakData) ?>,
                    backgroundColor: [
                        '#b91a1a', // bright red
                        '#666666', // medium grey
                        '#921515', // dark red
                        '#999999', // light grey
                        '#d32f2f', // light red
                        '#333333', // dark grey
                        '#7f1d1d', // maroon red
                        '#cccccc', // very light grey
                        '#e74c4c', // soft red
                        '#888888', // another grey
                        '#c62828', // another red
                        '#444444'  // another dark grey
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });

        // Daily Status Distribution Stacked Bar Chart
        new Chart(document.getElementById('dailyStatusChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($dailyStatusDates) ?>,
                datasets: <?php 
                    $datasets = [];
                    $colors = [
                        'reserved' => 'rgba(185, 26, 26, 0.8)',    // Maroon red
                        'expired' => 'rgba(102, 102, 102, 0.8)',   // Grey
                        'cancelled' => 'rgba(146, 21, 21, 0.8)'    // Dark maroon
                    ];
                    
                    foreach ($statusTypes as $status) {
                        $data = [];
                        foreach ($dailyStatusDates as $date) {
                            $data[] = $dailyStatusData[$date][$status] ?? 0;
                        }
                        $datasets[] = [
                            'label' => ucfirst($status),
                            'data' => $data,
                            'backgroundColor' => $colors[strtolower($status)],
                            'borderColor' => str_replace('0.8', '1', $colors[strtolower($status)]),
                            'borderWidth' => 1
                        ];
                    }
                    echo json_encode($datasets);
                ?>
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    </script>
</body>
</html> 