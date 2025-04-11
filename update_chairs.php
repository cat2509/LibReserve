<?php
$conn = require 'db_connect.php';

$queries = [
    // Rename existing chairs column to chairs_used
    "ALTER TABLE reservations CHANGE COLUMN chairs chairs_used INT NOT NULL",
    
    // Add constraint to ensure chairs_used is between 1 and 4
    "ALTER TABLE reservations ADD CONSTRAINT chk_chairs_used CHECK (chairs_used >= 1 AND chairs_used <= 4)",
    
    // Update any NULL values to default to 4 chairs
    "UPDATE reservations SET chairs_used = 4 WHERE chairs_used IS NULL",
    
    // Add index for faster queries
    "ALTER TABLE reservations ADD INDEX idx_table_status_time (table_number, status, reservation_time)",
    
    // Update existing expired reservations
    "UPDATE reservations SET status = 'expired' WHERE status = 'reserved' AND TIMESTAMPDIFF(MINUTE, reservation_time, NOW()) >= 60"
];

foreach ($queries as $query) {
    try {
        if ($conn->query($query)) {
            echo "Success: " . $query . "\n";
        } else {
            echo "Error: " . $conn->error . " for query: " . $query . "\n";
        }
    } catch (Exception $e) {
        // If the error is about existing constraint/index, we can safely ignore it
        if (strpos($e->getMessage(), 'Duplicate') === false) {
            echo "Error: " . $e->getMessage() . " for query: " . $query . "\n";
        } else {
            echo "Note: Constraint/Index already exists for query: " . $query . "\n";
        }
    }
}

$conn->close();
echo "Database update completed!\n";
?> 