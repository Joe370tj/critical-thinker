<?php
/**
 * =============================================
 * EMMN SECURITY SOLUTIONS
 * Helper Functions
 * =============================================
 * 
 * This file contains general helper functions
 * used throughout the application.
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
// DATE & TIME HELPERS
// ============================================

/**
 * Format a date
 */
function formatDate($date, $format = 'M d, Y') {
    if (empty($date)) return 'N/A';
    return date($format, strtotime($date));
}

/**
 * Format a datetime
 */
function formatDateTime($datetime, $format = 'M d, Y g:i A') {
    if (empty($datetime)) return 'N/A';
    return date($format, strtotime($datetime));
}

/**
 * Get time ago
 */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if ($difference < 60) return 'Just now';
    if ($difference < 3600) return floor($difference / 60) . ' minutes ago';
    if ($difference < 86400) return floor($difference / 3600) . ' hours ago';
    if ($difference < 604800) return floor($difference / 86400) . ' days ago';
    
    return formatDate($datetime);
}

// ============================================
// CURRENCY HELPERS
// ============================================

/**
 * Format currency in Namibian Dollars
 */
function formatCurrency($amount) {
    if (empty($amount)) return 'N$ 0.00';
    return 'N$ ' . number_format($amount, 2);
}

// ============================================
// RISK LEVEL HELPERS
// ============================================

/**
 * Get risk level badge class
 */
function getRiskBadge($level) {
    $classes = [
        'Low' => 'risk-low',
        'Medium' => 'risk-medium',
        'High' => 'risk-high',
        'Critical' => 'risk-critical'
    ];
    return $classes[$level] ?? 'risk-info';
}

/**
 * Get risk level color
 */
function getRiskColor($level) {
    $colors = [
        'Low' => RISK_COLOR_LOW,
        'Medium' => RISK_COLOR_MEDIUM,
        'High' => RISK_COLOR_HIGH,
        'Critical' => RISK_COLOR_CRITICAL
    ];
    return $colors[$level] ?? RISK_COLOR_INFO;
}

/**
 * Get risk level emoji
 */
function getRiskEmoji($level) {
    $emojis = [
        'Low' => '🟢',
        'Medium' => '🟡',
        'High' => '🔴',
        'Critical' => '🔴'
    ];
    return $emojis[$level] ?? '🔵';
}

/**
 * Calculate risk level from score
 */
function calculateRiskLevel($score) {
    if ($score >= 80) return 'Low';
    if ($score >= 60) return 'Medium';
    if ($score >= 40) return 'High';
    return 'Critical';
}

/**
 * Calculate risk score from 5 factors
 */
function calculateRiskScore($security, $data, $access, $response, $thirdParty) {
    return round(($security + $data + $access + $response + $thirdParty) / 5);
}

// ============================================
// STATUS HELPERS
// ============================================

/**
 * Get status badge class
 */
function getStatusBadge($status) {
    $classes = [
        'active' => 'status-active',
        'inactive' => 'status-inactive',
        'pending' => 'status-pending',
        'approved' => 'status-approved',
        'rejected' => 'status-rejected',
        'assessed' => 'status-active',
        'unread' => 'status-unread',
        'read' => 'status-read',
        'replied' => 'status-replied',
        'draft' => 'status-pending',
        'final' => 'status-active',
        'reviewed' => 'status-info'
    ];
    return $classes[$status] ?? 'status-pending';
}

/**
 * Get status label
 */
function getStatusLabel($status) {
    return ucfirst(str_replace('_', ' ', $status));
}

// ============================================
// STRING HELPERS
// ============================================

/**
 * Truncate a string
 */
function truncate($string, $length = 100, $suffix = '...') {
    if (strlen($string) <= $length) return $string;
    return substr($string, 0, $length) . $suffix;
}

/**
 * Generate a random string
 */
function generateRandomString($length = 16) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Generate a unique code
 */
function generateCode($prefix = '') {
    return $prefix . strtoupper(bin2hex(random_bytes(4)));
}

// ============================================
// FILE HELPERS
// ============================================

/**
 * Get file extension
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Format file size
 */
function formatFileSize($bytes) {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
    if ($bytes < 1073741824) return round($bytes / 1048576, 2) . ' MB';
    return round($bytes / 1073741824, 2) . ' GB';
}

// ============================================
// VALIDATION HELPERS
// ============================================

/**
 * Validate email address
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number
 */
function isValidPhone($phone) {
    return preg_match('/^[0-9+\s\-()]{7,20}$/', $phone);
}

/**
 * Validate URL
 */
function isValidUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

// ============================================
// ARRAY HELPERS
// ============================================

/**
 * Get value from array with default
 */
function arrayGet($array, $key, $default = null) {
    return isset($array[$key]) ? $array[$key] : $default;
}

/**
 * Group array by key
 */
function groupBy($array, $key) {
    $result = [];
    foreach ($array as $item) {
        $groupKey = $item[$key] ?? 'unknown';
        $result[$groupKey][] = $item;
    }
    return $result;
}

// ============================================
// OUTPUT HELPERS
// ============================================

/**
 * Escape HTML output
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Output JSON response
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

/**
 * Debug output
 */
function debug($data, $die = false) {
    echo '<pre style="background:#1a1a1a;color:#fff;padding:15px;border-radius:8px;margin:10px 0;">';
    print_r($data);
    echo '</pre>';
    if ($die) die();
}
?>
