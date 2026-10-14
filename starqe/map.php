<?php
require_once 'config/config.php';
$pageTitle = 'Live Map';
$activeNav = 'map';
$bodyClass = 'map-page';
$settings  = getSettings();
$focusSid  = si($_GET['sid']??0);
include 'includes/header.php';
?>
<style>
body.map-page { overflow: hidden; }
body.map-page .starqe-nav { border-bottom: 1px solid var(--border); }
</style>

<div class="page-wrap" style="position:relative">
  <!-- Map -->
  <div id="starqe-map"></div>

  <!-- Left Panel -->
  <div class="map-sidebar-panel" id="map-panel">

    <!-- Search Box -->
    <div class="search-box-wrap">
      <div class="search-input-row">
        <i class="bi bi-search" style="color:var(--text-3);font-size:1rem;flex-shrink:0"></i>
        <input type="text" id="map-search" placeholder="Search stations, city, address…">
        <button onclick="SEARCH.clearSearch();document.getElementById('map-search').value=''"
          style="background:none;border:none;color:var(--text-3);cursor:pointer;padding:0;font-size:1rem">
          <i class="bi bi-x-circle"></i>
        </button>
      </div>
      <!-- Filter Chips -->
      <div class="filter-chips-row">
        <div class="filter-chip" data-filter="status" data-value="available"
             onclick="SEARCH.setFilter('status', this.classList.contains('active')?'':'available');this.classList.toggle('active')">
          <span>🟢</span> Available
        </div>
        <div class="filter-chip" data-filter="fast" data-value="1"
             onclick="SEARCH.activeFilters.fast=!SEARCH.activeFilters.fast;this.classList.toggle('active');SEARCH.applyAll()">
          <span>⚡</span> Fast Charge
        </div>
        <div class="filter-chip" data-filter="nearby" data-value="1"
             onclick="SEARCH.activeFilters.nearby=!SEARCH.activeFilters.nearby;this.classList.toggle('active');SEARCH.applyAll()">
          <span>📍</span> Nearby (10km)
        </div>
        <div class="filter-chip" data-filter="type" data-value="DC"
             onclick="SEARCH.setFilter('type',this.classList.contains('active')?'':'DC');this.classList.toggle('active')">
          DC Only
        </div>
        <div class="filter-chip" data-filter="type" data-value="AC"
             onclick="SEARCH.setFilter('type',this.classList.contains('active')?'':'AC');this.classList.toggle('active')">
          AC Only
        </div>
      </div>
      <!-- Search dropdown -->
      <div class="search-dropdown" id="search-dropdown"></div>
    </div>

    <!-- Station List -->
    <div class="station-list-panel">
      <div class="station-list-header">
        <span>Stations <span id="station-count" style="background:var(--primary);color:#fff;padding:.1rem .45rem;border-radius:99px;font-size:.7rem;font-weight:700">0</span></span>
        <div style="display:flex;align-items:center;gap:.5rem">
          <div id="refresh-dot" style="width:7px;height:7px;border-radius:50%;background:#22c55e;flex-shrink:0"></div>
          <span id="last-refresh" style="font-size:.68rem">Now</span>
        </div>
      </div>
      <div class="station-list-body" id="station-list">
        <div style="padding:1.5rem;text-align:center">
          <div class="spinner-ring" style="width:32px;height:32px;border-width:3px;margin:0 auto"></div>
          <div style="font-size:.82rem;color:var(--text-3);margin-top:.6rem">Loading stations…</div>
        </div>
      </div>
    </div>

    <!-- Refresh row -->
    <div style="background:#fff;border-radius:var(--r-lg);padding:.55rem .9rem;box-shadow:var(--shadow-sm);border:1px solid var(--border);font-size:.72rem;color:var(--text-3);display:flex;align-items:center;gap:.5rem">
      <span>🔄</span> Auto-refresh every 3 min
    </div>
  </div>

  <!-- Map controls (right) -->
  <div style="position:absolute;top:1rem;right:1rem;z-index:900;display:flex;flex-direction:column;gap:.45rem">
    <button class="map-ctrl-btn" onclick="SMAP.recenter()" title="My Location">
      <i class="bi bi-crosshair2" style="font-size:1.1rem"></i>
    </button>
    <button class="map-ctrl-btn" onclick="SMAP.clearRoute()" title="Clear Route">
      <i class="bi bi-x-circle" style="font-size:1.1rem"></i>
    </button>
    <button class="map-ctrl-btn" onclick="SMAP.load()" title="Refresh">
      <i class="bi bi-arrow-clockwise" style="font-size:1.1rem"></i>
    </button>
  </div>

  <!-- Legend -->
  <div class="map-legend">
    <div class="legend-title">Station Status</div>
    <div class="legend-item"><div class="legend-dot" style="background:#22C55E"></div>Available</div>
    <div class="legend-item"><div class="legend-dot" style="background:#F59E0B"></div>Busy</div>
    <div class="legend-item"><div class="legend-dot" style="background:#EF4444"></div>Full</div>
    <div class="legend-item"><div class="legend-dot" style="background:#94A3B8"></div>Maintenance</div>
    <div class="legend-title mt-2">Type</div>
    <div class="legend-item">
      <span style="background:#dbeafe;color:#2563eb;padding:.1rem .35rem;border-radius:4px;font-size:.68rem;font-weight:700">AC</span>
      Alternating
    </div>
    <div class="legend-item">
      <span style="background:#dcfce7;color:#16a34a;padding:.1rem .35rem;border-radius:4px;font-size:.68rem;font-weight:700">DC</span>
      Direct
    </div>
    <div class="legend-item"><span style="font-size:.8rem">⚡</span>Fast Charge</div>
  </div>

  <!-- Mobile toggle -->
  <button onclick="document.getElementById('map-panel').classList.toggle('open')"
    style="display:none;position:fixed;bottom:1.5rem;right:1.5rem;z-index:950;width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#3b82f6);color:#fff;border:none;box-shadow:0 4px 16px rgba(37,99,235,.4);cursor:pointer;font-size:1.3rem;align-items:center;justify-content:center"
    id="map-panel-toggle">
    <i class="bi bi-list-ul"></i>
  </button>
</div>

<?php include 'includes/footer.php'; ?>
<script>
const BASE='<?= BASE_URL ?>';

// Init map
SMAP.init('starqe-map');

// Init search
SEARCH.init();

// Load user favorites for filter
<?php if(isUser()): ?>
APP.get(BASE+'/api/favorites.php').then(r=>{
  if(r.success){
    FAVS.ids=r.data.map(f=>+f.station_id);
    SEARCH.favIds=FAVS.ids;
  }
});
<?php endif; ?>

// Deep-link focus
<?php if($focusSid): ?>
setTimeout(()=>SMAP.focusStation(<?= $focusSid ?>),1800);
<?php endif; ?>

// Search input debounce
const searchInp=document.getElementById('map-search');
if(searchInp){
  searchInp.addEventListener('input',APP.debounce(()=>SEARCH.onInput(searchInp.value),280));
  searchInp.addEventListener('focus',()=>SEARCH.showDropdown());
}

// Mobile toggle
const mpt=document.getElementById('map-panel-toggle');
if(mpt) mpt.style.display='flex';

// Responsive check
function handleResize(){
  if(window.innerWidth<768 && mpt) mpt.style.display='flex';
  else if(mpt) mpt.style.display='none';
}
window.addEventListener('resize',handleResize);
handleResize();
</script>
