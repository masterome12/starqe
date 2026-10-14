<?php
$_pendingReports  = dbCount("SELECT COUNT(*) FROM reports WHERE status='pending'");
$_pendingBookings = dbCount("SELECT COUNT(*) FROM bookings WHERE status='pending'");
?>
<aside class="admin-sidebar" id="admin-sidebar">
  <div class="sidebar-section">MAIN</div>
  <a href="<?= BASE_URL ?>/admin/dashboard.php"
     class="sidebar-link <?= ($activePage??'')==='dash'?'active':'' ?>">
    <i class="bi bi-speedometer2"></i> Dashboard
  </a>
  <a href="<?= BASE_URL ?>/map.php" class="sidebar-link">
    <i class="bi bi-map"></i> Live Map
  </a>

  <div class="sidebar-section">MANAGE</div>
  <a href="<?= BASE_URL ?>/admin/stations.php"
     class="sidebar-link <?= ($activePage??'')==='stations'?'active':'' ?>">
    <i class="bi bi-ev-station"></i> Stations
  </a>
  <a href="<?= BASE_URL ?>/admin/users.php"
     class="sidebar-link <?= ($activePage??'')==='users'?'active':'' ?>">
    <i class="bi bi-people"></i> Users
  </a>
  <a href="<?= BASE_URL ?>/admin/bookings.php"
     class="sidebar-link <?= ($activePage??'')==='bookings'?'active':'' ?>">
    <i class="bi bi-calendar-check"></i> Bookings
    <?php if($_pendingBookings>0): ?>
    <span class="sidebar-badge"><?= $_pendingBookings ?></span>
    <?php endif; ?>
  </a>
  <a href="<?= BASE_URL ?>/admin/reports.php"
     class="sidebar-link <?= ($activePage??'')==='reports'?'active':'' ?>">
    <i class="bi bi-flag"></i> Reports
    <?php if($_pendingReports>0): ?>
    <span class="sidebar-badge"><?= $_pendingReports ?></span>
    <?php endif; ?>
  </a>

  <div class="sidebar-section">INSIGHTS</div>
  <a href="<?= BASE_URL ?>/admin/analytics.php"
     class="sidebar-link <?= ($activePage??'')==='analytics'?'active':'' ?>">
    <i class="bi bi-bar-chart-line"></i> Analytics
  </a>
  <a href="<?= BASE_URL ?>/admin/history.php"
     class="sidebar-link <?= ($activePage??'')==='history'?'active':'' ?>">
    <i class="bi bi-clock-history"></i> History Log
  </a>

  <div class="sidebar-section">SYSTEM</div>
  <a href="<?= BASE_URL ?>/admin/settings.php"
     class="sidebar-link <?= ($activePage??'')==='settings'?'active':'' ?>">
    <i class="bi bi-gear"></i> Settings
  </a>
  <a href="<?= BASE_URL ?>/profile.php" class="sidebar-link">
    <i class="bi bi-person-circle"></i> My Profile
  </a>
  <a href="<?= BASE_URL ?>/logout.php"
     class="sidebar-link" style="color:var(--danger)">
    <i class="bi bi-box-arrow-right"></i> Logout
  </a>
</aside>
