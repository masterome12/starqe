<?php
require_once 'config/config.php';
if(isLoggedIn()){ redirect('/dashboard.php'); }
$pageTitle='Create Account'; $bodyClass='auth-body'; $settings=getSettings();
include 'includes/header.php';
?>
<style>
body.auth-body{background:linear-gradient(135deg,#eff6ff 0%,#dbeafe 50%,#bfdbfe 100%)}
body.auth-body .starqe-nav{background:rgba(255,255,255,.8);backdrop-filter:blur(12px)}
.pw-strength-bar{height:4px;border-radius:99px;background:var(--border);overflow:hidden;margin-top:.35rem}
.pw-strength-fill{height:100%;border-radius:99px;width:0;transition:width .3s,background .3s}
</style>

<div class="auth-page" style="padding-top:calc(var(--nav-h)+1rem)">
  <div class="auth-card" style="max-width:460px">
    <div class="auth-logo">
      <svg width="44" height="44" viewBox="0 0 28 28" fill="none" style="margin-bottom:.5rem">
        <rect width="28" height="28" rx="8" fill="#2563EB"/>
        <path d="M16 5L8 15h8l-4 8 12-13h-9l3-5z" fill="#FFF"/>
      </svg>
      <div class="logo-text">STARQE</div>
      <p style="font-size:.875rem;color:var(--text-2);margin-top:.25rem">Create your free account in seconds.</p>
    </div>

    <div id="reg-alert" class="alert alert-danger d-none d-flex align-items-center gap-2"></div>

    <div class="form-group">
      <label class="inp-lbl">Full Name</label>
      <div class="inp-wrapper">
        <span class="inp-icon"><i class="bi bi-person"></i></span>
        <input type="text" id="r-name" class="inp with-icon" placeholder="Juan dela Cruz" autocomplete="name">
      </div>
      <div class="inp-error" id="err-name">Please enter your full name.</div>
    </div>

    <div class="form-group">
      <label class="inp-lbl">Email Address</label>
      <div class="inp-wrapper">
        <span class="inp-icon"><i class="bi bi-envelope"></i></span>
        <input type="email" id="r-email" class="inp with-icon" placeholder="you@example.com" autocomplete="email">
      </div>
      <div class="inp-error" id="err-remail">Please enter a valid email.</div>
    </div>

    <div class="form-group">
      <label class="inp-lbl">Password</label>
      <div class="inp-wrapper">
        <span class="inp-icon"><i class="bi bi-lock"></i></span>
        <input type="password" id="r-pw" class="inp with-icon with-icon-right" placeholder="Min. 8 chars, 1 uppercase, 1 number" oninput="checkStrength(this.value)">
        <span class="inp-icon-right" data-pw-toggle="r-pw">👁</span>
      </div>
      <div class="pw-strength-bar"><div class="pw-strength-fill" id="pw-fill"></div></div>
      <div id="pw-strength-lbl" style="font-size:.72rem;margin-top:.2rem;color:var(--text-3)"></div>
      <div class="inp-error" id="err-rpw">Password must be at least 8 chars with uppercase and number.</div>
    </div>

    <div class="form-group">
      <label class="inp-lbl">Confirm Password</label>
      <div class="inp-wrapper">
        <span class="inp-icon"><i class="bi bi-lock-fill"></i></span>
        <input type="password" id="r-pw2" class="inp with-icon with-icon-right" placeholder="Repeat your password">
        <span class="inp-icon-right" data-pw-toggle="r-pw2">👁</span>
      </div>
      <div class="inp-error" id="err-rpw2">Passwords do not match.</div>
    </div>

    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" id="r-terms">
      <label class="form-check-label" for="r-terms" style="font-size:.875rem;color:var(--text-2)">
        I agree to the <a href="#" style="color:var(--primary)">Terms of Service</a>
      </label>
      <div class="inp-error" id="err-terms">You must agree to the terms.</div>
    </div>

    <button id="reg-btn" class="btn-primary-s btn w-100 py-2 justify-content-center" onclick="doRegister()">
      <i class="bi bi-person-plus me-2"></i>Create Account
    </button>

    <p class="text-center mt-4 mb-0" style="font-size:.875rem;color:var(--text-2)">
      Already have an account? <a href="<?= BASE_URL ?>/login.php" style="color:var(--primary);font-weight:600">Sign in</a>
    </p>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
<script>
const BASE='<?= BASE_URL ?>';
function checkStrength(pw){
  let s=0;
  if(pw.length>=8)s++;if(/[A-Z]/.test(pw))s++;if(/[a-z]/.test(pw))s++;if(/[0-9]/.test(pw))s++;if(/[^A-Za-z0-9]/.test(pw))s++;
  const lvls=[{w:'20%',c:'#ef4444',t:'Very weak'},{w:'40%',c:'#f59e0b',t:'Weak'},{w:'60%',c:'#eab308',t:'Fair'},{w:'80%',c:'#22c55e',t:'Strong'},{w:'100%',c:'#16a34a',t:'Very strong'}];
  const l=lvls[Math.max(0,Math.min(s-1,4))];
  const fill=document.getElementById('pw-fill');
  const lbl=document.getElementById('pw-strength-lbl');
  fill.style.width=pw?l.w:'0';fill.style.background=l.c;
  lbl.textContent=pw?l.t:'';lbl.style.color=l.c;
}

function validateRegForm(){
  let ok=true;
  const checks=[
    ['r-name','err-name',v=>v.length>=2],
    ['r-email','err-remail',v=>/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)],
    ['r-pw','err-rpw',v=>v.length>=8&&/[A-Z]/.test(v)&&/[0-9]/.test(v)],
  ];
  checks.forEach(([id,errId,fn])=>{
    const el=document.getElementById(id);
    const err=document.getElementById(errId);
    if(!fn(el.value.trim())){err.classList.add('show');el.classList.add('is-invalid');ok=false;}
    else{err.classList.remove('show');el.classList.remove('is-invalid');}
  });
  const pw2=document.getElementById('r-pw2');const err2=document.getElementById('err-rpw2');
  if(pw2.value!==document.getElementById('r-pw').value){err2.classList.add('show');pw2.classList.add('is-invalid');ok=false;}
  else{err2.classList.remove('show');pw2.classList.remove('is-invalid');}
  const terms=document.getElementById('r-terms');const errT=document.getElementById('err-terms');
  if(!terms.checked){errT.classList.add('show');ok=false;}else errT.classList.remove('show');
  return ok;
}

async function doRegister(){
  if(!validateRegForm()) return;
  const btn=document.getElementById('reg-btn');
  const alert=document.getElementById('reg-alert');
  APP.btnLoad(btn,'Creating account...');
  alert.classList.add('d-none');

  const r=await APP.post(BASE+'/api/auth.php',{
    action:'register',
    fullname:document.getElementById('r-name').value.trim(),
    email:document.getElementById('r-email').value.trim(),
    password:document.getElementById('r-pw').value,
    confirm_password:document.getElementById('r-pw2').value
  });

  if(r.success){
    APP.toast('Account created! Welcome to STARQE 🎉','success');
    setTimeout(()=>window.location=r.data?.redirect||BASE+'/dashboard.php',900);
  } else {
    alert.innerHTML='<i class="bi bi-exclamation-circle-fill me-2"></i>'+r.message;
    alert.classList.remove('d-none');alert.classList.add('d-flex');
    APP.btnReset(btn);
  }
}
</script>
