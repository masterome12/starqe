<?php
require_once 'config/config.php';
requireLogin();
$pageTitle='Book a Station'; $activeNav='bookings'; $settings=getSettings();
$sid    = si($_GET['station_id']??0);
$ref    = s($_GET['ref']??'');
$station= $sid?dbOne('SELECT * FROM stations WHERE station_id=?',[$sid]):null;
$booking= $ref?dbOne('SELECT b.*,s.station_name,s.address,s.city,s.charging_type,s.power_capacity,s.price_per_kwh,s.operator,s.latitude,s.longitude,s.opening_time,s.closing_time FROM bookings b JOIN stations s ON b.station_id=s.station_id WHERE b.reference_no=?',[$ref]):null;
if($booking&&!$station) $station=dbOne('SELECT * FROM stations WHERE station_id=?',[(int)$booking['station_id']]);
$isFav=($station&&isUser())?isFav(uid(),(int)$station['station_id']):false;
include 'includes/header.php';
?>
<div class="page-wrap"><div class="content-pad" style="max-width:900px;margin:0 auto">
  <a href="javascript:history.back()" class="btn-ghost btn btn-sm mb-4" style="text-decoration:none"><i class="bi bi-arrow-left me-1"></i>Back</a>

<?php if($booking): ?>
  <!-- VIEW BOOKING -->
  <div class="card-s mb-4">
    <div class="card-hd" style="background:linear-gradient(135deg,#eff6ff,#dbeafe)">
      <div>
        <div class="booking-ref" style="font-size:1rem"><?= s($booking['reference_no']) ?></div>
        <div style="font-size:.82rem;color:var(--text-2);margin-top:.2rem">Booking Details</div>
      </div>
      <div class="ms-auto"><?= statusBadge($booking['status']) ?></div>
    </div>
    <div class="card-bd">
      <div class="row g-4">
        <div class="col-md-6">
          <div class="mb-3"><div class="inp-lbl">Station</div><div style="font-weight:700"><?= s($booking['station_name']) ?></div><div style="font-size:.82rem;color:var(--text-2)"><?= s($booking['address']??'') ?>, <?= s($booking['city']) ?></div></div>
          <div class="mb-3"><div class="inp-lbl">Date & Time</div>
            <div><?= date('l, F j, Y',strtotime($booking['booking_date'])) ?></div>
            <div style="color:var(--text-2)"><?= date('h:i A',strtotime($booking['booking_time'])) ?> · <?= $booking['duration_hours'] ?> hour(s)</div>
          </div>
          <?php if(!empty($booking['notes'])): ?><div class="mb-3"><div class="inp-lbl">Notes</div><div><?= s($booking['notes']) ?></div></div><?php endif; ?>
          <?php if(!empty($booking['admin_note'])): ?><div class="mb-3"><div class="inp-lbl">Admin Note</div><div style="color:var(--danger)"><?= s($booking['admin_note']) ?></div></div><?php endif; ?>
        </div>
        <div class="col-md-6">
          <div class="mb-3"><div class="inp-lbl">Charging</div><div><?= $booking['charging_type'] ?> · <?= $booking['power_capacity'] ?>kW</div></div>
          <div class="mb-3"><div class="inp-lbl">Operator</div><div><?= s($booking['operator']??'—') ?></div></div>
          <div class="mb-3"><div class="inp-lbl">Estimated Cost</div>
            <div style="font-family:inherit;font-size:1.6rem;font-weight:800;color:var(--primary)">₱<?= number_format($booking['total_amount'],2) ?></div>
            <div style="font-size:.75rem;color:var(--text-2)">₱<?= number_format($booking['price_per_kwh'],2) ?>/kWh × <?= $booking['power_capacity'] ?>kW × <?= $booking['duration_hours'] ?>h</div>
          </div>
        </div>
      </div>
      <div style="display:flex;gap:.5rem;flex-wrap:wrap;border-top:1px solid var(--border);padding-top:1rem;margin-top:.5rem">
        <?php if(!empty($booking['latitude'])): ?>
        <button class="btn-ghost btn btn-sm" onclick="APP.gmaps(<?= $booking['latitude'] ?>,<?= $booking['longitude'] ?>)"><i class="bi bi-map me-1"></i>Google Maps</button>
        <button class="btn-ghost btn btn-sm" onclick="APP.waze(<?= $booking['latitude'] ?>,<?= $booking['longitude'] ?>)"><i class="bi bi-sign-turn-right me-1"></i>Waze</button>
        <?php endif; ?>
        <?php if(in_array($booking['status'],['pending','confirmed','approved'])): ?>
        <button class="btn-danger-s btn btn-sm ms-auto" onclick="cancelBook(<?= $booking['booking_id'] ?>,'<?= s($booking['reference_no']) ?>')"><i class="bi bi-x-circle me-1"></i>Cancel</button>
        <?php elseif($booking['status']==='completed'): ?>
        <a href="<?= BASE_URL ?>/booking.php?station_id=<?= $booking['station_id'] ?>" class="btn-primary-s btn btn-sm ms-auto" style="text-decoration:none"><i class="bi bi-arrow-repeat me-1"></i>Book Again</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

<?php elseif($station): ?>
  <!-- NEW BOOKING FORM -->
  <div class="row g-4">
    <!-- Station Info -->
    <div class="col-md-5">
      <div class="card-s mb-3">
        <div style="background:linear-gradient(135deg,#eff6ff,#dbeafe);padding:.85rem 1.1rem;border-bottom:1px solid var(--border)">
          <div style="display:flex;gap:.4rem;align-items:center;flex-wrap:wrap">
            <?php echo isset($_GET['station_id'])?'':''; // space ?>
            <span style="background:<?= $station['status']==='available'?'#dcfce7':($station['status']==='busy'?'#fef9c3':'#fee2e2') ?>;color:<?= $station['status']==='available'?'#16a34a':($station['status']==='busy'?'#ca8a04':'#dc2626') ?>;padding:.2rem .6rem;border-radius:99px;font-size:.72rem;font-weight:600"><?= ucfirst($station['status']) ?></span>
            <span style="background:#dbeafe;color:#2563eb;padding:.2rem .6rem;border-radius:99px;font-size:.7rem;font-weight:700"><?= $station['charging_type'] ?></span>
            <?php if($station['is_fast_charge']): ?><span style="background:#fef3c7;color:#d97706;padding:.2rem .6rem;border-radius:99px;font-size:.7rem;font-weight:700">⚡ Fast</span><?php endif; ?>
            <?php if($station['is_verified']): ?><span style="color:#2563eb;font-size:.75rem">✓ Verified</span><?php endif; ?>
          </div>
          <div style="font-weight:800;font-size:1rem;color:var(--text);margin-top:.4rem"><?= s($station['station_name']) ?></div>
          <div style="font-size:.78rem;color:var(--text-2)"><i class="bi bi-geo-alt me-1"></i><?= s($station['address'].', '.$station['city']) ?></div>
        </div>
        <div style="padding:.9rem 1.1rem">
          <div class="row g-2 mb-3">
            <?php foreach([['Power',$station['power_capacity'].'kW'],['Ports',$station['available_ports'].'/'.$station['total_ports'].' Free'],['Rate','₱'.number_format($station['price_per_kwh'],2).'/kWh'],['Operator',s($station['operator']??'—')]] as [$l,$v]): ?>
            <div class="col-6"><div style="background:var(--bg-soft);border-radius:8px;padding:.45rem .6rem"><div style="font-size:.65rem;color:var(--text-2);text-transform:uppercase;letter-spacing:.04em"><?= $l ?></div><div style="font-weight:700;font-size:.85rem;color:var(--text)"><?= $v ?></div></div></div>
            <?php endforeach; ?>
          </div>
          <div id="mini-map" style="height:155px;border-radius:10px;overflow:hidden;margin-bottom:.9rem;border:1px solid var(--border)"></div>
          <div style="display:flex;gap:.4rem">
            <button class="btn-ghost btn btn-sm flex-fill" onclick="APP.gmaps(<?= $station['latitude'] ?>,<?= $station['longitude'] ?>)"><i class="bi bi-map"></i></button>
            <button class="btn-ghost btn btn-sm flex-fill" onclick="APP.waze(<?= $station['latitude'] ?>,<?= $station['longitude'] ?>)"><i class="bi bi-sign-turn-right"></i></button>
            <button class="fav-btn <?= $isFav?'active':'' ?>" id="fav-btn" onclick="FAVS.toggle(<?= $station['station_id'] ?>,this)" title="<?= $isFav?'Remove':'Add to Favorites' ?>">
              <i class="bi bi-heart<?= $isFav?'-fill':'' ?>" style="color:<?= $isFav?'var(--danger)':'var(--text-3)' ?>"></i>
            </button>
          </div>
        </div>
      </div>
      <div class="card-s"><div style="padding:.85rem 1.1rem;font-size:.82rem;color:var(--text-2)">
        <i class="bi bi-flag me-1"></i>Something wrong?
        <button class="btn-danger-s btn btn-sm d-block mt-2 w-100" data-bs-toggle="modal" data-bs-target="#reportModal">Submit a Report</button>
      </div></div>
    </div>

    <!-- Booking Form -->
    <div class="col-md-7">
      <div class="card-s">
        <div class="card-hd">
          <div class="metric-icon blue" style="width:32px;height:32px;font-size:.85rem"><i class="bi bi-calendar-plus"></i></div>
          <span class="title">Book This Station</span>
        </div>
        <div class="card-bd">
          <?php if($station['status']==='maintenance'): ?>
          <div class="alert alert-warning"><i class="bi bi-tools me-2"></i>This station is under maintenance and cannot be booked.</div>
          <?php else: ?>
          <div id="book-alert" class="alert alert-danger d-none d-flex align-items-center gap-2"></div>
          <div class="form-group">
            <label class="inp-lbl">Date</label>
            <input type="date" id="bdate" class="inp" min="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d',strtotime('+'.$settings['booking_advance_days'].' days')) ?>" value="<?= date('Y-m-d') ?>">
          </div>
          <div class="row g-3 mb-3">
            <div class="col-7">
              <label class="inp-lbl">Time Slot</label>
              <select id="btime" class="inp">
                <?php for($h=6;$h<=22;$h++) foreach(['00','30'] as $m){$v=sprintf('%02d:%s:00',$h,$m);echo "<option value='$v'>".date('h:i A',strtotime($v))."</option>";}?>
              </select>
            </div>
            <div class="col-5">
              <label class="inp-lbl">Duration</label>
              <select id="bdur" class="inp" onchange="updateCost()">
                <?php for($i=1;$i<=$settings['max_booking_hours'];$i++): ?><option value="<?= $i ?>"><?= $i ?> Hour<?= $i>1?'s':'' ?></option><?php endfor; ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="inp-lbl">Notes (optional)</label>
            <textarea id="bnotes" class="inp" rows="2" placeholder="Any special requests…" style="resize:none"></textarea>
          </div>
          <!-- Cost estimator -->
          <div style="background:linear-gradient(135deg,#eff6ff,#dbeafe);border-radius:var(--r);padding:1rem;margin-bottom:1rem;border:1px solid #bfdbfe">
            <div class="inp-lbl mb-1">Estimated Cost</div>
            <div style="display:flex;justify-content:space-between;align-items:center">
              <div style="font-size:.82rem;color:var(--text-2)"><?= $station['power_capacity'] ?>kW × ₱<?= number_format($station['price_per_kwh'],2) ?> × <span id="est-h">1</span>h</div>
              <div style="font-size:1.4rem;font-weight:800;color:var(--primary)" id="est-cost">₱<?= number_format($station['power_capacity']*$station['price_per_kwh'],2) ?></div>
            </div>
            <div style="font-size:.72rem;color:#64748b;margin-top:.3rem">⚠️ Actual cost may vary based on actual kWh consumed</div>
          </div>
          <button class="btn-primary-s btn w-100 py-2 justify-content-center" id="book-btn" onclick="doBook()">
            <i class="bi bi-calendar-check me-2"></i>Confirm Booking
          </button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

<?php else: ?>
  <div class="card-s"><div class="empty-state p-5">
    <div class="empty-icon">🔌</div><div class="empty-title">No Station Selected</div>
    <div class="empty-desc">Pick a station from the map to book</div>
    <a href="<?= BASE_URL ?>/map.php" class="btn-primary-s btn btn-sm mt-3" style="text-decoration:none">Open Map</a>
  </div></div>
<?php endif; ?>
</div></div>

<!-- Report Modal -->
<?php if($station): ?>
<div class="modal fade" id="reportModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">🚩 Report Station</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div id="rep-alert" class="alert alert-danger d-none"></div>
        <div class="form-group"><label class="inp-lbl">Issue Type</label>
          <select id="rtype" class="inp"><option value="full">Station Full/No Ports</option><option value="offline">Station Offline</option><option value="damaged">Equipment Damaged</option><option value="wrong_info">Wrong Info</option><option value="other">Other</option></select>
        </div>
        <div class="form-group"><label class="inp-lbl">Remarks</label><textarea id="rremarks" class="inp" rows="3" style="resize:none" placeholder="Describe the issue…"></textarea></div>
      </div>
      <div class="modal-footer">
        <button class="btn-ghost btn" data-bs-dismiss="modal">Cancel</button>
        <button class="btn-danger-s btn" onclick="submitReport()"><i class="bi bi-flag me-1"></i>Submit</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
<script>
const BASE='<?= BASE_URL ?>';
<?php if($station): ?>
const KWH=<?= $station['power_capacity'] ?>,RATE=<?= $station['price_per_kwh'] ?>;
function updateCost(){const h=+document.getElementById('bdur').value;document.getElementById('est-h').textContent=h;document.getElementById('est-cost').textContent='₱'+(KWH*RATE*h).toLocaleString('en-PH',{minimumFractionDigits:2});}

// Mini map
const mm=L.map('mini-map',{zoomControl:false,attributionControl:false,dragging:false});
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mm);
mm.setView([<?= $station['latitude'] ?>,<?= $station['longitude'] ?>],15);
L.marker([<?= $station['latitude'] ?>,<?= $station['longitude'] ?>]).addTo(mm).bindPopup('<?= addslashes(s($station['station_name'])) ?>').openPopup();

async function doBook(){
  const btn=document.getElementById('book-btn');
  const alr=document.getElementById('book-alert');
  APP.btnLoad(btn,'Processing...');
  alr.classList.add('d-none');
  const r=await APP.post(BASE+'/api/bookings.php',{station_id:<?= $station['station_id'] ?>,booking_date:document.getElementById('bdate').value,booking_time:document.getElementById('btime').value,duration_hours:document.getElementById('bdur').value,notes:document.getElementById('bnotes').value});
  if(r.success){APP.toast('Booking confirmed! Ref: '+r.data.reference_no,'success',0);setTimeout(()=>window.location=BASE+'/booking.php?ref='+r.data.reference_no,1000);}
  else{alr.innerHTML='<i class="bi bi-exclamation-circle me-2"></i>'+r.message;alr.classList.remove('d-none');alr.classList.add('d-flex');APP.btnReset(btn);}
}
async function submitReport(){
  const r=await APP.post(BASE+'/api/reports.php',{station_id:<?= $station['station_id'] ?>,report_type:document.getElementById('rtype').value,remarks:document.getElementById('rremarks').value});
  if(r.success){bootstrap.Modal.getInstance(document.getElementById('reportModal')).hide();APP.toast(r.message,'success');}
  else{document.getElementById('rep-alert').innerHTML=r.message;document.getElementById('rep-alert').classList.remove('d-none');}
}
<?php endif; ?>
<?php if($booking): ?>
async function cancelBook(id,ref){
  APP.confirm(`Cancel booking <b>${ref}</b>?`,async()=>{
    const fd=new FormData();fd.append('booking_id',id);fd.append('status','cancelled');fd.append('_method','PUT');
    const r=await APP.postForm(BASE+'/api/bookings.php',fd);
    if(r.success){APP.toast('Cancelled.','success');setTimeout(()=>location.reload(),800);}
    else APP.toast(r.message,'error');
  });
}
<?php endif; ?>
</script>
