<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Volume Air Dashboard</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .last-update {
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 25px;
            display: inline-block;
            backdrop-filter: blur(10px);
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.5rem;
            color: white;
        }

        .icon-distance { background: linear-gradient(135deg, #4CAF50, #45a049); }
        .icon-flow { background: linear-gradient(135deg, #2196F3, #1976D2); }
        .icon-volume { background: linear-gradient(135deg, #FF9800, #F57C00); }

        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }

        .card-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #2c3e50;
            margin: 10px 0;
        }

        .card-unit {
            font-size: 1rem;
            color: #7f8c8d;
            margin-left: 5px;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-on { background: #e8f5e8; color: #2e7d32; }
        .status-off { background: #ffebee; color: #c62828; }
        .status-standby { background: #fff3e0; color: #ef6c00; }

        .chart-container {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }

        .chart-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }

        .loading {
            text-align: center;
            padding: 50px;
            color: #666;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #c62828;
        }

        .success-message {
            background: #e8f5e8;
            color: #2e7d32;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #2e7d32;
        }

        .debug-info {
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            font-family: monospace;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .card-value {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-tint"></i> Monitoring Volume Air Dashboard</h1>
            <div class="last-update" id="lastUpdate">
                <i class="fas fa-sync-alt fa-spin"></i> Loading...
            </div>
        </div>

        <div id="errorContainer"></div>
        <div id="debugInfo" class="debug-info" style="display: none;"></div>

        <div class="dashboard-grid">
            <!-- Distance Card -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon icon-distance">
                        <i class="fas fa-ruler"></i>
                    </div>
                    <div class="card-title">Distance Sensor</div>
                </div>
                <div class="card-value" id="distance">
                    <div class="spinner"></div>
                </div>
            </div>

            <!-- Flow Rate Card -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon icon-flow">
                        <i class="fas fa-water"></i>
                    </div>
                    <div class="card-title">Flow Rate</div>
                </div>
                <div class="card-value" id="flowRate">
                    <div class="spinner"></div>
                </div>
            </div>

            <!-- Faucet Status Card -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon icon-flow">
                        <i class="fas fa-faucet"></i>
                    </div>
                    <div class="card-title">Faucet Status</div>
                </div>
                <div id="faucetStatus">
                    <div class="spinner"></div>
                </div>
            </div>

            <!-- Total Volume Card -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon icon-volume">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="card-title">Total Volume</div>
                </div>
                <div class="card-value" id="totalVolume">
                    <div class="spinner"></div>
                </div>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="chart-container">
            <div class="chart-title">
                <i class="fas fa-chart-area"></i> Monitoring Volume Air Real-time
            </div>
            <canvas id="volumeChart" width="400" height="200"></canvas>
        </div>
    </div>

    <script>
        // Global variables
        let volumeChart;
        let volumeData = [];

        // API Configuration - Multiple possible paths
        const API_PATHS = [
            './api.php',              // Same directory
            './volumeair/api.php',    // In volumeair subfolder
            '../api.php',             // Parent directory
            '/volumeair/api.php',     // From root
            'http://192.168.234.167/volumeair/api.php'  // Full URL
        ];
        
        let API_BASE = API_PATHS[0];  // Start with first option
        const DEMO_MODE = false;

        // Debug function
        function debugLog(message, data = null) {
            console.log(`[${new Date().toLocaleTimeString()}] ${message}`, data);
            
            // Only show debug info in development
            const showDebug = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
            
            if (showDebug) {
                const debugElement = document.getElementById('debugInfo');
                const timestamp = new Date().toLocaleTimeString();
                debugElement.innerHTML += `[${timestamp}] ${message}${data ? ': ' + JSON.stringify(data, null, 2) : ''}<br>`;
                debugElement.style.display = 'block';
            }
        }

        // Simulate data for demo
        function generateDemoData() {
            const now = new Date();
            const data = [];
            
            for (let i = 29; i >= 0; i--) {
                const time = new Date(now.getTime() - i * 60000);
                data.push({
                    timestamp: time.toISOString(),
                    jarak_cm: (Math.random() * 10 + 15).toFixed(1),
                    flow_rate: (Math.random() * 2 + 1).toFixed(2),
                    total_volume: (i * 0.05 + Math.random() * 0.1).toFixed(3),
                    status_kran: Math.random() > 0.7 ? 'ON' : 'OFF'
                });
            }
            
            return data;
        }

        // Generate current demo data
        function getCurrentDemoData() {
            const now = new Date();
            return {
                timestamp: now.toISOString(),
                jarak_cm: (Math.random() * 10 + 15).toFixed(1),
                flow_rate: (Math.random() * 2 + 1).toFixed(2),
                total_volume: (Math.random() * 5 + 2).toFixed(3),
                status_kran: Math.random() > 0.5 ? 'ON' : 'OFF'
            };
        }

        // Initialize chart
        function initChart() {
            const volumeCtx = document.getElementById('volumeChart').getContext('2d');
            volumeChart = new Chart(volumeCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Volume Air (L)',
                        data: [],
                        borderColor: 'rgb(255, 152, 0)',
                        backgroundColor: 'rgba(255, 152, 0, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: 'rgb(255, 152, 0)',
                        pointBorderColor: 'white',
                        pointBorderWidth: 2,
                        pointRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Volume (Liter)',
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Waktu',
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                font: {
                                    size: 12,
                                    weight: 'bold'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            titleColor: 'white',
                            bodyColor: 'white',
                            borderColor: 'rgb(255, 152, 0)',
                            borderWidth: 1
                        }
                    },
                    animation: {
                        duration: 750,
                        easing: 'easeInOutQuart'
                    }
                }
            });
        }

        // Test API endpoint
        async function testAPIEndpoint(url) {
            try {
                debugLog(`Testing API endpoint: ${url}`);
                
                // Test dengan GET parameter
                const testUrl = `${url}?action=health&t=${Date.now()}`;
                debugLog(`Full test URL: ${testUrl}`);
                
                const response = await fetch(testUrl, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'Cache-Control': 'no-cache'
                    },
                    cache: 'no-cache'
                });
                
                debugLog(`Response status: ${response.status}`);
                debugLog(`Response headers:`, Array.from(response.headers.entries()));
                
                if (!response.ok) {
                    const errorText = await response.text();
                    debugLog(`Error response body: ${errorText}`);
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const responseText = await response.text();
                debugLog(`Raw response: ${responseText}`);
                
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    debugLog('JSON parse error:', parseError);
                    throw new Error('Invalid JSON response');
                }
                
                debugLog('Parsed API Response:', data);
                
                if (data.status === 'success') {
                    return true;
                } else {
                    debugLog('API returned error:', data);
                    return false; // Still consider it working, just returned error
                }
            } catch (error) {
                debugLog(`API test failed for ${url}:`, error.message);
                return false;
            }
        }

        // Find working API endpoint
        async function findWorkingAPI() {
            debugLog('Searching for working API endpoint...');
            
            for (const path of API_PATHS) {
                if (await testAPIEndpoint(path)) {
                    API_BASE = path;
                    debugLog(`Found working API: ${API_BASE}`);
                    return true;
                }
            }
            
            debugLog('No working API endpoint found');
            return false;
        }

        // Fetch current sensor data
        async function fetchCurrentData() {
            if (DEMO_MODE) {
                const demoData = getCurrentDemoData();
                updateCurrentData(demoData);
                return demoData;
            }
            
            try {
                const url = `${API_BASE}?action=get_current&t=${Date.now()}`;
                debugLog(`Fetching current data from: ${url}`);
                
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'Cache-Control': 'no-cache'
                    },
                    cache: 'no-cache'
                });
                
                debugLog(`Response status: ${response.status}`);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    debugLog(`Error response: ${errorText}`);
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const responseText = await response.text();
                debugLog(`Raw response: ${responseText}`);
                
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    debugLog('JSON parse error:', parseError);
                    throw new Error('Invalid JSON response: ' + responseText.substring(0, 100));
                }
                
                debugLog('Current data response:', data);
                
                if (data.status === 'success' && data.data) {
                    updateCurrentData(data.data);
                    return data.data;
                } else if (data.status === 'error') {
                    throw new Error(data.message || 'API returned error');
                } else {
                    throw new Error('No data returned from API');
                }
            } catch (error) {
                debugLog('Fetch current data error:', error.message);
                showError('Failed to fetch current data: ' + error.message);
                return null;
            }
        }

        // Fetch latest data for chart
        async function fetchLatestData() {
            if (DEMO_MODE) {
                const demoData = generateDemoData();
                updateChart(demoData);
                return;
            }
            
            try {
                const url = `${API_BASE}?action=get_latest&limit=30&t=${Date.now()}`;
                debugLog(`Fetching chart data from: ${url}`);
                
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'Cache-Control': 'no-cache'
                    },
                    cache: 'no-cache'
                });
                
                debugLog(`Response status: ${response.status}`);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    debugLog(`Error response: ${errorText}`);
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const responseText = await response.text();
                debugLog(`Raw response: ${responseText}`);
                
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    debugLog('JSON parse error:', parseError);
                    throw new Error('Invalid JSON response: ' + responseText.substring(0, 100));
                }
                
                debugLog('Chart data response:', data);
                
                if (data.status === 'success' && data.data && Array.isArray(data.data)) {
                    updateChart(data.data);
                } else if (data.status === 'error') {
                    throw new Error(data.message || 'API returned error');
                } else {
                    // Jika tidak ada data, buat data dummy untuk chart
                    debugLog('No chart data available, using dummy data');
                    updateChart([]);
                }
            } catch (error) {
                debugLog('Fetch chart data error:', error.message);
                showError('Failed to fetch chart data: ' + error.message);
                
                // Use dummy data for chart if API fails
                updateChart([]);
            }
        }

        // Update current data display
        function updateCurrentData(data) {
            document.getElementById('distance').innerHTML = `${parseFloat(data.jarak_cm || 0).toFixed(1)}<span class="card-unit">cm</span>`;
            document.getElementById('flowRate').innerHTML = `${parseFloat(data.flow_rate || 0).toFixed(2)}<span class="card-unit">L/min</span>`;
            document.getElementById('totalVolume').innerHTML = `${parseFloat(data.total_volume || 0).toFixed(3)}<span class="card-unit">L</span>`;
            
            const statusElement = document.getElementById('faucetStatus');
            const status = data.status_kran || 'OFF';
            let statusClass = 'status-off';
            if (status === 'ON') statusClass = 'status-on';
            else if (status === 'STANDBY') statusClass = 'status-standby';
            
            statusElement.innerHTML = `<div class="status-badge ${statusClass}">${status}</div>`;
            
            const lastUpdate = new Date(data.timestamp || new Date()).toLocaleString('id-ID');
            document.getElementById('lastUpdate').innerHTML = `<i class="fas fa-clock"></i> Last Update: ${lastUpdate}`;
            
            clearError();
        }

        // Update chart with latest data
        function updateChart(data) {
            const volumeLabels = [];
            const volumeValues = [];
            
            if (data && data.length > 0) {
                data.slice(-30).forEach((item, index) => {
                    const time = new Date(item.timestamp);
                    volumeLabels.push(time.toLocaleTimeString('id-ID', { 
                        hour: '2-digit', 
                        minute: '2-digit',
                        second: '2-digit'
                    }));
                    volumeValues.push(parseFloat(item.total_volume) || 0);
                });
            } else {
                // If no data, show empty chart with current time
                const now = new Date();
                volumeLabels.push(now.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                }));
                volumeValues.push(0);
            }
            
            volumeChart.data.labels = volumeLabels;
            volumeChart.data.datasets[0].data = volumeValues;
            volumeChart.update('none');
        }

        // Show error message
        function showError(message) {
            const errorContainer = document.getElementById('errorContainer');
            errorContainer.innerHTML = `
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i> ${message}
                    <br><small>Current API: ${API_BASE}</small>
                </div>
            `;
        }

        // Clear error message
        function clearError() {
            document.getElementById('errorContainer').innerHTML = '';
        }

        // Fetch and update all data
        async function updateDashboard() {
            await fetchCurrentData();
            await fetchLatestData();
        }

        // Test API connectivity
        async function testAPI() {
            if (DEMO_MODE) {
                const errorContainer = document.getElementById('errorContainer');
                errorContainer.innerHTML = `
                    <div class="success-message">
                        <i class="fas fa-info-circle"></i> DEMO MODE - Menggunakan data simulasi untuk preview dashboard
                    </div>
                `;
                return true;
            }
            
            // Try to find working API
            const apiFound = await findWorkingAPI();
            
            if (apiFound) {
                const errorContainer = document.getElementById('errorContainer');
                errorContainer.innerHTML = `
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i> API Connected Successfully - Database: MySQL/MariaDB
                        <br><small>Endpoint: ${API_BASE}</small>
                    </div>
                `;
                
                // Hide debug info after successful connection
                setTimeout(() => {
                    const debugElement = document.getElementById('debugInfo');
                    if (debugElement) {
                        debugElement.style.display = 'none';
                    }
                }, 3000);
                
                return true;
            } else {
                showError('API Connection Failed - No working endpoint found. Please check server configuration.');
                return false;
            }
        }

        // Initialize dashboard
        async function init() {
            debugLog('Initializing dashboard...');
            debugLog('Current URL:', window.location.href);
            
            const apiOk = await testAPI();
            
            if (apiOk || DEMO_MODE) {
                initChart();
                await updateDashboard();
                
                // Set up auto-refresh every 10 seconds (reduced from 5 for better performance)
                setInterval(updateDashboard, 10000);
                debugLog('Dashboard initialized successfully - Auto-refresh every 10 seconds');
                
                // Hide success message after 5 seconds
                setTimeout(() => {
                    const errorContainer = document.getElementById('errorContainer');
                    if (errorContainer && errorContainer.innerHTML.includes('success-message')) {
                        errorContainer.innerHTML = '';
                    }
                }, 5000);
                
            } else {
                document.getElementById('lastUpdate').innerHTML = 
                    '<i class="fas fa-exclamation-triangle"></i> API Connection Failed';
                debugLog('Dashboard initialization failed - API not available');
            }
        }

        // Start when page loads
        document.addEventListener('DOMContentLoaded', init);
    </script>
</body>
</html>