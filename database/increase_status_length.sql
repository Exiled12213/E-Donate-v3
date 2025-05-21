-- Increase the length of the status column in donations table
USE e_donate;

-- Modify the status column to VARCHAR(20) to accommodate longer status values
ALTER TABLE donations MODIFY COLUMN status VARCHAR(20); 