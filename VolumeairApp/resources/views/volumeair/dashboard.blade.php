<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>VolumeAir Dashboard - Smart Faucet Monitor</title>
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;color:#333}
    .container{max-width:1400px;margin:0 auto;padding:20px}
    .header{text-align:center;color:white;margin-bottom:30px}
    .header h1{font-size:2.5rem;margin-bottom:10px;text-shadow:2px 2px 4px rgba(0,0,0,0.3)}
    .dashboard-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;margin-bottom:30px}
    .card{background:rgba(255,255,255,.95);border-radius:15px;padding:25px;box-shadow:0 8px 32px rgba(0,0,0,.1);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.18);transition:transform .3s ease}
    .card:hover{transform:translateY(-5px)}
    .card h3{color:#4a5568;margin-bottom:15px;font-size:1.3rem;display:flex;align-items:center;gap:10px}
    .status-indicator{width:12px;height:12px;border-radius:50%;display:inline-block}
    .status-on{background:#48bb78}.status-off{background:#f56565}.status-standby{background:#ed8936}
    .metric{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;padding:8px 0;border-bottom:1px solid #e2e8f0}
    .metric:last-child{border-bottom:none;margin-bottom:0}
    .metric-label{font-weight:500;color:#4a5568}
    .metric-value{font-weight:bold;color:#2d3748}
    .volume-large{font-size:2rem;color:#3182ce}
    .prayer-time{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;text-align:center;padding:20px;border-radius:10px;margin:15px 0}
    .prayer-active{background:linear-gradient(135deg,#48bb78 0%,#38a169 100%);animation:pulse 2s infinite}
    @keyframes pulse{0%{box-shadow:0 0 0 0 rgba(72,187,120,.4)}70%{box-shadow:0 0 0 10px rgba(72,187,120,0)}100%{box-shadow:0 0 0 0 rgba(72,187,120,0)}}
    .controls{display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap}
    .btn{padding:10px 20px;border:none;border-radius:8px;cursor:pointer;font-weight:500;transition:all .3s ease}
    .btn-primary{background:#3182ce;color:white}.btn-primary:hover{background:#2c5282}
    .btn-success{background:#48bb78;color:white}.btn-success:hover{background:#38a169}
    .data-table{width:100%;border-collapse:collapse;margin-top:15px}
    .data-table th,.data-table td{padding:12px;text-align:left;border-bottom:1px solid #e2e8f0}
    .data-table th{background:#f7fafc;font-weight:600;color:#4a5568}
    .data-table tr:hover{background:#f7fafc}
    .timestamp{font-size:.9rem;color:#718096}
    .alert{padding:15px;border-radius:8px;margin-bottom:20px}
    .alert-error{background:#fed7d7;color:#c53030;border:1px solid #feb2b2}
    .alert-success{background:#c6f6d5;color:#276749;border:1px solid #9ae6b4}
    .loading{display:inline-block;width:20px;height:20px;border:3px solid #f3f3f3;border-top:3px solid #3182ce;border-radius:50%;animation:spin 1s linear infinite}
    @keyframes spin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}
    .connection-status{position:fixed;top:20px;right:20px;padding:10px 15px;border-radius:25px;color:white;font-weight:500;z-index:1000}
    .connected{background:#48bb78}.disconnected{background:#f56565}
    @media (max-width:768px){.dashboard-grid{grid-template-columns:1fr}.header h1{font-size:2rem}.controls{justify-content:center}}
  </style>
</head>
<body>
  <div class="connection-status" id="connectionStatus">
    <div class="loading"></div> Connecting...
  </div>

  <div class="container">
    <div class="header">
      <h1>🚿 VolumeAir Dashboard</h1>
    </div>

    <div class="controls">
      <button class="btn btn-primary" onclick="refreshData()">🔄 Refresh Data</button>
      <button class="btn btn-success" onclick="testConnection()">🔗 Test Connection</button>
      <button class="btn btn-primary" onclick="getStats()">📊 Get Statistics</button>
    </div>

    <div id="alerts"></div>

    <div class="dashboard-grid">
      <div class="card">
        <h3><span class="status-indicator" id="statusIndicator"></span> Status Keran Saat Ini</h3>
        <div class="metric"><span class="metric-label">Jarak Sensor:</span><span class="metric-value" id="currentDistance">- cm</span></div>
        <div class="metric"><span class="metric-label">Flow Rate:</span><span class="metric-value" id="currentFlow">- L/min</span></div>
        <div class="metric"><span class="metric-label">Status Keran:</span><span class="metric-value" id="currentStatus">-</span></div>
        <div class="metric"><span class="metric-label">Last Update:</span><span class="metric-value timestamp" id="lastUpdate">-</span></div>
      </div>

      <div class="card">
        <h3>💧 Volume Air</h3>
        <div class="metric"><span class="metric-label">Total Volume (hari ini):</span><span class="metric-value volume-large" id="totalVolume">0.000 L</span></div>
        <div class="metric"><span class="metric-label">Session Volume:</span><span class="metric-value" id="sessionVolume">0.000 L</span></div>
      </div>

      <div class="card">
        <h3>⚙️ Informasi Sistem</h3>
        <div class="metric"><span class="metric-label">ESP32 Status:</span><span class="metric-value" id="esp32Status">Active</span></div>
        <div class="metric"><span class="metric-label">Sensor Type:</span><span class="metric-value">Ultrasonic + Flow</span></div>
        <div class="metric"><span class="metric-label">Update Interval:</span><span class="metric-value">5 seconds</span></div>
        <div class="metric"><span class="metric-label">Auto Refresh:</span><span class="metric-value" id="autoRefreshStatus">ON</span></div>
      </div>

      <div class="card">
        <h3>📈 Statistik Database</h3>
        <div class="metric"><span class="metric-label">Records Hari Ini:</span><span class="metric-value" id="todayRecords">-</span></div>
        <div class="metric"><span class="metric-label">Total Records:</span><span class="metric-value" id="totalRecords">-</span></div>
        <div class="metric"><span class="metric-label">Database Status:</span><span class="metric-value" id="dbStatus">-</span></div>
        <div class="metric"><span class="metric-label">Server Time:</span><span class="metric-value timestamp" id="serverTime">-</span></div>
      </div>
    </div>

    <div class="card">
      <h3>📋 Data Terbaru dari ESP32</h3>
      <button class="btn btn-primary" onclick="getLatestData()">Load Latest Data</button>
      <div style="overflow-x:auto">
        <table class="data-table" id="dataTable">
          <thead>
            <tr>
              <th>ID</th>
              <th>Jarak (cm)</th>
              <th>Flow Rate (L/min)</th>
              <th>Status Keran</th>
              <th>Timestamp</th>
            </tr>
          </thead>
          <tbody id="dataTableBody">
            <tr><td colspan="5" style="text-align:center;padding:20px">Klik "Load Latest Data" untuk mengambil data dari database</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    const API_BASE = `${window.location.origin}/api/volumeair`; // <- Laravel API prefix
    let connectionStatus = false, autoRefresh = null;

    document.addEventListener('DOMContentLoaded', () => { testConnection(); startAutoRefresh(); });

    function startAutoRefresh(){ autoRefresh=setInterval(()=>{ if(connectionStatus){ getCurrentData(); } },5000); }
    function stopAutoRefresh(){ if(autoRefresh){ clearInterval(autoRefresh); autoRefresh=null; } }

    async function testConnection(){
      try{
        showAlert('Testing server connection...','info');
        const r = await fetch(`${window.location.origin}/api/health`,{headers:{'Cache-Control':'no-cache'}});
        if(!r.ok) throw new Error(`HTTP ${r.status}`);
        const d = await r.json();
        if(d.status==='success'){
          connectionStatus = true;
          updateConnectionStatus(true);
          document.getElementById('serverTime').textContent = d.server_time || d.time;
          document.getElementById('dbStatus').textContent = d.database ? 'Connected' : 'Unknown';
          showAlert('✅ Server connection successful!','success');
          getCurrentData(); getStats();
        }else{ throw new Error(d.message||'Server returned error'); }
      }catch(e){
        connectionStatus=false; updateConnectionStatus(false);
        showAlert(`❌ Connection failed: ${e.message}`,'error');
      }
    }

    async function getCurrentData(){
      try{
        const r = await fetch(`${API_BASE}/current`,{headers:{'Cache-Control':'no-cache'}});
        if(!r.ok) throw new Error(`HTTP ${r.status}`);
        const res = await r.json();
        if(res.status==='success' && res.data){ updateCurrentData(res.data); }
      }catch(e){ showAlert(`Failed to get current data: ${e.message}`,'error'); }
    }

    async function getStats(){
      try{
        const r = await fetch(`${API_BASE}/stats`,{headers:{'Cache-Control':'no-cache'}});
        if(!r.ok) throw new Error(`HTTP ${r.status}`);
        const res = await r.json();
        if(res.status==='success'){
          document.getElementById('todayRecords').textContent = res.data.today_records || 0;
          document.getElementById('totalRecords').textContent = res.data.total_records || 0;
          document.getElementById('totalVolume').textContent = (res.data.today_total_volume || 0).toFixed(3) + ' L';
        }
      }catch(e){ /* silent */ }
    }

    async function getLatestData(){
      try{
        showAlert('Loading latest data...','info');
        const r = await fetch(`${API_BASE}/latest?limit=10`,{headers:{'Cache-Control':'no-cache'}});
        if(!r.ok) throw new Error(`HTTP ${r.status}`);
        const res = await r.json();
        updateDataTable(res.data||[]);
        showAlert(`✅ Loaded ${(res.data||[]).length} records`,'success');
      }catch(e){
        showAlert(`❌ Failed to load data: ${e.message}`,'error');
        updateDataTable([]);
      }
    }

    function updateCurrentData(d){
      document.getElementById('currentDistance').textContent = `${parseFloat(d.jarak_cm||0).toFixed(1)} cm`;
      document.getElementById('currentFlow').textContent     = `${parseFloat(d.flow_rate||0).toFixed(3)} L/min`;
      document.getElementById('currentStatus').textContent   = d.status_kran||'OFF';
      document.getElementById('lastUpdate').textContent      = d.timestamp||'N/A';

      const s = (d.status_kran||'off').toLowerCase();
      const dot = document.getElementById('statusIndicator');
      dot.className = 'status-indicator ' + (s==='on'?'status-on':(s==='standby'?'status-standby':'status-off'));

      // optional session volume jika API mengirim
      if(typeof d.session_volume!=='undefined'){
        document.getElementById('sessionVolume').textContent = parseFloat(d.session_volume||0).toFixed(3) + ' L';
      }
    }

    function updateDataTable(data){
      const tb = document.getElementById('dataTableBody');
      if(!data.length){
        tb.innerHTML = `<tr><td colspan="5" style="text-align:center;padding:20px;color:#718096">Tidak ada data</td></tr>`;
        return;
      }
      tb.innerHTML = data.map(r=>`
        <tr>
          <td>${r.id??'-'}</td>
          <td>${Number(r.jarak_cm??r.jarak??0).toFixed(1)}</td>
          <td>${Number(r.flow_rate??r.flow??0).toFixed(3)}</td>
          <td><span class="status-indicator ${getStatusClass(r.status_kran??r.status)}"></span> ${r.status_kran??r.status??'OFF'}</td>
          <td class="timestamp">${r.timestamp??r.created_at??'-'}</td>
        </tr>
      `).join('');
    }

    function getStatusClass(s){ s=(s||'off').toLowerCase(); if(s==='on') return 'status-on'; if(s==='standby') return 'status-standby'; return 'status-off'; }
    function updateConnectionStatus(ok){ const el=document.getElementById('connectionStatus'); el.className='connection-status '+(ok?'connected':'disconnected'); el.textContent = ok?'🟢 Connected':'🔴 Disconnected'; }
    function showAlert(msg,type='info'){ const box=document.getElementById('alerts'); const cls= type==='error'?'alert-error':'alert-success'; const el=document.createElement('div'); el.className=`alert ${cls}`; el.textContent=msg; box.innerHTML=''; box.appendChild(el); setTimeout(()=>{el.remove()},5000); }
    function refreshData(){ if(connectionStatus){ getCurrentData(); getStats(); showAlert('🔄 Data refreshed','success'); } else { testConnection(); } }
    document.addEventListener('visibilitychange',()=>{ if(document.hidden){stopAutoRefresh()} else {startAutoRefresh(); if(connectionStatus){getCurrentData()}} });
    window.addEventListener('error',e=>{ showAlert('❌ An error occurred. Check console for details.','error'); });
  </script>
</body>
</html>
