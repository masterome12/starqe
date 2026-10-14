<?php
require_once '../config/config.php';
requireAdmin();
$pageTitle='Station Reports'; $activeNav='admin'; $activePage='reports'; $settings=getSettings();
include '../includes/header.php';
include '../includes/admin_sidebar.php';
?>
<div class="admin-wrap"><div class="content-pad">
  <h1 style="font-size:1.7rem;font-weight:800;margin-bottom:1.5rem">Station Reports</h1>
  <div style="display:flex;gap:.4rem;margin-bottom:1.25rem;flex-wrap:wrap">
    <button class="filter-chip active" onclick="setStatusFilter('')">All</button>
    <button class="filter-chip" onclick="setStatusFilter('pending')">🕐 Pending</button>
    <button class="filter-chip" onclick="setStatusFilter('approved')">✅ Approved</button>
    <button class="filter-chip" onclick="setStatusFilter('rejected')">❌ Rejected</button>
  </div>
  <div class="card-s">
    <div style="padding:.75rem 1.1rem;border-bottom:1px solid var(--border)"><span id="rc" style="font-weight:600;font-size:.875rem">Loading…</span></div>
    <div style="overflow-x:auto"><table class="tbl w-100">
      <thead><tr><th>Reporter</th><th>Station</th><th>Type</th><th>Remarks</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
      <tbody id="rtbl"></tbody>
    </table></div>
    <div style="padding:.85rem 1.1rem;border-top:1px solid var(--border);display:flex;justify-content:center"><div class="pager" id="rpager"></div></div>
  </div>
</div></div>

<!-- Review Modal -->
<div class="modal fade" id="rvModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">🚩 Review Report</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div id="rv-details" style="background:var(--bg-soft);border-radius:var(--r);padding:1rem;margin-bottom:1rem;border:1px solid var(--border)"></div>
      <div><label class="inp-lbl">Admin Note (optional)</label>
        <textarea class="inp" id="rv-note" rows="3" style="resize:none" placeholder="Reason for decision…"></textarea>
      </div>
      <input type="hidden" id="rv-id">
    </div>
    <div class="modal-footer gap-2">
      <button class="btn-ghost btn" data-bs-dismiss="modal">Cancel</button>
      <button class="btn-danger-s btn" onclick="submitReview('rejected')"><i class="bi bi-x-circle me-1"></i>Reject</button>
      <button class="btn-primary-s btn" onclick="submitReview('approved')"><i class="bi bi-check-circle me-1"></i>Approve</button>
    </div>
  </div></div>
</div>
<?php include '../includes/footer.php'; ?>
<script>
const BASE='<?= BASE_URL ?>'; let curP=1,curStatus='';
const TLBLS={full:'Station Full/No Ports',offline:'Offline',damaged:'Damaged',wrong_info:'Wrong Info',other:'Other'};

function setStatusFilter(s){
  curStatus=s;
  document.querySelectorAll('.filter-chip').forEach(c=>c.classList.remove('active'));
  event.target.classList.add('active');
  load(1,s);
}

async function load(page=1,status=curStatus){
  curP=page;
  const r=await APP.get(BASE+'/api/reports.php',{page,status});
  if(!r.success)return;
  const{reports,total,pages}=r.data;
  document.getElementById('rc').textContent=total+' report(s)';
  const tbody=document.getElementById('rtbl');
  if(!reports.length){tbody.innerHTML='<tr><td colspan="7"><div class="empty-state py-4"><div class="empty-icon">🚩</div><div class="empty-title">No reports</div></div></td></tr>';return;}
  tbody.innerHTML=reports.map(r=>`<tr>
    <td><div style="font-weight:600;font-size:.875rem">${r.fullname}</div><div style="font-size:.75rem;color:var(--text-2)">${r.email}</div></td>
    <td><div style="font-weight:600;font-size:.875rem">${r.station_name}</div><div style="font-size:.75rem;color:var(--text-2)">${r.city}</div></td>
    <td><span style="background:#fef3c7;color:#d97706;padding:.18rem .55rem;border-radius:99px;font-size:.72rem;font-weight:600">${TLBLS[r.report_type]||r.report_type}</span></td>
    <td style="font-size:.82rem;max-width:200px"><div class="text-truncate">${r.remarks||'—'}</div></td>
    <td>${APP.statusBadge(r.status)}</td>
    <td style="font-size:.8rem;color:var(--text-2)">${APP.date(r.created_at)}</td>
    <td><button class="btn-icon ${r.status==='pending'?'':'opacity-75'}" onclick='openReview(${JSON.stringify(r)})' title="${r.status==='pending'?'Review':'View'}">
      <i class="bi bi-${r.status==='pending'?'check-lg':'eye'}"></i></button></td>
  </tr>`).join('');
  document.getElementById('rpager').innerHTML=Array.from({length:pages},(_,i)=>`<a href="#" class="${i+1===page?'cur':''}" onclick="event.preventDefault();load(${i+1})">${i+1}</a>`).join('');
}

function openReview(r){
  document.getElementById('rv-id').value=r.report_id;
  document.getElementById('rv-note').value=r.admin_note||'';
  document.getElementById('rv-details').innerHTML=`
    <div class="mb-2"><strong>Station:</strong> ${r.station_name} (${r.city})</div>
    <div class="mb-2"><strong>Reporter:</strong> ${r.fullname}</div>
    <div class="mb-2"><strong>Issue Type:</strong> ${TLBLS[r.report_type]||r.report_type}</div>
    <div><strong>Remarks:</strong> ${r.remarks||'—'}</div>`;
  new bootstrap.Modal(document.getElementById('rvModal')).show();
}

async function submitReview(status){
  const fd=new FormData();fd.append('report_id',document.getElementById('rv-id').value);fd.append('status',status);fd.append('admin_note',document.getElementById('rv-note').value);fd.append('_method','PUT');
  const r=await APP.postForm(BASE+'/api/reports.php',fd);
  if(r.success){bootstrap.Modal.getInstance(document.getElementById('rvModal')).hide();APP.toast('Report '+status+'.','success');load(curP);}
  else APP.toast(r.message,'error');
}
load();
setInterval(()=>load(curP),60000);
</script>
