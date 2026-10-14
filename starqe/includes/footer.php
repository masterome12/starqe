<!-- ── FOOTER ─────────────────────────────────────────────── -->
<footer class="site-footer mt-auto">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="footer-brand mb-2">
          <svg width="22" height="22" viewBox="0 0 28 28" fill="none" style="margin-right:.4rem;vertical-align:middle">
            <rect width="28" height="28" rx="8" fill="#3B82F6"/>
            <path d="M16 5L8 15h8l-4 8 12-13h-9l3-5z" fill="#FFF"/>
          </svg>
          STARQE
        </div>
        <p style="color:rgba(255,255,255,.45);font-size:.85rem;max-width:280px;line-height:1.6">
          Find and book EV charging stations across the Philippines. Drive electric, drive smart.
        </p>
      </div>
      <div class="col-6 col-lg-2">
        <div class="footer-heading">Navigate</div>
        <a href="<?= BASE_URL ?>/index.php"    class="footer-link">Home</a>
        <a href="<?= BASE_URL ?>/map.php"      class="footer-link">Live Map</a>
        <a href="<?= BASE_URL ?>/login.php"    class="footer-link">Login</a>
        <a href="<?= BASE_URL ?>/register.php" class="footer-link">Register</a>
      </div>
      <div class="col-6 col-lg-2">
        <div class="footer-heading">Features</div>
        <a href="<?= BASE_URL ?>/bookings.php"  class="footer-link">My Bookings</a>
        <a href="<?= BASE_URL ?>/favorites.php" class="footer-link">Favorites</a>
        <a href="<?= BASE_URL ?>/history.php"   class="footer-link">History</a>
      </div>
      <div class="col-lg-4">
        <div class="footer-heading">EV Operators</div>
        <p style="font-size:.82rem;color:rgba(255,255,255,.4)">
          AC Mobility · EVIDA · Shell Recharge · Meralco EV Charge · NLEX Corp · ChargeNow PH
        </p>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© <?= date('Y') ?> STARQE. All rights reserved.</span>
      <span style="color:rgba(255,255,255,.25);font-size:.78rem">Powered by OpenStreetMap · Leaflet.js</span>
    </div>
  </div>
</footer>

<!-- ── SCRIPTS ────────────────────────────────────────────── -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
<?php if(isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>
