<?php
require_once 'config/config.php';
requireLogin();
$pageTitle='Notifications'; $activeNav=''; $settings=getSettings();
$notifs=dbAll('SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 50',[uid()]);
// Mark all as read on page visit
dbQ('UPDATE notifications SET is_read=1 WHERE user_id=?',[uid()]);
include 'includes/header.php';
?>
<div class="page-wrap"><div class="content-pad" style="max-width:680px;margin:0 auto">
  <h1 style="font-size:1.7rem;font-weight:800;margin-bottom:.3rem">Notifications</h1>
  <p class="text-muted mb-4" style="font-size:.875rem"><?= count($notifs) ?> notification(s)</p>
  <?php if(empty($notifs)): ?>
  <div class="card-s"><div class="empty-state p-5"><div class="empty-icon">🔔</div><div class="empty-title">No notifications yet</div></div></div>
  <?php else: ?>
  <div class="card-s">
    <?php foreach($notifs as $n):
      $ic=$n['type']==='success'?'✅':($n['type']==='error'?'❌':'ℹ️');
      $bg=$n['type']==='success'?'green':($n['type']==='error'?'':'blue');
    ?>
    <div style="display:flex;align-items:flex-start;gap:.85rem;padding:.9rem 1.1rem;border-bottom:1px solid var(--border);background:<?= $n['is_read']?'#fff':'var(--primary-lite)' ?>">
      <div class="metric-icon <?= $bg ?>" style="width:34px;height:34px;font-size:.9rem;flex-shrink:0"><?= $ic ?></div>
      <div style="flex:1;min-width:0">
        <div style="font-weight:600;font-size:.875rem;color:var(--text)"><?= s($n['title']) ?></div>
        <div style="font-size:.82rem;color:var(--text-2)"><?= s($n['message']) ?></div>
        <?php if(!empty($n['link'])): ?>
        <a href="<?= s($n['link']) ?>" style="font-size:.75rem;color:var(--primary)">View details →</a>
        <?php endif; ?>
      </div>
      <div style="font-size:.75rem;color:var(--text-3);white-space:nowrap"><?= timeAgo($n['created_at']) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div></div>
<?php include 'includes/footer.php'; ?>
