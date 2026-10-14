<?php
require_once 'config/config.php';
if(isLoggedIn()){ redirect(isAdmin()?'/admin/dashboard.php':'/dashboard.php'); }
$pageTitle = 'Sign In';
$bodyClass = 'auth-body';
$settings  = getSettings();
include 'includes/header.php';
?>
<style>
body.auth-body { background:linear-gradient(135deg,#eff6ff 0%,#dbeafe 50%,#bfdbfe 100%); }
body.auth-body .starqe-nav { background:rgba(255,255,255,.8); backdrop-filter:blur(12px); }
</style>

<div class="auth-page" style="padding-top:calc(var(--nav-h) + 1rem)">
  <div class="auth-card">
    <!-- Logo -->
    <div class="auth-logo">
      <svg width="44" height="44" viewBox="0 0 28 28" fill="none" style="margin-bottom:.5rem">
        <rect width="28" height="28" rx="8" fill="#2563EB"/>
        <path d="M16 5L8 15h8l-4 8 12-13h-9l3-5z" fill="#FFF"/>
      </svg>
      <div class="logo-text">STARQE</div>
      <p style="font-size:.875rem;color:var(--text-2);margin-top:.25rem">Welcome back! Sign in to continue.</p>
    </div>

    <div id="login-alert" class="alert alert-danger d-none align-items-center gap-2" role="alert">
      <i class="bi bi-exclamation-circle-fill"></i>
      <span id="login-alert-msg"></span>
    </div>
    <div id="login-success" class="alert alert-success d-none align-items-center gap-2" role="alert">
      <i class="bi bi-check-circle-fill"></i>
      <span id="login-success-msg"></span>
    </div>

    <!-- Form -->
    <div class="form-group">
      <label class="inp-lbl">Email Address</label>
      <div class="inp-wrapper">
        <span class="inp-icon"><i class="bi bi-envelope"></i></span>
        <input type="email" id="login-email" class="inp with-icon" placeholder="you@example.com" autocomplete="email" value="admin@starqe.ph">
      </div>
      <div class="inp-error" id="err-email">Please enter a valid email.</div>
    </div>

    <div class="form-group">
      <div class="d-flex justify-content-between align-items-center mb-1">
        <label class="inp-lbl mb-0">Password</label>
        <a href="<?= BASE_URL ?>/forgot-password.php" style="font-size:.78rem;color:var(--primary)">Forgot password?</a>
      </div>
      <div class="inp-wrapper">
        <span class="inp-icon"><i class="bi bi-lock"></i></span>
        <input type="password" id="login-pw" class="inp with-icon with-icon-right" placeholder="••••••••" autocomplete="current-password" value="password">
        <span class="inp-icon-right" data-pw-toggle="login-pw" title="Show/hide password">👁</span>
      </div>
      <div class="inp-error" id="err-pw">Password must be at least 8 characters.</div>
    </div>

    <div class="d-flex align-items-center justify-content-between mb-3">
      <div class="form-check mb-0">
        <input class="form-check-input" type="checkbox" id="remember-me">
        <label class="form-check-label" for="remember-me" style="font-size:.875rem;color:var(--text-2)">Remember me</label>
      </div>
    </div>

    <button id="login-btn" class="btn-primary-s btn w-100 py-2 justify-content-center" onclick="doLogin()">
      <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
    </button>

    <div class="auth-divider"><span>or</span></div>

    <a href="<?= BASE_URL ?>/login.php?guest=1"
       style="display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.6rem;border:1.5px dashed var(--border);border-radius:var(--r);font-size:.875rem;color:var(--text-2);text-decoration:none;transition:all .2s"
       onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)'"
       onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-2)'">
      <i class="bi bi-eye"></i> Browse as Guest
    </a>

    <p class="text-center mt-4 mb-0" style="font-size:.875rem;color:var(--text-2)">
      Don't have an account?
      <a href="<?= BASE_URL ?>/register.php" style="color:var(--primary);font-weight:600">Create one free</a>
    </p>

    <!-- Demo hint -->
    <div class="mt-3 p-3 rounded" style="background:var(--bg-soft);border:1px solid var(--border);font-size:.8rem">
      <strong style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-3)">Demo Credentials</strong>
      <div class="mt-1" style="color:var(--text-2)">
        Admin: <code>admin@starqe.ph</code> / <code>password</code><br>
        User: <code>juan@example.com</code> / <code>password</code>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
<script>
const BASE='<?= BASE_URL ?>';

// Guest redirect
<?php if(!empty($_GET['guest'])): ?>
APP.post(BASE+'/api/auth.php',{action:'guest'}).then(r=>{
  if(r.success) window.location=r.data?.redirect||BASE+'/map.php';
});
<?php endif; ?>

function validateLoginForm(){
  let ok=true;
  const email=document.getElementById('login-email').value.trim();
  const pw=document.getElementById('login-pw').value;
  const errE=document.getElementById('err-email');
  const errP=document.getElementById('err-pw');
  const emailInp=document.getElementById('login-email');
  const pwInp=document.getElementById('login-pw');

  if(!email||!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){
    errE.classList.add('show'); emailInp.classList.add('is-invalid'); ok=false;
  } else { errE.classList.remove('show'); emailInp.classList.remove('is-invalid'); }

  if(pw.length<6){
    errP.classList.add('show'); pwInp.classList.add('is-invalid'); ok=false;
  } else { errP.classList.remove('show'); pwInp.classList.remove('is-invalid'); }
  return ok;
}

async function doLogin(){
  if(!validateLoginForm()) return;
  const btn=document.getElementById('login-btn');
  const alert=document.getElementById('login-alert');
  const success=document.getElementById('login-success');
  APP.btnLoad(btn,'Signing in...');
  alert.classList.add('d-none');

  const r=await APP.post(BASE+'/api/auth.php',{
    action:'login',
    email:document.getElementById('login-email').value.trim(),
    password:document.getElementById('login-pw').value,
    remember:document.getElementById('remember-me').checked?1:0
  });

  if(r.success){
    success.querySelector('#login-success-msg').textContent='Login successful! Redirecting...';
    success.classList.remove('d-none');
    success.classList.add('d-flex');
    setTimeout(()=>window.location=r.data?.redirect||BASE+'/dashboard.php',700);
  } else {
    document.getElementById('login-alert-msg').textContent=r.message;
    alert.classList.remove('d-none');
    alert.classList.add('d-flex');
    APP.btnReset(btn);
    document.getElementById('login-email').classList.add('is-invalid');
    document.getElementById('login-pw').classList.add('is-invalid');
  }
}

document.addEventListener('keydown',e=>{ if(e.key==='Enter') doLogin(); });
</script>
