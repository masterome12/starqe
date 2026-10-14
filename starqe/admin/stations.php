<?php
require_once '../config/config.php';
requireAdmin();
$pageTitle='Manage Stations'; $activeNav='admin'; $activePage='stations'; $settings=getSettings();
include '../includes/header.php';
include '../includes/admin_sidebar.php';
?>
<div class="admin-wrap"><div class="content-pad">
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <h1 style="font-size:1.7rem;font-weight:800;margin:0">Manage Stations</h1>
    <button class="btn-primary-s btn" onclick="openModal()"><i class="bi bi-plus-lg me-1"></i>Add Station</button>
  </div>

  <!-- Search & filter -->
  <div class="card-s mb-4"><div style="padding:.9rem 1.1rem">
    <div class="row g-2 align-items-center">
      <div class="col-md-4">
        <div class="inp-wrapper"><span class="inp-icon"><i class="bi bi-search"></i></span>
          <input type="text" class="inp with-icon" id="sq" placeholder="Search stations…" oninput="APP.debounce(load,320)()">
        </div>
      </div>
      <div class="col-md-2"><select class="inp" id="ss" onchange="load()"><option value="">All Status</option><option value="available">Available</option><option value="busy">Busy</option><option value="full">Full</option><option value="maintenance">Maintenance</option></select></div>
      <div class="col-md-2"><select class="inp" id="st" onchange="load()"><option value="">All Types</option><option value="AC">AC</option><option value="DC">DC</option><option value="AC/DC">AC/DC</option></select></div>
      <div class="col-md-2"><button class="btn-primary-s btn w-100" onclick="load()"><i class="bi bi-search me-1"></i>Search</button></div>
      <div class="col-md-2"><button class="btn-ghost btn w-100" onclick="toggleAdminMap()"><i class="bi bi-map me-1"></i>Map View</button></div>
    </div>
  </div></div>

  <!-- Admin Map -->
  <div id="admin-map-wrap" style="display:none;margin-bottom:1.25rem">
    <div class="card-s">
      <div class="card-hd"><div class="metric-icon blue" style="width:32px;height:32px;font-size:.85rem"><i class="bi bi-map"></i></div><span class="title">Admin Map — Click marker to edit</span>
        <button class="btn-ghost btn btn-sm ms-auto" onclick="toggleAdminMap()">Hide</button></div>
      <div id="admin-map" style="height:400px"></div>
    </div>
  </div>

  <!-- Table -->
  <div class="card-s">
    <div style="padding:.75rem 1.1rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
      <span style="font-weight:600;font-size:.875rem" id="sc">Loading…</span>
    </div>
    <div style="overflow-x:auto">
      <table class="tbl w-100">
        <thead><tr><th>Station</th><th>City</th><th>Type</th><th>Status</th><th>Ports</th><th>Rate</th><th>Verified</th><th>Actions</th></tr></thead>
        <tbody id="stbl"><tr><?= str_repeat('<td><div class="skeleton" style="height:18px"></div></td>',8) ?></tr></tbody>
      </table>
    </div>
  </div>
</div></div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="sModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="sModalTitle">Add Station</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="sm-alert" class="alert alert-danger d-none mb-3"></div>
        <!-- CRITICAL FIX: station_id stored in hidden field -->
        <input type="hidden" id="s-id" value="">
        <div class="row g-3">
          <div class="col-md-8"><label class="inp-lbl">Station Name *</label><input type="text" class="inp" id="s-name" placeholder="e.g. AC Mobility – Mall Name"></div>
          <div class="col-md-4"><label class="inp-lbl">Status</label><select class="inp" id="s-status"><option value="available">Available</option><option value="busy">Busy</option><option value="full">Full</option><option value="maintenance">Maintenance</option></select></div>
          <div class="col-12"><label class="inp-lbl">Address *</label><input type="text" class="inp" id="s-addr" placeholder="Street address"></div>
          <div class="col-md-4"><label class="inp-lbl">City *</label><input type="text" class="inp" id="s-city" placeholder="e.g. Makati"></div>
          <div class="col-md-4"><label class="inp-lbl">Latitude *</label><input type="number" step="any" class="inp" id="s-lat" placeholder="14.5547"></div>
          <div class="col-md-4"><label class="inp-lbl">Longitude *</label><input type="number" step="any" class="inp" id="s-lng" placeholder="121.0244"></div>
          <div class="col-md-3"><label class="inp-lbl">Type</label><select class="inp" id="s-type"><option value="AC">AC</option><option value="DC">DC</option><option value="AC/DC">AC/DC</option></select></div>
          <div class="col-md-3"><label class="inp-lbl">Power (kW)</label><input type="number" step=".01" class="inp" id="s-power" value="7.4"></div>
          <div class="col-md-3"><label class="inp-lbl">Price/kWh (₱)</label><input type="number" step=".01" class="inp" id="s-price" value="12.00"></div>
          <div class="col-md-3"><label class="inp-lbl">Operator</label><input type="text" class="inp" id="s-op" placeholder="e.g. AC Mobility"></div>
          <div class="col-md-3"><label class="inp-lbl">Available Ports</label><input type="number" class="inp" id="s-avail" value="2" min="0"></div>
          <div class="col-md-3"><label class="inp-lbl">Total Ports</label><input type="number" class="inp" id="s-total" value="2" min="1"></div>
          <div class="col-md-3"><label class="inp-lbl">Opening Time</label><input type="time" class="inp" id="s-open" value="06:00"></div>
          <div class="col-md-3"><label class="inp-lbl">Closing Time</label><input type="time" class="inp" id="s-close" value="22:00"></div>
          <div class="col-md-6 d-flex gap-4 align-items-center" style="margin-top:.25rem">
            <div class="form-check"><input class="form-check-input" type="checkbox" id="s-verified"><label class="form-check-label" for="s-verified" style="font-size:.875rem">Verified Station</label></div>
            <div class="form-check"><input class="form-check-input" type="checkbox" id="s-fast"><label class="form-check-label" for="s-fast" style="font-size:.875rem">⚡ Fast Charge</label></div>
          </div>
          <div class="col-md-6"><label class="inp-lbl">Station Image (optional)</label><input type="file" class="inp" id="s-img" accept="image/*"></div>
        </div>
        <!-- Click-to-pick map -->
        <div style="margin-top:1rem;padding:1rem;background:var(--bg-soft);border-radius:var(--r);border:1px solid var(--border)">
          <div style="font-size:.78rem;color:var(--text-2);margin-bottom:.5rem"><i class="bi bi-info-circle me-1"></i>Click on the map to auto-fill coordinates. Drag the marker to adjust.</div>
          <div id="modal-map" style="height:200px;border-radius:8px;overflow:hidden;border:1px solid var(--border)"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn-ghost btn" data-bs-dismiss="modal">Cancel</button>
        <button class="btn-primary-s btn" id="save-station-btn" onclick="saveStation()"><i class="bi bi-check-lg me-1"></i>Save Station</button>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
<script>
const BASE='<?= BASE_URL ?>'; let mmap=null,mmarker=null,amap=null,amapInit=false;

async function load(){
  const q=document.getElementById('sq').value,ss=document.getElementById('ss').value,st=document.getElementById('st').value;
  const r=await APP.get(BASE+'/api/stations.php',{q,status:ss,type:st});
  if(!r.success)return;
  document.getElementById('sc').textContent=r.data.length+' station(s)';
  const COLS={available:'#22C55E',busy:'#F59E0B',full:'#EF4444',maintenance:'#94A3B8'};
  const tbody=document.getElementById('stbl');
  if(!r.data.length){tbody.innerHTML='<tr><td colspan="8"><div class="empty-state py-4"><div class="empty-icon">🔌</div><div class="empty-title">No stations found</div></div></td></tr>';return;}
  tbody.innerHTML=r.data.map(s=>`<tr>
    <td><div style="font-weight:600;font-size:.875rem">${s.station_name}</div><div style="font-size:.75rem;color:var(--text-2)">${s.address}</div></td>
    <td style="font-size:.875rem">${s.city}</td>
    <td>${APP.typeBadge(s.charging_type)}</td>
    <td><span style="background:${COLS[s.status]}20;color:${COLS[s.status]};padding:.18rem .55rem;border-radius:99px;font-size:.72rem;font-weight:600">${s.status}</span></td>
    <td><span style="font-weight:600;color:${+s.available_ports>0?'#22c55e':'#ef4444'}">${s.available_ports}/${s.total_ports}</span></td>
    <td style="font-weight:600;color:var(--primary)">₱${(+s.price_per_kwh).toFixed(2)}</td>
    <td>${s.is_verified=='1'?'<span style="color:#2563eb;font-weight:600">✓</span>':'—'}</td>
    <td>
      <div style="display:flex;gap:.3rem">
        <button class="btn-icon" onclick="editStation(${s.station_id})" title="Edit"><i class="bi bi-pencil"></i></button>
        <button class="btn-icon" onclick="delStation(${s.station_id},'${s.station_name.replace(/'/g,"\\'")}')" style="color:var(--danger)" title="Delete"><i class="bi bi-trash"></i></button>
      </div>
    </td>
  </tr>`).join('');
}

function openModal(d=null){
  const isEdit=d!==null;
  document.getElementById('sModalTitle').textContent=isEdit?'Edit Station #'+d.station_id:'Add New Station';
  document.getElementById('sm-alert').classList.add('d-none');

  // CRITICAL FIX: Always set station_id field explicitly
  document.getElementById('s-id').value = isEdit ? String(d.station_id) : '';

  // Populate all fields
  document.getElementById('s-name').value   = isEdit?(d.station_name||''):'';
  document.getElementById('s-status').value = isEdit?(d.status||'available'):'available';
  document.getElementById('s-addr').value   = isEdit?(d.address||''):'';
  document.getElementById('s-city').value   = isEdit?(d.city||''):'';
  document.getElementById('s-lat').value    = isEdit?(d.latitude||''):'';
  document.getElementById('s-lng').value    = isEdit?(d.longitude||''):'';
  document.getElementById('s-type').value   = isEdit?(d.charging_type||'AC'):'AC';
  document.getElementById('s-power').value  = isEdit?(d.power_capacity||7.4):7.4;
  document.getElementById('s-price').value  = isEdit?(d.price_per_kwh||12.00):12.00;
  document.getElementById('s-op').value     = isEdit?(d.operator||''):'';
  document.getElementById('s-avail').value  = isEdit?(d.available_ports||2):2;
  document.getElementById('s-total').value  = isEdit?(d.total_ports||2):2;
  document.getElementById('s-open').value   = isEdit?((d.opening_time||'06:00:00').substring(0,5)):'06:00';
  document.getElementById('s-close').value  = isEdit?((d.closing_time||'22:00:00').substring(0,5)):'22:00';
  document.getElementById('s-verified').checked = isEdit&&d.is_verified=='1';
  document.getElementById('s-fast').checked     = isEdit&&d.is_fast_charge=='1';

  new bootstrap.Modal(document.getElementById('sModal')).show();

  // Init modal map after show
  setTimeout(()=>{
    if(!mmap){
      mmap=L.map('modal-map',{zoomControl:true,attributionControl:false});
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mmap);
      mmap.setView([14.5547,121.0244],11);
      mmap.on('click',async e=>{
        document.getElementById('s-lat').value=e.latlng.lat.toFixed(8);
        document.getElementById('s-lng').value=e.latlng.lng.toFixed(8);
        if(mmarker){mmarker.setLatLng(e.latlng);}
        else{mmarker=L.marker(e.latlng,{draggable:true}).addTo(mmap);
          mmarker.on('dragend',e2=>{
            document.getElementById('s-lat').value=e2.target.getLatLng().lat.toFixed(8);
            document.getElementById('s-lng').value=e2.target.getLatLng().lng.toFixed(8);
          });
        }
        // Reverse geocode address
        const addr=await SMAP.reverseGeocode(e.latlng.lat,e.latlng.lng);
        if(addr&&!document.getElementById('s-addr').value) document.getElementById('s-addr').value=addr;
      });
    }
    if(isEdit&&d.latitude&&d.longitude){
      const lt=+d.latitude,ln=+d.longitude;
      mmap.setView([lt,ln],15);
      if(mmarker){mmarker.setLatLng([lt,ln]);}
      else{mmarker=L.marker([lt,ln],{draggable:true}).addTo(mmap);
        mmarker.on('dragend',e=>{
          document.getElementById('s-lat').value=e.target.getLatLng().lat.toFixed(8);
          document.getElementById('s-lng').value=e.target.getLatLng().lng.toFixed(8);
        });
      }
    }
    mmap.invalidateSize();
  },350);
}

async function editStation(id){
  // Fetch latest station data before opening modal
  const r=await APP.get(BASE+'/api/stations.php',{id});
  if(!r.success){ APP.toast('Station not found: '+r.message,'error'); return; }
  if(!r.data||!r.data.station_id){ APP.toast('Invalid station data received.','error'); return; }
  openModal(r.data);
}

async function saveStation(){
  const btn=document.getElementById('save-station-btn');
  const alr=document.getElementById('sm-alert');
  // CRITICAL FIX: Read station_id from hidden field
  const stationId=document.getElementById('s-id').value.trim();
  const isEdit=stationId!=='';

  // Validate required fields
  const name=document.getElementById('s-name').value.trim();
  const city=document.getElementById('s-city').value.trim();
  const lat=document.getElementById('s-lat').value.trim();
  const lng=document.getElementById('s-lng').value.trim();
  if(!name||!city||!lat||!lng){
    alr.textContent='Please fill all required fields (Name, City, Lat, Lng).';
    alr.classList.remove('d-none'); return;
  }

  APP.btnLoad(btn, isEdit?'Updating...':'Creating...');
  alr.classList.add('d-none');

  const fd=new FormData();
  // CRITICAL FIX: Include station_id in FormData for PUT
  if(isEdit){
    fd.append('station_id', stationId);
    fd.append('_method','PUT');
  }
  fd.append('station_name',name);
  fd.append('status',document.getElementById('s-status').value);
  fd.append('address',document.getElementById('s-addr').value);
  fd.append('city',city);
  fd.append('latitude',lat);
  fd.append('longitude',lng);
  fd.append('charging_type',document.getElementById('s-type').value);
  fd.append('power_capacity',document.getElementById('s-power').value);
  fd.append('price_per_kwh',document.getElementById('s-price').value);
  fd.append('operator',document.getElementById('s-op').value);
  fd.append('available_ports',document.getElementById('s-avail').value);
  fd.append('total_ports',document.getElementById('s-total').value);
  fd.append('opening_time',document.getElementById('s-open').value+':00');
  fd.append('closing_time',document.getElementById('s-close').value+':00');
  fd.append('is_verified',document.getElementById('s-verified').checked?1:0);
  fd.append('is_fast_charge',document.getElementById('s-fast').checked?1:0);
  const imgFile=document.getElementById('s-img').files[0];
  if(imgFile) fd.append('image',imgFile);

  const r=await APP.postForm(BASE+'/api/stations.php',fd);
  if(r.success){
    bootstrap.Modal.getInstance(document.getElementById('sModal')).hide();
    APP.toast(r.message,'success');
    load(); // Refresh table immediately
  } else {
    alr.textContent=r.message;
    alr.classList.remove('d-none');
  }
  APP.btnReset(btn);
}

async function delStation(id,name){
  APP.confirm(`Delete <b>${name}</b>? All bookings will also be removed.`,async()=>{
    const fd=new FormData();fd.append('station_id',id);fd.append('_method','DELETE');
    const r=await APP.postForm(BASE+'/api/stations.php',fd);
    if(r.success){APP.toast('Station deleted.','success');load();}
    else APP.toast(r.message,'error');
  },'Delete Station');
}

function toggleAdminMap(){
  const w=document.getElementById('admin-map-wrap');
  const showing=w.style.display!=='none';
  w.style.display=showing?'none':'block';
  if(!showing&&!amapInit){
    amapInit=true;
    setTimeout(async()=>{
      amap=L.map('admin-map').setView([14.5995,120.9842],10);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19}).addTo(amap);
      const r=await APP.get(BASE+'/api/stations.php');
      if(r.success){const COLS={available:'#22C55E',busy:'#F59E0B',full:'#EF4444',maintenance:'#94A3B8'};
        r.data.forEach(s=>{L.circleMarker([+s.latitude,+s.longitude],{radius:9,fillColor:COLS[s.status]||'#ccc',color:'#fff',weight:2,fillOpacity:.9}).bindPopup(`<div style="font-family:Inter,sans-serif"><b>${s.station_name}</b><br>${s.city}<br><button onclick="editStation(${s.station_id})" style="margin-top:.4rem;padding:.25rem .6rem;background:#2563eb;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:.75rem">Edit</button></div>`).addTo(amap);});}
    },200);
  }
}

load();
setInterval(load,180000);
</script>
