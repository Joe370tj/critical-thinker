<?php
/**
 * =============================================
 * EMMN SECURITY SOLUTIONS
 * Main Application Configuration
 * =============================================
 * 
 * This is the main configuration file that
 * loads all other config files and initializes
 * the application.
 * 
 * @package EMMN Security
 * @version 2.1.0
 */

// ============================================
// DEFINE APPLICATION CONSTANT
// ============================================
if (!defined('EMMN_SECURITY')) {
    define('EMMN_SECURITY', true);
}

// ============================================
// START SESSION
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    ini_set('session.gc_maxlifetime', 1800); // 30 minutes
    
    session_start();
}

// ============================================
// LOAD CONFIGURATION FILES
// ============================================
require_once __DIR__ . '/constants.php'; // Personal details & constants
require_once __DIR__ . '/Database.php';  // Database connection (case-sensitive)
require_once __DIR__ . '/auth.php';      // Authentication & security
require_once __DIR__ . '/helper.php';    // General helper functions

// ============================================
// APPLICATION INITIALIZATION
// ============================================

// Set default timezone
date_default_timezone_set('Africa/Windhoek');

// Set charset (only if headers not already sent)
if (!headers_sent()) {
    header('Content-Type: text/html; charset=utf-8');
    // Security headers
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// ============================================
// CHECK SESSION VALIDITY
// ============================================
if (isset($_SESSION['user_id']) && function_exists('isSessionValid') && !isSessionValid()) {
    // Session expired - log out
    if (function_exists('logoutUser')) {
        logoutUser();
    }
    $_SESSION['session_expired'] = true;
}
?>
