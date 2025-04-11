-- Rename existing chairs column to chairs_used to match our new implementation
ALTER TABLE reservations 
CHANGE COLUMN chairs chairs_used INT NOT NULL;

-- Add constraint to ensure chairs_used is between 1 and 4
ALTER TABLE reservations
ADD CONSTRAINT chk_chairs_used 
CHECK (chairs_used >= 1 AND chairs_used <= 4);

-- Update any NULL values to default to 4 chairs
UPDATE reservations 
SET chairs_used = 4 
WHERE chairs_used IS NULL;

-- Add index for faster queries on table status and time
ALTER TABLE reservations 
ADD INDEX idx_table_status_time (table_number, status, reservation_time);

-- Update existing expired reservations
UPDATE reservations 
SET status = 'expired' 
WHERE status = 'reserved' 
AND TIMESTAMPDIFF(MINUTE, reservation_time, NOW()) >= 60; 