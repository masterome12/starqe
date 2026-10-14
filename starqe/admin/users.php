<?php
require_once '../config/config.php';
requireAdmin();
$pageTitle='Manage Users'; $activeNav='admin'; $activePage='users'; $settings=getSettings();
include '../includes/header.php';
include '../includes/admin_sidebar.php';
?>
<div class="admin-wrap"><div class="content-pad">
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <h1 style="font-size:1.7rem;font-weight:800;margin:0">Manage Users</h1>
    <a href="<?= BASE_URL ?>/api/users.php?export=1" class="btn-ghost btn" style="text-decoration:none"><i class="bi bi-download me-1"></i>Export CSV</a>
  </div>
  <div class="card-s mb-4"><div style="padding:.9rem 1.1rem">
    <div class="row g-2">
      <div class="col-md-5"><div class="inp-wrapper"><span class="inp-icon"><i class="bi bi-search"></i></span>
        <input type="text" class="inp with-icon" id="uq" placeholder="Search by name or email…" oninput="APP.debounce(()=>load(1),320)()">
      </div></div>
      <div class="col-md-3"><button class="btn-primary-s btn w-100" onclick="load(1)"><i class="bi bi-search me-1"></i>Search</button></div>
    </div>
  </div></div>
  <div class="card-s">
    <div style="padding:.75rem 1.1rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
      <span style="font-weight:600;font-size:.875rem" id="uc">Loading…</span>
    </div>
    <div style="overflow-x:auto"><table class="tbl w-100">
      <thead><tr><th>User</th><th>Email</th><th>Role</th><th>Status</th><th>Registered</th><th>Actions</th></tr></thead>
      <tbody id="utbl"><tr><?= str_repeat('<td><div class="skeleton" style="height:16px"></div></td>',6) ?></tr></tbody>
    </table></div>
    <div style="padding:.85rem 1.1rem;border-top:1px solid var(--border);display:flex;justify-content:center"><div class="pager" id="upager"></div></div>
  </div>
</div></div>

<!-- Edit Modal -->
<div class="modal fade" id="euModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Edit User</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div id="eu-alert" class="alert alert-danger d-none mb-3"></div>
      <input type="hidden" id="eu-id">
      <div class="form-group"><label class="inp-lbl">Full Name</label><input type="text" class="inp" id="eu-name"></div>
      <div class="form-group"><label class="inp-lbl">Role</label><select class="inp" id="eu-role"><option value="user">User</option><option value="admin">Admin</option></select></div>
      <div class="form-group"><label class="inp-lbl">Status</label><select class="inp" id="eu-status"><option value="enabled">Enabled</option><option value="disabled">Disabled</option></select></div>
    </div>
    <div class="modal-footer"><button class="btn-ghost btn" data-bs-dismiss="modal">Cancel</button><button class="btn-primary-s btn" onclick="saveUser()"><i class="bi bi-check-lg me-1"></i>Save</button></div>
  </div></div>
</div>
<?php include '../includes/footer.php'; ?>
<script>
const BASE='<?= BASE_URL ?>'; let curP=1;
async function load(page=1){
  curP=page;
  const r=await APP.get(BASE+'/api/users.php',{page,q:document.getElementById('uq').value});
  if(!r.success)return;
  const{users,total,pages}=r.data;
  document.getElementById('uc').textContent=total+' user(s)';
  const tbody=document.getElementById('utbl');
  if(!users.length){tbody.innerHTML='<tr><td colspan="6"><div class="empty-state py-4"><div class="empty-icon">👥</div><div class="empty-title">No users found</div></div></td></tr>';return;}
  tbody.innerHTML=users.map(u=>{
    const ini=u.fullname.split(' ').map(n=>n[0]||'').join('').substring(0,2).toUpperCase();
    const av=u.profile_picture?`<img src="${BASE}/assets/uploads/profiles/${u.profile_picture}" style="width:34px;height:34px;border-radius:50%;object-fit:cover">`:`<div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#3b82f6);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;flex-shrink:0">${ini}</div>`;
    return `<tr>
      <td><div style="display:flex;align-items:center;gap:.6rem">${av}<span style="font-weight:600;font-size:.875rem">${u.fullname}</span></div></td>
      <td style="font-size:.875rem;color:var(--text-2)">${u.email}</td>
      <td>${APP.statusBadge(u.role)}</td>
      <td>${APP.statusBadge(u.status)}</td>
      <td style="font-size:.8rem;color:var(--text-2)">${APP.date(u.created_at)}</td>
      <td><div style="display:flex;gap:.3rem">
        <button class="btn-icon" onclick='openEdit(${JSON.stringify(u)})' title="Edit"><i class="bi bi-pencil"></i></button>
        <button class="btn-icon" onclick="toggleUser(${u.user_id},'${u.status}')" title="${u.status==='enabled'?'Disable':'Enable'}" style="color:${u.status==='enabled'?'var(--warning)':'var(--success)'}"><i class="bi bi-${u.status==='enabled'?'lock':'unlock'}"></i></button>
        <button class="btn-icon" onclick="delUser(${u.user_id},'${u.fullname.replace(/'/g,"\\'")}')" title="Delete" style="color:var(--danger)"><i class="bi bi-trash"></i></button>
      </div></td>
    </tr>`;
  }).join('');
  document.getElementById('upager').innerHTML=Array.from({length:pages},(_,i)=>`<a href="#" class="${i+1===page?'cur':''}" onclick="event.preventDefault();load(${i+1})">${i+1}</a>`).join('');
}
function openEdit(u){
  document.getElementById('eu-id').value=u.user_id;
  document.getElementById('eu-name').value=u.fullname;
  document.getElementById('eu-role').value=u.role;
  document.getElementById('eu-status').value=u.status;
  document.getElementById('eu-alert').classList.add('d-none');
  new bootstrap.Modal(document.getElementById('euModal')).show();
}
async function saveUser(){
  const fd=new FormData();fd.append('user_id',document.getElementById('eu-id').value);fd.append('fullname',document.getElementById('eu-name').value);fd.append('role',document.getElementById('eu-role').value);fd.append('status',document.getElementById('eu-status').value);fd.append('_method','PUT');
  const r=await APP.postForm(BASE+'/api/users.php',fd);
  if(r.success){bootstrap.Modal.getInstance(document.getElementById('euModal')).hide();APP.toast('User updated.','success');load(curP);}
  else{document.getElementById('eu-alert').textContent=r.message;document.getElementById('eu-alert').classList.remove('d-none');}
}
async function toggleUser(id,cur){
  const ns=cur==='enabled'?'disabled':'enabled';
  const fd=new FormData();fd.append('user_id',id);fd.append('status',ns);fd.append('_method','PUT');
  const r=await APP.postForm(BASE+'/api/users.php',fd);
  if(r.success){APP.toast('User '+ns+'.','success');load(curP);}else APP.toast(r.message,'error');
}
async function delUser(id,name){
  APP.confirm(`Delete user <b>${name}</b>?`,async()=>{
    const fd=new FormData();fd.append('user_id',id);fd.append('_method','DELETE');
    const r=await APP.postForm(BASE+'/api/users.php',fd);
    if(r.success){APP.toast('Deleted.','success');load(curP);}else APP.toast(r.message,'error');
  },'Delete User');
}
load();
</script>
