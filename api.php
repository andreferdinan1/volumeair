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

// Create tables if not exists
createTablesIfNotExists();

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

// Log untuk debugging
error_log("=== INCOMING REQUEST DEBUG ===");
error_log("Method: " . $_SERVER['REQUEST_METHOD']);
error_log("GET: " . print_r($_GET, true));
error_log("POST: " . print_r($_POST, true));
error_log("Raw input: " . file_get_contents('php://input'));

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
        addSensorData($requestData);
        break;
        
    case 'add_shalat_report':
        addShalatReport($requestData);
        break;
        
    case 'get_shalat_reports':
        getShalatReports();
        break;
        
    case 'get_shalat_summary':
        getShalatSummary();
        break;
        
    case 'get_daily_report':
        getDailyReport();
        break;
        
    case 'latest':
        getLatestData();
        break;
        
    case 'stats':
        getStats();
        break;
        
    case 'health':
        healthCheck();
        break;
        
    case 'reset_shalat_data':
        resetShalatData();
        break;
        
    default:
        echo json_encode([
            "success" => false, 
            "message" => "Invalid action or missing action parameter",
            "available_actions" => [
                "add_data", "add_shalat_report", "get_shalat_reports", 
                "get_shalat_summary", "get_daily_report", "latest", 
                "stats", "health", "reset_shalat_data"
            ],
            "received_action" => $action,
            "debug_info" => $debug_info,
            "timestamp" => $timestamp
        ]);
        break;
}

// ==================== FUNCTIONS ====================

function createTablesIfNotExists() {
    global $pdo;
    
    try {
        // Table 1: log_data_sensor (existing, enhanced)
        $pdo->exec("CREATE TABLE IF NOT EXISTS log_data_sensor (
            id INT AUTO_INCREMENT PRIMARY KEY,
            jarak_cm FLOAT NOT NULL,
            flow_rate FLOAT NOT NULL,
            status_kran VARCHAR(20) NOT NULL,
            total_volume FLOAT DEFAULT 0,
            session_volume FLOAT DEFAULT 0,
            current_shalat VARCHAR(20) DEFAULT NULL,
            shalat_water FLOAT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // Table 2: shalat_reports (new)
        $pdo->exec("CREATE TABLE IF NOT EXISTS shalat_reports (
            id INT AUTO_INCREMENT PRIMARY KEY,
            shalat_name VARCHAR(20) NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            total_water FLOAT NOT NULL,
            report_date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_shalat_date (shalat_name, report_date)
        )");
        
        // Table 3: daily_summary (new)
        $pdo->exec("CREATE TABLE IF NOT EXISTS daily_summary (
            id INT AUTO_INCREMENT PRIMARY KEY,
            report_date DATE NOT NULL UNIQUE,
            subuh_water FLOAT DEFAULT 0,
            dzuhur_water FLOAT DEFAULT 0,
            ashar_water FLOAT DEFAULT 0,
            maghrib_water FLOAT DEFAULT 0,
            isya_water FLOAT DEFAULT 0,
            total_daily_water FLOAT DEFAULT 0,
            total_activations INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        error_log("Tables created/checked successfully");
        
    } catch(PDOException $e) {
        error_log("Error creating tables: " . $e->getMessage());
    }
}

function addSensorData($requestData) {
    global $pdo, $timestamp;
    
    // Get data with fallback
    $jarak = getValueFromRequest($requestData, 'jarak');
    $flow = getValueFromRequest($requestData, 'flow');
    $status = getValueFromRequest($requestData, 'status');
    $total_volume = getValueFromRequest($requestData, 'total_volume', 0);
    $session_volume = getValueFromRequest($requestData, 'session_volume', 0);
    $current_shalat = getValueFromRequest($requestData, 'current_shalat', null);
    $shalat_water = getValueFromRequest($requestData, 'shalat_water', 0);
    
    error_log("Extracted sensor data - Jarak: $jarak, Flow: $flow, Status: $status, Current Shalat: $current_shalat");
    
    // Validate required data
    if ($jarak === null || $flow === null || $status === null) {
        echo json_encode([
            "success" => false, 
            "message" => "Missing required parameters: jarak, flow, status",
            "received_data" => [
                "jarak" => $jarak,
                "flow" => $flow,
                "status" => $status
            ],
            "timestamp" => $timestamp
        ]);
        return;
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
        return;
    }
    
    try {
        // Insert data into database
        $stmt = $pdo->prepare("INSERT INTO log_data_sensor 
            (jarak_cm, flow_rate, status_kran, total_volume, session_volume, current_shalat, shalat_water, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            
        $result = $stmt->execute([
            $jarak, $flow, $status, $total_volume, $session_volume, $current_shalat, $shalat_water
        ]);
        
        if ($result) {
            $lastId = $pdo->lastInsertId();
            
            // Update daily summary if there's shalat activity
            if ($current_shalat && $current_shalat !== 'none') {
                updateDailySummary($current_shalat, $shalat_water);
            }
            
            $response = [
                "success" => true, 
                "message" => "Sensor data saved successfully",
                "data_id" => $lastId,
                "saved_data" => [
                    "jarak_cm" => $jarak,
                    "flow_rate" => $flow,
                    "status_kran" => $status,
                    "total_volume" => $total_volume,
                    "session_volume" => $session_volume,
                    "current_shalat" => $current_shalat,
                    "shalat_water" => $shalat_water,
                    "created_at" => $timestamp
                ],
                "timestamp" => $timestamp
            ];
            
            error_log("SUCCESS: Sensor data saved with ID $lastId");
            echo json_encode($response);
        } else {
            error_log("FAILED: Database insert failed");
            echo json_encode([
                "success" => false, 
                "message" => "Failed to save sensor data",
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
}

function addShalatReport($requestData) {
    global $pdo, $timestamp;
    
    $shalat_name = getValueFromRequest($requestData, 'shalat_name');
    $start_time = getValueFromRequest($requestData, 'start_time');
    $end_time = getValueFromRequest($requestData, 'end_time');
    $total_water = getValueFromRequest($requestData, 'total_water', 0);
    
    error_log("Shalat report data - Name: $shalat_name, Water: $total_water");
    
    if (!$shalat_name || !$start_time || !$end_time) {
        echo json_encode([
            "success" => false,
            "message" => "Missing required parameters: shalat_name, start_time, end_time",
            "timestamp" => $timestamp
        ]);
        return;
    }
    
    try {
        $report_date = date('Y-m-d');
        
        // Insert or update shalat report
        $stmt = $pdo->prepare("INSERT INTO shalat_reports 
            (shalat_name, start_time, end_time, total_water, report_date) 
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            total_water = VALUES(total_water), 
            updated_at = NOW()");
            
        $result = $stmt->execute([$shalat_name, $start_time, $end_time, $total_water, $report_date]);
        
        if ($result) {
            // Update daily summary
            updateDailySummary($shalat_name, $total_water, true);
            
            echo json_encode([
                "success" => true,
                "message" => "Shalat report saved successfully",
                "report_data" => [
                    "shalat_name" => $shalat_name,
                    "start_time" => $start_time,
                    "end_time" => $end_time,
                    "total_water" => $total_water,
                    "report_date" => $report_date
                ],
                "timestamp" => $timestamp
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Failed to save shalat report",
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
}

function getShalatReports() {
    global $pdo, $timestamp;
    
    try {
        $date = $_GET['date'] ?? date('Y-m-d');
        $limit = min(100, max(1, intval($_GET['limit'] ?? 50)));
        
        $stmt = $pdo->prepare("SELECT * FROM shalat_reports 
            WHERE report_date = ? 
            ORDER BY start_time ASC 
            LIMIT ?");
        $stmt->execute([$date, $limit]);
        $reports = $stmt->fetchAll();
        
        echo json_encode([
            "success" => true,
            "message" => "Shalat reports retrieved successfully",
            "date" => $date,
            "count" => count($reports),
            "data" => $reports,
            "timestamp" => $timestamp
        ]);
        
    } catch(PDOException $e) {
        echo json_encode([
            "success" => false,
            "message" => "Database error: " . $e->getMessage(),
            "timestamp" => $timestamp
        ]);
    }
}

function getShalatSummary() {
    global $pdo, $timestamp;
    
    try {
        $date = $_GET['date'] ?? date('Y-m-d');
        
        $stmt = $pdo->prepare("SELECT * FROM daily_summary WHERE report_date = ?");
        $stmt->execute([$date]);
        $summary = $stmt->fetch();
        
        if (!$summary) {
            // Create empty summary
            $summary = [
                "report_date" => $date,
                "subuh_water" => 0,
                "dzuhur_water" => 0,
                "ashar_water" => 0,
                "maghrib_water" => 0,
                "isya_water" => 0,
                "total_daily_water" => 0,
                "total_activations" => 0
            ];
        }
        
        // Add percentage calculation
        $total = floatval($summary['total_daily_water']);
        $summary['percentages'] = [
            "subuh" => $total > 0 ? round(($summary['subuh_water'] / $total) * 100, 2) : 0,
            "dzuhur" => $total > 0 ? round(($summary['dzuhur_water'] / $total) * 100, 2) : 0,
            "ashar" => $total > 0 ? round(($summary['ashar_water'] / $total) * 100, 2) : 0,
            "maghrib" => $total > 0 ? round(($summary['maghrib_water'] / $total) * 100, 2) : 0,
            "isya" => $total > 0 ? round(($summary['isya_water'] / $total) * 100, 2) : 0
        ];
        
        echo json_encode([
            "success" => true,
            "message" => "Shalat summary retrieved successfully",
            "data" => $summary,
            "timestamp" => $timestamp
        ]);
        
    } catch(PDOException $e) {
        echo json_encode([
            "success" => false,
            "message" => "Database error: " . $e->getMessage(),
            "timestamp" => $timestamp
        ]);
    }
}

function getDailyReport() {
    global $pdo, $timestamp;
    
    try {
        $days = min(30, max(1, intval($_GET['days'] ?? 7)));
        
        $stmt = $pdo->prepare("SELECT * FROM daily_summary 
            ORDER BY report_date DESC 
            LIMIT ?");
        $stmt->execute([$days]);
        $reports = $stmt->fetchAll();
        
        // Calculate totals
        $totals = [
            "total_days" => count($reports),
            "avg_daily_water" => 0,
            "max_daily_water" => 0,
            "total_water" => 0
        ];
        
        if (!empty($reports)) {
            $total_water = array_sum(array_column($reports, 'total_daily_water'));
            $totals['total_water'] = $total_water;
            $totals['avg_daily_water'] = round($total_water / count($reports), 3);
            $totals['max_daily_water'] = max(array_column($reports, 'total_daily_water'));
        }
        
        echo json_encode([
            "success" => true,
            "message" => "Daily reports retrieved successfully",
            "period" => "$days days",
            "totals" => $totals,
            "count" => count($reports),
            "data" => $reports,
            "timestamp" => $timestamp
        ]);
        
    } catch(PDOException $e) {
        echo json_encode([
            "success" => false,
            "message" => "Database error: " . $e->getMessage(),
            "timestamp" => $timestamp
        ]);
    }
}

function getLatestData() {
    global $pdo, $timestamp;
    
    try {
        $limit = min(100, max(1, intval($_GET['limit'] ?? 10)));
        
        $stmt = $pdo->prepare("SELECT * FROM log_data_sensor ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        $data = $stmt->fetchAll();
        
        echo json_encode([
            "success" => true, 
            "message" => "Latest data retrieved successfully",
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
}

function getStats() {
    global $pdo, $timestamp;
    
    try {
        // Statistik hari ini
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_today,
                AVG(jarak_cm) as avg_distance,
                AVG(flow_rate) as avg_flow,
                SUM(CASE WHEN status_kran = 'ON' THEN 1 ELSE 0 END) as on_count,
                SUM(CASE WHEN status_kran = 'OFF' THEN 1 ELSE 0 END) as off_count,
                SUM(CASE WHEN status_kran = 'STANDBY' THEN 1 ELSE 0 END) as standby_count,
                MAX(total_volume) as current_total_volume
            FROM log_data_sensor 
            WHERE DATE(created_at) = CURDATE()
        ");
        $stmt->execute();
        $stats = $stmt->fetch();
        
        // Statistik shalat hari ini
        $stmt = $pdo->prepare("SELECT * FROM daily_summary WHERE report_date = CURDATE()");
        $stmt->execute();
        $shalat_stats = $stmt->fetch();
        
        echo json_encode([
            "success" => true,
            "message" => "Statistics retrieved successfully",
            "sensor_stats" => $stats,
            "shalat_stats" => $shalat_stats,
            "timestamp" => $timestamp
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            "success" => false,
            "message" => "Database error: " . $e->getMessage(),
            "timestamp" => $timestamp
        ]);
    }
}

function healthCheck() {
    global $pdo, $timestamp;
    
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM log_data_sensor");
        $result = $stmt->fetch();
        $dbStatus = "OK";
        $totalRecords = $result['total'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM shalat_reports");
        $result = $stmt->fetch();
        $totalShalatReports = $result['total'];
        
    } catch(Exception $e) {
        $dbStatus = "FAILED: " . $e->getMessage();
        $totalRecords = 0;
        $totalShalatReports = 0;
    }
    
    echo json_encode([
        "success" => true, 
        "message" => "Server is running - Smart Water System with Shalat Schedule",
        "timestamp" => $timestamp,
        "server_info" => [
            "php_version" => phpversion(),
            "request_method" => $_SERVER['REQUEST_METHOD'],
            "server_software" => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
            "database_status" => $dbStatus,
            "total_sensor_records" => $totalRecords,
            "total_shalat_reports" => $totalShalatReports
        ],
        "system_info" => [
            "memory_usage" => memory_get_usage(true),
            "memory_limit" => ini_get('memory_limit'),
            "max_execution_time" => ini_get('max_execution_time')
        ],
        "shalat_schedule" => [
            "subuh" => "04:00 - 06:00",
            "dzuhur" => "12:00 - 14:00", 
            "ashar" => "15:30 - 17:30",
            "maghrib" => "18:00 - 20:00",
            "isya" => "19:00 - 21:00"
        ]
    ]);
}

function resetShalatData() {
    global $pdo, $timestamp;
    
    try {
        // Reset hanya data hari ini
        $date = $_GET['date'] ?? date('Y-m-d');
        
        $pdo->beginTransaction();
        
        // Delete shalat reports for the date
        $stmt = $pdo->prepare("DELETE FROM shalat_reports WHERE report_date = ?");
        $stmt->execute([$date]);
        $deletedReports = $stmt->rowCount();
        
        // Delete daily summary for the date
        $stmt = $pdo->prepare("DELETE FROM daily_summary WHERE report_date = ?");
        $stmt->execute([$date]);
        $deletedSummary = $stmt->rowCount();
        
        $pdo->commit();
        
        echo json_encode([
            "success" => true,
            "message" => "Shalat data reset successfully",
            "reset_date" => $date,
            "deleted_reports" => $deletedReports,
            "deleted_summary" => $deletedSummary,
            "timestamp" => $timestamp
        ]);
        
    } catch(PDOException $e) {
        $pdo->rollback();
        echo json_encode([
            "success" => false,
            "message" => "Reset failed: " . $e->getMessage(),
            "timestamp" => $timestamp
        ]);
    }
}

// ==================== HELPER FUNCTIONS ====================

function getValueFromRequest($requestData, $key, $default = null) {
    if (isset($requestData[$key])) {
        return $requestData[$key];
    } elseif (isset($_POST[$key])) {
        return $_POST[$key];
    } elseif (isset($_GET[$key])) {
        return $_GET[$key];
    }
    return $default;
}

function updateDailySummary($shalat_name, $water_amount, $is_final_report = false) {
    global $pdo;
    
    try {
        $report_date = date('Y-m-d');
        $column_name = strtolower($shalat_name) . '_water';
        
        // Check if record exists
        $stmt = $pdo->prepare("SELECT id FROM daily_summary WHERE report_date = ?");
        $stmt->execute([$report_date]);
        $exists = $stmt->fetch();
        
        if ($exists) {
            // Update existing record
            if ($is_final_report) {
                // Final report - set exact value
                $stmt = $pdo->prepare("UPDATE daily_summary SET 
                    $column_name = ?, 
                    total_daily_water = subuh_water + dzuhur_water + ashar_water + maghrib_water + isya_water,
                    updated_at = NOW() 
                    WHERE report_date = ?");
                $stmt->execute([$water_amount, $report_date]);
            } else {
                // Live update - increment if higher
                $stmt = $pdo->prepare("UPDATE daily_summary SET 
                    $column_name = GREATEST($column_name, ?), 
                    total_daily_water = subuh_water + dzuhur_water + ashar_water + maghrib_water + isya_water,
                    updated_at = NOW() 
                    WHERE report_date = ?");
                $stmt->execute([$water_amount, $report_date]);
            }
        } else {
            // Create new record
            $stmt = $pdo->prepare("INSERT INTO daily_summary 
                (report_date, $column_name, total_daily_water) 
                VALUES (?, ?, ?)");
            $stmt->execute([$report_date, $water_amount, $water_amount]);
        }
        
        error_log("Daily summary updated: $shalat_name = $water_amount L");
        
    } catch(PDOException $e) {
        error_log("Error updating daily summary: " . $e->getMessage());
    }
}

// Close database connection
$pdo = null;

// Log completion
error_log("Request completed for action: $action");
?>