<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_volumeair";
    
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// Set timezone to Indonesia
date_default_timezone_set('Asia/Jakarta');

// Get action parameter
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch ($action) {
    case 'health':
        echo json_encode(["status" => "ok", "message" => "Server is running", "time" => date('Y-m-d H:i:s')]);
        break;
        
    case 'add_data':
        addSensorData($conn);
        break;
        
    case 'add_prayer_volume':
        addPrayerVolume($conn);
        break;
        
    case 'get_daily_summary':
        getDailySummary($conn);
        break;
        
    case 'get_prayer_reports':
        getPrayerReports($conn);
        break;
        
    case 'get_weekly_view':
        getWeeklyView($conn);
        break;
        
    case 'create_tables':
        createTables($conn);
        break;
        
    default:
        echo json_encode(["status" => "error", "message" => "Invalid action"]);
        break;
}

$conn->close();

// === FUNCTION: Create Database Tables ===
function createTables($conn) {
    $sql = "
    -- Table untuk data sensor real-time
    CREATE TABLE IF NOT EXISTS sensor_data (
        id INT AUTO_INCREMENT PRIMARY KEY,
        jarak DECIMAL(5,2) NOT NULL,
        flow DECIMAL(6,3) NOT NULL,
        status VARCHAR(20) NOT NULL,
        active_prayer VARCHAR(50) DEFAULT NULL,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_timestamp (timestamp),
        INDEX idx_status (status)
    ) ENGINE=InnoDB;

    -- Table untuk ringkasan harian
    CREATE TABLE IF NOT EXISTS daily_water_summary (
        id INT AUTO_INCREMENT PRIMARY KEY,
        date DATE UNIQUE NOT NULL,
        total_volume DECIMAL(10,3) DEFAULT 0,
        total_usage_time INT DEFAULT 0, -- dalam detik
        peak_flow_rate DECIMAL(6,3) DEFAULT 0,
        average_flow_rate DECIMAL(6,3) DEFAULT 0,
        usage_sessions INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_date (date)
    ) ENGINE=InnoDB;

    -- Table untuk laporan volume waktu shalat
    CREATE TABLE IF NOT EXISTS shalat_water_reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        prayer_name VARCHAR(20) NOT NULL,
        total_volume DECIMAL(8,3) NOT NULL,
        date DATE NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_date_prayer (date, prayer_name),
        INDEX idx_prayer (prayer_name)
    ) ENGINE=InnoDB;

    -- Table untuk view mingguan
    CREATE TABLE IF NOT EXISTS weekly_water_view (
        id INT AUTO_INCREMENT PRIMARY KEY,
        week_start DATE NOT NULL,
        week_end DATE NOT NULL,
        total_volume DECIMAL(12,3) DEFAULT 0,
        daily_average DECIMAL(8,3) DEFAULT 0,
        subuh_total DECIMAL(8,3) DEFAULT 0,
        dzuhur_total DECIMAL(8,3) DEFAULT 0,
        ashar_total DECIMAL(8,3) DEFAULT 0,
        maghrib_total DECIMAL(8,3) DEFAULT 0,
        isya_total DECIMAL(8,3) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_week (week_start),
        INDEX idx_week_start (week_start)
    ) ENGINE=InnoDB;
    ";

    if ($conn->multi_query($sql)) {
        // Process all results
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());
        
        echo json_encode([
            "status" => "success", 
            "message" => "All tables created successfully",
            "tables" => [
                "sensor_data",
                "daily_water_summary", 
                "shalat_water_reports",
                "weekly_water_view"
            ]
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error creating tables: " . $conn->error]);
    }
}

// === FUNCTION: Add Sensor Data ===
function addSensorData($conn) {
    $jarak = floatval($_POST['jarak'] ?? 0);
    $flow = floatval($_POST['flow'] ?? 0);
    $status = $conn->real_escape_string($_POST['status'] ?? 'UNKNOWN');
    $active_prayer = isset($_POST['active_prayer']) ? $conn->real_escape_string($_POST['active_prayer']) : NULL;
    
    $sql = "INSERT INTO sensor_data (jarak, flow, status, active_prayer) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ddss", $jarak, $flow, $status, $active_prayer);
    
    if ($stmt->execute()) {
        // Update daily summary if there's flow
        if ($flow > 0.1) {
            updateDailySummary($conn, $flow);
        }
        
        echo json_encode([
            "status" => "success", 
            "message" => "Sensor data added",
            "data" => [
                "jarak" => $jarak,
                "flow" => $flow,
                "status" => $status,
                "active_prayer" => $active_prayer
            ]
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add sensor data: " . $stmt->error]);
    }
    
    $stmt->close();
}

// === FUNCTION: Add Prayer Volume ===
function addPrayerVolume($conn) {
    $prayer_name = $conn->real_escape_string($_POST['prayer_name'] ?? '');
    $total_volume = floatval($_POST['total_volume'] ?? 0);
    $timestamp = $_POST['timestamp'] ?? date('Y-m-d H:i:s');
    
    // Parse timestamp to get date and time
    $datetime = new DateTime($timestamp);
    $date = $datetime->format('Y-m-d');
    $time = $datetime->format('H:i:s');
    
    // Define prayer time ranges
    $prayer_times = [
        'Subuh' => ['start' => '03:30:00', 'end' => '05:30:00'],
        'Dzuhur' => ['start' => '12:00:00', 'end' => '14:00:00'],
        'Ashar' => ['start' => '16:30:00', 'end' => '18:30:00'],
        'Maghrib' => ['start' => '18:30:00', 'end' => '19:00:00'],
        'Isya' => ['start' => '19:00:00', 'end' => '21:00:00']
    ];
    
    $start_time = $prayer_times[$prayer_name]['start'];
    $end_time = $prayer_times[$prayer_name]['end'];
    
    $sql = "INSERT INTO shalat_water_reports (prayer_name, total_volume, date, start_time, end_time, timestamp) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdssss", $prayer_name, $total_volume, $date, $start_time, $end_time, $timestamp);
    
    if ($stmt->execute()) {
        // Update weekly view
        updateWeeklyView($conn, $date);
        
        echo json_encode([
            "status" => "success", 
            "message" => "Prayer volume data added",
            "data" => [
                "prayer_name" => $prayer_name,
                "total_volume" => $total_volume,
                "date" => $date,
                "timestamp" => $timestamp
            ]
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add prayer volume: " . $stmt->error]);
    }
    
    $stmt->close();
}

// === FUNCTION: Update Daily Summary ===
function updateDailySummary($conn, $flow_rate) {
    $today = date('Y-m-d');
    
    // Check if record exists for today
    $check_sql = "SELECT id, total_volume, usage_sessions, peak_flow_rate FROM daily_water_summary WHERE date = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $today);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    $volume_increment = ($flow_rate / 60.0) * (5.0 / 60.0); // 5 detik interval converted to hours
    
    if ($result->num_rows > 0) {
        // Update existing record
        $row = $result->fetch_assoc();
        $new_volume = $row['total_volume'] + $volume_increment;
        $new_peak = max($row['peak_flow_rate'], $flow_rate);
        
        $update_sql = "UPDATE daily_water_summary SET 
                       total_volume = ?, 
                       peak_flow_rate = ?,
                       total_usage_time = total_usage_time + 5,
                       updated_at = CURRENT_TIMESTAMP 
                       WHERE date = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("dds", $new_volume, $new_peak, $today);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        // Create new record
        $insert_sql = "INSERT INTO daily_water_summary (date, total_volume, total_usage_time, peak_flow_rate, average_flow_rate, usage_sessions) 
                       VALUES (?, ?, 5, ?, ?, 1)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("sddd", $today, $volume_increment, $flow_rate, $flow_rate);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
    
    $check_stmt->close();
}

// === FUNCTION: Update Weekly View ===
function updateWeeklyView($conn, $date) {
    // Get Monday of the week for the given date
    $datetime = new DateTime($date);
    $datetime->modify('monday this week');
    $week_start = $datetime->format('Y-m-d');
    
    $datetime->modify('+6 days');
    $week_end = $datetime->format('Y-m-d');
    
    // Calculate weekly totals
    $sql = "SELECT 
                SUM(total_volume) as total_volume,
                SUM(CASE WHEN prayer_name = 'Subuh' THEN total_volume ELSE 0 END) as subuh_total,
                SUM(CASE WHEN prayer_name = 'Dzuhur' THEN total_volume ELSE 0 END) as dzuhur_total,
                SUM(CASE WHEN prayer_name = 'Ashar' THEN total_volume ELSE 0 END) as ashar_total,
                SUM(CASE WHEN prayer_name = 'Maghrib' THEN total_volume ELSE 0 END) as maghrib_total,
                SUM(CASE WHEN prayer_name = 'Isya' THEN total_volume ELSE 0 END) as isya_total
            FROM shalat_water_reports 
            WHERE date BETWEEN ? AND ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $week_start, $week_end);
    $stmt->execute();
    $result = $stmt->get_result();
    $totals = $result->fetch_assoc();
    
    $total_volume = $totals['total_volume'] ?? 0;
    $daily_average = $total_volume / 7;
    
    // Insert or update weekly view
    $upsert_sql = "INSERT INTO weekly_water_view 
                   (week_start, week_end, total_volume, daily_average, subuh_total, dzuhur_total, ashar_total, maghrib_total, isya_total)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                   ON DUPLICATE KEY UPDATE
                   total_volume = VALUES(total_volume),
                   daily_average = VALUES(daily_average),
                   subuh_total = VALUES(subuh_total),
                   dzuhur_total = VALUES(dzuhur_total),
                   ashar_total = VALUES(ashar_total),
                   maghrib_total = VALUES(maghrib_total),
                   isya_total = VALUES(isya_total),
                   updated_at = CURRENT_TIMESTAMP";
    
    $upsert_stmt = $conn->prepare($upsert_sql);
    $upsert_stmt->bind_param("ssddddddd", 
        $week_start, $week_end, $total_volume, $daily_average,
        $totals['subuh_total'], $totals['dzuhur_total'], $totals['ashar_total'],
        $totals['maghrib_total'], $totals['isya_total']
    );
    
    $upsert_stmt->execute();
    $upsert_stmt->close();
    $stmt->close();
}

// === FUNCTION: Get Daily Summary ===
function getDailySummary($conn) {
    $date = $_GET['date'] ?? date('Y-m-d');
    
    $sql = "SELECT * FROM daily_water_summary WHERE date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        
        // Get prayer data for the same date
        $prayer_sql = "SELECT prayer_name, total_volume, timestamp FROM shalat_water_reports WHERE date = ? ORDER BY timestamp";
        $prayer_stmt = $conn->prepare($prayer_sql);
        $prayer_stmt->bind_param("s", $date);
        $prayer_stmt->execute();
        $prayer_result = $prayer_stmt->get_result();
        
        $prayer_data = [];
        while ($row = $prayer_result->fetch_assoc()) {
            $prayer_data[] = $row;
        }
        
        echo json_encode([
            "status" => "success",
            "date" => $date,
            "daily_summary" => $data,
            "prayer_volumes" => $prayer_data
        ]);
        
        $prayer_stmt->close();
    } else {
        echo json_encode([
            "status" => "success",
            "date" => $date,
            "daily_summary" => null,
            "prayer_volumes" => []
        ]);
    }
    
    $stmt->close();
}

// === FUNCTION: Get Prayer Reports ===
function getPrayerReports($conn) {
    $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
    $end_date = $_GET['end_date'] ?? date('Y-m-d');
    
    $sql = "SELECT 
                prayer_name,
                date,
                total_volume,
                start_time,
                end_time,
                timestamp
            FROM shalat_water_reports 
            WHERE date BETWEEN ? AND ? 
            ORDER BY date DESC, 
            CASE prayer_name 
                WHEN 'Subuh' THEN 1
                WHEN 'Dzuhur' THEN 2  
                WHEN 'Ashar' THEN 3
                WHEN 'Maghrib' THEN 4
                WHEN 'Isya' THEN 5
            END";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reports = [];
    $summary = [
        'total_volume' => 0,
        'by_prayer' => [
            'Subuh' => 0, 'Dzuhur' => 0, 'Ashar' => 0, 'Maghrib' => 0, 'Isya' => 0
        ]
    ];
    
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
        $summary['total_volume'] += $row['total_volume'];
        $summary['by_prayer'][$row['prayer_name']] += $row['total_volume'];
    }
    
    echo json_encode([
        "status" => "success",
        "period" => ["start" => $start_date, "end" => $end_date],
        "reports" => $reports,
        "summary" => $summary
    ]);
    
    $stmt->close();
}

// === FUNCTION: Get Weekly View ===
function getWeeklyView($conn) {
    $weeks = intval($_GET['weeks'] ?? 4); // Default 4 minggu terakhir
    
    $sql = "SELECT * FROM weekly_water_view ORDER BY week_start DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $weeks);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $weekly_data = [];
    while ($row = $result->fetch_assoc()) {
        $weekly_data[] = $row;
    }
    
    // Calculate trends
    $trends = calculateTrends($weekly_data);
    
    echo json_encode([
        "status" => "success",
        "weeks_requested" => $weeks,
        "weekly_data" => $weekly_data,
        "trends" => $trends
    ]);
    
    $stmt->close();
}

// === FUNCTION: Calculate Trends ===
function calculateTrends($weekly_data) {
    if (count($weekly_data) < 2) {
        return ["message" => "Insufficient data for trend analysis"];
    }
    
    $latest = $weekly_data[0];
    $previous = $weekly_data[1];
    
    $volume_change = $latest['total_volume'] - $previous['total_volume'];
    $volume_change_percent = $previous['total_volume'] > 0 ? 
        ($volume_change / $previous['total_volume']) * 100 : 0;
    
    return [
        "volume_change" => round($volume_change, 3),
        "volume_change_percent" => round($volume_change_percent, 2),
        "trend_direction" => $volume_change > 0 ? "increasing" : ($volume_change < 0 ? "decreasing" : "stable"),
        "average_weekly_volume" => round(array_sum(array_column($weekly_data, 'total_volume')) / count($weekly_data), 3)
    ];
}

