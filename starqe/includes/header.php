<?php
// header.php — Premium navbar component
$_settings   = getSettings();
$_siteName   = $_settings['site_name'] ?? 'STARQE';
$_unread     = isLoggedIn() ? dbCount("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0",[uid()]) : 0;
$_userInitial= strtoupper(substr($_SESSION['fullname']??'G',0,1));
$_bodyClass  = $bodyClass  ?? '';
$_activeNav  = $activeNav  ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="base-url" content="<?= BASE_URL ?>">
<meta name="csrf"     content="<?= csrf() ?>">
<meta name="description" content="STARQE — Find and book EV charging stations across the Philippines">
<title><?= isset($pageTitle) ? s($pageTitle).' — ' : '' ?><?= $_siteName ?></title>

<!-- Bootstrap 5 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<!-- Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
<!-- STARQE CSS -->
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<?php if(isset($extraHead)) echo $extraHead; ?>
</head>
<body class="<?= s($_bodyClass) ?>">

<!-- ── NAVBAR ──────────────────────────────────────────────── -->
<nav class="starqe-nav">
  <!-- Brand -->
  <a href="<?= BASE_URL ?>/index.php" class="nav-brand" style="text-decoration:none">
    <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
      <rect width="28" height="28" rx="8" fill="#2563EB"/>
      <path d="M16 5L8 15h8l-4 8 12-13h-9l3-5z" fill="#FFF" stroke="#FFF" stroke-width=".5"/>
    </svg>
    STAR<span class="brand-dot">QE</span>
  </a>

  <!-- Desktop nav links -->
  <div class="nav-links d-none d-md-flex">
    <a href="<?= BASE_URL ?>/map.php"
       class="nav-link-item <?= $_activeNav==='map'?'active':'' ?>" style="text-decoration:none">
      <i class="bi bi-map"></i> <span>Map</span>
    </a>
    <?php if(isLoggedIn() && urole()!=='guest'): ?>
    <a href="<?= BASE_URL ?>/dashboard.php"
       class="nav-link-item <?= $_activeNav==='dashboard'?'active':'' ?>" style="text-decoration:none">
      <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
    </a>
    <a href="<?= BASE_URL ?>/bookings.php"
       class="nav-link-item <?= $_activeNav==='bookings'?'active':'' ?>" style="text-decoration:none">
      <i class="bi bi-calendar-check"></i> <span>Bookings</span>
    </a>
    <a href="<?= BASE_URL ?>/favorites.php"
       class="nav-link-item <?= $_activeNav==='favorites'?'active':'' ?>" style="text-decoration:none">
      <i class="bi bi-heart"></i> <span>Favorites</span>
    </a>
    <?php if(isAdmin()): ?>
    <a href="<?= BASE_URL ?>/admin/dashboard.php"
       class="nav-link-item <?= $_activeNav==='admin'?'active':'' ?>" style="text-decoration:none">
      <i class="bi bi-shield-check"></i> <span>Admin</span>
    </a>
    <?php endif; ?>
    <?php endif; ?>
  </div>

  <!-- Right side -->
  <div class="d-flex align-items-center gap-2 ms-auto">
    <?php if(isLoggedIn() && urole()!=='guest'): ?>
    <!-- Notifications -->
    <div class="dropdown">
      <button class="nav-notif-btn" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
        <i class="bi bi-bell"></i>
        <span class="notif-badge" id="notif-badge" style="display:<?= $_unread>0?'flex':'none' ?>">
          <?= min($_unread, 99) ?>
        </span>
      </button>
      <div class="dropdown-menu dropdown-menu-end notif-dropdown p-0">
        <div class="px-3 py-2 border-bottom d-flex align-items-center justify-content-between">
          <span style="font-weight:700;font-size:.9rem">Notifications</span>
          <button onclick="APP.markAllRead()" class="btn btn-link btn-sm p-0" style="font-size:.75rem;color:var(--primary)">
            Mark all read
          </button>
        </div>
        <div id="notif-list" style="max-height:320px;overflow-y:auto">
          <div class="p-3 text-center" style="font-size:.85rem;color:#94a3b8">Loading...</div>
        </div>
        <div class="border-top px-3 py-2 text-center">
          <a href="<?= BASE_URL ?>/notifications.php" style="font-size:.8rem;color:var(--primary)">View all</a>
        </div>
      </div>
    </div>

    <!-- User menu -->
    <div class="dropdown">
      <button class="user-avatar-btn" data-bs-toggle="dropdown">
        <div class="avatar">
          <?php if(upic() && file_exists(__DIR__.'/../assets/uploads/profiles/'.upic())): ?>
          <img src="<?= BASE_URL ?>/assets/uploads/profiles/<?= s(upic()) ?>" alt="">
          <?php else: ?>
          <?= $_userInitial ?>
          <?php endif; ?>
        </div>
        <span class="d-none d-lg-block" style="font-size:.875rem;font-weight:600;color:var(--text)"><?= uname() ?></span>
        <i class="bi bi-chevron-down d-none d-lg-block" style="font-size:.7rem;color:var(--text-3)"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width:210px;border-radius:var(--r-lg);border:1px solid var(--border)">
        <li class="px-3 py-2 border-bottom">
          <div style="font-weight:700;font-size:.9rem"><?= uname() ?></div>
          <div style="font-size:.75rem;color:var(--text-2)"><?= uemail() ?> · <?= ucfirst(urole()) ?></div>
        </li>
        <li><a class="dropdown-item" href="<?= BASE_URL ?>/profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
        <li><a class="dropdown-item" href="<?= BASE_URL ?>/history.php"><i class="bi bi-clock-history me-2"></i>Activity</a></li>
        <?php if(isAdmin()): ?>
        <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
        <?php endif; ?>
        <li><hr class="dropdown-divider my-1"></li>
        <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
      </ul>
    </div>

    <?php else: ?>
    <a href="<?= BASE_URL ?>/login.php" class="btn-ghost btn btn-sm" style="text-decoration:none">Login</a>
    <a href="<?= BASE_URL ?>/register.php" class="btn-primary-s btn btn-sm" style="text-decoration:none">Get Started</a>
    <?php endif; ?>

    <!-- Mobile hamburger -->
    <?php if(isLoggedIn()): ?>
    <button class="btn-icon d-md-none" id="sidebar-toggle">
      <i class="bi bi-list" style="font-size:1.2rem"></i>
    </button>
    <?php endif; ?>
  </div>
</nav>

<!-- Mobile nav drawer -->
<?php if(isLoggedIn() && urole()!=='guest'): ?>
<div class="admin-sidebar d-md-none" id="mobile-nav" style="z-index:1041">
  <div class="px-3 py-2 border-bottom mb-2 d-flex align-items-center justify-content-between">
    <span style="font-weight:700;color:var(--primary)">Navigation</span>
    <button class="btn-icon" onclick="document.getElementById('mobile-nav').classList.remove('open')">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>
  <a href="<?= BASE_URL ?>/map.php"         class="sidebar-link"><i class="bi bi-map"></i> Live Map</a>
  <a href="<?= BASE_URL ?>/dashboard.php"   class="sidebar-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
  <a href="<?= BASE_URL ?>/bookings.php"    class="sidebar-link"><i class="bi bi-calendar-check"></i> Bookings</a>
  <a href="<?= BASE_URL ?>/favorites.php"   class="sidebar-link"><i class="bi bi-heart"></i> Favorites</a>
  <?php if(isAdmin()): ?>
  <a href="<?= BASE_URL ?>/admin/dashboard.php" class="sidebar-link"><i class="bi bi-shield-check"></i> Admin</a>
  <?php endif; ?>
  <a href="<?= BASE_URL ?>/profile.php"     class="sidebar-link"><i class="bi bi-person"></i> Profile</a>
  <a href="<?= BASE_URL ?>/logout.php"      class="sidebar-link" style="color:var(--danger)"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>
<div id="sidebar-overlay" onclick="document.getElementById('mobile-nav').classList.remove('open');this.style.display='none'"
  style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:1040"></div>
<script>
document.getElementById('sidebar-toggle')?.addEventListener('click',()=>{
  const n=document.getElementById('mobile-nav');
  const o=document.getElementById('sidebar-overlay');
  n.classList.toggle('open');
  if(o) o.style.display=n.classList.contains('open')?'block':'none';
});
</script>
<?php endif; ?>

<!-- Toast container -->
<div id="toast-container"></div>

<?php
// Flash message
if(!empty($_GET['flash_success'])): ?>
<div class="flash-bar alert alert-success mb-0 rounded-0"><?= s($_GET['flash_success']) ?></div>
<?php endif;
if(!empty($_GET['flash_error'])): ?>
<div class="flash-bar alert alert-danger mb-0 rounded-0"><?= s($_GET['flash_error']) ?></div>
<?php endif; ?>
