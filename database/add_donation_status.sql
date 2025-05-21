-- Add status and admin_notes columns to donations table if they don't exist
ALTER TABLE donations
ADD COLUMN IF NOT EXISTS status ENUM('pending', 'accepted', 'declined') DEFAULT 'pending',
ADD COLUMN IF NOT EXISTS admin_notes TEXT,
ADD COLUMN IF NOT EXISTS reviewed_at TIMESTAMP NULL; 