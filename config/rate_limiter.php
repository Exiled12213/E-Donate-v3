<?php
require_once 'logger.php';

class RateLimiter {
    private $conn;
    private $attempts_table = 'login_attempts';
    private $max_attempts = 5;
    private $lockout_time = 900; // 15 minutes in seconds

    public function __construct($db_connection) {
        $this->conn = $db_connection;
        $this->initTable();
    }

    private function initTable() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->attempts_table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            email VARCHAR(255) NOT NULL,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (ip_address, email, timestamp)
        )";
        
        if (!$this->conn->query($sql)) {
            Logger::error('RateLimiter', 'Failed to create attempts table: ' . $this->conn->error);
        }
    }

    public function isBlocked($ip_address, $email) {
        // Clean up old attempts
        $this->cleanupOldAttempts();

        // Count recent attempts
        $sql = "SELECT COUNT(*) as attempt_count FROM {$this->attempts_table} 
                WHERE (ip_address = ? OR email = ?) 
                AND timestamp > DATE_SUB(NOW(), INTERVAL ? SECOND)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssi', $ip_address, $email, $this->lockout_time);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['attempt_count'] >= $this->max_attempts;
    }

    public function logAttempt($ip_address, $email) {
        $sql = "INSERT INTO {$this->attempts_table} (ip_address, email) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ss', $ip_address, $email);
        
        if (!$stmt->execute()) {
            Logger::error('RateLimiter', 'Failed to log attempt: ' . $stmt->error);
        }
    }

    private function cleanupOldAttempts() {
        $sql = "DELETE FROM {$this->attempts_table} WHERE timestamp < DATE_SUB(NOW(), INTERVAL ? SECOND)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $this->lockout_time);
        
        if (!$stmt->execute()) {
            Logger::error('RateLimiter', 'Failed to cleanup old attempts: ' . $stmt->error);
        }
    }

    public function getRemainingAttempts($ip_address, $email) {
        $sql = "SELECT COUNT(*) as attempt_count FROM {$this->attempts_table} 
                WHERE (ip_address = ? OR email = ?) 
                AND timestamp > DATE_SUB(NOW(), INTERVAL ? SECOND)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssi', $ip_address, $email, $this->lockout_time);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return max(0, $this->max_attempts - $row['attempt_count']);
    }
}
?> 