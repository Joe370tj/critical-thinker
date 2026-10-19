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
    // Secure cookies automatically in HTTPS deployments while keeping local HTTP
    // development usable.
    ini_set('session.cookie_secure', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 1 : 0);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', 1);
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
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header("Content-Security-Policy: default-src 'self'; style-src 'self' https://cdnjs.cloudflare.com 'unsafe-inline'; script-src 'self' https://cdnjs.cloudflare.com; font-src 'self' https://cdnjs.cloudflare.com; img-src 'self' data:; form-action 'self'; frame-ancestors 'self'; base-uri 'self'");
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
