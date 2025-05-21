<?php
class PasswordValidator {
    private $minLength = 8;
    private $requireUppercase = true;
    private $requireLowercase = true;
    private $requireNumbers = true;
    private $requireSpecial = true;

    public function validate($password) {
        $errors = [];

        if (strlen($password) < $this->minLength) {
            $errors[] = "Password must be at least {$this->minLength} characters long";
        }

        if ($this->requireUppercase && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }

        if ($this->requireLowercase && !preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }

        if ($this->requireNumbers && !preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }

        if ($this->requireSpecial && !preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "Password must contain at least one special character";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    public function getRequirements() {
        return [
            "Minimum length: {$this->minLength} characters",
            "Must contain uppercase letters",
            "Must contain lowercase letters",
            "Must contain numbers",
            "Must contain special characters"
        ];
    }
}
?> 