/**
 * STARQE v2.0 — Core JavaScript
 * Native JS: API layer, Toast, Map, Search, Utils
 */

// ── Configuration ──────────────────────────────────────────
const APP = {
  base: document.querySelector('meta[name="base-url"]')?.content || '',
  csrf: document.querySelector('meta[name="csrf"]')?.content || '',
  version: '2.0.0',

  // ── Toast System ────────────────────────────────────────
  toast(msg, type = 'info', duration = 4000) {
    let container = document.getElementById('toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toast-container';
      document.body.appendChild(container);
    }
    const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
    const id = 'toast-' + Date.now();
    const el = document.createElement('div');
    el.id = id;
    el.className = `toast-item toast-${type}`;
    el.innerHTML = `
      <span class="toast-icon">${icons[type] || icons.info}</span>
      <div class="toast-body">
        <div class="toast-title">${type.charAt(0).toUpperCase() + type.slice(1)}</div>
        <div class="toast-msg">${msg}</div>
      </div>
      <span class="toast-close" onclick="APP.dismissToast('${id}')">&times;</span>`;
    container.appendChild(el);
    el.addEventListener('click', () => APP.dismissToast(id));
    if (duration > 0) setTimeout(() => APP.dismissToast(id), duration);
    return el;
  },

  dismissToast(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.add('toast-out');
    setTimeout(() => el.remove(), 280);
  },

  // ── Fetch API Layer ──────────────────────────────────────
  async get(url, params = {}) {
    try {
      const qs = Object.keys(params).length ? '?' + new URLSearchParams(params) : '';
      const res = await fetch(url + qs, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      return await res.json();
    } catch (e) { return { success: false, message: 'Network error' }; }
  },

  async post(url, data = {}) {
    try {
      const form = new FormData();
      form.append('csrf', this.csrf);
      for (const [k, v] of Object.entries(data)) form.append(k, v ?? '');
      const res = await fetch(url, {
        method: 'POST',
        body: form,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      return await res.json();
    } catch (e) { return { success: false, message: 'Network error' }; }
  },

  async postForm(url, formData) {
    try {
      formData.append('csrf', this.csrf);
      const res = await fetch(url, {
        method: 'POST', body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      return await res.json();
    } catch (e) { return { success: false, message: 'Network error' }; }
  },

  async put(url, data = {}) {
    try {
      const form = new FormData();
      form.append('csrf', this.csrf);
      form.append('_method', 'PUT');
      for (const [k, v] of Object.entries(data)) form.append(k, v ?? '');
      const res = await fetch(url, { method: 'POST', body: form, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      return await res.json();
    } catch (e) { return { success: false, message: 'Network error' }; }
  },

  async delete(url, data = {}) {
    try {
      const form = new FormData();
      form.append('csrf', this.csrf);
      form.append('_method', 'DELETE');
      for (const [k, v] of Object.entries(data)) form.append(k, v ?? '');
      const res = await fetch(url, { method: 'POST', body: form, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      return await res.json();
    } catch (e) { return { success: false, message: 'Network error' }; }
  },

  // ── Confirm Dialog ───────────────────────────────────────
  confirm(message, onConfirm, title = 'Confirm Action') {
    const id = 'confirm-' + Date.now();
    const html = `
    <div class="modal fade confirm-modal" id="${id}" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title">${title}</h5>
          </div>
          <div class="modal-body pt-2">
            <p class="text-secondary mb-0" style="font-size:.9rem">${message}</p>
          </div>
          <div class="modal-footer border-0 pt-1 gap-2">
            <button class="btn-ghost btn btn-sm" data-bs-dismiss="modal">Cancel</button>
            <button class="btn-danger-s btn btn-sm" id="${id}-ok">Confirm</button>
          </div>
        </div>
      </div>
    </div>`;
    document.body.insertAdjacentHTML('beforeend', html);
    const modal = new bootstrap.Modal(document.getElementById(id));
    document.getElementById(`${id}-ok`).onclick = () => { modal.hide(); onConfirm(); };
    document.getElementById(id).addEventListener('hidden.bs.modal', () =>
      document.getElementById(id)?.remove());
    modal.show();
  },

  // ── Utilities ────────────────────────────────────────────
  debounce(fn, ms = 350) {
    let t;
    return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); };
  },

  money(v)       { return '₱' + parseFloat(v).toLocaleString('en-PH', { minimumFractionDigits: 2 }); },
  date(s)        { if (!s) return '—'; return new Date(s).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' }); },
  datetime(s)    { if (!s) return '—'; return new Date(s).toLocaleString('en-PH', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }); },
  timeAgo(s) {
    const d = Math.floor((Date.now() - new Date(s)) / 1000);
    if (d < 60) return d + 's ago';
    if (d < 3600) return Math.floor(d / 60) + 'm ago';
    if (d < 86400) return Math.floor(d / 3600) + 'h ago';
    return Math.floor(d / 86400) + 'd ago';
  },
  distKm(la1, ln1, la2, ln2) {
    const R = 6371, dL = (la2 - la1) * Math.PI / 180, dN = (ln2 - ln1) * Math.PI / 180;
    const a = Math.sin(dL / 2) ** 2 + Math.cos(la1 * Math.PI / 180) * Math.cos(la2 * Math.PI / 180) * Math.sin(dN / 2) ** 2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  },
  fmtDist(km)    { return km < 1 ? Math.round(km * 1000) + 'm' : km.toFixed(1) + ' km'; },
  fmtETA(km)     { const m = Math.ceil(km / 30 * 60); return m < 60 ? m + ' min' : Math.floor(m / 60) + 'h ' + (m % 60) + 'm'; },
  gmaps(lat, lng){ window.open(`https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`, '_blank'); },
  waze(lat, lng) { window.open(`https://waze.com/ul?ll=${lat},${lng}&navigate=yes`, '_blank'); },

  statusBadge(s) {
    const m = {
      available:   ['#dcfce7','#16a34a','Available'],
      busy:        ['#fef9c3','#ca8a04','Busy'],
      full:        ['#fee2e2','#dc2626','Full'],
      maintenance: ['#f1f5f9','#64748b','Maintenance'],
      pending:     ['#fef9c3','#ca8a04','Pending'],
      confirmed:   ['#dbeafe','#2563eb','Confirmed'],
      approved:    ['#dcfce7','#16a34a','Approved'],
      rejected:    ['#fee2e2','#dc2626','Rejected'],
      cancelled:   ['#fee2e2','#dc2626','Cancelled'],
      completed:   ['#ecfeff','#0891b2','Completed'],
    };
    const [bg, tc, lbl] = m[s] || ['#f1f5f9', '#64748b', s];
    return `<span style="background:${bg};color:${tc};padding:.2rem .65rem;border-radius:999px;font-size:.72rem;font-weight:600;white-space:nowrap">${lbl}</span>`;
  },

  typeBadge(t) {
    const m = { 'AC': '#dbeafe,#2563eb', 'DC': '#dcfce7,#16a34a', 'AC/DC': '#f3e8ff,#9333ea' };
    const [bg, tc] = (m[t] || '#f1f5f9,#64748b').split(',');
    return `<span style="background:${bg};color:${tc};padding:.2rem .6rem;border-radius:999px;font-size:.7rem;font-weight:700">${t}</span>`;
  },

  // ── Loading Button ───────────────────────────────────────
  btnLoad(btn, text = 'Loading...') {
    btn.disabled = true;
    btn._orig = btn.innerHTML;
    btn.innerHTML = `<span class="spin-sm"></span>${text}`;
  },
  btnReset(btn) {
    btn.disabled = false;
    if (btn._orig) btn.innerHTML = btn._orig;
  },

  // ── Skeleton Loader ──────────────────────────────────────
  skeleton(rows = 4, cols = 4) {
    return Array(rows).fill(0).map(() =>
      `<tr>${Array(cols).fill(0).map(() =>
        `<td><div class="skeleton" style="height:18px;border-radius:6px"></div></td>`
      ).join('')}</tr>`
    ).join('');
  },

  // ── Notifications ────────────────────────────────────────
  async loadNotifs() {
    const r = await this.get(this.base + '/api/notifications.php');
    if (!r.success) return;
    const { items, unread } = r.data;
    const badge = document.getElementById('notif-badge');
    if (badge) {
      badge.textContent = unread;
      badge.style.display = unread > 0 ? 'flex' : 'none';
    }
    const list = document.getElementById('notif-list');
    if (!list) return;
    if (!items.length) {
      list.innerHTML = `<div class="p-3 text-center" style="font-size:.85rem;color:#94a3b8">No notifications</div>`;
      return;
    }
    list.innerHTML = items.map(n => `
      <div class="notif-item ${n.is_read == 0 ? 'unread' : ''}" onclick="APP.markNotif(${n.notif_id}, '${n.link || ''}')">
        <div class="notif-type-icon ${n.type === 'success' ? 'bg-success-soft' : n.type === 'error' ? 'bg-danger-soft' : 'bg-primary-soft'}">
          ${n.type === 'success' ? '✅' : n.type === 'error' ? '❌' : 'ℹ️'}
        </div>
        <div style="flex:1;min-width:0">
          <div class="notif-msg">${n.title}: ${n.message}</div>
          <div class="notif-time">${APP.timeAgo(n.created_at)}</div>
        </div>
      </div>`).join('');
  },

  async markNotif(id, link) {
    await this.post(this.base + '/api/notifications.php', { action: 'read', notif_id: id });
    if (link) window.location.href = link;
    else this.loadNotifs();
  },

  async markAllRead() {
    await this.post(this.base + '/api/notifications.php', { action: 'read_all' });
    this.loadNotifs();
  }
};

// ── Map Module ────────────────────────────────────────────
const SMAP = {
  map: null, cluster: null, markers: {}, route: null,
  userLat: null, userLng: null, stations: [],
  selectedId: null,

  statusColors: {
    available:   '#22C55E',
    busy:        '#F59E0B',
    full:        '#EF4444',
    maintenance: '#94A3B8'
  },

  init(el = 'starqe-map') {
    this.map = L.map(el, {
      center: [12.8797, 121.7740], zoom: 7,
      zoomControl: false, attributionControl: true
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap',
      maxZoom: 19
    }).addTo(this.map);

    L.control.zoom({ position: 'bottomright' }).addTo(this.map);

    if (typeof L.markerClusterGroup !== 'undefined') {
      this.cluster = L.markerClusterGroup({
        chunkedLoading: true,
        iconCreateFunction: cluster => {
          const n = cluster.getChildCount();
          return L.divIcon({
            html: `<div style="
              width:40px;height:40px;border-radius:50%;
              background:linear-gradient(135deg,#2563eb,#3b82f6);
              color:#fff;display:flex;align-items:center;justify-content:center;
              font-weight:700;font-size:.82rem;
              box-shadow:0 4px 12px rgba(37,99,235,.4);">${n}</div>`,
            className: '', iconSize: [40, 40]
          });
        }
      });
      this.map.addLayer(this.cluster);
    }

    this.locate();
    this.load();
    setInterval(() => this.refresh(), 180000);
    return this;
  },

  async load() {
    const r = await APP.get(APP.base + '/api/stations.php');
    if (!r.success) return;
    this.stations = r.data;
    this.renderMarkers();
    this.renderList();
  },

  async refresh() {
    const r = await APP.get(APP.base + '/api/stations.php');
    if (!r.success) return;
    this.stations = r.data;
    r.data.forEach(s => {
      if (this.markers[s.station_id]) {
        this.markers[s.station_id]._sData = s;
      }
    });
    this.renderList();
    const dot = document.getElementById('refresh-dot');
    if (dot) { dot.style.background = '#f59e0b'; setTimeout(() => dot.style.background = '#22c55e', 800); }
    const ts = document.getElementById('last-refresh');
    if (ts) ts.textContent = new Date().toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' });
  },

  makeIcon(station) {
    const col = this.statusColors[station.status] || '#94A3B8';
    const isFast = station.is_fast_charge == 1;
    const outline = isFast ? '#f59e0b' : col;
    return L.divIcon({
      className: '',
      iconSize: [36, 44], iconAnchor: [18, 44], popupAnchor: [0, -44],
      html: `<div style="position:relative;width:36px;height:44px">
        <svg viewBox="0 0 36 44" xmlns="http://www.w3.org/2000/svg">
          <path d="M18 0C8.06 0 0 8.06 0 18c0 13.5 18 26 18 26S36 31.5 36 18C36 8.06 27.94 0 18 0z"
            fill="${col}" stroke="${outline}" stroke-width="${isFast ? 2.5 : 0}"/>
          <circle cx="18" cy="18" r="10" fill="rgba(255,255,255,.9)"/>
          <text x="18" y="22.5" text-anchor="middle" font-size="10" font-weight="700"
            fill="${col}" font-family="'Inter',sans-serif">
            ${station.charging_type === 'AC/DC' ? '⚡' : station.charging_type}
          </text>
        </svg>
      </div>`
    });
  },

  makeSelectedIcon(station) {
    const col = this.statusColors[station.status] || '#94A3B8';
    return L.divIcon({
      className: '',
      iconSize: [44, 54], iconAnchor: [22, 54], popupAnchor: [0, -54],
      html: `<div style="position:relative;width:44px;height:54px;animation:bounceIn .4s cubic-bezier(.34,1.56,.64,1)">
        <svg viewBox="0 0 44 54" xmlns="http://www.w3.org/2000/svg">
          <path d="M22 0C9.85 0 0 9.85 0 22c0 16.5 22 32 22 32S44 38.5 44 22C44 9.85 34.15 0 22 0z"
            fill="#2563EB" stroke="#fff" stroke-width="2"/>
          <circle cx="22" cy="22" r="12" fill="rgba(255,255,255,.95)"/>
          <text x="22" y="27" text-anchor="middle" font-size="12" font-weight="700"
            fill="#2563EB" font-family="'Inter',sans-serif">
            ${station.charging_type === 'AC/DC' ? '⚡' : station.charging_type}
          </text>
        </svg>
      </div>`
    });
  },

  buildPopup(s) {
    const dist = (this.userLat && this.userLng)
      ? APP.fmtDist(APP.distKm(this.userLat, this.userLng, +s.latitude, +s.longitude)) : null;
    const eta = (this.userLat && this.userLng)
      ? APP.fmtETA(APP.distKm(this.userLat, this.userLng, +s.latitude, +s.longitude)) : null;
    const portColor = +s.available_ports > 0 ? '#22c55e' : '#ef4444';
    const col = this.statusColors[s.status] || '#94a3b8';
    const verifiedBadge = s.is_verified == 1 ? '<span style="color:#2563eb;font-size:.65rem">✓ Verified</span>' : '';
    const fastBadge = s.is_fast_charge == 1 ? '<span style="background:#fef3c7;color:#d97706;padding:.15rem .45rem;border-radius:99px;font-size:.65rem;font-weight:700">⚡ Fast</span>' : '';

    return `<div class="popup-wrap">
      <div class="popup-head" style="border-left:4px solid ${col}">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem">
          <span style="font-weight:700;font-size:.9rem;color:#0f172a;line-height:1.2">${s.station_name}</span>
          ${APP.statusBadge(s.status)}
        </div>
        <div style="font-size:.75rem;color:#64748b;margin-top:.2rem;display:flex;align-items:center;gap:.4rem">
          <span>📍 ${s.address}, ${s.city}</span>
        </div>
        <div style="display:flex;gap:.35rem;margin-top:.4rem">${verifiedBadge}${fastBadge}${APP.typeBadge(s.charging_type)}</div>
      </div>
      <div class="popup-body" style="padding:.75rem 1rem">
        <div class="popup-grid">
          <div class="popup-stat">
            <div class="popup-stat-lbl">Power</div>
            <div class="popup-stat-val">${s.power_capacity}kW</div>
          </div>
          <div class="popup-stat">
            <div class="popup-stat-lbl">Price</div>
            <div class="popup-stat-val" style="color:#2563eb">₱${(+s.price_per_kwh).toFixed(2)}/kWh</div>
          </div>
          <div class="popup-stat">
            <div class="popup-stat-lbl">Ports Free</div>
            <div class="popup-stat-val" style="color:${portColor}">${s.available_ports}/${s.total_ports}</div>
          </div>
          ${dist ? `<div class="popup-stat">
            <div class="popup-stat-lbl">Distance</div>
            <div class="popup-stat-val">${dist} · ${eta}</div>
          </div>` : ''}
        </div>
        <div style="font-size:.75rem;color:#64748b;margin-bottom:.5rem">
          <span>🏢 ${s.operator || 'Independent'}</span>
          &nbsp;·&nbsp;<span>🕒 ${s.opening_time?.substring(0,5) || '06:00'}–${s.closing_time?.substring(0,5) || '22:00'}</span>
        </div>
      </div>
      <div class="popup-actions" style="padding:.65rem 1rem;border-top:1px solid #e2e8f0;display:flex;gap:.4rem">
        <a href="${APP.base}/booking.php?station_id=${s.station_id}"
           style="flex:1;padding:.38rem .5rem;font-size:.78rem;text-align:center;background:linear-gradient(135deg,#2563eb,#3b82f6);color:#fff;border-radius:8px;font-weight:600;border:none;cursor:pointer;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:.3rem">
          📅 Book
        </a>
        <button onclick="SMAP.drawRoute(${s.latitude},${s.longitude})"
          style="padding:.38rem .55rem;font-size:.78rem;background:#f8fafc;color:#2563eb;border:1.5px solid #2563eb;border-radius:8px;cursor:pointer;font-weight:600">
          🗺 Route
        </button>
        <button onclick="APP.gmaps(${s.latitude},${s.longitude})"
          style="padding:.38rem .55rem;font-size:.78rem;background:#f8fafc;color:#475569;border:1px solid #e2e8f0;border-radius:8px;cursor:pointer">
          G
        </button>
        <button onclick="APP.waze(${s.latitude},${s.longitude})"
          style="padding:.38rem .55rem;font-size:.78rem;background:#f8fafc;color:#475569;border:1px solid #e2e8f0;border-radius:8px;cursor:pointer">
          W
        </button>
      </div>
    </div>`;
  },

  renderMarkers() {
    if (this.cluster) this.cluster.clearLayers();
    else Object.values(this.markers).forEach(m => this.map.removeLayer(m));
    this.markers = {};

    this.stations.forEach(s => {
      const m = L.marker([+s.latitude, +s.longitude], { icon: this.makeIcon(s) });
      m._sData = s;
      m.bindPopup(() => this.buildPopup(m._sData), {
        maxWidth: 300, closeButton: false
      });
      m.on('click', () => {
        this.selectMarker(s.station_id);
        this.map.setView([+s.latitude, +s.longitude], 16, { animate: true, duration: 0.5 });
        this.hiliteListItem(s.station_id);
      });
      if (this.cluster) this.cluster.addLayer(m);
      else m.addTo(this.map);
      this.markers[s.station_id] = m;
    });
  },

  selectMarker(id) {
    if (this.selectedId && this.markers[this.selectedId]) {
      this.markers[this.selectedId].setIcon(this.makeIcon(this.markers[this.selectedId]._sData));
    }
    this.selectedId = id;
    if (this.markers[id]) {
      this.markers[id].setIcon(this.makeSelectedIcon(this.markers[id]._sData));
    }
  },

  renderList(data = null) {
    const list = document.getElementById('station-list');
    if (!list) return;
    const arr = data || this.stations;
    const cnt = document.getElementById('station-count');
    if (cnt) cnt.textContent = arr.length;

    if (!arr.length) {
      list.innerHTML = `<div class="empty-state py-4">
        <div class="empty-icon" style="font-size:2.5rem">🔌</div>
        <div class="empty-title">No stations found</div>
        <div class="empty-desc">Try different search terms</div>
      </div>`;
      return;
    }

    list.innerHTML = arr.map(s => {
      const dist = (this.userLat && this.userLng)
        ? APP.fmtDist(APP.distKm(this.userLat, this.userLng, +s.latitude, +s.longitude)) : null;
      const col = this.statusColors[s.status] || '#94a3b8';
      return `<div class="station-list-item ${this.selectedId == s.station_id ? 'active' : ''}"
               id="li-${s.station_id}" onclick="SMAP.focusStation(${s.station_id})">
        <div style="display:flex;align-items:flex-start;gap:.65rem">
          <div style="width:8px;height:8px;border-radius:50%;background:${col};margin-top:6px;flex-shrink:0"></div>
          <div style="flex:1;min-width:0">
            <div style="font-weight:600;font-size:.875rem;color:#0f172a" class="text-truncate">${s.station_name}</div>
            <div style="font-size:.75rem;color:#64748b">${s.city} · ${s.charging_type} · ₱${(+s.price_per_kwh).toFixed(2)}/kWh</div>
            <div style="display:flex;align-items:center;gap:.75rem;margin-top:.2rem">
              <span style="font-size:.7rem;color:${+s.available_ports > 0 ? '#16a34a' : '#dc2626'};font-weight:600">
                ${s.available_ports}/${s.total_ports} ports
              </span>
              ${dist ? `<span style="font-size:.7rem;color:#94a3b8">${dist}</span>` : ''}
              ${s.is_fast_charge == 1 ? '<span style="font-size:.68rem;background:#fef3c7;color:#d97706;padding:.08rem .35rem;border-radius:99px">⚡</span>' : ''}
            </div>
          </div>
        </div>
      </div>`;
    }).join('');
  },

  focusStation(id) {
    const m = this.markers[id];
    if (!m) return;
    const ll = m.getLatLng();
    this.map.setView(ll, 16, { animate: true, duration: 0.6 });
    setTimeout(() => {
      if (this.cluster) this.cluster.zoomToShowLayer(m, () => m.openPopup());
      else m.openPopup();
    }, 400);
    this.selectMarker(id);
    this.hiliteListItem(id);
  },

  hiliteListItem(id) {
    document.querySelectorAll('.station-list-item').forEach(el => el.classList.remove('active'));
    const el = document.getElementById('li-' + id);
    if (el) { el.classList.add('active'); el.scrollIntoView({ block: 'nearest', behavior: 'smooth' }); }
  },

  locate() {
    if (!navigator.geolocation) return;
    navigator.geolocation.getCurrentPosition(pos => {
      this.userLat = pos.coords.latitude;
      this.userLng = pos.coords.longitude;
      const icon = L.divIcon({
        className: '',
        iconSize: [20, 20], iconAnchor: [10, 10],
        html: '<div class="user-dot"></div>'
      });
      L.marker([this.userLat, this.userLng], { icon })
        .bindPopup('<b>📍 Your Location</b>').addTo(this.map);
      this.map.setView([this.userLat, this.userLng], 13, { animate: true });
      this.renderList();
      this.sortByDistance();
    }, () => {}, { enableHighAccuracy: true, timeout: 8000 });
  },

  recenter() {
    if (this.userLat) this.map.setView([this.userLat, this.userLng], 14, { animate: true });
    else this.locate();
  },

  sortByDistance() {
    if (!this.userLat) return;
    const sorted = [...this.stations].sort((a, b) =>
      APP.distKm(this.userLat, this.userLng, +a.latitude, +a.longitude) -
      APP.distKm(this.userLat, this.userLng, +b.latitude, +b.longitude)
    );
    this.renderList(sorted);
  },

  filter(q = '', status = '', type = '', fastOnly = false, nearbyOnly = false, favIds = []) {
    q = q.toLowerCase().trim();
    let arr = this.stations.filter(s => {
      const mq = !q || [s.station_name, s.address, s.city, s.operator || '']
        .some(v => v.toLowerCase().includes(q));
      const ms = !status || s.status === status;
      const mt = !type   || s.charging_type === type;
      const mf = !fastOnly || s.is_fast_charge == 1;
      const mfav = !favIds.length || favIds.includes(+s.station_id);
      return mq && ms && mt && mf && mfav;
    });

    if (nearbyOnly && this.userLat) {
      arr = arr.filter(s => APP.distKm(this.userLat, this.userLng, +s.latitude, +s.longitude) <= 10);
    }
    if (this.userLat) {
      arr.sort((a, b) =>
        APP.distKm(this.userLat, this.userLng, +a.latitude, +a.longitude) -
        APP.distKm(this.userLat, this.userLng, +b.latitude, +b.longitude)
      );
    }

    this.renderList(arr);
    // Update map markers
    Object.values(this.markers).forEach(m => { if (this.cluster) this.cluster.removeLayer(m); });
    arr.forEach(s => { if (this.markers[s.station_id] && this.cluster) this.cluster.addLayer(this.markers[s.station_id]); });
  },

  resetFilter() {
    this.renderList();
    this.stations.forEach(s => { if (this.markers[s.station_id] && this.cluster) this.cluster.addLayer(this.markers[s.station_id]); });
  },

  async drawRoute(toLat, toLng) {
    if (!this.userLat) { APP.toast('Enable location to draw routes.', 'warning'); return; }
    if (this.route) this.map.removeLayer(this.route);
    try {
      const url = `https://router.project-osrm.org/route/v1/driving/${this.userLng},${this.userLat};${toLng},${toLat}?overview=full&geometries=geojson`;
      const res = await fetch(url);
      const d   = await res.json();
      if (d.routes?.length) {
        this.route = L.geoJSON(d.routes[0].geometry, {
          style: { color: '#2563EB', weight: 5, opacity: .85,
            dashArray: null,
            lineCap: 'round', lineJoin: 'round' }
        }).addTo(this.map);
        this.map.fitBounds(this.route.getBounds(), { padding: [50, 50], animate: true });
        const km  = (d.routes[0].distance / 1000).toFixed(1);
        const min = Math.ceil(d.routes[0].duration / 60);
        APP.toast(`🗺 Route: ${km} km · ~${min} minutes`, 'info');
      }
    } catch {
      APP.toast('Route unavailable. Opening Google Maps instead.', 'warning');
      APP.gmaps(toLat, toLng);
    }
  },

  clearRoute() { if (this.route) { this.map.removeLayer(this.route); this.route = null; } },

  // Reverse geocode using Nominatim
  async reverseGeocode(lat, lng) {
    try {
      const r = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`);
      const d = await r.json();
      return d.display_name || '';
    } catch { return ''; }
  }
};

// ── Search Module ─────────────────────────────────────────
const SEARCH = {
  history: JSON.parse(localStorage.getItem('sq_history') || '[]'),
  maxHistory: 8,
  activeFilters: { status: '', type: '', fast: false, nearby: false, favs: false },
  favIds: [],

  init() {
    const inp = document.getElementById('map-search');
    if (!inp) return;
    inp.addEventListener('input', APP.debounce(() => this.onInput(inp.value), 300));
    inp.addEventListener('focus', () => this.showDropdown());
    document.addEventListener('click', e => {
      if (!e.target.closest('.search-box-wrap')) this.hideDropdown();
    });
  },

  onInput(q) {
    if (!q.trim()) { this.hideDropdown(); SMAP.resetFilter(); return; }
    const results = SMAP.stations.filter(s =>
      [s.station_name, s.address, s.city, s.operator || '']
        .some(v => v.toLowerCase().includes(q.toLowerCase()))
    ).slice(0, 8);
    this.renderDropdown(results, q);
    this.applyAll();
  },

  renderDropdown(results, q = '') {
    const dd = document.getElementById('search-dropdown');
    if (!dd) return;
    if (!results.length) {
      dd.innerHTML = `<div style="padding:1rem;text-align:center;font-size:.85rem;color:#94a3b8">No stations found for "<b>${q}</b>"</div>`;
    } else {
      const col = SMAP.statusColors;
      dd.innerHTML = results.map(s => `
        <div class="search-item" onclick="SEARCH.selectResult(${s.station_id}, '${s.station_name.replace(/'/g,"\\'")}')">
          <div class="search-item-icon">⚡</div>
          <div>
            <div class="search-item-name">${s.station_name.replace(new RegExp(q,'gi'), m => `<mark style="background:#dbeafe;color:#2563eb;border-radius:3px">${m}</mark>`)}</div>
            <div class="search-item-addr">${s.city} · <span style="color:${col[s.status]}">${s.status}</span></div>
          </div>
        </div>`).join('');
    }
    this.showDropdown();
  },

  showDropdown() {
    const dd = document.getElementById('search-dropdown');
    if (dd) dd.classList.add('open');
  },

  hideDropdown() {
    const dd = document.getElementById('search-dropdown');
    if (dd) dd.classList.remove('open');
  },

  selectResult(id, name) {
    document.getElementById('map-search').value = name;
    this.hideDropdown();
    this.addToHistory(name);
    SMAP.focusStation(id);
  },

  addToHistory(q) {
    this.history = [q, ...this.history.filter(h => h !== q)].slice(0, this.maxHistory);
    localStorage.setItem('sq_history', JSON.stringify(this.history));
  },

  clearSearch() {
    const inp = document.getElementById('map-search');
    if (inp) inp.value = '';
    this.hideDropdown();
    this.applyAll();
  },

  setFilter(key, val) {
    this.activeFilters[key] = val;
    // Update chip UI
    document.querySelectorAll('.filter-chip').forEach(c => {
      const fkey = c.dataset.filter, fval = c.dataset.value;
      if (fkey === key) c.classList.toggle('active', fval === String(val) || (fval === '1' && val));
    });
    this.applyAll();
  },

  applyAll() {
    const q = document.getElementById('map-search')?.value || '';
    SMAP.filter(q,
      this.activeFilters.status,
      this.activeFilters.type,
      this.activeFilters.fast,
      this.activeFilters.nearby,
      this.activeFilters.favs ? this.favIds : []
    );
  }
};

// ── Favorites ─────────────────────────────────────────────
const FAVS = {
  ids: [],

  async toggle(stationId, btn) {
    const isFav = this.ids.includes(+stationId);
    const r = await APP.post(APP.base + '/api/favorites.php', { station_id: stationId });
    if (!r.success) { APP.toast(r.message, 'error'); return; }
    if (r.data.action === 'added') {
      this.ids.push(+stationId);
      if (btn) { btn.classList.add('active', 'pop'); btn.title = 'Remove Favorite'; }
      APP.toast('Added to favorites ❤️', 'success');
    } else {
      this.ids = this.ids.filter(i => i !== +stationId);
      if (btn) { btn.classList.remove('active'); btn.title = 'Add to Favorites'; }
      APP.toast('Removed from favorites', 'info');
    }
    setTimeout(() => btn?.classList.remove('pop'), 400);
    SEARCH.favIds = this.ids;
  },

  isFav(id) { return this.ids.includes(+id); }
};

// ── DOM Ready ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  // Auto-dismiss flash alerts
  document.querySelectorAll('.flash-bar').forEach(el =>
    setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 300); }, 4000)
  );

  // Sidebar toggle
  document.getElementById('sidebar-toggle')?.addEventListener('click', () => {
    document.querySelector('.admin-sidebar')?.classList.toggle('open');
  });

  // Password toggles
  document.querySelectorAll('[data-pw-toggle]').forEach(btn => {
    btn.addEventListener('click', () => {
      const inp = document.getElementById(btn.dataset.pwToggle);
      if (!inp) return;
      inp.type = inp.type === 'password' ? 'text' : 'password';
      btn.innerHTML = inp.type === 'password' ? '👁' : '🙈';
    });
  });

  // Tooltips
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el =>
    new bootstrap.Tooltip(el)
  );

  // Load notifications
  if (document.getElementById('notif-badge')) {
    APP.loadNotifs();
    setInterval(() => APP.loadNotifs(), 30000);
  }

  // Add animation class to page content
  document.querySelector('.page-wrap, .admin-wrap')?.classList.add('fade-in');
});
