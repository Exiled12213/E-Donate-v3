-- Add missing columns to donations table - simpler version
USE e_donate;

-- Try to add admin_notes column (using ALTER IGNORE to prevent errors if column exists)
ALTER TABLE donations ADD COLUMN admin_notes TEXT NULL AFTER status;

-- Try to add reviewed_at column
ALTER TABLE donations ADD COLUMN reviewed_at TIMESTAMP NULL AFTER admin_notes;

-- Try to add updated_at column
ALTER TABLE donations ADD COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP; 