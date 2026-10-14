<?php
require_once '../config/config.php';
requireAdmin();
$pageTitle='Admin Dashboard'; $activeNav='admin'; $activePage='dash'; $settings=getSettings();
include '../includes/header.php';
include '../includes/admin_sidebar.php';
?>
<div class="admin-wrap"><div class="content-pad">
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
      <h1 style="font-size:1.7rem;font-weight:800;margin-bottom:.15rem">Admin Dashboard</h1>
      <p class="text-muted mb-0" style="font-size:.875rem"><?= date('l, F j, Y') ?></p>
    </div>
    <div class="d-flex gap-2 align-items-center">
      <select id="period-sel" class="inp" style="width:auto;padding:.42rem .85rem;font-size:.875rem" onchange="loadStats()">
        <option value="week">Last 7 Days</option>
        <option value="month" selected>Last 30 Days</option>
        <option value="year">Last 12 Months</option>
      </select>
      <button class="btn-primary-s btn btn-sm" onclick="loadStats()"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</button>
    </div>
  </div>

  <!-- Metric Cards -->
  <div class="row g-3 mb-4" id="metrics-row">
    <?php for($i=0;$i<4;$i++): ?>
    <div class="col-6 col-lg-3">
      <div class="metric-card"><div class="skeleton mb-2" style="height:46px;width:46px;border-radius:10px"></div><div class="skeleton mb-1" style="height:28px;width:60%"></div><div class="skeleton" style="height:14px;width:40%"></div></div>
    </div>
    <?php endfor; ?>
  </div>

  <!-- Charts -->
  <div class="row g-4 mb-4">
    <div class="col-lg-8">
      <div class="card-s h-100">
        <div class="card-hd"><div class="metric-icon blue" style="width:32px;height:32px;font-size:.85rem"><i class="bi bi-graph-up"></i></div><span class="title">Revenue Trend</span></div>
        <div class="card-bd"><div class="chart-wrap" style="height:260px"><canvas id="revChart"></canvas></div></div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card-s h-100">
        <div class="card-hd"><div class="metric-icon purple" style="width:32px;height:32px;font-size:.85rem"><i class="bi bi-pie-chart"></i></div><span class="title">Station Status</span></div>
        <div class="card-bd"><div class="chart-wrap"><canvas id="statusChart"></canvas></div></div>
      </div>
    </div>
  </div>

  <div class="row g-4 mb-4">
    <div class="col-lg-6">
      <div class="card-s h-100">
        <div class="card-hd"><div class="metric-icon orange" style="width:32px;height:32px;font-size:.85rem"><i class="bi bi-clock"></i></div><span class="title">Peak Booking Hours</span></div>
        <div class="card-bd"><div class="chart-wrap"><canvas id="peakChart"></canvas></div></div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card-s h-100">
        <div class="card-hd"><div class="metric-icon green" style="width:32px;height:32px;font-size:.85rem"><i class="bi bi-trophy"></i></div><span class="title">Top Stations</span></div>
        <div id="top-list" style="padding:0"></div>
      </div>
    </div>
  </div>

  <!-- Quick actions -->
  <div class="row g-3">
    <?php foreach([['bi-ev-station','blue','Manage Stations','/admin/stations.php'],['bi-people','purple','Manage Users','/admin/users.php'],['bi-calendar-check','green','View Bookings','/admin/bookings.php'],['bi-flag','orange','Review Reports','/admin/reports.php']] as [$ico,$col,$lbl,$url]): ?>
    <div class="col-6 col-lg-3">
      <a href="<?= BASE_URL.$url ?>" class="metric-card d-flex align-items-center gap-3" style="text-decoration:none;cursor:pointer">
        <div class="metric-icon <?= $col ?>" style="margin:0"><i class="bi <?= $ico ?>"></i></div>
        <span style="font-weight:600;font-size:.875rem;color:var(--text)"><?= $lbl ?></span>
        <i class="bi bi-chevron-right ms-auto" style="color:var(--text-3)"></i>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
</div></div>

<?php include '../includes/footer.php'; ?>
<script>
const BASE='<?= BASE_URL ?>'; let RC,SC,PC;
async function loadStats(){
  const p=document.getElementById('period-sel').value;
  const r=await APP.get(BASE+'/api/analytics.php',{period:p});
  if(!r.success)return;
  const d=r.data;
  const metrics=[
    ['blue','bi-people-fill',d.totals.users.toLocaleString(),'Total Users','+'+d.totals.new_users+' this month'],
    ['green','bi-ev-station-fill',d.totals.stations,'Stations',d.totals.available+' available'],
    ['purple','bi-calendar-check-fill',d.totals.bookings,'Bookings',d.totals.bookings_month+' this month'],
    ['orange','bi-currency-exchange','₱'+parseInt(d.totals.revenue).toLocaleString(),'Revenue',d.totals.pending_bookings+' pending'],
  ];
  document.getElementById('metrics-row').innerHTML=metrics.map(([col,ico,val,lbl,sub])=>`
    <div class="col-6 col-lg-3"><div class="metric-card">
      <div class="metric-icon ${col}"><i class="bi ${ico}"></i></div>
      <div class="metric-val">${val}</div><div class="metric-lbl">${lbl}</div><div class="metric-sub">${sub}</div>
    </div></div>`).join('');

  // Revenue chart
  if(RC)RC.destroy();
  RC=new Chart(document.getElementById('revChart'),{type:'line',data:{
    labels:d.revenue_chart.map(x=>x.period),
    datasets:[{label:'Revenue (₱)',data:d.revenue_chart.map(x=>+x.revenue),borderColor:'#2563EB',backgroundColor:'rgba(37,99,235,.08)',tension:.4,fill:true,pointBackgroundColor:'#2563EB',pointRadius:3,pointHoverRadius:5}]
  },options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{grid:{display:false},ticks:{maxTicksLimit:8,font:{size:11}}},y:{grid:{color:'#f1f5f9'},ticks:{callback:v=>'₱'+v.toLocaleString(),font:{size:11}}}}}});

  // Status doughnut
  const scols={available:'#22C55E',busy:'#F59E0B',full:'#EF4444',maintenance:'#94A3B8'};
  if(SC)SC.destroy();
  SC=new Chart(document.getElementById('statusChart'),{type:'doughnut',data:{
    labels:d.station_status.map(s=>s.status),
    datasets:[{data:d.station_status.map(s=>+s.cnt),backgroundColor:d.station_status.map(s=>scols[s.status]||'#ccc'),borderWidth:2,borderColor:'#fff'}]
  },options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{font:{size:11},padding:12}}},cutout:'65%'}});

  // Peak hours
  const ph=Array(24).fill(0);d.peak_hours.forEach(x=>{ph[+x.hr]=+x.cnt;});
  if(PC)PC.destroy();
  PC=new Chart(document.getElementById('peakChart'),{type:'bar',data:{
    labels:Array.from({length:24},(_,i)=>i+':00'),
    datasets:[{data:ph,backgroundColor:ph.map(v=>`rgba(37,99,235,${Math.max(.2,Math.min(.9,v/Math.max(...ph)))})`),borderRadius:4,borderSkipped:false}]
  },options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{grid:{display:false},ticks:{maxTicksLimit:12,font:{size:10}}},y:{grid:{color:'#f1f5f9'},ticks:{font:{size:11}}}}}});

  // Top stations
  document.getElementById('top-list').innerHTML=d.station_usage.map((s,i)=>`
    <div style="display:flex;align-items:center;gap:.85rem;padding:.85rem 1.1rem;border-bottom:1px solid var(--border)">
      <div style="width:24px;height:24px;border-radius:50%;background:${i===0?'#fef3c7':i===1?'#f1f5f9':'#fff7ed'};color:${i===0?'#ca8a04':i===1?'#64748b':'#ea580c'};display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;flex-shrink:0">${i+1}</div>
      <div style="flex:1;min-width:0"><div style="font-weight:600;font-size:.875rem;color:var(--text)" class="text-truncate">${s.station_name}</div><div style="font-size:.75rem;color:var(--text-2)">${s.bookings} bookings</div></div>
      <div style="font-weight:700;color:var(--primary);font-size:.875rem">₱${(+s.revenue).toLocaleString('en-PH',{minimumFractionDigits:0})}</div>
    </div>`).join('');
}
loadStats();
setInterval(loadStats,180000);
</script>
