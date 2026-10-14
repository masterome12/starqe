<?php
require_once '../config/config.php';
requireAdmin();
$pageTitle='History Log'; $activeNav='admin'; $activePage='history'; $settings=getSettings();
$page  = max(1, si($_GET['page']??1));
$per   = 40; $off = ($page-1)*$per;
$rows  = dbAll("SELECT h.*,u.fullname FROM history h LEFT JOIN users u ON h.user_id=u.user_id ORDER BY h.created_at DESC LIMIT $per OFFSET $off");
$total = dbCount('SELECT COUNT(*) FROM history');
$pages = (int)ceil($total/$per);
include '../includes/header.php';
include '../includes/admin_sidebar.php';
?>
<div class="admin-wrap"><div class="content-pad">
  <h1 style="font-size:1.7rem;font-weight:800;margin-bottom:1.5rem">System History Log</h1>
  <div class="card-s">
    <div style="padding:.75rem 1.1rem;border-bottom:1px solid var(--border)">
      <span style="font-weight:600;font-size:.875rem"><?= number_format($total) ?> total events</span>
    </div>
    <?php if(empty($rows)): ?>
    <div class="empty-state p-5"><div class="empty-icon">📋</div><div class="empty-title">No history yet</div></div>
    <?php else: ?>
    <div style="overflow-x:auto"><table class="tbl w-100">
      <thead><tr><th>User</th><th>Action</th><th>Description</th><th>IP</th><th>Date & Time</th></tr></thead>
      <tbody>
      <?php
      $icons=['LOGIN'=>['bi-box-arrow-in-right','green'],'LOGOUT'=>['bi-box-arrow-right',''],'REGISTER'=>['bi-person-plus','blue'],'BOOKING_CREATED'=>['bi-calendar-plus','green'],'BOOKING_STATUS'=>['bi-calendar-check','orange'],'STATION_CREATED'=>['bi-plus-circle','green'],'STATION_UPDATED'=>['bi-pencil','blue'],'STATION_DELETED'=>['bi-trash','red'],'USER_UPDATED'=>['bi-person-gear','orange'],'USER_DELETED'=>['bi-person-x',''],'REPORT_SUBMITTED'=>['bi-flag','orange'],'REPORT_UPDATED'=>['bi-flag-check',''],'SETTINGS_UPDATED'=>['bi-gear','blue'],'PROFILE_UPDATED'=>['bi-person-check','blue'],'PASSWORD_CHANGED'=>['bi-shield-lock','orange']];
      foreach($rows as $h):[$ico,$col]=$icons[$h['action']]??['bi-activity',''];?>
      <tr>
        <td style="font-size:.875rem"><span style="font-weight:600"><?= $h['fullname']?s($h['fullname']):'<span style="color:var(--text-3)">System</span>' ?></span></td>
        <td>
          <div style="display:flex;align-items:center;gap:.5rem">
            <div class="metric-icon <?= $col ?>" style="width:28px;height:28px;font-size:.75rem;flex-shrink:0"><i class="bi <?= $ico ?>"></i></div>
            <span style="font-size:.82rem;font-weight:600;color:var(--text)"><?= s(str_replace('_',' ',$h['action'])) ?></span>
          </div>
        </td>
        <td style="font-size:.82rem;color:var(--text-2);max-width:300px"><div class="text-truncate"><?= s($h['description']) ?></div></td>
        <td style="font-size:.78rem;color:var(--text-3)"><?= s($h['ip_address']??'—') ?></td>
        <td style="font-size:.8rem;white-space:nowrap;color:var(--text-2)"><?= date('M d, Y H:i',strtotime($h['created_at'])) ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
    <div style="padding:.85rem 1.1rem;border-top:1px solid var(--border);display:flex;justify-content:center">
      <div class="pager">
        <?php for($i=1;$i<=$pages;$i++): ?>
        <a href="?page=<?= $i ?>" class="<?= $i===$page?'cur':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div></div>
<?php include '../includes/footer.php'; ?>
