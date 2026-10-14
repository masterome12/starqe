<?php
require_once 'config/config.php';
requireLogin();
$pageTitle='Activity History'; $activeNav=''; $settings=getSettings();
$history=dbAll('SELECT * FROM history WHERE user_id=? ORDER BY created_at DESC LIMIT 100',[uid()]);
include 'includes/header.php';
?>
<div class="page-wrap"><div class="content-pad" style="max-width:720px;margin:0 auto">
  <h1 style="font-size:1.7rem;font-weight:800;margin-bottom:.3rem">Activity History</h1>
  <p class="text-muted mb-4" style="font-size:.875rem">Your recent actions in STARQE</p>
  <?php if(empty($history)): ?>
  <div class="card-s"><div class="empty-state p-5"><div class="empty-icon">📋</div><div class="empty-title">No activity yet</div></div></div>
  <?php else: ?>
  <div class="card-s">
    <?php
    $icons=['LOGIN'=>['bi-box-arrow-in-right','green'],'LOGOUT'=>['bi-box-arrow-right',''],'REGISTER'=>['bi-person-plus','blue'],'BOOKING_CREATED'=>['bi-calendar-plus','green'],'BOOKING_STATUS'=>['bi-calendar-check','orange'],'PROFILE_UPDATED'=>['bi-person-check','blue'],'PASSWORD_CHANGED'=>['bi-shield-lock','orange'],'REPORT_SUBMITTED'=>['bi-flag','red'],'STATION_CREATED'=>['bi-plus-circle','green'],'STATION_UPDATED'=>['bi-pencil','blue'],'STATION_DELETED'=>['bi-trash','red']];
    foreach($history as $h):[$ico,$col]=$icons[$h['action']]??['bi-activity',''];?>
    <div style="display:flex;align-items:flex-start;gap:.85rem;padding:.9rem 1.1rem;border-bottom:1px solid var(--border)">
      <div class="metric-icon <?= $col ?>" style="width:34px;height:34px;font-size:.85rem;flex-shrink:0"><i class="bi <?= $ico ?>"></i></div>
      <div style="flex:1;min-width:0">
        <div style="font-weight:600;font-size:.875rem;color:var(--text)"><?= s(str_replace('_',' ',$h['action'])) ?></div>
        <div style="font-size:.82rem;color:var(--text-2)" class="text-truncate"><?= s($h['description']) ?></div>
      </div>
      <div style="font-size:.75rem;color:var(--text-3);white-space:nowrap"><?= date('M d, H:i',strtotime($h['created_at'])) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div></div>
<?php include 'includes/footer.php'; ?>
