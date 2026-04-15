<?php
namespace App\Controllers;

use App\Models\User;

class AuthController {
    private $userModel;

    public function __construct($db) {
        $this->userModel = new User($db);
    }

    // ── Login / Logout ────────────────────────────────────────────────────────

    public function showLogin() { return 'login'; }

    public function login($username, $password) {
        if (empty($username) || empty($password))
            return ['success' => false, 'message' => 'Username and password are required'];

        $user = $this->userModel->validateLogin($username, $password);

        if (!$user) {
            // Differentiate inactive vs wrong password for better UX
            $exists = $this->userModel->getByUsername($username);
            if ($exists && isset($exists['is_active']) && (int)$exists['is_active'] === 0)
                return ['success' => false, 'message' => 'Your account has been deactivated. Contact the doctor.'];
            return ['success' => false, 'message' => 'Invalid username or password'];
        }

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['email']     = $user['email'];
        $_SESSION['fullname']  = User::getFullName($user);
        $_SESSION['role']      = $user['role'] ?? 'doctor';
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time']= time();

        return ['success' => true, 'message' => 'Login successful', 'redirect' => '/dashboard'];
    }

    public function logout() {
        session_unset();
        session_destroy();
        return ['success' => true, 'redirect' => '/login'];
    }

    // ── Session helpers ───────────────────────────────────────────────────────

    public static function isLoggedIn(): bool {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    public static function getRole(): string {
        return $_SESSION['role'] ?? 'doctor';
    }

    /** Returns true if current user has at least the given role level */
    public static function hasRole(string ...$roles): bool {
        return in_array(self::getRole(), $roles, true);
    }

    public static function getCurrentUser(): ?array {
        if (!self::isLoggedIn()) return null;
        return [
            'id'       => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email'    => $_SESSION['email'],
            'fullname' => $_SESSION['fullname'],
            'role'     => $_SESSION['role'] ?? 'doctor',
        ];
    }

    // ── Guards ────────────────────────────────────────────────────────────────

    /** Redirect to login if not authenticated */
    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Require one of the given roles.
     * If the user is logged in but lacks the role → show Access Denied page.
     * If not logged in at all → redirect to login.
     */
    public static function requireRole(string ...$roles): void {
        self::requireLogin();
        if (!self::hasRole(...$roles)) {
            http_response_code(403);
            $roleName = User::roleLabel(self::getRole());
            require __DIR__ . '/../../views/error/403.php';
            exit;
        }
    }

    public static function checkSessionTimeout(): void {
        if (!isset($_SESSION['login_time'])) return;
        $timeout = defined('SESSION_TIMEOUT') ? SESSION_TIMEOUT : 3600;
        if (time() - $_SESSION['login_time'] > $timeout) {
            session_unset();
            session_destroy();
            header('Location: /login?expired=1');
            exit;
        }
        $_SESSION['login_time'] = time();
    }
}
