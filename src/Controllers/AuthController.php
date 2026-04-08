<?php
/**
 * Authentication Controller
 * Handles login, logout, and session management
 */

namespace App\Controllers;

use App\Models\User;

class AuthController {
    private $userModel;

    public function __construct($db) {
        $this->userModel = new User($db);
    }

    /**
     * Show login page
     */
    public function showLogin() {
        return 'login';
    }

    /**
     * Handle login request
     */
    public function login($username, $password) {
        if (empty($username) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Username and password are required'
            ];
        }

        $user = $this->userModel->validateLogin($username, $password);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid username or password'
            ];
        }

        // Create session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['fullname'] = User::getFullName($user);
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();

        return [
            'success' => true,
            'message' => 'Login successful',
            'redirect' => '/dashboard'
        ];
    }

    /**
     * Handle logout
     */
    public function logout() {
        session_unset();
        session_destroy();

        return [
            'success' => true,
            'message' => 'Logged out successfully',
            'redirect' => '/login'
        ];
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    /**
     * Get current user
     */
    public static function getCurrentUser() {
        if (!self::isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'fullname' => $_SESSION['fullname']
        ];
    }

    /**
     * Require authentication
     */
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Check session timeout
     */
    public static function checkSessionTimeout() {
        if (!isset($_SESSION['login_time'])) {
            return;
        }

        $timeout = SESSION_TIMEOUT ?? 3600;

        if (time() - $_SESSION['login_time'] > $timeout) {
            session_unset();
            session_destroy();
            header('Location: /login?expired=1');
            exit;
        }

        // Update login time on each request
        $_SESSION['login_time'] = time();
    }
}