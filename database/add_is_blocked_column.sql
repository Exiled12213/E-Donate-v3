-- Add is_blocked column to users table if it doesn't exist
ALTER TABLE users
ADD COLUMN IF NOT EXISTS is_blocked BOOLEAN DEFAULT FALSE; 