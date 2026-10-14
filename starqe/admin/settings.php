<?php
require_once '../config/config.php';
requireAdmin();
$pageTitle='System Settings'; $activeNav='admin'; $activePage='settings'; $settings=getSettings();
include '../includes/header.php';
include '../includes/admin_sidebar.php';
?>
<div class="admin-wrap"><div class="content-pad" style="max-width:760px">
  <h1 style="font-size:1.7rem;font-weight:800;margin-bottom:1.5rem">System Settings</h1>
  <div id="s-alert" class="alert d-none mb-4"></div>

  <!-- General -->
  <div class="card-s mb-4">
    <div class="card-hd"><div class="metric-icon blue" style="width:32px;height:32px;font-size:.85rem"><i class="bi bi-gear"></i></div><span class="title">General Settings</span></div>
    <div class="card-bd">
      <div class="row g-3">
        <div class="col-md-6"><label class="inp-lbl">Site Name</label><input type="text" class="inp" id="sn" value="<?= s($settings['site_name']) ?>"></div>
        <div class="col-md-6"><label class="inp-lbl">Contact Email</label><input type="email" class="inp" id="se" value="<?= s($settings['contact_email']) ?>"></div>
        <div class="col-md-4"><label class="inp-lbl">Default Price/kWh (₱)</label><input type="number" step="0.01" class="inp" id="sp" value="<?= $settings['default_price'] ?>"></div>
        <div class="col-md-4"><label class="inp-lbl">Max Advance Booking (Days)</label><input type="number" class="inp" id="sad" value="<?= $settings['booking_advance_days'] ?>" min="1" max="30"></div>
        <div class="col-md-4"><label class="inp-lbl">Max Booking Hours</label><input type="number" class="inp" id="smh" value="<?= $settings['max_booking_hours'] ?>" min="1" max="24"></div>
      </div>
    </div>
  </div>

  <!-- Maintenance Mode -->
  <div class="card-s mb-4">
    <div class="card-hd"><div class="metric-icon orange" style="width:32px;height:32px;font-size:.85rem"><i class="bi bi-tools"></i></div><span class="title">Maintenance Mode</span></div>
    <div class="card-bd">
      <div class="d-flex align-items-center justify-content-between p-3 rounded" id="maint-banner"
           style="background:<?= $settings['maintenance_mode']?'#fff7ed':'#f0fdf4' ?>;border:1px solid <?= $settings['maintenance_mode']?'#fdba74':'#86efac' ?>">
        <div>
          <div id="maint-title" style="font-weight:700"><?= $settings['maintenance_mode']?'⚠️ Maintenance Mode ON':'✅ Site is Live' ?></div>
          <div id="maint-desc" style="font-size:.82rem;color:var(--text-2);margin-top:.2rem"><?= $settings['maintenance_mode']?'Only admins can access the system.':'All users can access normally.' ?></div>
        </div>
        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" id="mm" role="switch"
                 style="width:3rem;height:1.5rem;cursor:pointer" <?= $settings['maintenance_mode']?'checked':'' ?>>
        </div>
      </div>
    </div>
  </div>

  <!-- Admin Password -->
  <div class="card-s mb-4">
    <div class="card-hd"><div class="metric-icon green" style="width:32px;height:32px;font-size:.85rem"><i class="bi bi-shield-lock"></i></div><span class="title">Admin Password</span></div>
    <div class="card-bd">
      <div id="pw-alert" class="alert d-none mb-3"></div>
      <div class="row g-3">
        <div class="col-md-4"><label class="inp-lbl">Current Password</label>
          <div class="inp-wrapper"><span class="inp-icon"><i class="bi bi-lock"></i></span>
            <input type="password" class="inp with-icon" id="acp" placeholder="••••••••">
          </div>
        </div>
        <div class="col-md-4"><label class="inp-lbl">New Password</label>
          <div class="inp-wrapper"><span class="inp-icon"><i class="bi bi-lock-fill"></i></span>
            <input type="password" class="inp with-icon" id="anp" placeholder="Min 8 chars">
          </div>
        </div>
        <div class="col-md-4"><label class="inp-lbl">Confirm</label>
          <div class="inp-wrapper"><span class="inp-icon"><i class="bi bi-lock-fill"></i></span>
            <input type="password" class="inp with-icon" id="acnp" placeholder="Repeat">
          </div>
        </div>
      </div>
      <button class="btn-outline-s btn mt-3" onclick="changePw()"><i class="bi bi-lock me-1"></i>Update Password</button>
    </div>
  </div>

  <!-- System Info -->
  <div class="card-s mb-4">
    <div class="card-hd"><div class="metric-icon" style="width:32px;height:32px;font-size:.85rem;background:var(--bg-soft);color:var(--text-2)"><i class="bi bi-info-circle"></i></div><span class="title" style="color:var(--text-2)">System Info</span></div>
    <div class="card-bd">
      <div class="row g-3" style="font-size:.875rem">
        <?php foreach([
          ['Application','STARQE v2.0.0'],
          ['PHP Version', phpversion()],
          ['Server Time', date('Y-m-d H:i:s T')],
          ['Database',    'MySQL (PDO)'],
          ['Total Users',    dbCount('SELECT COUNT(*) FROM users').' registered'],
          ['Total Stations', dbCount('SELECT COUNT(*) FROM stations').' loaded'],
          ['Total Bookings', dbCount('SELECT COUNT(*) FROM bookings').' all time'],
          ['Pending Reports',dbCount("SELECT COUNT(*) FROM reports WHERE status='pending'").' pending'],
        ] as [$lbl,$val]): ?>
        <div class="col-md-6">
          <div class="inp-lbl"><?= $lbl ?></div>
          <div style="color:var(--text)"><?= $val ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-end gap-3">
    <button class="btn-ghost btn" onclick="location.reload()"><i class="bi bi-arrow-clockwise me-1"></i>Reset</button>
    <button class="btn-primary-s btn px-5" onclick="saveSettings()"><i class="bi bi-check-lg me-1"></i>Save Settings</button>
  </div>
</div></div>
<?php include '../includes/footer.php'; ?>
<script>
const BASE='<?= BASE_URL ?>';

document.getElementById('mm').addEventListener('change',function(){
  const on=this.checked;
  const b=document.getElementById('maint-banner');
  b.style.background=on?'#fff7ed':'#f0fdf4';
  b.style.borderColor=on?'#fdba74':'#86efac';
  document.getElementById('maint-title').textContent=on?'⚠️ Maintenance Mode ON':'✅ Site is Live';
  document.getElementById('maint-desc').textContent=on?'Only admins can access the system.':'All users can access normally.';
});

async function saveSettings(){
  const btn=event.target;
  APP.btnLoad(btn,'Saving…');
  const alr=document.getElementById('s-alert');
  alr.className='alert d-none';
  const fd=new FormData();
  fd.append('site_name',            document.getElementById('sn').value);
  fd.append('contact_email',        document.getElementById('se').value);
  fd.append('default_price',        document.getElementById('sp').value);
  fd.append('maintenance_mode',     document.getElementById('mm').checked?1:0);
  fd.append('booking_advance_days', document.getElementById('sad').value);
  fd.append('max_booking_hours',    document.getElementById('smh').value);
  fd.append('_method','PUT');
  const r=await APP.postForm(BASE+'/api/settings.php',fd);
  alr.className='alert '+(r.success?'alert-success':'alert-danger');
  alr.textContent=r.message;
  alr.classList.remove('d-none');
  APP.btnReset(btn);
  if(r.success) APP.toast('Settings saved!','success');
  setTimeout(()=>alr.classList.add('d-none'),5000);
}

async function changePw(){
  const alr=document.getElementById('pw-alert');
  alr.className='alert d-none';
  const r=await APP.post(BASE+'/api/users.php',{
    action:'change_password',
    current_password: document.getElementById('acp').value,
    new_password:     document.getElementById('anp').value,
    confirm_password: document.getElementById('acnp').value
  });
  alr.className='alert '+(r.success?'alert-success':'alert-danger');
  alr.textContent=r.message;
  alr.classList.remove('d-none');
  if(r.success){
    document.getElementById('acp').value='';
    document.getElementById('anp').value='';
    document.getElementById('acnp').value='';
    APP.toast('Password updated!','success');
  }
  setTimeout(()=>alr.classList.add('d-none'),5000);
}
</script>
