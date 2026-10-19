<?php
/**
 * =============================================
 * EMMN SECURITY SOLUTIONS
 * Database Connection
 * =============================================
 * 
 * Uses SQLite by default so the app works out of the box
 * without requiring a MySQL server. Switch to MySQL by
 * setting USE_SQLITE to false and configuring credentials.
 * 
 * @package EMMN Security
 * @version 2.1.0
 */

// ============================================
// PREVENT DIRECT ACCESS
// ============================================
if (!defined('EMMN_SECURITY')) {
    die('Direct access not permitted');
}

// ============================================
// DATABASE CONFIGURATION
// ============================================
// Set to true for portable SQLite (recommended for demo/local)
// Set to false and configure below for MySQL/MariaDB
define('USE_SQLITE', true);

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'emmn_security_db');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', 3306);

// SQLite database file path (inside project, outside public web root is ideal)
define('SQLITE_PATH', dirname(__DIR__) . '/data/emmn_security.db');

// ============================================
// DATABASE CONNECTION (PDO)
// ============================================
try {
    if (USE_SQLITE) {
        // Ensure data directory exists
        $dataDir = dirname(SQLITE_PATH);
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }
        
        $dsn = 'sqlite:' . SQLITE_PATH;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $db = new PDO($dsn, null, null, $options);
        // Enable foreign keys for SQLite
        $db->exec('PRAGMA foreign_keys = ON');
    } else {
        $dsn = "mysql:host=" . DB_HOST . 
               ";port=" . DB_PORT . 
               ";dbname=" . DB_NAME . 
               ";charset=" . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE " . DB_CHARSET . "_unicode_ci"
        ];
        
        $db = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    
} catch (PDOException $e) {
    error_log('Database Connection Failed: ' . $e->getMessage());
    
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        die('Database Connection Failed: ' . $e->getMessage());
    } else {
        die('Unable to connect to the database. Please try again later.');
    }
}

// ============================================
// DATABASE HELPER FUNCTIONS
// ============================================

/**
 * Execute a query and return statement
 */
function dbQuery($sql, $params = []) {
    global $db;
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log('Query Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Fetch a single row
 */
function dbFetchOne($sql, $params = []) {
    $stmt = dbQuery($sql, $params);
    return $stmt ? $stmt->fetch() : null;
}

/**
 * Fetch all rows
 */
function dbFetchAll($sql, $params = []) {
    $stmt = dbQuery($sql, $params);
    return $stmt ? $stmt->fetchAll() : [];
}

/**
 * Fetch a single column value
 */
function dbFetchColumn($sql, $params = []) {
    $stmt = dbQuery($sql, $params);
    return $stmt ? $stmt->fetchColumn() : null;
}

/**
 * Insert data and return ID
 */
function dbInsert($table, $data) {
    global $db;
    try {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $db->prepare($sql);
        $stmt->execute(array_values($data));
        return $db->lastInsertId();
    } catch (PDOException $e) {
        error_log('Insert Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Update data
 */
function dbUpdate($table, $data, $where, $whereParams = []) {
    global $db;
    try {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        $params = array_merge(array_values($data), $whereParams);
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log('Update Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Delete data
 */
function dbDelete($table, $where, $params = []) {
    global $db;
    try {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log('Delete Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Count rows
 */
function dbCount($table, $where = '1=1', $params = []) {
    return (int) dbFetchColumn("SELECT COUNT(*) FROM {$table} WHERE {$where}", $params);
}
?>
