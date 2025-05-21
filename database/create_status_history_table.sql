CREATE TABLE IF NOT EXISTS donation_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donation_id INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    comment TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donation_id) REFERENCES donations(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add index for faster queries
CREATE INDEX idx_donation_status_history_donation_id ON donation_status_history(donation_id); 