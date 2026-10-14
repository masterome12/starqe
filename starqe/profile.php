<?php
require_once 'config/config.php';
requireLogin();
$pageTitle='My Profile'; $activeNav='profile'; $settings=getSettings();
$user=dbOne('SELECT * FROM users WHERE user_id=?',[uid()]);
include 'includes/header.php';
?>
<div class="page-wrap"><div class="content-pad" style="max-width:760px;margin:0 auto">
  <h1 style="font-size:1.7rem;font-weight:800;margin-bottom:1.5rem">My Profile</h1>
  <div class="row g-4">
    <!-- Avatar & info -->
    <div class="col-md-4">
      <div class="card-s text-center" style="padding:1.5rem">
        <div style="position:relative;width:96px;height:96px;margin:0 auto 1rem">
          <div class="avatar avatar-xl mx-auto" id="av-container">
            <?php if(!empty($user['profile_picture'])&&file_exists(__DIR__.'/assets/uploads/profiles/'.$user['profile_picture'])): ?>
            <img src="<?= BASE_URL ?>/assets/uploads/profiles/<?= s($user['profile_picture']) ?>" alt="" id="av-img">
            <?php else: ?>
            <span id="av-letter"><?= strtoupper(substr($user['fullname'],0,1)) ?></span>
            <?php endif; ?>
          </div>
          <label for="pic-input" style="position:absolute;bottom:-4px;right:-4px;width:30px;height:30px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.8rem;border:2px solid #fff;box-shadow:0 2px 8px rgba(37,99,235,.3)">
            <i class="bi bi-camera-fill"></i>
          </label>
          <input type="file" id="pic-input" accept="image/*" style="display:none" onchange="uploadPic(this)">
        </div>
        <div style="font-weight:700;font-size:1rem"><?= s($user['fullname']) ?></div>
        <div style="font-size:.82rem;color:var(--text-2)"><?= s($user['email']) ?></div>
        <div style="margin-top:.5rem"><?= statusBadge($user['role']) ?></div>
        <div style="font-size:.75rem;color:var(--text-3);margin-top:.4rem">Member since <?= date('M Y',strtotime($user['created_at'])) ?></div>
      </div>
    </div>
    <!-- Forms -->
    <div class="col-md-8 d-flex flex-column gap-4">
      <!-- Update profile -->
      <div class="card-s">
        <div class="card-hd"><div class="metric-icon blue" style="width:32px;height:32px;font-size:.85rem"><i class="bi bi-person"></i></div><span class="title">Personal Info</span></div>
        <div class="card-bd">
          <div id="prof-alert" class="alert d-none mb-3"></div>
          <div class="form-group"><label class="inp-lbl">Full Name</label><input type="text" class="inp" id="pn" value="<?= s($user['fullname']) ?>"></div>
          <div class="form-group"><label class="inp-lbl">Email Address</label><input type="email" class="inp" id="pe" value="<?= s($user['email']) ?>"></div>
          <button class="btn-primary-s btn" onclick="saveProfile()"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
        </div>
      </div>
      <!-- Change password -->
      <div class="card-s">
        <div class="card-hd"><div class="metric-icon green" style="width:32px;height:32px;font-size:.85rem"><i class="bi bi-shield-lock"></i></div><span class="title">Change Password</span></div>
        <div class="card-bd">
          <div id="pw-alert" class="alert d-none mb-3"></div>
          <div class="form-group"><label class="inp-lbl">Current Password</label>
            <div class="inp-wrapper"><span class="inp-icon"><i class="bi bi-lock"></i></span><input type="password" class="inp with-icon" id="cp" placeholder="••••••••"></div>
          </div>
          <div class="form-group"><label class="inp-lbl">New Password</label>
            <div class="inp-wrapper"><span class="inp-icon"><i class="bi bi-lock-fill"></i></span><input type="password" class="inp with-icon" id="np" placeholder="Min 8 chars"></div>
          </div>
          <div class="form-group"><label class="inp-lbl">Confirm New Password</label>
            <div class="inp-wrapper"><span class="inp-icon"><i class="bi bi-lock-fill"></i></span><input type="password" class="inp with-icon" id="cnp" placeholder="Repeat"></div>
          </div>
          <button class="btn-outline-s btn" onclick="changePw()"><i class="bi bi-lock me-1"></i>Update Password</button>
        </div>
      </div>
    </div>
  </div>
</div></div>
<?php include 'includes/footer.php'; ?>
<script>
const BASE='<?= BASE_URL ?>';
async function saveProfile(){
  const a=document.getElementById('prof-alert');a.className='alert d-none';
  const r=await APP.post(BASE+'/api/users.php',{action:'update_profile',fullname:document.getElementById('pn').value,email:document.getElementById('pe').value});
  a.className='alert '+(r.success?'alert-success':'alert-danger');
  a.textContent=r.message;a.classList.remove('d-none');
  setTimeout(()=>a.classList.add('d-none'),4000);
}
async function changePw(){
  const a=document.getElementById('pw-alert');a.className='alert d-none';
  const r=await APP.post(BASE+'/api/users.php',{action:'change_password',current_password:document.getElementById('cp').value,new_password:document.getElementById('np').value,confirm_password:document.getElementById('cnp').value});
  a.className='alert '+(r.success?'alert-success':'alert-danger');
  a.textContent=r.message;a.classList.remove('d-none');
  if(r.success){document.getElementById('cp').value='';document.getElementById('np').value='';document.getElementById('cnp').value='';}
}
async function uploadPic(inp){
  if(!inp.files.length)return;
  const fd=new FormData();fd.append('picture',inp.files[0]);fd.append('action','upload_picture');
  const r=await APP.postForm(BASE+'/api/users.php',fd);
  if(r.success){
    APP.toast('Photo updated!','success');
    const c=document.getElementById('av-container');
    c.innerHTML=`<img src="${r.data.url}?t=${Date.now()}" alt="" style="width:100%;height:100%;object-fit:cover">`;
  }else APP.toast(r.message,'error');
}
</script>
