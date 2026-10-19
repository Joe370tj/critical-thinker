<?php
/**
 * =============================================
 * EMMN SECURITY SOLUTIONS
 * Constants & Personal Details
 * =============================================
 * 
 * This file contains all company information,
 * contact details, and system constants.
 * 
 * @package EMMN Security
 * @version 2.0.0
 */

// ============================================
// PREVENT DIRECT ACCESS
// ============================================
if (!defined('EMMN_SECURITY')) {
    define('EMMN_SECURITY', true);
}

// ============================================
// COMPANY INFORMATION
// ============================================
define('COMPANY_NAME', 'EMMN Security Solutions');
define('COMPANY_SHORT_NAME', 'EMMN Security');
define('COMPANY_TAGLINE', 'Supply Chain Risk Checker');
define('COMPANY_DESCRIPTION', 'A web-based cyber security risk assessment system for supply chain security');
define('COMPANY_FOUNDED', '2024');
define('COMPANY_VERSION', '2.2.0');

// ============================================
// CONTACT DETAILS
// ============================================
define('CONTACT_PHONE_1', '0816074626');
define('CONTACT_PHONE_2', '0816606306');
define('CONTACT_PHONE_3', '0816279764');
define('CONTACT_PHONE_4', '0812218855');
define('CONTACT_EMAIL', 'emmnsecuritysolution4@gmail.com');
define('CONTACT_ADDRESS', 'Windhoek, Namibia');
define('CONTACT_CITY', 'Windhoek');
define('CONTACT_COUNTRY', 'Namibia');

// ============================================
// WORKING HOURS
// ============================================
define('WORKING_HOURS_WEEKDAY', 'Monday - Friday: 8:00 AM - 6:00 PM');
define('WORKING_HOURS_SATURDAY', 'Saturday: 9:00 AM - 1:00 PM');
define('WORKING_HOURS_SUNDAY', 'Sunday: Closed');

// ============================================
// SOCIAL MEDIA (Optional)
// ============================================
define('SOCIAL_LINKEDIN', '#');

// ============================================
// SITE URL CONFIGURATION
// ============================================
// Change this to match your folder name
define('SITE_URL', '/'); // Relative to web root (public_html). Change if deployed in subfolder.
define('SITE_NAME', COMPANY_NAME);
define('SITE_TAGLINE', COMPANY_TAGLINE);

// ============================================
// PATHS
// ============================================
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('CLIENT_PATH', ROOT_PATH . '/client');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('UPLOAD_PATH', ROOT_PATH . '/uploads');

// ============================================
// SECURITY CONFIGURATION
// ============================================
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('MAX_LOGIN_ATTEMPTS', 5); // Max failed attempts
define('LOCKOUT_TIME', 900); // 15 minutes lockout
define('PASSWORD_MIN_LENGTH', 8); // Minimum password length
define('CSRF_TOKEN_EXPIRY', 3600); // 1 hour

// ============================================
// THEME COLORS (Dark Gray + Red + White)
// ============================================
define('THEME_PRIMARY', '#1a1a1a'); // Dark Gray
define('THEME_SECONDARY', '#dc3545'); // Red
define('THEME_ACCENT', '#ff4757'); // Light Red
define('THEME_WHITE', '#ffffff'); // White

// Risk Level Colors
define('RISK_COLOR_LOW', '#28a745'); // Green
define('RISK_COLOR_MEDIUM', '#ffc107'); // Yellow/Orange
define('RISK_COLOR_HIGH', '#dc3545'); // Red
define('RISK_COLOR_CRITICAL', '#a71d2a'); // Dark Red
define('RISK_COLOR_INFO', '#17a2b8'); // Blue

// ============================================
// TIMEZONE
// ============================================
date_default_timezone_set('Africa/Windhoek');

// ============================================
// ERROR REPORTING (Set to 0 in production)
// ============================================
define('DEBUG_MODE', true);
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ============================================
// EMAIL CONFIGURATION (Optional - PHPMailer)
// ============================================
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'emmnsecuritysolution4@gmail.com');
define('MAIL_PASSWORD', 'your-app-password-here');
define('MAIL_FROM_NAME', COMPANY_NAME);
define('MAIL_FROM_EMAIL', CONTACT_EMAIL);
?>
