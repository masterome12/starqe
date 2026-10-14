<?php
require_once 'config/config.php';
requireLogin();
$pageTitle='Favorites'; $activeNav='favorites'; $settings=getSettings();
$favs=dbAll('SELECT f.*,s.station_name,s.address,s.city,s.status,s.charging_type,s.available_ports,s.total_ports,s.price_per_kwh,s.is_fast_charge,s.is_verified,s.station_id FROM favorites f JOIN stations s ON f.station_id=s.station_id WHERE f.user_id=? ORDER BY f.created_at DESC',[uid()]);
include 'includes/header.php';
?>
<div class="page-wrap"><div class="content-pad" style="max-width:1000px;margin:0 auto">
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
      <h1 style="font-size:1.7rem;font-weight:800;margin-bottom:.15rem">Favorite Stations</h1>
      <p class="text-muted mb-0" style="font-size:.875rem"><?= count($favs) ?> saved station(s)</p>
    </div>
    <a href="<?= BASE_URL ?>/map.php" class="btn-outline-s btn" style="text-decoration:none"><i class="bi bi-map-fill me-1"></i>Browse Map</a>
  </div>
  <?php if(empty($favs)): ?>
  <div class="card-s"><div class="empty-state p-5">
    <div style="font-size:4rem">❤️</div>
    <div class="empty-title">No favorites yet</div>
    <div class="empty-desc">Tap the heart icon on any station in the map to save it here for quick access.</div>
    <a href="<?= BASE_URL ?>/map.php" class="btn-primary-s btn btn-sm mt-3" style="text-decoration:none">Explore Stations</a>
  </div></div>
  <?php else: ?>
  <div class="row g-3">
  <?php foreach($favs as $fav): ?>
  <div class="col-md-6 col-lg-4" id="fc-<?= $fav['station_id'] ?>">
    <div class="station-card">
      <div style="padding:1rem">
        <div style="display:flex;align-items:center;gap:.4rem;margin-bottom:.5rem;flex-wrap:wrap">
          <span style="background:<?= $fav['status']==='available'?'#dcfce7':($fav['status']==='busy'?'#fef9c3':'#fee2e2') ?>;color:<?= $fav['status']==='available'?'#16a34a':($fav['status']==='busy'?'#ca8a04':'#dc2626') ?>;padding:.2rem .55rem;border-radius:99px;font-size:.72rem;font-weight:600"><?= ucfirst($fav['status']) ?></span>
          <span style="background:#dbeafe;color:#2563eb;padding:.2rem .55rem;border-radius:99px;font-size:.7rem;font-weight:700"><?= $fav['charging_type'] ?></span>
          <?php if($fav['is_fast_charge']): ?><span style="background:#fef3c7;color:#d97706;padding:.2rem .5rem;border-radius:99px;font-size:.68rem;font-weight:700">⚡</span><?php endif; ?>
        </div>
        <div style="font-weight:700;font-size:.95rem;color:var(--text);margin-bottom:.2rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= s($fav['station_name']) ?></div>
        <div style="font-size:.78rem;color:var(--text-2);margin-bottom:.6rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><i class="bi bi-geo-alt me-1"></i><?= s($fav['address']) ?>, <?= s($fav['city']) ?></div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem">
          <span style="font-size:.8rem;color:var(--text-2)"><i class="bi bi-plug me-1"></i><?= $fav['available_ports'] ?>/<?= $fav['total_ports'] ?> free</span>
          <span style="font-size:.875rem;font-weight:700;color:var(--primary)">₱<?= number_format($fav['price_per_kwh'],2) ?>/kWh</span>
        </div>
        <div style="display:flex;gap:.4rem">
          <a href="<?= BASE_URL ?>/map.php?sid=<?= $fav['station_id'] ?>" style="flex:1;padding:.4rem;text-align:center;background:var(--bg-soft);color:var(--text-2);border:1px solid var(--border);border-radius:8px;font-size:.78rem;font-weight:600;text-decoration:none">View on Map</a>
          <a href="<?= BASE_URL ?>/booking.php?station_id=<?= $fav['station_id'] ?>" style="flex:1;padding:.4rem;text-align:center;background:linear-gradient(135deg,#2563eb,#3b82f6);color:#fff;border-radius:8px;font-size:.78rem;font-weight:600;text-decoration:none">Book</a>
          <button onclick="removeFav(<?= $fav['station_id'] ?>)" style="padding:.4rem .6rem;background:#fee2e2;color:#dc2626;border:none;border-radius:8px;cursor:pointer;font-size:.9rem" title="Remove">❤️</button>
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
async function removeFav(sid){
  const r=await APP.post(BASE+'/api/favorites.php',{station_id:sid});
  if(r.success&&r.data.action==='removed'){
    const card=document.getElementById('fc-'+sid);
    if(card){card.style.transform='scale(.9)';card.style.opacity='0';card.style.transition='all .3s';setTimeout(()=>card.remove(),300);}
    APP.toast('Removed from favorites','info');
  }else APP.toast(r.message,'error');
}
</script>
