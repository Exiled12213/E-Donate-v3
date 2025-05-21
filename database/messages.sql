CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donation_id INT NOT NULL,
    sender_id INT NOT NULL,
    receiver_email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (donation_id) REFERENCES donations(id),
    FOREIGN KEY (sender_id) REFERENCES users(id)
); 