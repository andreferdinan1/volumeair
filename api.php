<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Enhanced debugging dengan file log
$logFile = 'api_debug.log';
$timestamp = date('Y-m-d H:i:s');
$logData = "$timestamp | " . $_SERVER['REQUEST_METHOD'] . " | ";
$logData .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . " | ";
$logData .= "Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set') . " | ";
$logData .= "User-Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'not set') . " | ";
$logData .= "Raw Input: " . file_get_contents('php://input') . "\n";

@file_put_contents($logFile, $logData, FILE_APPEND | LOCK_EX);

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_volumeair";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo json_encode([
        "success" => false, 
        "message" => "Database connection failed: " . $e->getMessage(),
        "timestamp" => $timestamp
    ]);
    exit();
}

// Debug: Log semua data yang masuk
$debug_info = [
    "request_method" => $_SERVER['REQUEST_METHOD'],
    "get_data" => $_GET,
    "post_data" => $_POST,
    "raw_input" => file_get_contents('php://input'),
    "content_type" => $_SERVER['CONTENT_TYPE'] ?? 'not set',
    "content_length" => $_SERVER['CONTENT_LENGTH'] ?? 'not set',
    "user_agent" => $_SERVER['HTTP_USER_AGENT'] ?? 'not set',
    "remote_addr" => $_SERVER['REMOTE_ADDR'] ?? 'not set'
];

// Log untuk debugging (opsional, bisa dihapus di production)
error_log("=== INCOMING REQUEST DEBUG ===");
error_log("Method: " . $_SERVER['REQUEST_METHOD']);
error_log("GET: " . print_r($_GET, true));
error_log("POST: " . print_r($_POST, true));
error_log("Raw input: " . file_get_contents('php://input'));
error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));

// Debug headers lengkap
if (function_exists('getallheaders')) {
    $allHeaders = getallheaders();
    error_log("=== ALL HEADERS ===");
    foreach ($allHeaders as $key => $value) {
        error_log("$key: $value");
    }
}

// Test koneksi database
try {
    $testStmt = $pdo->query("SELECT 1");
    error_log("Database connection: OK");
} catch(Exception $e) {
    error_log("Database connection: FAILED - " . $e->getMessage());
}

// Get action parameter dengan prioritas POST > GET > RAW INPUT
$action = '';
$requestData = [];

if (!empty($_POST['action'])) {
    $action = $_POST['action'];
    $requestData = $_POST;
    error_log("Action from POST: $action");
} elseif (!empty($_GET['action'])) {
    $action = $_GET['action'];
    $requestData = $_GET;
    error_log("Action from GET: $action");
}

// Jika masih kosong, coba parse raw input untuk POST
if (empty($action) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw_input = file_get_contents('php://input');
    error_log("Parsing raw input: $raw_input");
    
    if (!empty($raw_input)) {
        // Coba parse sebagai form data
        parse_str($raw_input, $parsed_data);
        if (!empty($parsed_data['action'])) {
            $action = $parsed_data['action'];
            $requestData = $parsed_data;
            // Merge parsed data ke $_POST
            $_POST = array_merge($_POST, $parsed_data);
            error_log("Action from raw input: $action");
        }
        
        // Jika masih gagal, coba parse sebagai JSON
        if (empty($action)) {
            $json_data = json_decode($raw_input, true);
            if (json_last_error() === JSON_ERROR_NONE && !empty($json_data['action'])) {
                $action = $json_data['action'];
                $requestData = $json_data;
                error_log("Action from JSON: $action");
            }
        }
    }
}

error_log("Final action: $action");
error_log("Final request data: " . print_r($requestData, true));

switch($action) {
    case 'add_data':
        // Get data from both GET and POST methods dengan fallback
        $jarak = null;
        $flow = null;
        $status = null;
        
        // Prioritas: POST > GET > parsed data
        if (!empty($requestData['jarak'])) {
            $jarak = floatval($requestData['jarak']);
        } elseif (!empty($_POST['jarak'])) {
            $jarak = floatval($_POST['jarak']);
        } elseif (!empty($_GET['jarak'])) {
            $jarak = floatval($_GET['jarak']);
        }
        
        if (!empty($requestData['flow'])) {
            $flow = floatval($requestData['flow']);
        } elseif (!empty($_POST['flow'])) {
            $flow = floatval($_POST['flow']);
        } elseif (!empty($_GET['flow'])) {
            $flow = floatval($_GET['flow']);
        }
        
        if (!empty($requestData['status'])) {
            $status = $requestData['status'];
        } elseif (!empty($_POST['status'])) {
            $status = $_POST['status'];
        } elseif (!empty($_GET['status'])) {
            $status = $_GET['status'];
        }
        
        error_log("Extracted data - Jarak: $jarak, Flow: $flow, Status: $status");
        
        // Validate data
        if ($jarak === null || $flow === null || $status === null) {
            echo json_encode([
                "success" => false, 
                "message" => "Missing required parameters: jarak, flow, status",
                "received_data" => [
                    "jarak" => $jarak,
                    "flow" => $flow,
                    "status" => $status
                ],
                "debug_info" => $debug_info,
                "timestamp" => $timestamp
            ]);
            exit();
        }
        
        // Validate status values
        $validStatuses = ['ON', 'OFF', 'STANDBY'];
        if (!in_array($status, $validStatuses)) {
            echo json_encode([
                "success" => false, 
                "message" => "Invalid status value. Must be: " . implode(', ', $validStatuses),
                "received_status" => $status,
                "timestamp" => $timestamp
            ]);
            exit();
        }
        
        try {
            // Insert data into database - sesuai dengan tabel log_data_sensor
            $stmt = $pdo->prepare("INSERT INTO log_data_sensor (jarak_cm, flow_rate, status_kran, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
            $result = $stmt->execute([$jarak, $flow, $status]);
            
            if ($result) {
                $lastId = $pdo->lastInsertId();
                $response = [
                    "success" => true, 
                    "message" => "Data saved to log_data_sensor successfully",
                    "data_id" => $lastId,
                    "saved_data" => [
                        "jarak_cm" => $jarak,
                        "flow_rate" => $flow,
                        "status_kran" => $status,
                        "created_at" => $timestamp,
                        "updated_at" => $timestamp
                    ],
                    "timestamp" => $timestamp
                ];
                
                error_log("SUCCESS: Data saved with ID $lastId");
                echo json_encode($response);
            } else {
                error_log("FAILED: Database insert failed");
                echo json_encode([
                    "success" => false, 
                    "message" => "Failed to save data to log_data_sensor",
                    "timestamp" => $timestamp
                ]);
            }
        } catch(PDOException $e) {
            error_log("DATABASE ERROR: " . $e->getMessage());
            echo json_encode([
                "success" => false, 
                "message" => "Database error: " . $e->getMessage(),
                "timestamp" => $timestamp
            ]);
        }
        break;
        
    case 'latest':
        try {
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
            $limit = max(1, min(100, $limit)); // Batasi antara 1-100
            
            $stmt = $pdo->prepare("SELECT * FROM log_data_sensor ORDER BY created_at DESC LIMIT ?");
            $stmt->execute([$limit]);
            $data = $stmt->fetchAll();
            
            echo json_encode([
                "success" => true, 
                "message" => "Data retrieved successfully from log_data_sensor",
                "count" => count($data),
                "limit" => $limit,
                "data" => $data,
                "timestamp" => $timestamp
            ]);
        } catch(PDOException $e) {
            echo json_encode([
                "success" => false, 
                "message" => "Database error: " . $e->getMessage(),
                "timestamp" => $timestamp
            ]);
        }
        break;
        
    case 'health':
        // Check database connection
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM log_data_sensor");
            $result = $stmt->fetch();
            $dbStatus = "OK";
            $totalRecords = $result['total'];
        } catch(Exception $e) {
            $dbStatus = "FAILED: " . $e->getMessage();
            $totalRecords = 0;
        }
        
        echo json_encode([
            "success" => true, 
            "message" => "Server is running",
            "timestamp" => $timestamp,
            "server_info" => [
                "php_version" => phpversion(),
                "request_method" => $_SERVER['REQUEST_METHOD'],
                "server_software" => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
                "database_status" => $dbStatus,
                "total_records" => $totalRecords
            ],
            "system_info" => [
                "memory_usage" => memory_get_usage(true),
                "memory_limit" => ini_get('memory_limit'),
                "max_execution_time" => ini_get('max_execution_time')
            ]
        ]);
        break;
        
    case 'stats':
        try {
            // Statistik data hari ini
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total_today,
                    AVG(jarak_cm) as avg_distance,
                    AVG(flow_rate) as avg_flow,
                    SUM(CASE WHEN status_kran = 'ON' THEN 1 ELSE 0 END) as on_count,
                    SUM(CASE WHEN status_kran = 'OFF' THEN 1 ELSE 0 END) as off_count,
                    SUM(CASE WHEN status_kran = 'STANDBY' THEN 1 ELSE 0 END) as standby_count
                FROM log_data_sensor 
                WHERE DATE(created_at) = CURDATE()
            ");
            $stmt->execute();
            $stats = $stmt->fetch();
            
            echo json_encode([
                "success" => true,
                "message" => "Statistics retrieved successfully",
                "stats" => $stats,
                "timestamp" => $timestamp
            ]);
        } catch(PDOException $e) {
            echo json_encode([
                "success" => false,
                "message" => "Database error: " . $e->getMessage(),
                "timestamp" => $timestamp
            ]);
        }
        break;
        
    default:
        echo json_encode([
            "success" => false, 
            "message" => "Invalid action or missing action parameter",
            "available_actions" => ["add_data", "latest", "health", "stats"],
            "received_action" => $action,
            "debug_info" => $debug_info,
            "timestamp" => $timestamp
        ]);
        break;
}

// Close database connection
$pdo = null;

// Log completion
error_log("Request completed for action: $action");
?>