<?php
// DB Connection
$conn = new mysqli("localhost", "root", "", "library_booking");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("DESCRIBE reservations");

if ($result) {
    $hasQrScanned = false;
    
    echo "<h2>Reservations Table Structure</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["Field"] . "</td>";
        echo "<td>" . $row["Type"] . "</td>";
        echo "<td>" . $row["Null"] . "</td>";
        echo "<td>" . $row["Key"] . "</td>";
        echo "<td>" . $row["Default"] . "</td>";
        echo "<td>" . $row["Extra"] . "</td>";
        echo "</tr>";
        
        if ($row["Field"] == "qr_scanned") {
            $hasQrScanned = true;
        }
    }
    
    echo "</table>";
    
    if ($hasQrScanned) {
        echo "<p style='color:green'>The 'qr_scanned' column exists in the table.</p>";
    } else {
        echo "<p style='color:red'>The 'qr_scanned' column does NOT exist in the table.</p>";
    }
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?> 