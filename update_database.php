<?php
$conn = include 'db_connect.php';

// Add chairs_used column if it doesn't exist
$alterTableQuery = "ALTER TABLE reservations ADD COLUMN IF NOT EXISTS chairs_used INT DEFAULT 4";
if ($conn->query($alterTableQuery)) {
    echo "Successfully added chairs_used column\n";
} else {
    echo "Error adding chairs_used column: " . $conn->error . "\n";
}

// Update existing records to set default chair usage
$updateQuery = "UPDATE reservations SET chairs_used = 4 WHERE chairs_used IS NULL";
if ($conn->query($updateQuery)) {
    echo "Successfully updated existing records\n";
} else {
    echo "Error updating existing records: " . $conn->error . "\n";
}

$conn->close();
?> 