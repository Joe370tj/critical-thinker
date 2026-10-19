<?php
/**
 * =============================================
 * EMMN SECURITY SOLUTIONS
 * Authentication & Security Functions
 * =============================================
 * 
 * This file handles user authentication,
 * session security, and access control.
 * 
 * @package EMMN Security
 * @version 2.0.0
 */

// ============================================
// PREVENT DIRECT ACCESS
// ============================================
if (!defined('EMMN_SECURITY')) {
    die('Direct access not permitted');
}

// ============================================
// SESSION SECURITY
// ============================================

/**
 * Regenerate session ID for security
 */
function regenerateSession() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

/**
 * Check if session is valid (timeout, IP, user agent)
 */
function isSessionValid() {
    if (!isset($_SESSION['created_at'])) {
        return false;
    }
    
    // Check session timeout
    if (time() - $_SESSION['created_at'] > SESSION_TIMEOUT) {
        return false;
    }
    
    // Check IP address
    if (isset($_SESSION['ip']) && $_SESSION['ip'] !== $_SERVER['REMOTE_ADDR']) {
        return false;
    }
    
    // Check user agent
    if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        return false;
    }
    
    return true;
}

/**
 * Create a secure session for logged in user
 */
function createSecureSession($user) {
    regenerateSession();
    
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['company_name'] = $user['company_name'] ?? null;
    $_SESSION['created_at'] = time();
    $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    return isSessionValid();
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if user is client
 */
function isClient() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'client';
}

/**
 * Require login (redirect if not logged in)
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect('admin/login.php');
    }
}

/**
 * Require admin role
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        redirect('admin/dashboard.php');
    }
}

/**
 * Require client role
 */
function requireClient() {
    requireLogin();
    if (!isClient()) {
        redirect('../admin/dashboard.php');
    }
}

// ============================================
// PASSWORD SECURITY
// ============================================

/**
 * Hash a password using bcrypt
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

/**
 * Verify a password against its hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Check password strength
 */
function isPasswordStrong($password) {
    if (strlen($password) < PASSWORD_MIN_LENGTH) return false;
    if (!preg_match('/[A-Z]/', $password)) return false;
    if (!preg_match('/[a-z]/', $password)) return false;
    if (!preg_match('/[0-9]/', $password)) return false;
    return true;
}

/**
 * Get password strength message
 */
function getPasswordStrengthMessage($password) {
    $messages = [];
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $messages[] = "At least " . PASSWORD_MIN_LENGTH . " characters";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $messages[] = "One uppercase letter";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $messages[] = "One lowercase letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $messages[] = "One number";
    }
    return $messages;
}

// ============================================
// CSRF PROTECTION
// ============================================

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    if (empty($_SESSION['csrf_token'])) return false;
    if (empty($_SESSION['csrf_token_time'])) return false;
    if (time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_EXPIRY) return false;
    return hash_equals($_SESSION['csrf_token'], $token);
}

// ============================================
// LOGIN ATTEMPT LIMITING
// ============================================

/**
 * Check if user can attempt login
 */
function canAttemptLogin($username) {
    global $db;
    try {
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM login_attempts 
             WHERE username = ? 
             AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)"
        );
        $stmt->execute([$username, LOCKOUT_TIME]);
        return $stmt->fetchColumn() < MAX_LOGIN_ATTEMPTS;
    } catch (PDOException $e) {
        return true;
    }
}

/**
 * Log a failed login attempt
 */
function logFailedLogin($username) {
    global $db;
    try {
        $stmt = $db->prepare(
            "INSERT INTO login_attempts (username, ip_address, user_agent) 
             VALUES (?, ?, ?)"
        );
        $stmt->execute([
            $username,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    } catch (PDOException $e) {
        // Silent fail
    }
}

/**
 * Clear failed login attempts
 */
function clearLoginAttempts($username) {
    global $db;
    try {
        $stmt = $db->prepare("DELETE FROM login_attempts WHERE username = ?");
        $stmt->execute([$username]);
    } catch (PDOException $e) {
        // Silent fail
    }
}

// ============================================
// ACTIVITY LOGGING
// ============================================

/**
 * Log user activity
 */
function logActivity($userId, $action, $details = null) {
    global $db;
    try {
        $stmt = $db->prepare(
            "INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent) 
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $userId,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// ============================================
// USER FUNCTIONS
// ============================================

/**
 * Get user by ID
 */
function getUserById($id) {
    return dbFetchOne("SELECT * FROM users WHERE id = ?", [$id]);
}

/**
 * Get user by username or email
 */
function getUserByUsernameOrEmail($username) {
    return dbFetchOne(
        "SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active'",
        [$username, $username]
    );
}

/**
 * Get current logged in user
 */
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    return getUserById($_SESSION['user_id']);
}

// ============================================
// INPUT SANITIZATION
// ============================================

/**
 * Sanitize user input
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize for SQL (use prepared statements instead!)
 */
function cleanInput($input) {
    return trim($input);
}

// ============================================
// REDIRECT HELPER
// ============================================

/**
 * Redirect to a URL
 */
function redirect($url) {
    if (strpos($url, 'http') !== 0) {
        $url = SITE_URL . $url;
    }
    header("Location: " . $url);
    exit();
}

// ============================================
// LOGOUT FUNCTION
// ============================================

/**
 * Logout user
 */
function logoutUser() {
    if (isLoggedIn()) {
        logActivity($_SESSION['user_id'], 'LOGOUT', 'User logged out');
    }
    
    // Clear all session data
    $_SESSION = [];
    
    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    // Destroy session
    session_destroy();
}
?>
