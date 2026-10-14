<?php
require_once '../config/config.php';
requireAdmin();
$pageTitle='Analytics'; $activeNav='admin'; $activePage='analytics'; $settings=getSettings();
include '../includes/header.php';
include '../includes/admin_sidebar.php';
?>
<div class="admin-wrap"><div class="content-pad">
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <h1 style="font-size:1.7rem;font-weight:800;margin:0">Analytics</h1>
    <select id="period-sel" class="inp" style="width:auto;padding:.42rem .85rem;font-size:.875rem" onchange="loadAll()">
      <option value="week">Last 7 Days</option><option value="month" selected>Last 30 Days</option><option value="year">Last 12 Months</option>
    </select>
  </div>
  <div class="row g-3 mb-4" id="am-metrics"></div>
  <div class="row g-4 mb-4">
    <div class="col-12"><div class="card-s">
      <div class="card-hd"><div class="metric-icon blue" style="width:32px;height:32px;font-size:.85rem"><i class="bi bi-graph-up"></i></div><span class="title">Revenue Over Time</span></div>
      <div class="card-bd"><div class="chart-wrap" style="height:280px"><canvas id="revC"></canvas></div></div>
    </div></div>
  </div>
  <div class="row g-4 mb-4">
    <div class="col-md-6"><div class="card-s h-100">
      <div class="card-hd"><div class="metric-icon purple" style="width:32px;height:32px;font-size:.85rem"><i class="bi bi-pie-chart"></i></div><span class="title">Station Status</span></div>
      <div class="card-bd"><div class="chart-wrap"><canvas id="doC"></canvas></div></div>
    </div></div>
    <div class="col-md-6"><div class="card-s h-100">
      <div class="card-hd"><div class="metric-icon orange" style="width:32px;height:32px;font-size:.85rem"><i class="bi bi-clock"></i></div><span class="title">Peak Booking Hours</span></div>
      <div class="card-bd"><div class="chart-wrap"><canvas id="peakC"></canvas></div></div>
    </div></div>
  </div>
  <div class="card-s">
    <div class="card-hd"><div class="metric-icon green" style="width:32px;height:32px;font-size:.85rem"><i class="bi bi-bar-chart"></i></div><span class="title">Top Stations by Revenue</span></div>
    <div class="card-bd"><div class="chart-wrap" style="height:280px"><canvas id="barC"></canvas></div></div>
  </div>
</div></div>
<?php include '../includes/footer.php'; ?>
<script>
const BASE='<?= BASE_URL ?>'; let C={};
async function loadAll(){
  const p=document.getElementById('period-sel').value;
  const r=await APP.get(BASE+'/api/analytics.php',{period:p});
  if(!r.success)return;
  const d=r.data;
  document.getElementById('am-metrics').innerHTML=[
    ['blue','bi-people-fill',d.totals.users.toLocaleString(),'Users','+'+d.totals.new_users+' this month'],
    ['green','bi-ev-station-fill',d.totals.stations,'Stations',d.totals.available+' available'],
    ['purple','bi-calendar-check-fill',d.totals.bookings,'Bookings',d.totals.bookings_month+' this month'],
    ['orange','bi-currency-exchange','₱'+parseInt(d.totals.revenue).toLocaleString(),'Revenue',d.totals.pending_bookings+' pending'],
  ].map(([c,i,v,l,s])=>`<div class="col-6 col-lg-3"><div class="metric-card"><div class="metric-icon ${c}"><i class="bi ${i}"></i></div><div class="metric-val">${v}</div><div class="metric-lbl">${l}</div><div class="metric-sub">${s}</div></div></div>`).join('');

  if(C.rev)C.rev.destroy();
  C.rev=new Chart(document.getElementById('revC'),{type:'line',data:{labels:d.revenue_chart.map(x=>x.period),datasets:[{label:'Revenue',data:d.revenue_chart.map(x=>+x.revenue),borderColor:'#2563EB',backgroundColor:'rgba(37,99,235,.08)',tension:.4,fill:true},{label:'Bookings',data:d.revenue_chart.map(x=>+x.cnt),borderColor:'#22C55E',backgroundColor:'rgba(34,197,94,.05)',tension:.4,fill:true,yAxisID:'y2'}]},options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},plugins:{legend:{position:'top',labels:{font:{size:11}}}},scales:{x:{grid:{display:false},ticks:{maxTicksLimit:10}},y:{grid:{color:'#f1f5f9'},ticks:{callback:v=>'₱'+v.toLocaleString()}},y2:{position:'right',grid:{display:false}}}}});

  const sc={available:'#22C55E',busy:'#F59E0B',full:'#EF4444',maintenance:'#94A3B8'};
  if(C.do)C.do.destroy();
  C.do=new Chart(document.getElementById('doC'),{type:'doughnut',data:{labels:d.station_status.map(s=>s.status),datasets:[{data:d.station_status.map(s=>+s.cnt),backgroundColor:d.station_status.map(s=>sc[s.status]||'#ccc'),borderWidth:2,borderColor:'#fff'}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{font:{size:11}}}},cutout:'65%'}});

  const ph=Array(24).fill(0);d.peak_hours.forEach(x=>{ph[+x.hr]=+x.cnt;});
  if(C.pk)C.pk.destroy();
  C.pk=new Chart(document.getElementById('peakC'),{type:'bar',data:{labels:Array.from({length:24},(_,i)=>i+':00'),datasets:[{data:ph,backgroundColor:ph.map(v=>`rgba(37,99,235,${Math.max(.2,Math.min(.9,v/Math.max(1,...ph)))})`),borderRadius:5}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{grid:{display:false},ticks:{maxTicksLimit:12,font:{size:10}}},y:{grid:{color:'#f1f5f9'}}}}});

  if(C.bar)C.bar.destroy();
  C.bar=new Chart(document.getElementById('barC'),{type:'bar',data:{labels:d.station_usage.map(s=>s.station_name.substring(0,22)+(s.station_name.length>22?'…':'')),datasets:[{label:'Revenue',data:d.station_usage.map(s=>+s.revenue),backgroundColor:'rgba(37,99,235,.75)',borderRadius:6}]},options:{indexAxis:'y',responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{grid:{color:'#f1f5f9'},ticks:{callback:v=>'₱'+v.toLocaleString()}},y:{grid:{display:false}}}}});
}
loadAll();
setInterval(loadAll,180000);
</script>
