<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

// Library coordinates (set these to your library's actual coordinates)
$libraryLat = 19.0461189; // Example: Somaiya Vidyavihar latitude
$libraryLng = 72.8713324; // Example: Somaiya Vidyavihar longitude

// Include database connection
require_once 'db_connect.php';

// Use either $mysqli or $conn, whichever is available
$db = $mysqli ?? $conn;

// Expire old reservations after 60 minutes
$db->query("UPDATE reservations SET status='expired' WHERE status='reserved' AND TIMESTAMPDIFF(MINUTE, reservation_time, NOW()) >= 60");

// Fetch today's reservations with chair information
$result = $db->query("
    SELECT r.table_number, r.status, 
           r.chairs_used,
           r.reservation_time,
           (4 - r.chairs_used) as available_chairs
    FROM reservations r
    WHERE r.status = 'reserved'
    AND DATE(r.reservation_time) = CURDATE()
    AND NOW() BETWEEN r.reservation_time AND DATE_ADD(r.reservation_time, INTERVAL 60 MINUTE)
");

$reservations = [];
$chairsUsed = [];
$availableChairs = [];
while ($row = $result->fetch_assoc()) {
    $reservations[$row['table_number']] = 'reserved';
    $chairsUsed[$row['table_number']] = intval($row['chairs_used']);
    $availableChairs[$row['table_number']] = intval($row['available_chairs']);
}

// Close the database connection
$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Library Table Booking</title>
  <style>
    * { box-sizing: border-box; }
body { font-family: Arial, sans-serif; background-color: #ffffff; margin: 0; padding: 0; }

.banner {
  background-color: #b91a1a; height: 7vh; width: 100%;
  position: fixed; top: 0; left: 0; z-index: 1;
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

header {
  margin-top: 7vh; text-align: center; padding: 20px 10px;
}

h1, h2 { color: #444444; margin: 10px 0; }

main {
  padding: 20px; max-width: 1200px; margin: auto; min-height: 130vh;
}

#libraryLayout {
  display: grid; grid-template-columns: repeat(7, 1fr);
  gap: 10px; padding: 20px; background-color: #fff;
  border-radius: 8px; margin: 0 auto 30px auto;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.row-break { grid-column: 1 / -1; height: 0; }

.table {
  height: 80px;
  font-size: 14px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: center;
  padding: 10px;
  position: relative;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.table::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 4px;
  background: currentColor;
  opacity: 0.3;
}

.available {
  background-color: #e8f5e9;
  color: #2e7d32;
  border: 2px solid #c8e6c9;
}

.available:hover {
  background-color: #c8e6c9;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.reserved {
  background-color: #ffebee;
  color: #c62828;
  border: 2px solid #ffcdd2;
  cursor: not-allowed;
  opacity: 0.8;
}

.selected { background-color: red !important; color: white !important; }

form {
  background-color: #ffffff; border-radius: 8px; padding: 20px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); margin-bottom: 40px;
}

label {
  display: block; margin: 10px 0 5px; color: #444444;
}

select,
input[type="date"],
input[type="time"],
input[type="text"] {
  width: 100%; padding: 10px; margin-bottom: 15px;
  border: 1px solid #444444; border-radius: 4px;
}

.reserve-button {
  width: 100%; padding: 12px; background-color: #b91a1a;
  color: white; border: none; border-radius: 4px;
  font-size: 16px; cursor: pointer; transition: background-color 0.3s ease;
}

.reserve-button:hover { background-color: #444444; }

#confirmation-message {
  display: none; padding: 15px; background-color: #eceff1;
  border: 1px solid #cfd8dc; color: #37474f;
  border-radius: 8px; margin-top: 20px; text-align: center;
}

footer {
  background-color: #343a40; color: white;
  text-align: center; padding: 15px;
}

@media (max-width: 768px) {
  .table { height: 50px; font-size: 12px; }
  h1 { font-size: 22px; }
  h2 { font-size: 18px; }
  #libraryLayout { grid-template-columns: repeat(3, 1fr); }
}

#location-status {
  background-color: #ffecb3;
  color: #856404;
  padding: 20px;
  border-radius: 12px;
  margin-bottom: 20px;
  text-align: center;
  display: none;
  font-size: 16px;
  border-left: 5px solid #ffeeba;
  box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}

#location-status.verified {
  background-color: #d4edda;
  color: #155724;
  border-left: 5px solid #c3e6cb;
}

#location-status.error {
  background-color: #f8d7da;
  color: #721c24;
  border-left: 5px solid #f5c6cb;
}

#check-location-btn {
  display: block;
  width: 100%;
  padding: 16px;
  background-color: #b91a1a;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 18px;
  cursor: pointer;
  margin-bottom: 30px;
  transition: all 0.3s ease;
  font-weight: 600;
  letter-spacing: 0.5px;
  box-shadow: 0 4px 8px rgba(185, 26, 26, 0.25);
}

#check-location-btn:hover {
  background-color: #921414;
  transform: translateY(-3px);
  box-shadow: 0 6px 12px rgba(185, 26, 26, 0.3);
}

#check-location-btn:active {
  transform: translateY(1px);
}

.form-section {
  margin-bottom: 30px;
  padding-bottom: 25px;
  border-bottom: 1px solid #eee;
}

.form-section h3 {
  color: #444;
  margin-top: 0;
  font-size: 20px;
  font-weight: 600;
  margin-bottom: 15px;
}

.rules-info {
  background-color: #fff8e1;
  border-left: 5px solid #ffc107;
  padding: 15px 20px;
  margin: 15px 0;
  font-size: 15px;
  color: #856404;
  border-radius: 8px;
}

.rules-info ul {
  margin: 10px 0 5px 20px;
  padding: 0;
}

.rules-info li {
  margin-bottom: 8px;
}

.partially-available {
    background-color: #fff8e1;
    color: #f57f17;
    border: 2px solid #ffe082;
    animation: pulse 2s infinite;
}

.partially-available:hover {
    background-color: #ffecb3;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(255, 167, 38, 0.4);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(255, 167, 38, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(255, 167, 38, 0);
    }
}

.table .table-number {
    font-weight: bold;
    font-size: 16px;
    margin-bottom: 4px;
}

.table .chairs-info {
    font-size: 12px;
    opacity: 0.9;
    display: flex;
    align-items: center;
    gap: 4px;
}

.table .chairs-info i {
    font-size: 14px;
}

/* Style for the chairs dropdown */
#chairs {
    background-color: white;
    border: 2px solid #ddd;
    border-radius: 6px;
    padding: 10px;
    font-size: 14px;
    transition: all 0.3s ease;
}

#chairs:focus {
    border-color: #b91a1a;
    box-shadow: 0 0 0 2px rgba(185, 26, 26, 0.1);
}

/* Add tooltip styles */
.table .tooltip {
    position: absolute;
    bottom: -40px;
    left: 50%;
    transform: translateX(-50%);
    background-color: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    white-space: nowrap;
    z-index: 100;
}

.table:hover .tooltip {
    opacity: 1;
    visibility: visible;
    bottom: -30px;
}

.alert {
  padding: 15px;
  margin: 20px 0;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 10px;
  animation: slideIn 0.5s ease-out;
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

.alert i {
  font-size: 20px;
}

@keyframes slideIn {
  from {
    transform: translateY(-20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

/* Add these styles for the new button */
.popup-btn {
    display: block;
    width: 100%;
    padding: 8px 12px;
    margin: 8px 0;
    border: none;
    border-radius: 6px;
    background-color: #b91a1a;
    color: white;
    font-size: 14px;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: background-color 0.3s ease;
}

.popup-btn i {
    margin-right: 8px;
}

.popup-btn:hover {
    background-color: #7f1d1d;
}
  </style>
</head>
<body>
  <div class="banner"></div>

  <!-- Account Popup Section -->
  <div class="account-section">
    <button id="accountBtn" title="My Account">👤 My Account</button>
    <div id="accountPopup" class="popup">
      <p><strong>Email:</strong> <span id="userEmail"><?php echo $_SESSION['email'] ?? 'user@example.com'; ?></span></p>
      <p><strong>Student ID:</strong> <span id="userId"><?php echo $_SESSION['student_id'] ?? 'S101'; ?></span></p>
      <a href="my_reservations.php" class="popup-btn"><i class="fas fa-calendar-alt"></i> My Reservations</a>
      <button onclick="logout()" class="popup-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
    </div>
  </div>

  <header><h1>Library Table Booking</h1></header>
  <main>
    <?php
    // Handle success messages
    if (isset($_GET['success'])) {
        $message = '';
        switch($_GET['success']) {
            case 'reservation_cancelled':
                $message = 'Reservation has been successfully cancelled.';
                break;
        }
        if ($message) {
            echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i>' . htmlspecialchars($message) . '</div>';
        }
    }

    // Handle error messages
    if (isset($_GET['error'])) {
        $message = '';
        switch($_GET['error']) {
            case 'invalid_reservation':
                $message = 'Invalid reservation request.';
                break;
            case 'no_reservation_found':
                $message = 'No reservation found with the provided details.';
                break;
            case 'cancellation_failed':
                $message = 'Failed to cancel the reservation. Please try again.';
                break;
        }
        if ($message) {
            echo '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i>' . htmlspecialchars($message) . '</div>';
        }
    }
    ?>

    <div id="location-status"></div>
    <button id="check-location-btn"><i class="fas fa-map-marker-alt"></i> Verify Your Location</button>

    <div class="form-section">
      <h3>Important Information</h3>
      <div class="rules-info">
        <p><strong>Reservation Rules:</strong></p>
        <ul>
          <li><i class="fas fa-map-marker-alt"></i> You must be within 500 meters of the library to book a table</li>
          <li><i class="fas fa-clock"></i> You cannot book time slots that have already passed</li>
          <li><i class="fas fa-calendar-alt"></i> Reservations are available from 8:00 AM to 7:00 PM</li>
        </ul>
      </div>
    </div>

    <div id="booking-form" style="display: none;">
      <h2>Library Layout</h2>
      <div id="libraryLayout"></div>

      <h2>Seat Selection</h2>
      <form id="reservationForm">
        <label for="studentId">Student ID:</label>
        <input type="text" name="student_id" id="studentId" value="<?php echo $_SESSION['student_id'] ?? ''; ?>" readonly required>

        <label for="tableNumber">Table Number:</label>
        <select name="table_number" id="tableNumber" required></select>

        <label for="chairs">Number of Chairs:</label>
        <select name="chairs" id="chairs" required>
          <option value="">Select Number of Chairs</option>
        </select>

        <label for="date">Date:</label>
        <input type="date" id="date" name="date" required>

        <label for="time">Time:</label>
        <input type="time" id="time" name="time" min="08:00" max="19:00" required>

        <label for="duration">Duration:</label>
        <select id="duration" name="duration" required>
          <option value="60">1 Hour</option>
        </select>

        <button type="submit" class="reserve-button">Reserve Table</button>
      </form>

      <div id="confirmation-message">
        <h3>Reservation Successful!</h3>
        <p>Your table has been booked.</p>
      </div>
    </div>
  </main>
  <footer>
    <p>© 2025 Smart Library Booking</p>
  </footer>

<script>
  // Constants
  const LIBRARY_LAT = <?php echo $libraryLat; ?>;
  const LIBRARY_LNG = <?php echo $libraryLng; ?>;
  const MAX_DISTANCE = 500; // in meters

  const layout = document.getElementById('libraryLayout');
  const tableDropdown = document.getElementById('tableNumber');
  let selectedTableId = null;

  const reservedTables = new Set(<?php echo json_encode(array_keys($reservations)); ?>);
  const chairsUsed = <?php echo json_encode($chairsUsed); ?>;
  const availableChairs = <?php echo json_encode($availableChairs); ?>;
  const layoutPattern = [7, 7, 5, 7];
  const tablesData = Array.from({ length: 26 }, (_, i) => ({ id: i + 1 }));

  // Initialize library layout
  document.addEventListener('DOMContentLoaded', function() {
    // Setup location verification
    const locationBtn = document.getElementById('check-location-btn');
    const locationStatus = document.getElementById('location-status');
    const bookingForm = document.getElementById('booking-form');

    locationBtn.addEventListener('click', checkLocation);

    // Create library layout
    renderLayout();

    // Form submission
    document.getElementById('reservationForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Validate time before proceeding
      const timeInput = document.getElementById('time').value;
      const time = new Date(`1970-01-01T${timeInput}:00`);
      const startTime = new Date('1970-01-01T08:00:00');
      const endTime = new Date('1970-01-01T19:00:00');

      if (time < startTime || time > endTime) {
          alert('Please select a time between 08:00 AM and 07:00 PM.');
          return; // Stop form submission if time is invalid
      }
      
      const formData = new URLSearchParams(new FormData(this)).toString();

      fetch("reservetable.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === "success") {
          document.getElementById("confirmation-message").style.display = "block";
          reservedTables.add(Number(document.getElementById("tableNumber").value));
          renderLayout();

          // Redirect to confirmation.php after 2 second
          setTimeout(() => {
            window.location.href = "confirmation.php";
          }, 2000);
        } else {
          alert("Error: " + data.message);
        }
      })
      .catch(err => {
        console.error("Reservation error:", err);
        alert("There was an error processing your reservation.");
      });
    });

    // Add message auto-hide functionality
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

  // Function to check user's location
  function checkLocation() {
    const locationBtn = document.getElementById('check-location-btn');
    const locationStatus = document.getElementById('location-status');
    const bookingForm = document.getElementById('booking-form');
    
    locationStatus.style.display = 'block';
    locationStatus.innerHTML = 'Checking your location...';
    locationStatus.className = '';
    
    if (!navigator.geolocation) {
      locationStatus.innerHTML = 'Geolocation is not supported by your browser.';
      locationStatus.className = 'error';
      return;
    }
    
    locationBtn.disabled = true;
    
    navigator.geolocation.getCurrentPosition(
      // Success callback
      function(position) {
        const userLat = position.coords.latitude;
        const userLng = position.coords.longitude;
        const distance = calculateDistance(userLat, userLng, LIBRARY_LAT, LIBRARY_LNG);
        
        if (distance <= MAX_DISTANCE) {
          locationStatus.innerHTML = `✅ Location verified! You are ${Math.round(distance)} meters from the library.`;
          locationStatus.className = 'verified';
          bookingForm.style.display = 'block';
          locationBtn.style.display = 'none';
          
          // Make the success message disappear after 2 seconds
          setTimeout(() => {
            locationStatus.style.transition = 'opacity 0.5s ease-out';
            locationStatus.style.opacity = '0';
            setTimeout(() => {
              locationStatus.style.display = 'none';
              locationStatus.style.opacity = '1';
            }, 500);
          }, 2000);
        } else {
          locationStatus.innerHTML = `❌ You must be within 500 meters of the library to make a reservation. You are currently ${Math.round(distance)} meters away.`;
          locationStatus.className = 'error';
          locationBtn.disabled = false;
          
          // Make the error message disappear after 2 seconds
          setTimeout(() => {
            locationStatus.style.transition = 'opacity 0.5s ease-out';
            locationStatus.style.opacity = '0';
            setTimeout(() => {
              locationStatus.style.display = 'none';
              locationStatus.style.opacity = '1';
            }, 500);
          }, 2000);
        }
      },
      // Error callback
      function(error) {
        let errorMessage;
        switch(error.code) {
          case error.PERMISSION_DENIED:
            errorMessage = "You denied the request for geolocation.";
            break;
          case error.POSITION_UNAVAILABLE:
            errorMessage = "Location information is unavailable.";
            break;
          case error.TIMEOUT:
            errorMessage = "The request to get user location timed out.";
            break;
          case error.UNKNOWN_ERROR:
            errorMessage = "An unknown error occurred.";
            break;
        }
        locationStatus.innerHTML = `❌ Error: ${errorMessage}`;
        locationStatus.className = 'error';
        locationBtn.disabled = false;
        
        // Make the error message disappear after 2 seconds
        setTimeout(() => {
          locationStatus.style.transition = 'opacity 0.5s ease-out';
          locationStatus.style.opacity = '0';
          setTimeout(() => {
            locationStatus.style.display = 'none';
            locationStatus.style.opacity = '1';
          }, 500);
        }, 2000);
      },
      // Options
      {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
      }
    );
  }

  // Calculate distance between two coordinates in meters (Haversine formula)
  function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371e3; // Earth's radius in meters
    const φ1 = lat1 * Math.PI/180;
    const φ2 = lat2 * Math.PI/180;
    const Δφ = (lat2-lat1) * Math.PI/180;
    const Δλ = (lon2-lon1) * Math.PI/180;

    const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
              Math.cos(φ1) * Math.cos(φ2) *
              Math.sin(Δλ/2) * Math.sin(Δλ/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

    return R * c; // Distance in meters
  }

  function createLibraryLayout() {
    const layout = document.getElementById('libraryLayout');
    layout.innerHTML = '';
    let tableIndex = 0;

    layoutPattern.forEach((count, rowIndex) => {
        for (let i = 0; i < count; i++) {
            const table = tablesData[tableIndex];
            const tableEl = document.createElement('div');
            tableEl.classList.add('table');
            tableEl.dataset.id = table.id;

            // Create inner elements
            const tableNumber = document.createElement('div');
            tableNumber.classList.add('table-number');
            
            const chairsInfo = document.createElement('div');
            chairsInfo.classList.add('chairs-info');
            
            const tooltip = document.createElement('div');
            tooltip.classList.add('tooltip');

            if (reservedTables.has(table.id)) {
                const availableChairs = 4 - (chairsUsed[table.id] || 0);
                if (availableChairs > 0) {
                    tableEl.classList.add('partially-available');
                    tableNumber.textContent = `Table ${table.id}`;
                    chairsInfo.innerHTML = `<i class="fas fa-chair"></i> ${availableChairs} available`;
                    tooltip.textContent = `${availableChairs} chairs available for sharing`;
                    
                    tableEl.addEventListener('click', () => {
                        if (selectedTableId === table.id) {
                            // Deselect the table
                            selectedTableId = null;
                            tableDropdown.value = '';
                            highlightSelected(null);
                            updateChairsDropdown(4);
                        } else {
                            // Select the table
                            tableDropdown.value = table.id;
                            highlightSelected(table.id);
                            updateChairsDropdown(availableChairs);
                        }
                    });

                    const option = document.createElement('option');
                    option.value = table.id;
                    option.textContent = `Table ${table.id} (${availableChairs} chairs available)`;
                    tableDropdown.appendChild(option);
                } else {
                    tableEl.classList.add('reserved');
                    tableNumber.textContent = `Table ${table.id}`;
                    chairsInfo.innerHTML = `<i class="fas fa-lock"></i> Reserved`;
                    tooltip.textContent = 'This table is fully reserved';
                }
            } else {
                tableEl.classList.add('available');
                tableNumber.textContent = `Table ${table.id}`;
                chairsInfo.innerHTML = `<i class="fas fa-chair"></i> 4 available`;
                tooltip.textContent = 'Full table available';
                
                tableEl.addEventListener('click', () => {
                    if (selectedTableId === table.id) {
                        // Deselect the table
                        selectedTableId = null;
                        tableDropdown.value = '';
                        highlightSelected(null);
                        updateChairsDropdown(4);
                    } else {
                        // Select the table
                        tableDropdown.value = table.id;
                        highlightSelected(table.id);
                        updateChairsDropdown(availableChairs);
                    }
                });

                const option = document.createElement('option');
                option.value = table.id;
                option.textContent = `Table ${table.id} (4 chairs available)`;
                tableDropdown.appendChild(option);
            }

            tableEl.appendChild(tableNumber);
            tableEl.appendChild(chairsInfo);
            tableEl.appendChild(tooltip);
            layout.appendChild(tableEl);
            tableIndex++;
        }
        
        if (rowIndex < layoutPattern.length - 1) {
            const rowBreak = document.createElement('div');
            rowBreak.classList.add('row-break');
            layout.appendChild(rowBreak);
        }
    });
  }

  function renderLayout() {
    layout.innerHTML = '';
    tableDropdown.innerHTML = '<option value="">Select Table</option>';
    createLibraryLayout();
  }

  function highlightSelected(id) {
    document.querySelectorAll('.table').forEach(el => {
      el.classList.remove('selected');
      if (el.dataset.id == id && !el.classList.contains('reserved')) {
        el.classList.add('selected');
      }
    });
    selectedTableId = id;
  }

  tableDropdown.addEventListener('change', () => {
    const selectedTable = tableDropdown.value;
    if (selectedTable) {
        highlightSelected(selectedTable);
        // Get available chairs for the selected table
        const availableChairs = reservedTables.has(parseInt(selectedTable))
            ? 4 - (chairsUsed[selectedTable] || 0)
            : 4;
        updateChairsDropdown(availableChairs);
    }
  });

  // Set date input to today's date only
  const dateInput = document.getElementById("date");
  const today = new Date();
  const yyyy = today.getFullYear();
  const mm = String(today.getMonth() + 1).padStart(2, '0');
  const dd = String(today.getDate()).padStart(2, '0');
  const todayStr = `${yyyy}-${mm}-${dd}`;
  dateInput.min = todayStr;
  dateInput.max = todayStr;
  dateInput.value = todayStr;

  // Set time input to prevent booking past time slots
  const timeInput = document.getElementById("time");
  const currentHour = today.getHours();
  const currentMinute = today.getMinutes();
  const currentTimeStr = `${String(currentHour).padStart(2, '0')}:${String(currentMinute).padStart(2, '0')}`;
  
  // Set minimum time to current time
  timeInput.min = currentTimeStr;
  
  // Add event listener to validate time selection
  timeInput.addEventListener('change', function() {
    const selectedTime = this.value;
    const [selectedHour, selectedMinute] = selectedTime.split(':').map(Number);
    
    // If selected time is earlier than current time, show error and reset
    if (selectedHour < currentHour || (selectedHour === currentHour && selectedMinute < currentMinute)) {
      alert("You cannot book time slots that have already passed. Please select a future time.");
      this.value = currentTimeStr;
    }
  });

  document.getElementById("studentId").value = "<?php echo $_SESSION['student_id'] ?? ''; ?>";

  // Account popup toggle and logout
  const accountBtn = document.getElementById("accountBtn");
  const accountPopup = document.getElementById("accountPopup");

  accountBtn.addEventListener("click", () => {
    accountPopup.style.display = accountPopup.style.display === "block" ? "none" : "block";
  });

  function logout() {
    window.location.href = "logout.php";
  }

  // Auto-close popup when clicking outside
  document.addEventListener("click", function(event) {
    const isClickInside = accountBtn.contains(event.target) || accountPopup.contains(event.target);
    if (!isClickInside) {
      accountPopup.style.display = "none";
    }
  });

  // Add function to update chairs dropdown
  function updateChairsDropdown(maxChairs) {
    const chairsDropdown = document.getElementById('chairs');
    if (!chairsDropdown) return; // Safety check
    
    chairsDropdown.innerHTML = '<option value="">Select Number of Chairs</option>';
    
    // If maxChairs is null/undefined/0, show all 4 chairs
    const chairsToShow = maxChairs > 0 ? maxChairs : 4;
    
    for (let i = 1; i <= chairsToShow; i++) {
        const option = document.createElement('option');
        option.value = i;
        option.textContent = `${i} chair${i > 1 ? 's' : ''}`;
        chairsDropdown.appendChild(option);
    }
  }

  // Initialize chairs dropdown with all options when page loads
  document.addEventListener('DOMContentLoaded', function() {
    updateChairsDropdown(4);
  });
</script>
</body>
</html>
