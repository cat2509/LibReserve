<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_email'])) {
    header("Location: admin-login.php");
    exit();
}

// First, update expired reservations
$updateExpiredQuery = "
    UPDATE reservations 
    SET status = 'expired' 
    WHERE status = 'reserved' 
    AND DATE_ADD(reservation_time, INTERVAL 60 MINUTE) < NOW()";
$conn->query($updateExpiredQuery);

// Get the filter status from URL parameter, default to 'all'
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Modify the SQL query based on the filter
$sql = "SELECT * FROM reservations";
if ($status_filter !== 'all') {
    $sql .= " WHERE status = '" . $conn->real_escape_string($status_filter) . "'";
}
$sql .= " ORDER BY reservation_time DESC";

$reservations = [];
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $reservations[] = $row;
    }
}

// Get counts for each status
$statusCounts = [
    'all' => 0,
    'reserved' => 0,
    'expired' => 0,
    'cancelled' => 0
];

$countQuery = "SELECT status, COUNT(*) as count FROM reservations GROUP BY status";
$countResult = $conn->query($countQuery);
if ($countResult) {
    while ($row = $countResult->fetch_assoc()) {
        $statusCounts[$row['status']] = $row['count'];
    }
}
$statusCounts['all'] = array_sum($statusCounts);

// Update queries to use chairs_used column
$currentlyReservedQuery = "
    SELECT COUNT(*) as count 
    FROM reservations 
    WHERE status = 'reserved' 
    AND NOW() BETWEEN reservation_time AND DATE_ADD(reservation_time, INTERVAL 60 MINUTE)";

// Query to get total expired reservations
$totalExpiredQuery = "
    SELECT COUNT(*) as count 
    FROM reservations 
    WHERE status = 'expired'";

// Query to get total cancelled reservations
$totalCancelledQuery = "
    SELECT COUNT(*) as count 
    FROM reservations 
    WHERE status = 'cancelled'";

// Query to get upcoming reservations
$upcomingQuery = "
    SELECT COUNT(*) as count 
    FROM reservations 
    WHERE status = 'reserved' 
    AND reservation_time > NOW()";

// Query to get recent reservations with chair information
$recentReservationsQuery = "
    SELECT r.*, u.name as student_name 
    FROM reservations r 
    LEFT JOIN user u ON r.student_id = u.student_id 
    WHERE r.status = 'reserved' 
    ORDER BY r.reservation_time DESC 
    LIMIT 10";

// Function to check if a reservation is expired
function isExpired($reservationTime) {
    $endTime = strtotime($reservationTime) + (60 * 60); // Add 1 hour
    return time() > $endTime;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta http-equiv="refresh" content="60">
  <title>Admin Dashboard - Library Reservations</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      font-family: Arial, sans-serif; margin: 0;
      background-color: #f4f4f4;
    }

    header {
      background-color: #b91a1a;
      color: white;
      padding: 20px;
      text-align: center;
      position: relative;
    }

    .account-section {
      position: fixed;
      top: 10px;
      right: 20px;
      z-index: 100;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    #accountBtn {
      background-color: #fff;
      border: 2px solid #b91a1a;
      color: #b91a1a;
      padding: 8px 16px;
      border-radius: 25px;
      font-weight: bold;
      font-size: 14px;
      cursor: pointer;
      transition: background-color 0.3s, color 0.3s;
    }

    #accountBtn:hover {
      background-color: #b91a1a;
      color: white;
    }

    .popup {
      display: none;
      position: absolute;
      top: 45px;
      right: 0;
      background-color: #fff;
      padding: 15px;
      border: 1px solid #ccc;
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
      border-radius: 10px;
      width: 220px;
      animation: fadeIn 0.3s ease-in-out;
    }

    .popup p {
      margin: 10px 0;
      color: #333;
      font-size: 14px;
    }

    .popup button {
      background-color: #b91a1a;
      color: white;
      border: none;
      padding: 8px 12px;
      border-radius: 6px;
      cursor: pointer;
      width: 100%;
      font-size: 14px;
      transition: background-color 0.3s ease;
    }

    .popup button:hover {
      background-color: #7f1d1d;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    h1 { margin: 0; }

    .hero-section {
      background-color: #fff;
      padding: 25px 20px;
      text-align: center;
      margin-bottom: 20px;
      border-bottom: 1px solid #eee;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      display: flex;
      justify-content: center;
    }

    .scan-button {
      display: inline-flex;
      align-items: center;
      background-color: #b91a1a;
      color: white;
      font-size: 18px;
      padding: 15px 30px;
      border-radius: 50px;
      text-decoration: none;
      box-shadow: 0 4px 8px rgba(185, 26, 26, 0.2);
      transition: all 0.3s ease;
      border: none;
      cursor: pointer;
    }

    .scan-button i {
      margin-right: 10px;
    }

    .scan-button:hover {
      background-color: #333;
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }

    main {
      max-width: 1200px;
      margin: 30px auto;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    th, td {
      padding: 12px;
      border: 1px solid #ccc;
      text-align: center;
    }

    th {
      background-color: #eeeeee;
    }

    .status-reserved {
      color: green; font-weight: bold;
    }

    .status-expired {
      color: red; font-weight: bold;
    }

    .status-cancelled {
      color: #ff9800; font-weight: bold;
    }

    .cancel-button {
      padding: 6px 12px;
      border: none;
      background-color: #b91a1a;
      color: white;
      border-radius: 4px;
      cursor: pointer;
      transition: background-color 0.2s;
    }

    .cancel-button:hover {
      background-color: #333;
    }

    @media (max-width: 768px) {
      table { font-size: 12px; }
      td, th { padding: 8px; }
    }

    .alert {
        padding: 15px;
        margin: 20px 0;
        border: 1px solid transparent;
        border-radius: 4px;
    }

    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
    }

    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
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

    @media (max-width: 768px) {
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
  <header>
    <h1>Admin Dashboard</h1>
    <p>Library Table Reservations Overview</p>
    <div class="account-section">
      <button id="accountBtn" title="My Account">👤 My Account</button>
      <div id="accountPopup" class="popup">
        <p><strong>Email:</strong> <span id="userEmail"><?php echo htmlspecialchars($_SESSION['admin_email']); ?></span></p>
        <button onclick="logout()">Logout</button>
      </div>
    </div>
  </header>

  <!-- Status Messages -->
  <?php if (isset($_GET['success']) && $_GET['success'] == 'reservation_cancelled'): ?>
    <div class="alert alert-success">
      <i class="fas fa-check-circle"></i> Reservation has been successfully cancelled.
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
      <i class="fas fa-exclamation-circle"></i>
      <?php
      switch($_GET['error']) {
        case 'invalid_id':
          echo 'Invalid reservation ID provided.';
          break;
        case 'no_reservation_found':
          echo 'No reservation found with the provided ID.';
          break;
        case 'cancellation_failed':
          echo 'Failed to cancel the reservation. Please try again.';
          break;
        default:
          echo 'An error occurred while processing your request.';
      }
      ?>
    </div>
  <?php endif; ?>

  <!-- Hero Section -->
  <div class="hero-section">
    <a href="admin-scanner.php" class="scan-button">
      <i class="fas fa-qrcode"></i> Scan Reservation QR Code
    </a>
    <a href="admin-report.php" class="scan-button" style="margin-left: 15px;">
      <i class="fas fa-chart-bar"></i> View Reports
    </a>
  </div>

  <main>
    <h2>All Reservations</h2>
    
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

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Student ID</th>
          <th>Table Number</th>
          <th>Chairs Used</th>
          <th>Reservation Time</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($reservations) === 0): ?>
          <tr><td colspan="7">No reservations found for the selected filter.</td></tr>
        <?php else: ?>
          <?php foreach ($reservations as $res): ?>
            <tr>
              <td><?= $res['id'] ?></td>
              <td><?= htmlspecialchars($res['student_id']) ?></td>
              <td><?= $res['table_number'] ?></td>
              <td><?= $res['chairs_used'] ?? '0' ?></td>
              <td><?= date("Y-m-d H:i", strtotime($res['reservation_time'])) ?></td>
              <td class="status-<?= strtolower($res['status']) ?>"><?= ucfirst($res['status']) ?></td>
              <td>
                <?php if ($res['status'] === 'reserved'): ?>
                  <form action="admin-cancel.php" method="POST" onsubmit="return confirm('Cancel this reservation?');">
                    <input type="hidden" name="id" value="<?= $res['id'] ?>">
                    <button type="submit" class="cancel-button">Cancel</button>
                  </form>
                <?php else: ?>
                  —
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </main>

  <script>
    // Account popup toggle and logout
    const accountBtn = document.getElementById("accountBtn");
    const accountPopup = document.getElementById("accountPopup");

    accountBtn.addEventListener("click", () => {
      accountPopup.style.display = accountPopup.style.display === "block" ? "none" : "block";
    });

    function logout() {
      window.location.href = "admin-logout.php";
    }

    // Auto-close popup when clicking outside
    document.addEventListener("click", function(event) {
      const isClickInside = accountBtn.contains(event.target) || accountPopup.contains(event.target);
      if (!isClickInside) {
        accountPopup.style.display = "none";
      }
    });

    // Handle URL parameters and messages
    window.onload = function() {
        // Remove success/error parameters from URL after showing message
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success') || urlParams.has('error')) {
            // Remove the parameter but keep the message visible
            const newUrl = window.location.pathname;
            window.history.pushState({}, '', newUrl);

            // Auto-hide the message after 3 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    alert.style.transition = 'opacity 0.5s ease-in-out';
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 500);
                });
            }, 3000);
        }
    };

    // Preserve filter when auto-refreshing
    const currentFilter = '<?php echo $status_filter; ?>';
    setInterval(function() {
      window.location.href = '?status=' + currentFilter;
    }, 60000); // Refresh every minute
  </script>
</body>
</html>
