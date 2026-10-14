<?php
require_once '../config/config.php';
requireAdmin();
$pageTitle='Manage Bookings'; $activeNav='admin'; $activePage='bookings'; $settings=getSettings();
include '../includes/header.php';
include '../includes/admin_sidebar.php';
?>
<div class="admin-wrap"><div class="content-pad">
  <h1 style="font-size:1.7rem;font-weight:800;margin-bottom:1.5rem">Manage Bookings</h1>
  <div class="card-s mb-4"><div style="padding:.9rem 1.1rem">
    <div class="row g-2">
      <div class="col-md-4"><div class="inp-wrapper"><span class="inp-icon"><i class="bi bi-search"></i></span>
        <input type="text" class="inp with-icon" id="bq" placeholder="Search ref, user, station…" oninput="APP.debounce(()=>load(1),320)()">
      </div></div>
      <div class="col-md-3"><select class="inp" id="bst" onchange="load(1)"><option value="">All Status</option><option value="pending">Pending</option><option value="approved">Approved</option><option value="confirmed">Confirmed</option><option value="completed">Completed</option><option value="cancelled">Cancelled</option><option value="rejected">Rejected</option></select></div>
      <div class="col-md-2"><input type="date" class="inp" id="bdate" onchange="load(1)"></div>
      <div class="col-md-3"><button class="btn-primary-s btn w-100" onclick="load(1)"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</button></div>
    </div>
  </div></div>
  <div class="card-s">
    <div style="padding:.75rem 1.1rem;border-bottom:1px solid var(--border)"><span style="font-weight:600;font-size:.875rem" id="bc">Loading…</span></div>
    <div style="overflow-x:auto"><table class="tbl w-100">
      <thead><tr><th>Reference</th><th>User</th><th>Station</th><th>Date & Time</th><th>Dur.</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody id="btbl"></tbody>
    </table></div>
    <div style="padding:.85rem 1.1rem;border-top:1px solid var(--border);display:flex;justify-content:center"><div class="pager" id="bpager"></div></div>
  </div>
</div></div>

<!-- View Modal -->
<div class="modal fade" id="bvModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Booking Details</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body" id="bv-body">Loading…</div>
    <div class="modal-footer gap-2" id="bv-actions"></div>
  </div></div>
</div>
<?php include '../includes/footer.php'; ?>
<script>
const BASE='<?= BASE_URL ?>'; let curP=1;
async function load(page=1){
  curP=page;
  const r=await APP.get(BASE+'/api/bookings.php',{page,q:document.getElementById('bq').value,status:document.getElementById('bst').value,date:document.getElementById('bdate').value});
  if(!r.success)return;
  const{bookings,total,pages}=r.data;
  document.getElementById('bc').textContent=total+' booking(s)';
  const tbody=document.getElementById('btbl');
  if(!bookings.length){tbody.innerHTML='<tr><td colspan="8"><div class="empty-state py-4"><div class="empty-icon">📅</div><div class="empty-title">No bookings found</div></div></td></tr>';return;}
  tbody.innerHTML=bookings.map(b=>`<tr>
    <td><span style="font-family:'Courier New',monospace;font-size:.82rem;font-weight:700;color:var(--primary)">${b.reference_no}</span></td>
    <td><div style="font-weight:600;font-size:.875rem">${b.fullname}</div><div style="font-size:.75rem;color:var(--text-2)">${b.email}</div></td>
    <td><div style="font-weight:600;font-size:.875rem">${b.station_name}</div><div style="font-size:.75rem;color:var(--text-2)">${b.city}</div></td>
    <td style="font-size:.82rem">${APP.date(b.booking_date)}<br><span style="color:var(--text-2)">${b.booking_time}</span></td>
    <td style="font-size:.875rem">${b.duration_hours}h</td>
    <td style="font-weight:700;color:var(--primary)">₱${(+b.total_amount).toLocaleString('en-PH',{minimumFractionDigits:2})}</td>
    <td>${APP.statusBadge(b.status)}</td>
    <td><div style="display:flex;gap:.3rem">
      <button class="btn-icon" onclick="viewBooking(${b.booking_id})" title="View"><i class="bi bi-eye"></i></button>
      ${b.status==='pending'?`<button class="btn-icon" onclick="setStatus(${b.booking_id},'approved')" title="Approve" style="color:#16a34a"><i class="bi bi-check-lg"></i></button>`:''}
      ${['pending','confirmed','approved'].includes(b.status)?`<button class="btn-icon" onclick="setStatus(${b.booking_id},'rejected')" title="Reject" style="color:var(--danger)"><i class="bi bi-x-lg"></i></button>`:''}
      ${b.status==='approved'?`<button class="btn-icon" onclick="setStatus(${b.booking_id},'completed')" title="Mark Complete" style="color:#2563eb"><i class="bi bi-check-all"></i></button>`:''}
    </div></td>
  </tr>`).join('');
  document.getElementById('bpager').innerHTML=Array.from({length:pages},(_,i)=>`<a href="#" class="${i+1===page?'cur':''}" onclick="event.preventDefault();load(${i+1})">${i+1}</a>`).join('');
}

async function viewBooking(id){
  const r=await APP.get(BASE+'/api/bookings.php',{id});
  if(!r.success)return;
  const b=r.data;
  document.getElementById('bv-body').innerHTML=`
    <div class="row g-3">
      <div class="col-sm-6"><div class="inp-lbl">Reference</div><div style="font-family:'Courier New',monospace;font-size:1.1rem;font-weight:800;color:var(--primary)">${b.reference_no}</div></div>
      <div class="col-sm-6"><div class="inp-lbl">Status</div>${APP.statusBadge(b.status)}</div>
      <div class="col-sm-6"><div class="inp-lbl">Station</div><div style="font-weight:600">${b.station_name}</div><div style="font-size:.82rem;color:var(--text-2)">${b.city}</div></div>
      <div class="col-sm-6"><div class="inp-lbl">Charging</div>${b.charging_type} · ${b.power_capacity}kW</div>
      <div class="col-sm-4"><div class="inp-lbl">Date</div>${APP.date(b.booking_date)}</div>
      <div class="col-sm-4"><div class="inp-lbl">Time</div>${b.booking_time}</div>
      <div class="col-sm-4"><div class="inp-lbl">Duration</div>${b.duration_hours}h</div>
      <div class="col-12"><div class="inp-lbl">Total Amount</div><div style="font-size:1.4rem;font-weight:800;color:var(--primary)">₱${(+b.total_amount).toLocaleString('en-PH',{minimumFractionDigits:2})}</div></div>
      ${b.notes?`<div class="col-12"><div class="inp-lbl">Notes</div>${b.notes}</div>`:''}
      <div class="col-12"><label class="inp-lbl">Admin Note</label>
        <textarea id="admin-note-inp" class="inp" rows="2" style="resize:none" placeholder="Optional note to user…">${b.admin_note||''}</textarea>
      </div>
      <div class="col-12"><div class="inp-lbl">Booked At</div>${APP.datetime(b.created_at)}</div>
    </div>`;
  document.getElementById('bv-actions').innerHTML=`
    <button class="btn-ghost btn" data-bs-dismiss="modal">Close</button>
    ${b.status==='pending'?`<button class="btn-primary-s btn" onclick="setStatusModal(${b.booking_id},'approved')"><i class="bi bi-check-lg me-1"></i>Approve</button>`:''}
    ${['pending','confirmed','approved'].includes(b.status)?`<button class="btn-danger-s btn" onclick="setStatusModal(${b.booking_id},'rejected')"><i class="bi bi-x-lg me-1"></i>Reject</button>`:''}
    ${b.status==='approved'?`<button class="btn-outline-s btn" onclick="setStatusModal(${b.booking_id},'completed')"><i class="bi bi-check-all me-1"></i>Complete</button>`:''}`;
  new bootstrap.Modal(document.getElementById('bvModal')).show();
}

async function setStatus(id,status){
  const fd=new FormData();fd.append('booking_id',id);fd.append('status',status);fd.append('_method','PUT');
  const r=await APP.postForm(BASE+'/api/bookings.php',fd);
  if(r.success){APP.toast('Booking '+status+'.','success');load(curP);}
  else APP.toast(r.message,'error');
}
async function setStatusModal(id,status){
  const note=document.getElementById('admin-note-inp')?.value||'';
  const fd=new FormData();fd.append('booking_id',id);fd.append('status',status);fd.append('admin_note',note);fd.append('_method','PUT');
  const r=await APP.postForm(BASE+'/api/bookings.php',fd);
  if(r.success){bootstrap.Modal.getInstance(document.getElementById('bvModal')).hide();APP.toast('Booking '+status+'.','success');load(curP);}
  else APP.toast(r.message,'error');
}

load();
setInterval(()=>load(curP),60000); // Auto-refresh every 1 min for admin
</script>
