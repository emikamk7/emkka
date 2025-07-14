<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Database configuration
$host = 'localhost';
$dbname = 'financial_dashboard';
$username = 'root';
$password = '';

// Data file path (fallback for shared hosting without database)
$dataFile = '../data/transactions.json';

// Simple file-based storage class
class FileStorage {
    private $dataFile;
    
    public function __construct($dataFile) {
        $this->dataFile = $dataFile;
        $this->ensureDataFileExists();
    }
    
    private function ensureDataFileExists() {
        if (!file_exists($this->dataFile)) {
            $dir = dirname($this->dataFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents($this->dataFile, json_encode([]));
        }
    }
    
    public function getTransactions() {
        $data = file_get_contents($this->dataFile);
        return json_decode($data, true) ?: [];
    }
    
    public function saveTransactions($transactions) {
        file_put_contents($this->dataFile, json_encode($transactions, JSON_PRETTY_PRINT));
    }
    
    public function addTransaction($transaction) {
        $transactions = $this->getTransactions();
        $transaction['id'] = uniqid();
        $transaction['amount'] = floatval($transaction['amount']);
        $transactions[] = $transaction;
        $this->saveTransactions($transactions);
        return $transaction;
    }
    
    public function updateTransaction($id, $updates) {
        $transactions = $this->getTransactions();
        foreach ($transactions as &$transaction) {
            if ($transaction['id'] === $id) {
                $transaction = array_merge($transaction, $updates);
                $transaction['amount'] = floatval($transaction['amount']);
                $this->saveTransactions($transactions);
                return $transaction;
            }
        }
        return null;
    }
    
    public function deleteTransaction($id) {
        $transactions = $this->getTransactions();
        $originalCount = count($transactions);
        $transactions = array_filter($transactions, function($t) use ($id) {
            return $t['id'] !== $id;
        });
        
        if (count($transactions) < $originalCount) {
            $this->saveTransactions(array_values($transactions));
            return true;
        }
        return false;
    }
}

// Initialize storage
$storage = new FileStorage($dataFile);

// Get request method and data
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// Handle different HTTP methods
try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $transactions = $storage->getTransactions();
                $transaction = null;
                foreach ($transactions as $t) {
                    if ($t['id'] === $_GET['id']) {
                        $transaction = $t;
                        break;
                    }
                }
                
                if ($transaction) {
                    echo json_encode($transaction);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Transaction not found']);
                }
            } else {
                $transactions = $storage->getTransactions();
                echo json_encode($transactions);
            }
            break;
            
        case 'POST':
            if (!$input) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid input']);
                break;
            }
            
            // Validate required fields
            $requiredFields = ['description', 'amount', 'date', 'category', 'type'];
            foreach ($requiredFields as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    http_response_code(400);
                    echo json_encode(['error' => "Missing required field: $field"]);
                    exit;
                }
            }
            
            // Validate category
            $validCategories = ['salario', 'alimentacao', 'transporte', 'saude', 'lazer', 'ayla', 'outros'];
            if (!in_array($input['category'], $validCategories)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid category']);
                break;
            }
            
            // Validate type
            $validTypes = ['income', 'expense'];
            if (!in_array($input['type'], $validTypes)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid type']);
                break;
            }
            
            // Validate amount
            if (!is_numeric($input['amount']) || floatval($input['amount']) <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Amount must be a positive number']);
                break;
            }
            
            $transaction = $storage->addTransaction($input);
            http_response_code(201);
            echo json_encode($transaction);
            break;
            
        case 'PUT':
            if (!isset($_GET['id']) || !$input) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid request']);
                break;
            }
            
            // Validate fields if present
            if (isset($input['category'])) {
                $validCategories = ['salario', 'alimentacao', 'transporte', 'saude', 'lazer', 'ayla', 'outros'];
                if (!in_array($input['category'], $validCategories)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid category']);
                    break;
                }
            }
            
            if (isset($input['type'])) {
                $validTypes = ['income', 'expense'];
                if (!in_array($input['type'], $validTypes)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid type']);
                    break;
                }
            }
            
            if (isset($input['amount'])) {
                if (!is_numeric($input['amount']) || floatval($input['amount']) <= 0) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Amount must be a positive number']);
                    break;
                }
            }
            
            $transaction = $storage->updateTransaction($_GET['id'], $input);
            if ($transaction) {
                echo json_encode($transaction);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Transaction not found']);
            }
            break;
            
        case 'DELETE':
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Transaction ID required']);
                break;
            }
            
            $success = $storage->deleteTransaction($_GET['id']);
            if ($success) {
                http_response_code(204);
                echo '';
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Transaction not found']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>