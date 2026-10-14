<?php
require_once 'config/config.php';
requireLogin();
$pageTitle='Dashboard'; $activeNav='dashboard'; $settings=getSettings();
$myBookings  = dbAll('SELECT b.*,s.station_name,s.city,s.charging_type FROM bookings b JOIN stations s ON b.station_id=s.station_id WHERE b.user_id=? ORDER BY b.created_at DESC LIMIT 5',[uid()]);
$myFavs      = dbAll('SELECT f.*,s.station_name,s.city,s.status,s.charging_type,s.available_ports,s.total_ports,s.price_per_kwh FROM favorites f JOIN stations s ON f.station_id=s.station_id WHERE f.user_id=? ORDER BY f.created_at DESC LIMIT 4',[uid()]);
$totalBooks  = dbCount('SELECT COUNT(*) FROM bookings WHERE user_id=?',[uid()]);
$activeBooks = dbCount("SELECT COUNT(*) FROM bookings WHERE user_id=? AND status IN ('pending','confirmed','approved')",[uid()]);
$totalFavs   = dbCount('SELECT COUNT(*) FROM favorites WHERE user_id=?',[uid()]);
$totalSpent  = (float)(dbOne("SELECT COALESCE(SUM(total_amount),0) r FROM bookings WHERE user_id=? AND status IN ('approved','completed')",[uid()])['r']??0);
$recentAct   = dbAll('SELECT * FROM history WHERE user_id=? ORDER BY created_at DESC LIMIT 6',[uid()]);
include 'includes/header.php';
?>

<div class="page-wrap">
<div class="content-pad">
  <!-- Header -->
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
      <h1 style="font-size:1.7rem;font-weight:800;margin-bottom:.15rem">
        Good <?= date('H')<12?'morning':( date('H')<17?'afternoon':'evening') ?>, <?= explode(' ',uname())[0] ?> 👋
      </h1>
      <p class="text-muted mb-0" style="font-size:.875rem">Here's your EV charging overview</p>
    </div>
    <a href="<?= BASE_URL ?>/map.php" class="btn-primary-s btn" style="text-decoration:none">
      <i class="bi bi-map-fill me-2"></i>Find Stations
    </a>
  </div>

  <!-- Metric Cards -->
  <div class="row g-3 mb-4">
    <?php foreach([
      ['blue','bi-calendar-check-fill',$totalBooks,'Total Bookings','All time'],
      ['green','bi-lightning-charge-fill',$activeBooks,'Active Bookings','Pending/confirmed'],
      ['purple','bi-heart-fill',$totalFavs,'Saved Stations','Your favorites'],
      ['orange','bi-currency-exchange','₱'.number_format($totalSpent,0),'Total Spent','All confirmed'],
    ] as [$col,$icon,$val,$lbl,$sub]): ?>
    <div class="col-6 col-lg-3">
      <div class="metric-card">
        <div class="metric-icon <?= $col ?>"><i class="bi <?= $icon ?>"></i></div>
        <div class="metric-val"><?= $val ?></div>
        <div class="metric-lbl"><?= $lbl ?></div>
        <div class="metric-sub"><?= $sub ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="row g-4">
    <!-- Recent Bookings -->
    <div class="col-lg-7">
      <div class="card-s h-100">
        <div class="card-hd">
          <div class="metric-icon blue" style="width:32px;height:32px;font-size:.85rem"><i class="bi bi-calendar-check"></i></div>
          <span class="title">Recent Bookings</span>
          <a href="<?= BASE_URL ?>/bookings.php" style="margin-left:auto;font-size:.78rem;color:var(--primary);text-decoration:none">View all</a>
        </div>
        <?php if(empty($myBookings)): ?>
        <div class="empty-state">
          <div class="empty-icon">📅</div>
          <div class="empty-title">No bookings yet</div>
          <div class="empty-desc">Find a station and book your first charging slot!</div>
          <a href="<?= BASE_URL ?>/map.php" class="btn-primary-s btn btn-sm mt-2" style="text-decoration:none">Find Stations</a>
        </div>
        <?php else: ?>
        <div class="p-3 d-flex flex-column gap-3">
          <?php foreach($myBookings as $b): ?>
          <div class="booking-card">
            <div class="d-flex align-items-start justify-content-between gap-2">
              <div style="flex:1;min-width:0">
                <div class="booking-ref"><?= s($b['reference_no']) ?></div>
                <div style="font-weight:700;font-size:.95rem;color:var(--text)" class="text-truncate"><?= s($b['station_name']) ?></div>
                <div style="font-size:.8rem;color:var(--text-2);margin-top:.2rem">
                  <i class="bi bi-calendar3 me-1"></i><?= date('M d, Y',strtotime($b['booking_date'])) ?>
                  &nbsp;<i class="bi bi-clock me-1"></i><?= date('h:i A',strtotime($b['booking_time'])) ?>
                  &nbsp;· <?= $b['duration_hours'] ?>h
                </div>
              </div>
              <div class="d-flex flex-column align-items-end gap-1">
                <?= statusBadge($b['status']) ?>
                <span style="font-size:.85rem;font-weight:700;color:var(--primary)">₱<?= number_format($b['total_amount'],2) ?></span>
              </div>
            </div>
            <?php if(in_array($b['status'],['pending','confirmed','approved'])): ?>
            <div class="d-flex gap-2 mt-2">
              <a href="<?= BASE_URL ?>/booking.php?ref=<?= s($b['reference_no']) ?>" class="btn-outline-s btn btn-sm" style="text-decoration:none">View</a>
              <button class="btn-danger-s btn btn-sm" onclick="cancelBook(<?= $b['booking_id'] ?>,'<?= s($b['reference_no']) ?>')">Cancel</button>
            </div>
            <?php elseif($b['status']==='completed'): ?>
            <a href="<?= BASE_URL ?>/booking.php?station_id=<?= $b['station_id'] ?>" class="btn-outline-s btn btn-sm mt-2" style="text-decoration:none">Rebook</a>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Right Column -->
    <div class="col-lg-5 d-flex flex-column gap-4">
      <!-- Favorites -->
      <div class="card-s">
        <div class="card-hd">
          <div class="metric-icon purple" style="width:32px;height:32px;font-size:.85rem"><i class="bi bi-heart-fill"></i></div>
          <span class="title">Favorites</span>
          <a href="<?= BASE_URL ?>/favorites.php" style="margin-left:auto;font-size:.78rem;color:var(--primary);text-decoration:none">View all</a>
        </div>
        <?php if(empty($myFavs)): ?>
        <div class="empty-state py-3">
          <div class="empty-icon" style="font-size:2rem">❤️</div>
          <div class="empty-title" style="font-size:.9rem">No favorites yet</div>
        </div>
        <?php else: ?>
        <?php foreach($myFavs as $fav): ?>
        <div class="station-list-item" style="padding:.8rem 1.1rem" onclick="window.location='<?= BASE_URL ?>/map.php?sid=<?= $fav['station_id'] ?>'">
          <div class="d-flex align-items-center gap-2">
            <div style="width:8px;height:8px;border-radius:50%;flex-shrink:0;background:<?= $fav['status']==='available'?'#22c55e':($fav['status']==='busy'?'#f59e0b':'#ef4444') ?>"></div>
            <div style="flex:1;min-width:0">
              <div style="font-weight:600;font-size:.875rem;color:var(--text)" class="text-truncate"><?= s($fav['station_name']) ?></div>
              <div style="font-size:.75rem;color:var(--text-2)"><?= s($fav['city']) ?> · <?= $fav['charging_type'] ?></div>
            </div>
            <a href="<?= BASE_URL ?>/booking.php?station_id=<?= $fav['station_id'] ?>" onclick="event.stopPropagation()" class="btn-outline-s btn btn-sm" style="text-decoration:none;padding:.25rem .6rem;font-size:.75rem">Book</a>
          </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Activity -->
      <div class="card-s">
        <div class="card-hd">
          <div class="metric-icon cyan" style="width:32px;height:32px;font-size:.85rem"><i class="bi bi-clock-history"></i></div>
          <span class="title">Recent Activity</span>
        </div>
        <?php if(empty($recentAct)): ?>
        <div class="empty-state py-3"><div class="empty-icon" style="font-size:2rem">📋</div><div class="empty-title" style="font-size:.9rem">No activity yet</div></div>
        <?php else: ?>
        <?php foreach($recentAct as $h): ?>
        <div class="px-3 py-2 border-bottom" style="font-size:.82rem">
          <div style="font-weight:600;color:var(--text)"><?= s(str_replace('_',' ',$h['action'])) ?></div>
          <div style="color:var(--text-2)" class="text-truncate"><?= s($h['description']) ?></div>
          <div style="font-size:.7rem;color:var(--text-3)"><?= timeAgo($h['created_at']) ?></div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
</div>

<?php include 'includes/footer.php'; ?>
<script>
const BASE='<?= BASE_URL ?>';
async function cancelBook(id,ref){
  APP.confirm(`Cancel booking <b>${ref}</b>? This cannot be undone.`,async()=>{
    const fd=new FormData();fd.append('booking_id',id);fd.append('status','cancelled');fd.append('_method','PUT');
    const r=await APP.postForm(BASE+'/api/bookings.php',fd);
    if(r.success){APP.toast('Booking cancelled.','success');setTimeout(()=>location.reload(),900);}
    else APP.toast(r.message,'error');
  },'Cancel Booking');
}
</script>
