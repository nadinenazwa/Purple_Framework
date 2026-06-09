@extends('layouts.master')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="page-header">
      <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
          <i class="mdi mdi-barcode-scan"></i>
        </span>
        Barcode &amp; QR Code Reader
      </h3>
      <nav aria-label="breadcrumb">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
          <li class="breadcrumb-item active">Barcode Scanner</li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<div class="row justify-content-center">

  {{-- ====== SCANNER CARD ====== --}}
  <div class="col-lg-6 col-md-8 col-12">
    <div class="card shadow-sm border-0" id="scanner-card">
      <div class="card-body p-4">

        {{-- Header --}}
        <div class="text-center mb-3">
          <div class="scanner-icon-wrap mb-2">
            <i class="mdi mdi-line-scan scanner-main-icon"></i>
          </div>
          <h5 class="fw-bold mb-1">Scan Barcode / QR Code</h5>
          <p class="text-muted small mb-0">Arahkan kamera ke barcode atau QR code barang</p>
        </div>

        {{-- Error alert --}}
        <div id="error-alert" class="alert alert-danger d-none" role="alert">
          <i class="mdi mdi-alert-circle me-2"></i>
          <span id="error-message"></span>
        </div>

        {{-- Camera viewfinder --}}
        <div class="scanner-wrapper mb-3" id="scanner-wrapper">
          <div id="qr-reader"></div>
          <div class="scanner-overlay" id="scanner-overlay">
            <div class="scanner-frame">
              <span class="corner tl"></span>
              <span class="corner tr"></span>
              <span class="corner bl"></span>
              <span class="corner br"></span>
              <div class="scan-line"></div>
            </div>
            <p class="scan-hint">Posisikan barcode di dalam kotak</p>
          </div>
        </div>

        {{-- Status badge --}}
        <div class="text-center mb-3">
          <span class="badge status-badge" id="status-badge">
            <i class="mdi mdi-camera me-1"></i>
            <span id="status-text">Memulai kamera…</span>
          </span>
        </div>

        {{-- Scan Again button (hidden initially) --}}
        <div class="text-center d-none" id="btn-scan-again-wrap">
          <button class="btn btn-gradient-primary btn-fw" id="btn-scan-again" onclick="startScanning()">
            <i class="mdi mdi-refresh me-2"></i> Scan Lagi
          </button>
        </div>

      </div>
    </div>
  </div>

  {{-- ====== RESULT CARD ====== --}}
  <div class="col-lg-6 col-md-8 col-12 mt-3 mt-lg-0 d-none" id="result-section">
    <div class="card border-0 shadow-sm result-card" id="result-card">
      <div class="card-body p-4">

        <div class="text-center mb-4">
          <div class="success-icon-wrap mb-2">
            <i class="mdi mdi-check-circle success-icon"></i>
          </div>
          <h5 class="fw-bold text-success mb-0">Barang Ditemukan!</h5>
          <p class="text-muted small">Hasil scan barcode</p>
        </div>

        <div class="result-details">
          <div class="result-row">
            <div class="result-label">
              <i class="mdi mdi-identifier me-2 text-primary"></i>ID Barang
            </div>
            <div class="result-value" id="res-id">—</div>
          </div>
          <hr class="result-divider">
          <div class="result-row">
            <div class="result-label">
              <i class="mdi mdi-package-variant me-2 text-primary"></i>Nama Barang
            </div>
            <div class="result-value" id="res-nama">—</div>
          </div>
          <hr class="result-divider">
          <div class="result-row">
            <div class="result-label">
              <i class="mdi mdi-currency-usd me-2 text-primary"></i>Harga
            </div>
            <div class="result-value text-success fw-bold" id="res-harga">—</div>
          </div>
        </div>

        {{-- Raw barcode value --}}
        <div class="mt-3 p-3 bg-light rounded">
          <small class="text-muted d-block mb-1">
            <i class="mdi mdi-barcode me-1"></i>Raw Scan Value
          </small>
          <code id="res-raw" class="text-dark">—</code>
        </div>

      </div>
    </div>
  </div>

</div>{{-- /row --}}


{{-- ======================================================
     STYLES
====================================================== --}}
<style>
/* ---- Scanner wrapper ---- */
.scanner-wrapper {
  position: relative;
  border-radius: 16px;
  overflow: hidden;
  background: #0d0d0d;
  min-height: 280px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* html5-qrcode puts a <video> inside #qr-reader, make it fill the wrapper */
#qr-reader {
  width: 100% !important;
  border: none !important;
  background: transparent !important;
}
#qr-reader video {
  width: 100% !important;
  border-radius: 0 !important;
  display: block;
}
/* Hide the default UI controls injected by html5-qrcode */
#qr-reader > img,
#qr-reader__camera_selection,
#qr-reader__camera_permission_button,
#qr-reader__status_span,
#qr-reader__header_message,
#qr-reader__filescan_input,
#qr-reader__dashboard_section_csr span,
#qr-reader__dashboard {
  display: none !important;
}

/* ---- Overlay (viewfinder frame) ---- */
.scanner-overlay {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  pointer-events: none;
}

.scanner-frame {
  position: relative;
  width: 220px;
  height: 220px;
}

/* Corners */
.corner {
  position: absolute;
  width: 28px;
  height: 28px;
  border-color: #7c4dff;
  border-style: solid;
}
.corner.tl { top: 0; left: 0;  border-width: 3px 0 0 3px; border-radius: 4px 0 0 0; }
.corner.tr { top: 0; right: 0; border-width: 3px 3px 0 0; border-radius: 0 4px 0 0; }
.corner.bl { bottom: 0; left: 0;  border-width: 0 0 3px 3px; border-radius: 0 0 0 4px; }
.corner.br { bottom: 0; right: 0; border-width: 0 3px 3px 0; border-radius: 0 0 4px 0; }

/* Scan line animation */
.scan-line {
  position: absolute;
  left: 4px; right: 4px;
  height: 2px;
  background: linear-gradient(90deg, transparent, #7c4dff, transparent);
  top: 0;
  animation: scanMove 2s ease-in-out infinite;
  box-shadow: 0 0 8px #7c4dff;
}
@keyframes scanMove {
  0%   { top: 0; }
  50%  { top: calc(100% - 2px); }
  100% { top: 0; }
}

.scan-hint {
  margin-top: 14px;
  color: rgba(255,255,255,0.7);
  font-size: 12px;
  text-align: center;
  text-shadow: 0 1px 3px rgba(0,0,0,0.8);
}

/* ---- Scanner icon header ---- */
.scanner-icon-wrap {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 60px; height: 60px;
  border-radius: 50%;
  background: linear-gradient(135deg, #7c4dff22, #7c4dff44);
}
.scanner-main-icon {
  font-size: 2rem;
  color: #7c4dff;
}

/* ---- Status badge ---- */
.status-badge {
  font-size: 13px;
  padding: 6px 16px;
  border-radius: 20px;
  background: linear-gradient(135deg, #7c4dff, #448aff);
  color: #fff;
  transition: background 0.4s;
}
.status-badge.success {
  background: linear-gradient(135deg, #00c853, #69f0ae) !important;
  color: #fff !important;
}
.status-badge.error {
  background: linear-gradient(135deg, #f44336, #ff5722) !important;
}

/* ---- Result card ---- */
.result-card {
  border-left: 4px solid #00c853 !important;
  animation: slideInRight 0.4s ease;
}
@keyframes slideInRight {
  from { opacity: 0; transform: translateX(30px); }
  to   { opacity: 1; transform: translateX(0); }
}

.success-icon-wrap {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 64px; height: 64px;
  border-radius: 50%;
  background: #e8f5e9;
  animation: popIn 0.4s ease;
}
@keyframes popIn {
  0%   { transform: scale(0); }
  70%  { transform: scale(1.15); }
  100% { transform: scale(1); }
}
.success-icon {
  font-size: 2.2rem;
  color: #00c853;
}

.result-row {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
  padding: 8px 0;
}
.result-label {
  font-size: 13px;
  color: #888;
  white-space: nowrap;
}
.result-value {
  font-size: 15px;
  font-weight: 600;
  color: #222;
  text-align: right;
  word-break: break-all;
}
.result-divider {
  margin: 2px 0;
  border-color: #f0f0f0;
}
</style>

@endsection


{{-- ======================================================
     SCRIPTS
====================================================== --}}
@push('js-page')
{{-- html5-qrcode CDN --}}
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
/* ============================================================
   GLOBAL STATE
============================================================ */
let html5QrCode = null;
let scanning    = false;
let debounceTimer = null;

/* ============================================================
   WEB AUDIO API  –  beep pendek 880 Hz, 120 ms
============================================================ */
function playBeep() {
  try {
    const ctx  = new (window.AudioContext || window.webkitAudioContext)();
    const osc  = ctx.createOscillator();
    const gain = ctx.createGain();

    osc.connect(gain);
    gain.connect(ctx.destination);

    osc.type      = 'sine';
    osc.frequency.setValueAtTime(880, ctx.currentTime);
    gain.gain.setValueAtTime(0.6, ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.12);

    osc.start(ctx.currentTime);
    osc.stop(ctx.currentTime + 0.12);
  } catch (e) {
    // Web Audio API tidak tersedia — diam saja
  }
}

/* ============================================================
   FORMAT CURRENCY
============================================================ */
function formatRupiah(angka) {
  return 'Rp ' + Number(angka).toLocaleString('id-ID');
}

/* ============================================================
   UI HELPERS
============================================================ */
function setStatus(text, type = 'default') {
  const badge = document.getElementById('status-badge');
  badge.querySelector('#status-text').textContent = text;
  badge.className = 'badge status-badge';
  if (type === 'success') badge.classList.add('success');
  if (type === 'error')   badge.classList.add('error');
}

function showError(msg) {
  const el = document.getElementById('error-alert');
  document.getElementById('error-message').textContent = msg;
  el.classList.remove('d-none');
}

function hideError() {
  document.getElementById('error-alert').classList.add('d-none');
}

function showScanAgainButton() {
  document.getElementById('btn-scan-again-wrap').classList.remove('d-none');
}

function hideScanAgainButton() {
  document.getElementById('btn-scan-again-wrap').classList.add('d-none');
}

function showResultSection() {
  document.getElementById('result-section').classList.remove('d-none');
}

function hideResultSection() {
  document.getElementById('result-section').classList.add('d-none');
}

/* ============================================================
   ON SCAN SUCCESS
============================================================ */
function onScanSuccess(decodedText, decodedResult) {
  if (!scanning) return;

  // Debounce: ignore duplicate reads within 1 s
  if (debounceTimer) return;
  debounceTimer = setTimeout(() => { debounceTimer = null; }, 1000);

  scanning = false;

  // Putar beep
  playBeep();

  // Tampilkan nilai raw di UI sementara fetch berjalan
  document.getElementById('res-raw').textContent  = decodedText;
  document.getElementById('res-id').textContent   = decodedText;
  document.getElementById('res-nama').textContent = '…';
  document.getElementById('res-harga').textContent = '…';
  showResultSection();

  setStatus('Barcode terbaca — mencari data…', 'success');

  // Hentikan kamera
  stopScanning().then(() => {
    // Update overlay: sembunyikan scan-line
    document.getElementById('scanner-overlay').style.display = 'none';
    showScanAgainButton();
  });

  // Fetch data barang dari server
  fetchBarang(decodedText);
}

/* ============================================================
   FETCH BARANG VIA AJAX
============================================================ */
function fetchBarang(id) {
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  fetch(`/api/barcode/barang/${encodeURIComponent(id)}`, {
    headers: {
      'X-CSRF-TOKEN': csrfToken,
      'Accept': 'application/json',
    }
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      hideError();
      document.getElementById('res-id').textContent    = data.id_barang;
      document.getElementById('res-nama').textContent  = data.nama;
      document.getElementById('res-harga').textContent = formatRupiah(data.harga);
      setStatus('✓ Barang ditemukan', 'success');
    } else {
      showError(data.message || 'Barang tidak ditemukan.');
      document.getElementById('res-nama').textContent  = '—';
      document.getElementById('res-harga').textContent = '—';
      setStatus('Barang tidak ditemukan', 'error');
    }
  })
  .catch(err => {
    showError('Terjadi kesalahan jaringan. Periksa koneksi internet Anda.');
    setStatus('Error jaringan', 'error');
    console.error('Fetch error:', err);
  });
}

/* ============================================================
   ON SCAN FAILURE (diabaikan — terlalu banyak dipanggil)
============================================================ */
function onScanFailure(error) {
  // Diam saja — kamera terus mencari
}

/* ============================================================
   STOP SCANNING
============================================================ */
async function stopScanning() {
  if (html5QrCode) {
    try {
      await html5QrCode.stop();
    } catch(e) { /* sudah berhenti */ }
  }
}

/* ============================================================
   START SCANNING
============================================================ */
function startScanning() {
  hideError();
  hideResultSection();
  hideScanAgainButton();

  // Reset result fields
  document.getElementById('res-id').textContent    = '—';
  document.getElementById('res-nama').textContent  = '—';
  document.getElementById('res-harga').textContent = '—';
  document.getElementById('res-raw').textContent   = '—';

  // Tampilkan kembali overlay
  document.getElementById('scanner-overlay').style.display = '';

  setStatus('Memulai kamera…');

  // Buat / reset instance
  if (html5QrCode) {
    html5QrCode.clear();
  }
  html5QrCode = new Html5Qrcode('qr-reader');

  const config = {
    fps: 15,
    qrbox: { width: 220, height: 220 },
    aspectRatio: 1.0,
    supportedScanTypes: [
      Html5QrcodeScanType.SCAN_TYPE_CAMERA,
    ],
    formatsToSupport: [
      Html5QrcodeSupportedFormats.QR_CODE,
      Html5QrcodeSupportedFormats.CODE_128,
      Html5QrcodeSupportedFormats.CODE_39,
      Html5QrcodeSupportedFormats.EAN_13,
      Html5QrcodeSupportedFormats.EAN_8,
      Html5QrcodeSupportedFormats.UPC_A,
      Html5QrcodeSupportedFormats.UPC_E,
      Html5QrcodeSupportedFormats.DATA_MATRIX,
    ],
  };

  Html5Qrcode.getCameras()
    .then(devices => {
      if (!devices || devices.length === 0) {
        throw new Error('Tidak ada kamera yang terdeteksi.');
      }

      // Pilih kamera belakang jika ada
      let cameraId = devices[0].id;
      const back = devices.find(d =>
        d.label.toLowerCase().includes('back') ||
        d.label.toLowerCase().includes('belakang') ||
        d.label.toLowerCase().includes('environment')
      );
      if (back) cameraId = back.id;

      return html5QrCode.start(
        cameraId,
        config,
        onScanSuccess,
        onScanFailure
      );
    })
    .then(() => {
      scanning = true;
      setStatus('Sedang scanning…');
    })
    .catch(err => {
      let msg = 'Kamera tidak dapat diakses.';
      if (typeof err === 'string') {
        msg = err;
      } else if (err && err.message) {
        msg = err.message;
      }

      // Pesan ramah
      if (msg.includes('Permission') || msg.includes('permission') || msg.includes('NotAllowed')) {
        msg = 'Izin kamera ditolak. Silakan izinkan akses kamera di pengaturan browser.';
      } else if (msg.includes('NotFound') || msg.includes('not found')) {
        msg = 'Kamera tidak ditemukan pada perangkat ini.';
      } else if (msg.includes('NotReadable') || msg.includes('Could not start')) {
        msg = 'Kamera sedang digunakan oleh aplikasi lain.';
      }

      showError(msg);
      setStatus('Kamera gagal', 'error');
      showScanAgainButton();
      console.error('Camera error:', err);
    });
}

/* ============================================================
   INIT
============================================================ */
document.addEventListener('DOMContentLoaded', function () {
  startScanning();
});
</script>
@endpush
