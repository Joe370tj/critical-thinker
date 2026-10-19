<?php
/**
 * One-time database installer / migrator for EMMN Security
 * Run from CLI: php config/install_db.php
 * Or visit once via browser after setup.
 */
define('EMMN_SECURITY', true);
require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/Database.php';

echo "Installing / updating EMMN Security database...\n";

$isSqlite = defined('USE_SQLITE') && USE_SQLITE;

if ($isSqlite) {
    $autoInc = 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $datetime = "TEXT DEFAULT (datetime('now'))";
    $engine = '';
} else {
    $autoInc = 'INT AUTO_INCREMENT PRIMARY KEY';
    $datetime = 'DATETIME DEFAULT CURRENT_TIMESTAMP';
    $engine = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';
}

$statements = [
"CREATE TABLE IF NOT EXISTS users (
    id $autoInc,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    company_name VARCHAR(150) DEFAULT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'client',
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at $datetime,
    updated_at $datetime
) $engine",

"CREATE TABLE IF NOT EXISTS suppliers (
    id $autoInc,
    client_id INTEGER NOT NULL,
    name VARCHAR(150) NOT NULL,
    service VARCHAR(150) DEFAULT NULL,
    contact_email VARCHAR(100) DEFAULT NULL,
    contact_phone VARCHAR(30) DEFAULT NULL,
    risk_level VARCHAR(20) DEFAULT 'Medium',
    risk_score INTEGER DEFAULT 50,
    status VARCHAR(20) DEFAULT 'pending',
    notes TEXT,
    created_at $datetime,
    updated_at $datetime
) $engine",

"CREATE TABLE IF NOT EXISTS assessments (
    id $autoInc,
    supplier_id INTEGER NOT NULL,
    client_id INTEGER NOT NULL,
    security_score INTEGER DEFAULT 0,
    data_score INTEGER DEFAULT 0,
    access_score INTEGER DEFAULT 0,
    response_score INTEGER DEFAULT 0,
    third_party_score INTEGER DEFAULT 0,
    overall_score INTEGER DEFAULT 0,
    risk_level VARCHAR(20) DEFAULT 'Medium',
    status VARCHAR(20) DEFAULT 'draft',
    notes TEXT,
    created_at $datetime,
    updated_at $datetime
) $engine",

"CREATE TABLE IF NOT EXISTS messages (
    id $autoInc,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) DEFAULT NULL,
    message TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'unread',
    created_at $datetime
) $engine",

"CREATE TABLE IF NOT EXISTS login_attempts (
    id $autoInc,
    username VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT,
    created_at $datetime
) $engine",

"CREATE TABLE IF NOT EXISTS activity_logs (
    id $autoInc,
    user_id INTEGER DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT,
    created_at $datetime
) $engine",
];

foreach ($statements as $sql) {
    try {
        $db->exec($sql);
        echo "OK: table created/verified\n";
    } catch (PDOException $e) {
        echo "ERR: " . $e->getMessage() . "\n";
    }
}

// Seed default admin if not exists
$admin = $db->query("SELECT id FROM users WHERE username = 'admin'")->fetch();
if (!$admin) {
    $hash = password_hash('admin123', PASSWORD_BCRYPT);
    $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name, role, status) VALUES (?, ?, ?, ?, 'admin', 'active')");
    $stmt->execute(['admin', 'admin@emmnsecurity.local', $hash, 'System Administrator']);
    echo "Seeded admin user (admin / admin123)\n";
} else {
    echo "Admin user already exists\n";
}

// Sample suppliers for demo
$count = (int) $db->query("SELECT COUNT(*) FROM suppliers")->fetchColumn();
if ($count === 0) {
    $samples = [
        ['Techlogix Ltd', 'Cloud Hosting', 'High', 35],
        ['DataBridge Inc', 'Data Analytics', 'High', 40],
        ['SecureNet GmbH', 'Network Security', 'Medium', 65],
        ['SafeCloud SA', 'Backup Services', 'Low', 85],
        ['PayGate Namibia', 'Payment Gateway', 'Medium', 58],
    ];
    $stmt = $db->prepare("INSERT INTO suppliers (client_id, name, service, risk_level, risk_score, status) VALUES (0, ?, ?, ?, ?, 'assessed')");
    foreach ($samples as $s) {
        $stmt->execute($s);
    }
    echo "Seeded sample suppliers\n";
}

echo "Done.\n";
