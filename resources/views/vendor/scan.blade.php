@extends('layouts.master')

@section('content')

{{-- ============================================================
     STYLES
============================================================ --}}
<style>
/* ======= Layout ======= */
.vendor-scan-wrap { max-width: 1100px; margin: 0 auto; }

/* ======= Page header ======= */
.vscan-header {
  display: flex; align-items: center; gap: 14px;
  margin-bottom: 28px; flex-wrap: wrap;
}
.vscan-icon {
  width: 52px; height: 52px; border-radius: 14px;
  background: linear-gradient(135deg, #7c4dff, #448aff);
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 1.6rem; flex-shrink: 0;
}

/* ======= Scanner card ======= */
.scanner-card {
  border: none; border-radius: 18px;
  box-shadow: 0 6px 30px rgba(0,0,0,.09);
  overflow: hidden;
}
.scanner-card-header {
  background: linear-gradient(135deg, #7c4dff, #448aff);
  color: #fff; padding: 16px 22px; font-weight: 700; font-size: 15px;
}

/* ======= Camera wrapper ======= */
.cam-wrapper {
  position: relative;
  background: #0a0a14;
  min-height: 300px;
  display: flex; align-items: center; justify-content: center;
  overflow: hidden;
}
#qr-reader { width: 100% !important; background: transparent !important; border: none !important; }
#qr-reader video { width: 100% !important; display: block; }
/* hide html5-qrcode built-in UI */
#qr-reader > img, #qr-reader__dashboard,
#qr-reader__camera_selection, #qr-reader__camera_permission_button,
#qr-reader__status_span, #qr-reader__header_message,
#qr-reader__filescan_input, #qr-reader__dashboard_section_csr span { display: none !important; }

/* Viewfinder overlay */
.scan-overlay {
  position: absolute; inset: 0;
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  pointer-events: none;
}
.scan-frame { position: relative; width: 230px; height: 230px; }
.corner {
  position: absolute; width: 30px; height: 30px;
  border-color: #7c4dff; border-style: solid;
}
.corner.tl { top:0; left:0;   border-width: 3px 0 0 3px; border-radius: 4px 0 0 0; }
.corner.tr { top:0; right:0;  border-width: 3px 3px 0 0; border-radius: 0 4px 0 0; }
.corner.bl { bottom:0; left:0;  border-width: 0 0 3px 3px; border-radius: 0 0 0 4px; }
.corner.br { bottom:0; right:0; border-width: 0 3px 3px 0; border-radius: 0 0 4px 0; }
.scan-line {
  position: absolute; left: 4px; right: 4px; height: 2px;
  background: linear-gradient(90deg, transparent, #7c4dff, transparent);
  top: 0; animation: scanMove 2s ease-in-out infinite;
  box-shadow: 0 0 8px #7c4dff;
}
@keyframes scanMove { 0%{top:0} 50%{top:calc(100% - 2px)} 100%{top:0} }
.scan-hint {
  margin-top: 14px; color: rgba(255,255,255,.7);
  font-size: 12px; text-align: center;
  text-shadow: 0 1px 3px rgba(0,0,0,.8);
}

/* ======= Status badge ======= */
.status-badge {
  font-size: 13px; padding: 6px 18px; border-radius: 20px;
  background: linear-gradient(135deg, #7c4dff, #448aff);
  color: #fff; display: inline-flex; align-items: center; gap: 6px;
  transition: background .4s;
}
.status-badge.success { background: linear-gradient(135deg, #00c853, #69f0ae) !important; }
.status-badge.warning { background: linear-gradient(135deg, #ff9100, #ffca28) !important; color: #333 !important; }
.status-badge.error   { background: linear-gradient(135deg, #f44336, #ff5722) !important; }

/* ======= Alert ======= */
#error-alert { border-radius: 10px; font-size: 14px; }

/* ======= Scan Again button ======= */
.btn-scan-again {
  background: linear-gradient(135deg, #7c4dff, #448aff);
  border: none; color: #fff; font-weight: 600;
  border-radius: 10px; padding: 10px 28px;
  display: inline-flex; align-items: center; gap: 8px;
  transition: opacity .2s; cursor: pointer;
}
.btn-scan-again:hover { opacity: .85; }

/* ======= Result card ======= */
.result-card {
  border: none; border-radius: 18px;
  box-shadow: 0 6px 30px rgba(0,0,0,.09);
  animation: slideUp .4s ease;
  overflow: hidden;
}
@keyframes slideUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
.result-header {
  background: linear-gradient(135deg, #00c853, #69f0ae);
  color: #fff; padding: 16px 22px;
  display: flex; align-items: center; gap: 10px;
}
.result-header.warning-bg { background: linear-gradient(135deg, #ff9100, #ffca28); color: #333; }

/* Info grid */
.info-grid {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 12px; padding: 18px 20px; border-bottom: 1px solid #f0f0f0;
}
@media (max-width:500px) { .info-grid { grid-template-columns: 1fr; } }
.info-item label { font-size: 11px; color: #aaa; display: block; margin-bottom: 2px; }
.info-item span  { font-size: 14px; font-weight: 600; color: #333; word-break: break-all; }
.info-item.full  { grid-column: 1 / -1; }

/* Payment status pill */
.pay-pill {
  display: inline-flex; align-items: center; gap: 5px;
  font-size: 12px; font-weight: 700; border-radius: 20px;
  padding: 3px 12px;
}
.pay-pill.lunas   { background: #e8f5e9; color: #2e7d32; }
.pay-pill.pending { background: #fff8e1; color: #f57f17; }
.pay-pill.belum   { background: #fce4ec; color: #c62828; }

/* Menu table */
.menu-table-wrap { padding: 0 0 6px; }
.menu-table { width: 100%; border-collapse: collapse; }
.menu-table thead th {
  background: #f8f5ff; padding: 10px 16px;
  font-size: 12px; color: #7c4dff; font-weight: 700;
  text-transform: uppercase; letter-spacing: .4px;
  border-bottom: 2px solid #ede7ff;
}
.menu-table tbody td {
  padding: 12px 16px; font-size: 13.5px; color: #333;
  border-bottom: 1px solid #f5f5f5;
}
.menu-table tbody tr:last-child td { border-bottom: none; }
.menu-table tbody tr:hover td { background: #fafafa; }
.catatan-badge {
  display: inline-block; background: #fff8e1; color: #795548;
  border-radius: 6px; padding: 2px 8px; font-size: 11px; margin-top: 3px;
}
.total-row td { font-weight: 700; color: #7c4dff; background: #f5f3ff; font-size: 14px; }

/* Vendor badge */
.vendor-pill {
  display: inline-flex; align-items: center; gap: 4px;
  background: #ede7ff; color: #5c35cc;
  border-radius: 20px; padding: 3px 10px; font-size: 11px; font-weight: 600;
}

/* Other-vendor notice */
.other-items-notice {
  margin: 12px 16px;
  padding: 10px 14px;
  background: #fff8e1;
  border-radius: 8px;
  font-size: 12.5px;
  color: #795548;
}
</style>

{{-- ============================================================
     PAGE CONTENT
============================================================ --}}
<div class="vendor-scan-wrap">

  {{-- Header --}}
  <div class="vscan-header">
    <span class="vscan-icon"><i class="mdi mdi-qrcode-scan"></i></span>
    <div>
      <h4 class="mb-0 fw-bold">Scan QR Code Pesanan</h4>
      <small class="text-muted">Arahkan kamera ke QR Code milik customer untuk verifikasi pesanan</small>
    </div>
    <div class="ms-auto">
      <span class="vendor-pill">
        <i class="mdi mdi-store me-1"></i>{{ Auth::user()->name }}
      </span>
    </div>
  </div>

  <div class="row g-4">

    {{-- ===== LEFT: Scanner ===== --}}
    <div class="col-lg-5">
      <div class="scanner-card">
        <div class="scanner-card-header">
          <i class="mdi mdi-camera me-2"></i>Kamera Scanner
        </div>
        <div class="card-body p-3">

          {{-- Error alert --}}
          <div id="error-alert" class="alert alert-danger d-none mb-3">
            <i class="mdi mdi-alert-circle me-2"></i>
            <span id="error-msg"></span>
          </div>

          {{-- Camera --}}
          <div class="cam-wrapper rounded-3 mb-3" id="cam-wrapper">
            <div id="qr-reader"></div>
            <div class="scan-overlay" id="scan-overlay">
              <div class="scan-frame">
                <span class="corner tl"></span><span class="corner tr"></span>
                <span class="corner bl"></span><span class="corner br"></span>
                <div class="scan-line"></div>
              </div>
              <p class="scan-hint">Arahkan QR Code customer di sini</p>
            </div>
          </div>

          {{-- Status badge --}}
          <div class="text-center mb-3">
            <span class="status-badge" id="status-badge">
              <i class="mdi mdi-camera-outline"></i>
              <span id="status-text">Memulai kamera…</span>
            </span>
          </div>

          {{-- Scan Again --}}
          <div class="text-center d-none" id="scan-again-wrap">
            <button class="btn-scan-again" onclick="startScanning()">
              <i class="mdi mdi-refresh"></i> Scan Lagi
            </button>
          </div>

        </div>
      </div>
    </div>

    {{-- ===== RIGHT: Result ===== --}}
    <div class="col-lg-7">

      {{-- Idle placeholder --}}
      <div id="result-placeholder" class="text-center py-5">
        <i class="mdi mdi-qrcode" style="font-size:5rem;color:#d0c8ff;display:block;margin-bottom:12px"></i>
        <p class="text-muted">Hasil scan akan muncul di sini</p>
      </div>

      {{-- Spinner --}}
      <div id="result-loading" class="text-center py-5 d-none">
        <div class="spinner-border text-primary" role="status" style="width:2.5rem;height:2.5rem"></div>
        <p class="text-muted mt-3">Mencari data pesanan…</p>
      </div>

      {{-- Result content --}}
      <div id="result-content" class="d-none">

        <div class="result-card" id="result-card">

          {{-- Result header --}}
          <div class="result-header" id="result-header">
            <i class="mdi mdi-check-circle" style="font-size:1.5rem"></i>
            <div>
              <div class="fw-bold" style="font-size:15px" id="res-header-title">Pesanan Ditemukan</div>
              <div style="font-size:12px;opacity:.85" id="res-order-id-short">—</div>
            </div>
            <div class="ms-auto" id="res-pay-pill"></div>
          </div>

          {{-- Info grid --}}
          <div class="info-grid">
            <div class="info-item">
              <label><i class="mdi mdi-receipt me-1"></i>ID Pesanan</label>
              <span id="res-order-id">—</span>
            </div>
            <div class="info-item">
              <label><i class="mdi mdi-account-outline me-1"></i>Nama Pemesan</label>
              <span id="res-nama">—</span>
            </div>
            <div class="info-item">
              <label><i class="mdi mdi-clock-outline me-1"></i>Waktu Pesan</label>
              <span id="res-time">—</span>
            </div>
            <div class="info-item">
              <label><i class="mdi mdi-cash me-1"></i>Total Pesanan</label>
              <span id="res-total" class="text-success fw-bold">—</span>
            </div>
          </div>

          {{-- Menu items table --}}
          <div class="menu-table-wrap">
            <div style="padding:12px 16px 6px;font-size:12px;color:#7c4dff;font-weight:700;text-transform:uppercase;letter-spacing:.4px">
              <i class="mdi mdi-silverware-fork-knife me-1"></i>Daftar Pesanan Anda
            </div>
            <table class="menu-table">
              <thead>
                <tr>
                  <th>Menu</th>
                  <th class="text-center">Jml</th>
                  <th class="text-end">Subtotal</th>
                </tr>
              </thead>
              <tbody id="res-items-tbody"></tbody>
            </table>
          </div>

          {{-- Other-vendor notice --}}
          <div id="other-items-notice" class="other-items-notice d-none">
            <i class="mdi mdi-information-outline me-1"></i>
            Pesanan ini juga berisi item dari vendor lain yang tidak ditampilkan di sini.
          </div>

        </div><!-- /.result-card -->
      </div><!-- /#result-content -->

    </div>
  </div><!-- /.row -->
</div><!-- /.vendor-scan-wrap -->

@endsection


{{-- ============================================================
     SCRIPTS
============================================================ --}}
@push('js-page')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
/* ============================================================
   STATE
============================================================ */
let html5QrCode   = null;
let scanning      = false;
let debounceTimer = null;

/* ============================================================
   BEEP  (Web Audio API)
============================================================ */
function playBeep() {
  try {
    const ctx  = new (window.AudioContext || window.webkitAudioContext)();
    const osc  = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain); gain.connect(ctx.destination);
    osc.type = 'sine';
    osc.frequency.setValueAtTime(940, ctx.currentTime);
    gain.gain.setValueAtTime(0.5, ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.14);
    osc.start(ctx.currentTime);
    osc.stop(ctx.currentTime + 0.14);
  } catch(e) {}
}

/* ============================================================
   UI HELPERS
============================================================ */
function setStatus(text, type = 'default') {
  const badge = document.getElementById('status-badge');
  document.getElementById('status-text').textContent = text;
  badge.className = 'status-badge';
  if (type === 'success') badge.classList.add('success');
  if (type === 'warning') badge.classList.add('warning');
  if (type === 'error')   badge.classList.add('error');
}

function showError(msg) {
  const el = document.getElementById('error-alert');
  document.getElementById('error-msg').textContent = msg;
  el.classList.remove('d-none');
}
function hideError() { document.getElementById('error-alert').classList.add('d-none'); }

function showScanAgain()  { document.getElementById('scan-again-wrap').classList.remove('d-none'); }
function hideScanAgain()  { document.getElementById('scan-again-wrap').classList.add('d-none'); }

function showLoading() {
  document.getElementById('result-placeholder').classList.add('d-none');
  document.getElementById('result-loading').classList.remove('d-none');
  document.getElementById('result-content').classList.add('d-none');
}
function showPlaceholder() {
  document.getElementById('result-placeholder').classList.remove('d-none');
  document.getElementById('result-loading').classList.add('d-none');
  document.getElementById('result-content').classList.add('d-none');
}
function showResult() {
  document.getElementById('result-placeholder').classList.add('d-none');
  document.getElementById('result-loading').classList.add('d-none');
  document.getElementById('result-content').classList.remove('d-none');
}

/* ============================================================
   FORMAT HELPERS
============================================================ */
function formatRp(v) {
  return 'Rp ' + Number(v).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}
function formatDate(raw) {
  if (!raw) return '—';
  try {
    return new Date(raw).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
  } catch(e) { return String(raw); }
}

/* ============================================================
   RENDER RESULT
============================================================ */
function renderResult(data) {
  // Header
  const isLunas   = /lunas|paid|settlement|capture/i.test(String(data.status_bayar))
                  || data.status_bayar == 1
                  || data.status_bayar === true;
  const isPending = /pending/i.test(String(data.status_bayar));

  const header = document.getElementById('result-header');
  header.className = 'result-header' + (isLunas ? '' : ' warning-bg');

  document.getElementById('res-header-title').textContent = isLunas ? 'Pesanan Terverifikasi ✓' : 'Pesanan Belum Lunas';
  document.getElementById('res-order-id-short').textContent = data.order_id;

  // Pay pill
  let pillClass = 'belum', pillIcon = 'mdi-close-circle', pillText = data.status_bayar || 'Belum';
  if (isLunas)   { pillClass = 'lunas';   pillIcon = 'mdi-check-circle'; pillText = 'Lunas'; }
  if (isPending) { pillClass = 'pending'; pillIcon = 'mdi-clock-outline'; pillText = 'Pending'; }
  document.getElementById('res-pay-pill').innerHTML =
    `<span class="pay-pill ${pillClass}"><i class="mdi ${pillIcon}"></i>${pillText}</span>`;

  // Info fields
  document.getElementById('res-order-id').textContent = data.order_id;
  document.getElementById('res-nama').textContent     = data.nama_pemesan || 'Guest';
  document.getElementById('res-time').textContent     = formatDate(data.created_at);
  document.getElementById('res-total').textContent    = formatRp(data.total || 0);

  // Items table
  const tbody = document.getElementById('res-items-tbody');
  tbody.innerHTML = '';

  const items = data.items || [];
  if (items.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="3" class="text-center text-muted py-4">
          <i class="mdi mdi-food-off me-1"></i>
          Tidak ada menu dari vendor Anda dalam pesanan ini
        </td>
      </tr>`;
  } else {
    let grandTotal = 0;
    items.forEach(it => {
      const sub = Number(it.subtotal) || (Number(it.harga || 0) * Number(it.jumlah || 1));
      grandTotal += sub;
      const catatanHtml = it.catatan
        ? `<br><span class="catatan-badge"><i class="mdi mdi-note-text-outline me-1"></i>${it.catatan}</span>`
        : '';
      tbody.innerHTML += `
        <tr>
          <td>
            <strong>${it.nama}</strong>${catatanHtml}
          </td>
          <td class="text-center">${it.jumlah}×</td>
          <td class="text-end fw-semibold">${formatRp(sub)}</td>
        </tr>`;
    });
    // Total row
    tbody.innerHTML += `
      <tr class="total-row">
        <td colspan="2"><i class="mdi mdi-sigma me-1"></i>Total Pesanan Anda</td>
        <td class="text-end">${formatRp(grandTotal)}</td>
      </tr>`;
  }

  // Other-vendor notice
  const allItems = data.all_items || [];
  const otherCount = allItems.length - items.length;
  const noticeEl = document.getElementById('other-items-notice');
  if (otherCount > 0) {
    noticeEl.innerHTML = `<i class="mdi mdi-information-outline me-1"></i>
      Pesanan ini juga berisi <strong>${otherCount} item</strong> dari vendor lain.`;
    noticeEl.classList.remove('d-none');
  } else {
    noticeEl.classList.add('d-none');
  }

  showResult();
}

/* ============================================================
   FETCH ORDER FROM API
============================================================ */
function fetchOrder(orderId) {
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  fetch('/api/vendor/scan/' + encodeURIComponent(orderId), {
    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
  })
  .then(r => r.json())
  .then(data => {
    if (!data.success) {
      hideError();
      showError(data.message || 'Pesanan tidak ditemukan.');
      setStatus('Pesanan tidak ditemukan', 'error');
      showPlaceholder();
      showScanAgain();
      return;
    }
    hideError();
    renderResult(data);
    const isLunas = /lunas|paid|settlement|capture/i.test(String(data.status_bayar))
                 || data.status_bayar == 1
                 || data.status_bayar === true;
    setStatus(isLunas ? '✓ Pesanan terverifikasi' : '⚠ Belum lunas', isLunas ? 'success' : 'warning');
  })
  .catch(err => {
    showError('Terjadi kesalahan jaringan. Periksa koneksi Anda.');
    setStatus('Gagal terhubung ke server', 'error');
    showPlaceholder();
    showScanAgain();
    console.error(err);
  });
}

/* ============================================================
   QR SCAN SUCCESS
============================================================ */
function onScanSuccess(decodedText) {
  if (!scanning) return;

  // Debounce: abaikan bacaan duplikat dalam 1 detik
  if (debounceTimer) return;
  debounceTimer = setTimeout(() => { debounceTimer = null; }, 1000);

  scanning = false;
  playBeep();

  setStatus('QR terbaca — mencari pesanan…', 'success');
  document.getElementById('scan-overlay').style.display = 'none';
  showScanAgain();
  showLoading();

  // Stop camera
  stopScanning().then(() => fetchOrder(decodedText));
}

function onScanFailure() { /* diam */ }

/* ============================================================
   CAMERA CONTROL
============================================================ */
async function stopScanning() {
  if (html5QrCode) {
    try { await html5QrCode.stop(); } catch(e) {}
  }
}

function startScanning() {
  hideError();
  showPlaceholder();
  hideScanAgain();
  document.getElementById('scan-overlay').style.display = '';
  setStatus('Memulai kamera…');

  if (html5QrCode) { try { html5QrCode.clear(); } catch(e){} }
  html5QrCode = new Html5Qrcode('qr-reader');

  const config = {
    fps: 15,
    qrbox: { width: 230, height: 230 },
    aspectRatio: 1.0,
    supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA],
    formatsToSupport: [
      Html5QrcodeSupportedFormats.QR_CODE,
      Html5QrcodeSupportedFormats.DATA_MATRIX,
    ],
  };

  Html5Qrcode.getCameras()
    .then(devices => {
      if (!devices || !devices.length) throw new Error('Tidak ada kamera yang terdeteksi.');

      // Prefer kamera belakang
      let camId = devices[0].id;
      const back = devices.find(d =>
        /back|belakang|environment/i.test(d.label)
      );
      if (back) camId = back.id;

      return html5QrCode.start(camId, config, onScanSuccess, onScanFailure);
    })
    .then(() => {
      scanning = true;
      setStatus('Sedang scanning…');
    })
    .catch(err => {
      let msg = typeof err === 'string' ? err : (err?.message || 'Kamera tidak dapat diakses.');
      if (/permission|notallowed/i.test(msg)) msg = 'Izin kamera ditolak. Izinkan akses kamera di pengaturan browser.';
      else if (/notfound|not found/i.test(msg)) msg = 'Kamera tidak ditemukan pada perangkat ini.';
      else if (/notreadable|could not start/i.test(msg)) msg = 'Kamera sedang digunakan aplikasi lain.';
      showError(msg);
      setStatus('Kamera gagal dimulai', 'error');
      showScanAgain();
    });
}

/* ============================================================
   INIT
============================================================ */
document.addEventListener('DOMContentLoaded', startScanning);
</script>
@endpush
