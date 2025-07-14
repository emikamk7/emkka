<?php
// Configuration file for the Financial Dashboard

// Database configuration (if using MySQL/PostgreSQL)
define('DB_HOST', 'localhost');
define('DB_NAME', 'financial_dashboard');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application settings
define('APP_NAME', 'Mark Finanças');
define('APP_VERSION', '1.0.0');
define('APP_TIMEZONE', 'America/Sao_Paulo');

// File storage settings
define('DATA_DIR', __DIR__ . '/data/');
define('TRANSACTIONS_FILE', DATA_DIR . 'transactions.json');
define('BACKUP_DIR', DATA_DIR . 'backups/');

// API settings
define('API_VERSION', 'v1');
define('API_BASE_URL', '/api/');

// Security settings
define('ALLOWED_ORIGINS', ['*']); // Change to specific domains in production
define('MAX_TRANSACTIONS_PER_REQUEST', 100);
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB

// Default categories and their configurations
define('DEFAULT_CATEGORIES', [
    'salario' => [
        'label' => 'Salário',
        'color' => '#4CAF50',
        'icon' => 'briefcase'
    ],
    'alimentacao' => [
        'label' => 'Alimentação',
        'color' => '#FF6384',
        'icon' => 'utensils'
    ],
    'transporte' => [
        'label' => 'Transporte',
        'color' => '#36A2EB',
        'icon' => 'car'
    ],
    'saude' => [
        'label' => 'Saúde',
        'color' => '#FFCE56',
        'icon' => 'heart'
    ],
    'lazer' => [
        'label' => 'Lazer',
        'color' => '#4BC0C0',
        'icon' => 'gamepad-2'
    ],
    'ayla' => [
        'label' => 'Ayla',
        'color' => '#FF9F43',
        'icon' => 'user'
    ],
    'outros' => [
        'label' => 'Outros',
        'color' => '#9966FF',
        'icon' => 'more-horizontal'
    ]
]);

// Currency settings
define('CURRENCY_CODE', 'BRL');
define('CURRENCY_SYMBOL', 'R$');
define('CURRENCY_DECIMAL_PLACES', 2);

// Backup settings
define('AUTO_BACKUP_ENABLED', true);
define('BACKUP_INTERVAL_HOURS', 24);
define('MAX_BACKUP_FILES', 30);

// Error reporting (set to false in production)
define('DEBUG_MODE', true);

// Timezone setting
date_default_timezone_set(APP_TIMEZONE);

// Create necessary directories
if (!file_exists(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

if (!file_exists(BACKUP_DIR)) {
    mkdir(BACKUP_DIR, 0755, true);
}

// Utility functions
function formatCurrency($amount) {
    return CURRENCY_SYMBOL . ' ' . number_format($amount, CURRENCY_DECIMAL_PLACES, ',', '.');
}

function createBackup() {
    if (!AUTO_BACKUP_ENABLED || !file_exists(TRANSACTIONS_FILE)) {
        return false;
    }
    
    $backupFile = BACKUP_DIR . 'transactions_' . date('Y-m-d_H-i-s') . '.json';
    return copy(TRANSACTIONS_FILE, $backupFile);
}

function cleanOldBackups() {
    $backupFiles = glob(BACKUP_DIR . 'transactions_*.json');
    
    if (count($backupFiles) <= MAX_BACKUP_FILES) {
        return;
    }
    
    // Sort files by modification time (oldest first)
    usort($backupFiles, function($a, $b) {
        return filemtime($a) - filemtime($b);
    });
    
    // Delete oldest files
    $filesToDelete = array_slice($backupFiles, 0, count($backupFiles) - MAX_BACKUP_FILES);
    foreach ($filesToDelete as $file) {
        unlink($file);
    }
}

// Auto-backup on script initialization
$lastBackup = DATA_DIR . '.last_backup';
if (AUTO_BACKUP_ENABLED && 
    (!file_exists($lastBackup) || 
     (time() - filemtime($lastBackup)) > (BACKUP_INTERVAL_HOURS * 3600))) {
    
    if (createBackup()) {
        touch($lastBackup);
        cleanOldBackups();
    }
}

// Error handling
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// CORS headers function
function setCORSHeaders() {
    foreach (ALLOWED_ORIGINS as $origin) {
        header("Access-Control-Allow-Origin: $origin");
    }
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Max-Age: 86400'); // 24 hours
}

// JSON response function
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Error response function
function errorResponse($message, $statusCode = 400) {
    jsonResponse(['error' => $message], $statusCode);
}

// Validation functions
function validateTransactionData($data) {
    $errors = [];
    
    if (empty($data['description'])) {
        $errors[] = 'Description is required';
    }
    
    if (!isset($data['amount']) || !is_numeric($data['amount']) || $data['amount'] <= 0) {
        $errors[] = 'Amount must be a positive number';
    }
    
    if (empty($data['date'])) {
        $errors[] = 'Date is required';
    } elseif (!strtotime($data['date'])) {
        $errors[] = 'Invalid date format';
    }
    
    if (empty($data['category']) || !array_key_exists($data['category'], DEFAULT_CATEGORIES)) {
        $errors[] = 'Valid category is required';
    }
    
    if (empty($data['type']) || !in_array($data['type'], ['income', 'expense'])) {
        $errors[] = 'Type must be either income or expense';
    }
    
    return $errors;
}

// Database connection function (if using database)
function getDatabaseConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die('Database connection failed: ' . $e->getMessage());
            } else {
                die('Database connection failed');
            }
        }
    }
    
    return $pdo;
}
?>