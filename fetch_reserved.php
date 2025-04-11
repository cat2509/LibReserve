<?php
include 'db_connect.php';

$sql = "SELECT table_number FROM seat_reservation WHERE status = 'reserved'";
$result = $conn->query($sql);

$reservedTables = [];
while ($row = $result->fetch_assoc()) {
    $reservedTables[] = $row['table_number'];
}

echo json_encode($reservedTables);
$conn->close();
?>
