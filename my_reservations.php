<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Get the filter status from URL parameter, default to 'all'
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Get counts for each status
$statusCounts = [
    'all' => 0,
    'reserved' => 0,
    'expired' => 0,
    'cancelled' => 0
];

$countQuery = "SELECT status, COUNT(*) as count FROM reservations WHERE student_id = ? GROUP BY status";
$stmt = $conn->prepare($countQuery);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$countResult = $stmt->get_result();
if ($countResult) {
    while ($row = $countResult->fetch_assoc()) {
        $statusCounts[$row['status']] = $row['count'];
    }
}
$statusCounts['all'] = array_sum($statusCounts);

// Get all reservations for the current user with filter
$sql = "SELECT r.*, 
        CASE 
            WHEN r.status = 'reserved' AND r.reservation_time > DATE_ADD(NOW(), INTERVAL 60 MINUTE) THEN 'upcoming'
            WHEN r.status = 'reserved' AND NOW() BETWEEN DATE_SUB(r.reservation_time, INTERVAL 15 MINUTE) AND DATE_ADD(r.reservation_time, INTERVAL 60 MINUTE) THEN 'ongoing'
            WHEN r.status = 'reserved' AND DATE_ADD(r.reservation_time, INTERVAL 60 MINUTE) < NOW() THEN 'expired'
            ELSE r.status
        END as reservation_status
        FROM reservations r 
        WHERE r.student_id = ?";
if ($status_filter !== 'all') {
    $sql .= " AND r.status = ?";
}
$sql .= " ORDER BY r.reservation_time DESC";

$stmt = $conn->prepare($sql);
if ($status_filter !== 'all') {
    $stmt->bind_param("ss", $student_id, $status_filter);
} else {
    $stmt->bind_param("s", $student_id);
}
$stmt->execute();
$result = $stmt->get_result();
$reservations = $result->fetch_all(MYSQLI_ASSOC);

// Close database connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reservations</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        header {
            background-color: #b91a1a;
            color: white;
            position: fixed;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            top: 0;
            height: 7vh;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            max-width: 1200px;
            margin: 90px auto 20px;  /* Added top margin to account for fixed header */
            padding: 20px;
            flex: 1;
        }

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }

        .reservations-table {
            width: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-collapse: collapse;
            margin-bottom: 20px;
            min-width: 1000px; /* Added minimum width */
        }

        .reservations-table th,
        .reservations-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .reservations-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .reservations-table tr:hover {
            background-color: #f5f5f5;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .status-upcoming {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .status-ongoing {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .status-expired {
            background-color: #ffebee;
            color: #c62828;
        }

        .status-cancelled {
            background-color: #fafafa;
            color: #616161;
        }

        .cancel-btn {
            padding: 6px 12px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .cancel-btn:hover {
            background-color: #c82333;
        }

        .cancel-btn:disabled {
            background-color: #ddd;
            cursor: not-allowed;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            background-color: #b91a1a;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-bottom: 20px;
            transition: background-color 0.3s;
        }

        .back-btn i {
            margin-right: 8px;
        }

        .back-btn:hover {
            background-color: #7f1d1d;
        }

        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .filter-section {
            margin: 20px 0;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-button {
            padding: 10px 20px;
            border: 2px solid #b91a1a;
            background-color: white;
            color: #b91a1a;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .filter-button:hover,
        .filter-button.active {
            background-color: #b91a1a;
            color: white;
        }

        .filter-button .count {
            background-color: #f8d7da;
            color: #b91a1a;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.9em;
        }

        .filter-button:hover .count,
        .filter-button.active .count {
            background-color: white;
            color: #b91a1a;
        }

        footer {
            background-color: #343a40;
            color: white;
            text-align: center;
            padding: 15px;
            margin-top: auto;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
                margin-top: 80px;  /* Adjusted for mobile */
            }

            .reservations-table {
                display: block;
                overflow-x: auto;
            }

            .status-badge {
                padding: 4px 8px;
                font-size: 12px;
            }

            .filter-section {
                justify-content: center;
            }
            .filter-button {
                font-size: 14px;
                padding: 8px 16px;
            }
        }
    </style>
</head>
<body>
    <header></header>
    
    <div class="container">
        <a href="layout.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Layout
        </a>

        <h1>My Reservations</h1>

        <?php if (isset($_GET['success']) || isset($_GET['error'])): ?>
            <div class="alert <?php echo isset($_GET['success']) ? 'alert-success' : 'alert-error'; ?>">
                <i class="fas <?php echo isset($_GET['success']) ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php 
                if (isset($_GET['success']) && $_GET['success'] == 'cancelled') {
                    echo "Reservation cancelled successfully!";
                } elseif (isset($_GET['error'])) {
                    echo htmlspecialchars($_GET['error']);
                }
                ?>
            </div>
        <?php endif; ?>

        <!-- Add Filter Section -->
        <div class="filter-section">
            <a href="?status=all" class="filter-button <?php echo $status_filter === 'all' ? 'active' : ''; ?>">
                <i class="fas fa-list"></i> All
                <span class="count"><?php echo $statusCounts['all']; ?></span>
            </a>
            <a href="?status=reserved" class="filter-button <?php echo $status_filter === 'reserved' ? 'active' : ''; ?>">
                <i class="fas fa-clock"></i> Reserved
                <span class="count"><?php echo $statusCounts['reserved']; ?></span>
            </a>
            <a href="?status=expired" class="filter-button <?php echo $status_filter === 'expired' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-times"></i> Expired
                <span class="count"><?php echo $statusCounts['expired']; ?></span>
            </a>
            <a href="?status=cancelled" class="filter-button <?php echo $status_filter === 'cancelled' ? 'active' : ''; ?>">
                <i class="fas fa-ban"></i> Cancelled
                <span class="count"><?php echo $statusCounts['cancelled']; ?></span>
            </a>
        </div>

        <?php if (empty($reservations)): ?>
            <p style="text-align: center; color: #666;">You don't have any reservations yet.</p>
        <?php else: ?>
            <table class="reservations-table">
                <thead>
                    <tr>
                        <th>Table</th>
                        <th>Chairs</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $reservation): ?>
                        <tr>
                            <td>Table <?php echo htmlspecialchars($reservation['table_number']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['chairs_used']); ?> chairs</td>
                            <td><?php echo date('F j, Y', strtotime($reservation['reservation_time'])); ?></td>
                            <td><?php echo date('h:i A', strtotime($reservation['reservation_time'])); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $reservation['reservation_status']; ?>">
                                    <?php echo ucfirst($reservation['reservation_status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($reservation['reservation_status'] == 'upcoming' || $reservation['reservation_status'] == 'ongoing' || $reservation['reservation_status'] == 'reserved'): ?>
                                    <form action="cancel_reservation.php" method="POST" style="margin: 0;" 
                                          onsubmit="return confirm('Are you sure you want to cancel this reservation?');">
                                        <input type="hidden" name="reservation_id" 
                                               value="<?php echo htmlspecialchars($reservation['id']); ?>">
                                        <button type="submit" class="cancel-btn">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    </form>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <footer>
        <p>© 2025 Smart Library Booking</p>
    </footer>

    <script>
    // Auto-hide alerts after 3 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.5s ease-out';
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.remove();
                }, 500);
            }, 3000);
        });

        // Clean URL after displaying messages
        if (window.history.replaceState) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });
    </script>
</body>
</html> 