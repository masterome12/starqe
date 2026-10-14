<?php
require_once 'config/config.php';
requireLogin();
$pageTitle='My Bookings'; $activeNav='bookings'; $settings=getSettings();
$filter=s($_GET['status']??'');
$w=$filter?'AND b.status=?':'';
$p=$filter?[uid(),$filter]:[uid()];
$bookings=dbAll("SELECT b.*,s.station_name,s.city,s.charging_type,s.latitude,s.longitude FROM bookings b JOIN stations s ON b.station_id=s.station_id WHERE b.user_id=? $w ORDER BY b.created_at DESC",$p);
include 'includes/header.php';
?>
<div class="page-wrap"><div class="content-pad" style="max-width:900px;margin:0 auto">
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
      <h1 style="font-size:1.7rem;font-weight:800;margin-bottom:.15rem">My Bookings</h1>
      <p class="text-muted mb-0" style="font-size:.875rem"><?= count($bookings) ?> booking(s)</p>
    </div>
    <a href="<?= BASE_URL ?>/map.php" class="btn-primary-s btn" style="text-decoration:none"><i class="bi bi-plus-lg me-1"></i>New Booking</a>
  </div>
  <!-- Filter tabs -->
  <div style="display:flex;gap:.4rem;margin-bottom:1.25rem;flex-wrap:wrap">
    <?php foreach([''=>'All','pending'=>'Pending','approved'=>'Approved','confirmed'=>'Confirmed','completed'=>'Completed','cancelled'=>'Cancelled','rejected'=>'Rejected'] as $v=>$l): ?>
    <a href="?status=<?= $v ?>" style="padding:.3rem .8rem;border-radius:var(--r-full);font-size:.8rem;font-weight:600;text-decoration:none;border:1.5px solid <?= $filter===$v?'var(--primary)':'var(--border)' ?>;background:<?= $filter===$v?'var(--primary)':'#fff' ?>;color:<?= $filter===$v?'#fff':'var(--text-2)' ?>;transition:all .2s"><?= $l ?></a>
    <?php endforeach; ?>
  </div>
  <?php if(empty($bookings)): ?>
  <div class="card-s"><div class="empty-state p-5">
    <div class="empty-icon">📅</div><div class="empty-title">No bookings found</div>
    <div class="empty-desc">Book your first EV charging slot</div>
    <a href="<?= BASE_URL ?>/map.php" class="btn-primary-s btn btn-sm mt-3" style="text-decoration:none">Find Stations</a>
  </div></div>
  <?php else: ?>
  <div class="d-flex flex-column gap-3">
  <?php foreach($bookings as $b): ?>
  <div class="card-s">
    <div style="padding:1.1rem">
      <div class="row align-items-center g-3">
        <div class="col-md-5">
          <div class="booking-ref"><?= s($b['reference_no']) ?></div>
          <div style="font-weight:700;font-size:.95rem;color:var(--text)"><?= s($b['station_name']) ?></div>
          <div style="font-size:.8rem;color:var(--text-2)"><i class="bi bi-geo-alt me-1"></i><?= s($b['city']) ?> · <?= $b['charging_type'] ?></div>
        </div>
        <div class="col-md-4">
          <div style="font-size:.8rem;color:var(--text-2)">
            <div><i class="bi bi-calendar3 me-1"></i><?= date('M d, Y',strtotime($b['booking_date'])) ?></div>
            <div><i class="bi bi-clock me-1"></i><?= date('h:i A',strtotime($b['booking_time'])) ?> · <?= $b['duration_hours'] ?>h</div>
            <div style="font-weight:700;color:var(--primary);margin-top:.2rem">₱<?= number_format($b['total_amount'],2) ?></div>
          </div>
        </div>
        <div class="col-md-3 d-flex flex-column align-items-md-end gap-2">
          <?= statusBadge($b['status']) ?>
          <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/booking.php?ref=<?= s($b['reference_no']) ?>" class="btn-outline-s btn btn-sm" style="text-decoration:none;padding:.28rem .7rem;font-size:.78rem">View</a>
            <?php if(in_array($b['status'],['pending','confirmed','approved'])): ?>
            <button class="btn-danger-s btn btn-sm" style="padding:.28rem .7rem;font-size:.78rem" onclick="cancelBook(<?= $b['booking_id'] ?>,'<?= s($b['reference_no']) ?>')">Cancel</button>
            <?php elseif($b['status']==='completed'): ?>
            <a href="<?= BASE_URL ?>/booking.php?station_id=<?= $b['station_id'] ?>" class="btn-outline-s btn btn-sm" style="text-decoration:none;padding:.28rem .7rem;font-size:.78rem">Rebook</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div></div>
<?php include 'includes/footer.php'; ?>
<script>
const BASE='<?= BASE_URL ?>';
async function cancelBook(id,ref){
  APP.confirm(`Cancel booking <b>${ref}</b>?`,async()=>{
    const fd=new FormData();fd.append('booking_id',id);fd.append('status','cancelled');fd.append('_method','PUT');
    const r=await APP.postForm(BASE+'/api/bookings.php',fd);
    if(r.success){APP.toast('Cancelled.','success');setTimeout(()=>location.reload(),800);}
    else APP.toast(r.message,'error');
  });
}
</script>
