<?php
require_once 'config/config.php';
$pageTitle = 'Home';
$activeNav = 'home';
$settings  = getSettings();
$totalStations = dbCount('SELECT COUNT(*) FROM stations');
$availStations = dbCount("SELECT COUNT(*) FROM stations WHERE status='available'");
$totalCities   = dbCount('SELECT COUNT(DISTINCT city) FROM stations');
$totalBookings = dbCount('SELECT COUNT(*) FROM bookings');
include 'includes/header.php';
?>

<!-- HERO -->
<section class="hero">
  <div class="hero-grid"></div>
  <div class="container py-5" style="position:relative;z-index:2">
    <div class="row align-items-center g-5 min-vh-100 py-5">
      <div class="col-lg-6 hero-content">
        <div class="hero-eyebrow">
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
            <path d="M9 2L4 8h5l-2 4L14 5H8L9 2z" fill="#93C5FD"/>
          </svg>
          Philippines' Premium EV Network
        </div>
        <h1 class="hero-title">
          Charge Up,<br><span class="hl">Drive Further</span>
        </h1>
        <p class="hero-sub">
          Discover, navigate, and book EV charging stations across the Philippines.
          Real-time availability, instant booking, seamless navigation.
        </p>
        <div class="d-flex flex-wrap gap-3">
          <a href="<?= BASE_URL ?>/map.php" class="btn-primary-s btn btn-lg" style="text-decoration:none">
            <i class="bi bi-map-fill me-2"></i>Find Stations
          </a>
          <?php if(!isLoggedIn()): ?>
          <a href="<?= BASE_URL ?>/register.php" class="btn btn-lg" style="background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.25);border-radius:var(--r);text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;padding:.6rem 1.4rem;font-weight:600;transition:all .2s">
            <i class="bi bi-person-plus me-1"></i>Get Started Free
          </a>
          <?php else: ?>
          <a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-lg" style="background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.25);border-radius:var(--r);text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;padding:.6rem 1.4rem;font-weight:600">
            <i class="bi bi-speedometer2 me-1"></i>My Dashboard
          </a>
          <?php endif; ?>
        </div>
        <div class="hero-stats">
          <?php foreach([[$totalStations.'+','Stations'],[$availStations,'Available Now'],[$totalCities.'+','Cities'],[$totalBookings.'+','Bookings']] as [$num,$lbl]): ?>
          <div>
            <div class="hero-stat-num"><?= $num ?></div>
            <div class="hero-stat-lbl"><?= $lbl ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="col-lg-6 d-none d-lg-block">
        <div style="border-radius:20px;overflow:hidden;box-shadow:0 24px 60px rgba(0,0,0,.35);border:1px solid rgba(255,255,255,.15);height:440px">
          <div id="hero-map" style="width:100%;height:100%"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES SECTION -->
<section class="py-5" style="background:var(--bg-soft)">
  <div class="container py-3">
    <div class="text-center mb-5">
      <span style="background:var(--primary-lite);color:var(--primary);padding:.3rem .9rem;border-radius:var(--r-full);font-size:.75rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase">Why STARQE</span>
      <h2 class="mt-3" style="font-size:2.1rem;font-weight:800">Everything you need to stay charged</h2>
      <p class="text-muted mt-2" style="max-width:480px;margin:0 auto">Built for Filipino EV drivers — fast, reliable, and always up to date.</p>
    </div>
    <div class="row g-4">
      <?php
      $features = [
        ['bi-map-fill','blue','Live Map','Real-time Leaflet map with clustering, color-coded markers, and 3-minute auto-refresh.'],
        ['bi-calendar-check','green','Instant Booking','Reserve charging slots in seconds. Get a reference code immediately.'],
        ['bi-geo-alt-fill','orange','Smart Navigation','OSRM routing, ETA, distance, plus Google Maps and Waze integration.'],
        ['bi-heart-fill','purple','Favorites','Save your go-to stations with animated hearts and quick-access from anywhere.'],
        ['bi-lightning-charge-fill','cyan','Fast Charging','AC Mobility, EVIDA, Shell Recharge, NLEX and more — AC, DC, AC/DC.'],
        ['bi-shield-check-fill','blue','Secure & Verified','PDO prepared statements, CSRF, session auth, role-based access control.'],
      ];
      foreach($features as [$icon,$col,$title,$desc]): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card-s p-4 h-100 slide-up">
          <div class="metric-icon <?= $col ?> mb-3"><i class="bi <?= $icon ?>" style="font-size:1.25rem"></i></div>
          <h5 style="font-weight:700;font-size:1rem;margin-bottom:.4rem"><?= $title ?></h5>
          <p style="font-size:.875rem;color:var(--text-2);margin:0;line-height:1.6"><?= $desc ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- LIVE STATIONS PREVIEW -->
<section class="py-5">
  <div class="container py-3">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
      <div>
        <h2 style="font-size:1.75rem;font-weight:800;margin:0">Available Now</h2>
        <p class="text-muted mb-0" style="font-size:.875rem">Stations ready to charge your EV right now</p>
      </div>
      <a href="<?= BASE_URL ?>/map.php" class="btn-outline-s btn" style="text-decoration:none">
        View All on Map <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>
    <div class="row g-3" id="station-preview">
      <!-- Skeleton loaders -->
      <?php for($i=0;$i<6;$i++): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card-s p-3">
          <div class="skeleton mb-2" style="height:20px;border-radius:6px;width:70%"></div>
          <div class="skeleton mb-2" style="height:14px;border-radius:6px;width:50%"></div>
          <div class="skeleton" style="height:34px;border-radius:8px;margin-top:.75rem"></div>
        </div>
      </div>
      <?php endfor; ?>
    </div>
  </div>
</section>

<!-- CTA -->
<?php if(!isLoggedIn()): ?>
<section class="py-5" style="background:linear-gradient(135deg,#1e3a8a,#2563eb)">
  <div class="container py-3 text-center">
    <h2 style="color:#fff;font-size:2rem;font-weight:800;margin-bottom:.75rem">Ready to go electric?</h2>
    <p style="color:rgba(255,255,255,.7);max-width:440px;margin:0 auto 2rem">Join thousands of EV drivers using STARQE across the Philippines.</p>
    <div class="d-flex gap-3 justify-content-center flex-wrap">
      <a href="<?= BASE_URL ?>/register.php" class="btn-primary-s btn btn-lg" style="background:#fff;color:var(--primary);text-decoration:none">
        <i class="bi bi-person-plus me-2"></i>Create Free Account
      </a>
      <a href="<?= BASE_URL ?>/map.php" class="btn btn-lg" style="background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:var(--r);text-decoration:none;padding:.6rem 1.4rem;font-weight:600">
        Explore Map
      </a>
    </div>
  </div>
</section>
<?php endif; ?>

<?php
$extraScripts = '';
include 'includes/footer.php';
?>
<script>
const BASE = '<?= BASE_URL ?>';

// Hero mini map
const hmap = L.map('hero-map',{zoomControl:false,scrollWheelZoom:false,dragging:false,attributionControl:false});
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(hmap);
hmap.setView([14.5995,120.9842],11);
const COLS={available:'#22C55E',busy:'#F59E0B',full:'#EF4444',maintenance:'#94A3B8'};

// Load stations
APP.get(BASE+'/api/stations.php').then(r=>{
  if(!r.success) return;
  const prev=document.getElementById('station-preview');
  prev.innerHTML='';
  let cnt=0;
  r.data.forEach(s=>{
    // Mini map markers
    L.circleMarker([+s.latitude,+s.longitude],{
      radius:7,fillColor:COLS[s.status]||'#94a3b8',
      color:'#fff',weight:2,fillOpacity:.9
    }).bindPopup(`<b>${s.station_name}</b><br>${s.city}`).addTo(hmap);

    // Station cards (available only, max 6)
    if(cnt<6 && s.status==='available'){
      cnt++;
      prev.insertAdjacentHTML('beforeend',`
      <div class="col-md-6 col-lg-4">
        <div class="station-card slide-up" style="padding:1.1rem">
          <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem">
            ${APP.typeBadge(s.charging_type)}
            ${APP.statusBadge(s.status)}
            ${s.is_verified=='1'?'<span style="font-size:.68rem;color:#2563eb">✓</span>':''}
          </div>
          <div style="font-weight:700;font-size:.95rem;margin-bottom:.2rem;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${s.station_name}</div>
          <div style="font-size:.78rem;color:#64748b;margin-bottom:.6rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">📍 ${s.address}, ${s.city}</div>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem">
            <span style="font-size:.8rem;color:#64748b">🔌 ${s.available_ports}/${s.total_ports} free</span>
            <span style="font-size:.875rem;font-weight:700;color:#2563eb">₱${(+s.price_per_kwh).toFixed(2)}/kWh</span>
          </div>
          <div style="display:flex;gap:.5rem">
            <a href="${BASE}/map.php?sid=${s.station_id}" style="flex:1;padding:.4rem;text-align:center;background:#f8fafc;color:#475569;border:1px solid #e2e8f0;border-radius:8px;font-size:.78rem;font-weight:600;text-decoration:none">View</a>
            <a href="${BASE}/booking.php?station_id=${s.station_id}" style="flex:1;padding:.4rem;text-align:center;background:linear-gradient(135deg,#2563eb,#3b82f6);color:#fff;border-radius:8px;font-size:.78rem;font-weight:600;text-decoration:none">Book</a>
          </div>
        </div>
      </div>`);
    }
  });
  if(!cnt) prev.innerHTML='<div class="col-12"><div class="empty-state"><div class="empty-icon">🔌</div><div class="empty-title">No available stations right now</div></div></div>';
});
</script>
