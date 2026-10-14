<?php
require_once 'config/config.php';
if(isLoggedIn()){ redirect('/dashboard.php'); }
$pageTitle='Forgot Password'; $bodyClass='auth-body'; $settings=getSettings();
$sent=false; $error='';

if(!empty($_POST['email'])){
  $email=se($_POST['email']??'');
  if(!validEmail($email)){ $error='Please enter a valid email address.'; }
  else {
    $u=dbOne('SELECT user_id,fullname FROM users WHERE email=? AND status=\'enabled\'',[$email]);
    if($u){
      // In production: generate token, store in DB, send email
      // For demo: just show success
      logHistory((int)$u['user_id'],'PASSWORD_RESET_REQUEST','Reset requested for: '.$email);
    }
    // Always show success to prevent email enumeration
    $sent=true;
  }
}
include 'includes/header.php';
?>
<style>
body.auth-body{background:linear-gradient(135deg,#eff6ff 0%,#dbeafe 50%,#bfdbfe 100%)}
body.auth-body .starqe-nav{background:rgba(255,255,255,.8);backdrop-filter:blur(12px)}
</style>
<div class="auth-page" style="padding-top:calc(var(--nav-h)+1rem)">
  <div class="auth-card">
    <div class="auth-logo">
      <svg width="44" height="44" viewBox="0 0 28 28" fill="none" style="margin-bottom:.5rem">
        <rect width="28" height="28" rx="8" fill="#2563EB"/>
        <path d="M16 5L8 15h8l-4 8 12-13h-9l3-5z" fill="#FFF"/>
      </svg>
      <div class="logo-text">STARQE</div>
    </div>

    <?php if($sent): ?>
    <div class="text-center">
      <div style="font-size:3rem;margin-bottom:1rem">📧</div>
      <h4 style="font-weight:700;margin-bottom:.5rem">Check Your Email</h4>
      <p style="color:var(--text-2);font-size:.9rem;margin-bottom:1.5rem">
        If an account exists for that email, we've sent password reset instructions.
      </p>
      <a href="<?= BASE_URL ?>/login.php" class="btn-primary-s btn w-100 justify-content-center" style="text-decoration:none">
        <i class="bi bi-arrow-left me-2"></i>Back to Login
      </a>
    </div>
    <?php else: ?>
    <h4 class="text-center" style="font-weight:700;margin-bottom:.25rem">Reset Password</h4>
    <p class="text-center" style="color:var(--text-2);font-size:.875rem;margin-bottom:1.5rem">Enter your email to receive reset instructions.</p>

    <?php if($error): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 mb-3">
      <i class="bi bi-exclamation-circle-fill"></i><?= s($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group">
        <label class="inp-lbl">Email Address</label>
        <div class="inp-wrapper">
          <span class="inp-icon"><i class="bi bi-envelope"></i></span>
          <input type="email" name="email" class="inp with-icon" placeholder="you@example.com" required autocomplete="email">
        </div>
      </div>
      <button type="submit" class="btn-primary-s btn w-100 py-2 justify-content-center mb-3">
        <i class="bi bi-send me-2"></i>Send Reset Link
      </button>
    </form>
    <p class="text-center mb-0" style="font-size:.875rem;color:var(--text-2)">
      Remember it? <a href="<?= BASE_URL ?>/login.php" style="color:var(--primary);font-weight:600">Sign in</a>
    </p>
    <?php endif; ?>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
