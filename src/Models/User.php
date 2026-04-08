<?php
/**
 * User Model
 * Handles user/doctor authentication and profile
 */

namespace App\Models;

class User extends BaseModel {
    protected $table = 'user';

    /**
     * Get user by email
     */
    public function getByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = ?";
        $stmt = $this->query($sql, [$email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get user by username
     */
    public function getByUsername($username) {
        $sql = "SELECT * FROM {$this->table} WHERE username = ?";
        $stmt = $this->query($sql, [$username]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Validate login credentials
     */
    public function validateLogin($username, $password) {
        $user = $this->getByUsername($username);

        if (!$user) {
            return false;
        }

        // Check password - try both plain text and hashed
        if ($password === $user['password'] || password_verify($password, $user['password'])) {
            unset($user['password']); // Don't return password
            return $user;
        }

        return false;
    }

    /**
     * Create new user
     */
    public function create($data) {
        $requiredFields = ['fname', 'username', 'email', 'password', 'contact_no'];

        foreach ($requiredFields as $field) {
            if (empty($data[$field] ?? null)) {
                throw new \Exception("Field '{$field}' is required");
            }
        }

        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        // Set default values
        $data['mname'] = $data['mname'] ?? '';
        $data['lname'] = $data['lname'] ?? '';
        $data['dob'] = $data['dob'] ?? date('Y-m-d');
        $data['gender'] = $data['gender'] ?? 'M';
        $data['address'] = $data['address'] ?? '';
        $data['city'] = $data['city'] ?? '';
        $data['state'] = $data['state'] ?? '';
        $data['country'] = $data['country'] ?? '';
        $data['zip'] = $data['zip'] ?? '';

        return $this->insert($data);
    }

    /**
     * Update user password
     */
    public function updatePassword($id, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $this->update($id, ['password' => $hashedPassword]);
        return true;
    }

    /**
     * Update user profile
     */
    public function updateProfile($id, $data) {
        // Don't allow updating sensitive fields
        unset($data['id'], $data['password'], $data['username']);

        $this->update($id, $data);
        return true;
    }

    /**
     * Get user full name
     */
    public static function getFullName($user) {
        return trim("{$user['fname']} {$user['mname']} {$user['lname']}");
    }
}
?>
