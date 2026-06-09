@extends('layouts.master')
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
.toko-card{border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.08);}
.toko-card .card-header{background:linear-gradient(135deg,#7c4dff,#448aff);color:#fff;border-radius:16px 16px 0 0!important;font-weight:700;padding:14px 20px;}
.status-badge{font-size:12px;padding:4px 14px;border-radius:20px;font-weight:700;}
.badge-diterima{background:#e8f5e9;color:#2e7d32;}
.badge-ditolak{background:#fce4ec;color:#c62828;}
.gps-loading{display:none;color:#7c4dff;font-size:13px;}
.result-box{border-radius:12px;padding:16px;margin-top:12px;display:none;}
.result-diterima{background:#e8f5e9;border-left:4px solid #00c853;}
.result-ditolak{background:#fce4ec;border-left:4px solid #f44336;}
.scan-wrapper{position:relative;background:#0a0a14;border-radius:12px;overflow:hidden;min-height:240px;}
#qr-reader2{width:100%!important;background:transparent!important;border:none!important;}
#qr-reader2 video{width:100%!important;display:block;}
#qr-reader2 img,#qr-reader2__dashboard,#qr-reader2__camera_selection,
#qr-reader2__camera_permission_button,#qr-reader2__status_span,
#qr-reader2__header_message,#qr-reader2__filescan_input{display:none!important;}
.scan-corners{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none;}
.scan-frame{position:relative;width:200px;height:200px;}
.c{position:absolute;width:24px;height:24px;border-color:#7c4dff;border-style:solid;}
.c.tl{top:0;left:0;border-width:3px 0 0 3px;border-radius:4px 0 0 0}
.c.tr{top:0;right:0;border-width:3px 3px 0 0;border-radius:0 4px 0 0}
.c.bl{bottom:0;left:0;border-width:0 0 3px 3px;border-radius:0 0 0 4px}
.c.br{bottom:0;right:0;border-width:0 3px 3px 0;border-radius:0 0 4px 0}
.scan-line{position:absolute;left:3px;right:3px;height:2px;background:linear-gradient(90deg,transparent,#7c4dff,transparent);animation:sl 2s ease-in-out infinite;box-shadow:0 0 6px #7c4dff;}
@keyframes sl{0%{top:0}50%{top:calc(100% - 2px)}100%{top:0}}
</style>

{{-- Flash --}}
@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if($errors->any())
  <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div class="row">
  <div class="col-12">
    <div class="page-header">
      <h3 class="page-title"><span class="page-title-icon bg-gradient-primary text-white me-2"><i class="mdi mdi-store-marker"></i></span>Kunjungan Toko</h3>
    </div>
  </div>
</div>

<div class="row g-4">

{{-- ===== TABEL TOKO ===== --}}
<div class="col-12">
<div class="card toko-card">
  <div class="card-header"><i class="mdi mdi-format-list-bulleted me-2"></i>Daftar Lokasi Toko</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm table-hover mb-0">
        <thead class="table-light">
          <tr><th>Barcode</th><th>Nama Toko</th><th>Latitude</th><th>Longitude</th><th>Accuracy (m)</th><th>Cetak</th></tr>
        </thead>
        <tbody>
          @forelse($tokos as $t)
          <tr>
            <td><code>{{ $t->barcode }}</code></td>
            <td>{{ $t->nama_toko }}</td>
            <td>{{ $t->latitude }}</td>
            <td>{{ $t->longitude }}</td>
            <td>{{ $t->accuracy }}</td>
            <td>
              <button class="btn btn-sm btn-outline-primary btn-cetak"
                data-barcode="{{ $t->barcode }}" data-nama="{{ $t->nama_toko }}">
                <i class="mdi mdi-printer"></i>
              </button>
            </td>
          </tr>
          @empty
          <tr><td colspan="6" class="text-center text-muted py-3">Belum ada data toko</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>

{{-- ===== FORM INPUT TOKO ===== --}}
<div class="col-lg-5">
<div class="card toko-card h-100">
  <div class="card-header"><i class="mdi mdi-map-marker-plus me-2"></i>Input Titik Awal Toko</div>
  <div class="card-body">
    <form action="{{ route('toko.store') }}" method="POST" id="formToko">
      @csrf
      <div class="mb-3">
        <label class="form-label fw-semibold">Barcode <small class="text-muted">(maks 8 karakter)</small></label>
        <input type="text" name="barcode" maxlength="8" class="form-control @error('barcode') is-invalid @enderror"
               value="{{ old('barcode') }}" placeholder="Contoh: TK000001" required>
        @error('barcode')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Nama Toko</label>
        <input type="text" name="nama_toko" maxlength="50" class="form-control @error('nama_toko') is-invalid @enderror"
               value="{{ old('nama_toko') }}" placeholder="Nama toko" required>
        @error('nama_toko')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="row g-2 mb-3">
        <div class="col">
          <label class="form-label fw-semibold">Latitude</label>
          <input type="text" name="latitude" id="toko_lat" class="form-control @error('latitude') is-invalid @enderror"
                 value="{{ old('latitude') }}" readonly placeholder="Klik Geoloc">
          @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col">
          <label class="form-label fw-semibold">Longitude</label>
          <input type="text" name="longitude" id="toko_lng" class="form-control" readonly placeholder="Klik Geoloc">
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Accuracy (meter)</label>
        <input type="text" name="accuracy" id="toko_acc" class="form-control" readonly placeholder="Klik Geoloc">
      </div>
      <div class="gps-loading" id="gps1load"><i class="mdi mdi-loading mdi-spin me-1"></i>Mengambil lokasi GPS (maks 20 detik)…</div>
      <div class="d-flex gap-2 mt-2">
        <button type="button" class="btn btn-outline-primary" id="btnGeoToko">
          <i class="mdi mdi-crosshairs-gps me-1"></i>Geoloc
        </button>
        <button type="submit" class="btn btn-primary">
          <i class="mdi mdi-content-save me-1"></i>Simpan Toko
        </button>
      </div>
    </form>
  </div>
</div>
</div>

{{-- ===== KUNJUNGAN SALES ===== --}}
<div class="col-lg-7">
<div class="card toko-card h-100">
  <div class="card-header"><i class="mdi mdi-account-location me-2"></i>Titik Kunjungan Sales</div>
  <div class="card-body">

    {{-- Scanner --}}
    <p class="fw-semibold mb-2"><i class="mdi mdi-qrcode-scan me-1 text-primary"></i>Scan Barcode Toko</p>
    <div class="scan-wrapper mb-2" id="scanWrap">
      <div id="qr-reader2"></div>
      <div class="scan-corners"><div class="scan-frame">
        <span class="c tl"></span><span class="c tr"></span>
        <span class="c bl"></span><span class="c br"></span>
        <div class="scan-line"></div>
      </div></div>
    </div>
    <div class="d-flex gap-2 mb-3">
      <span class="badge bg-secondary" id="scanStatus">Sedang scanning…</span>
      <button class="btn btn-sm btn-outline-secondary d-none" id="btnScanLagi" onclick="restartScan()">
        <i class="mdi mdi-refresh me-1"></i>Scan Lagi
      </button>
    </div>

    {{-- Info toko hasil scan --}}
    <div id="tokoInfo" class="p-3 bg-light rounded mb-3" style="display:none">
      <p class="mb-1 fw-bold text-primary" id="tokoNama">—</p>
      <small class="text-muted">
        Barcode: <code id="tokoBarcode">—</code> &nbsp;|&nbsp;
        Lat: <span id="tokoLat">—</span> &nbsp;|&nbsp;
        Lng: <span id="tokoLng">—</span> &nbsp;|&nbsp;
        Acc: <span id="tokoAcc">—</span> m
      </small>
    </div>

    {{-- Tombol Ambil Lokasi --}}
    <div class="gps-loading" id="gps2load"><i class="mdi mdi-loading mdi-spin me-1"></i>Mengambil lokasi GPS Sales (maks 20 detik)…</div>
    <button class="btn btn-success" id="btnAmbilLokasi" disabled>
      <i class="mdi mdi-crosshairs-gps me-1"></i>Ambil Lokasi & Kirim Kunjungan
    </button>

    {{-- Hasil kunjungan --}}
    <div class="result-box result-diterima" id="resDiterima">
      <h6 class="fw-bold text-success"><i class="mdi mdi-check-circle me-2"></i>DITERIMA</h6>
      <div class="row g-2 small">
        <div class="col-6"><b>Jarak:</b> <span id="r_jarak">—</span> m</div>
        <div class="col-6"><b>Threshold Efektif:</b> <span id="r_threshold">—</span> m</div>
        <div class="col-6"><b>Acc Toko:</b> <span id="r_acc_toko">—</span> m</div>
        <div class="col-6"><b>Acc Sales:</b> <span id="r_acc_sales">—</span> m</div>
        <div class="col-6"><b>Lat Sales:</b> <span id="r_lat">—</span></div>
        <div class="col-6"><b>Lng Sales:</b> <span id="r_lng">—</span></div>
      </div>
    </div>
    <div class="result-box result-ditolak" id="resDitolak">
      <h6 class="fw-bold text-danger"><i class="mdi mdi-close-circle me-2"></i>DITOLAK</h6>
      <div class="row g-2 small">
        <div class="col-6"><b>Jarak:</b> <span id="r_jarak2">—</span> m</div>
        <div class="col-6"><b>Threshold Efektif:</b> <span id="r_threshold2">—</span> m</div>
        <div class="col-6"><b>Acc Toko:</b> <span id="r_acc_toko2">—</span> m</div>
        <div class="col-6"><b>Acc Sales:</b> <span id="r_acc_sales2">—</span> m</div>
        <div class="col-6"><b>Lat Sales:</b> <span id="r_lat2">—</span></div>
        <div class="col-6"><b>Lng Sales:</b> <span id="r_lng2">—</span></div>
      </div>
    </div>

  </div>
</div>
</div>

</div>{{-- /row --}}

{{-- Hidden print iframe --}}
<iframe id="printFrame" style="display:none"></iframe>

@endsection

@push('js-page')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

/* ============================================================
   getAccuratePosition (sesuai spesifikasi)
============================================================ */
function getAccuratePosition(targetAccuracy = 50, maxWait = 20000) {
  return new Promise((resolve, reject) => {
    let bestResult = null;
    const startTime = Date.now();
    const watchId = navigator.geolocation.watchPosition(
      (position) => {
        const acc = position.coords.accuracy;
        if (!bestResult || acc < bestResult.coords.accuracy) bestResult = position;
        if (acc <= targetAccuracy) { navigator.geolocation.clearWatch(watchId); resolve(bestResult); }
        if (Date.now() - startTime >= maxWait) {
          navigator.geolocation.clearWatch(watchId);
          if (bestResult) resolve(bestResult); else reject(new Error('Timeout'));
        }
      },
      (error) => reject(error),
      { enableHighAccuracy: true, maximumAge: 0, timeout: maxWait }
    );
  });
}

/* ============================================================
   FORM TOKO — Geoloc
============================================================ */
document.getElementById('btnGeoToko').addEventListener('click', async () => {
  const loader = document.getElementById('gps1load');
  loader.style.display = 'block';
  try {
    const pos = await getAccuratePosition();
    document.getElementById('toko_lat').value = pos.coords.latitude;
    document.getElementById('toko_lng').value = pos.coords.longitude;
    document.getElementById('toko_acc').value = pos.coords.accuracy.toFixed(2);
  } catch (e) {
    alert('GPS Error: ' + (e.message || 'Izin GPS ditolak atau tidak tersedia.'));
  } finally {
    loader.style.display = 'none';
  }
});

/* ============================================================
   BARCODE SCANNER (html5-qrcode)
============================================================ */
let qr2 = null;
let scannedBarcode = null;

function startScan() {
  if (qr2) { try { qr2.clear(); } catch(e){} }
  qr2 = new Html5Qrcode('qr-reader2');
  Html5Qrcode.getCameras().then(cams => {
    if (!cams.length) { setScanStatus('Tidak ada kamera', 'danger'); return; }
    const cam = cams.find(c => /back|environment/i.test(c.label)) || cams[0];
    qr2.start(cam.id,
      { fps: 15, qrbox: { width: 200, height: 200 }, aspectRatio: 1.0,
        supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA] },
      onScanOk, () => {}
    ).then(() => setScanStatus('Scanning…', 'secondary'))
     .catch(e => setScanStatus('Kamera gagal: ' + e.message, 'danger'));
  }).catch(e => setScanStatus('Kamera tidak tersedia: ' + e.message, 'danger'));
}

function onScanOk(text) {
  qr2.stop().catch(()=>{});
  scannedBarcode = text;
  setScanStatus('Barcode: ' + text, 'success');
  document.getElementById('btnScanLagi').classList.remove('d-none');
  fetchToko(text);
}

function restartScan() {
  scannedBarcode = null;
  document.getElementById('tokoInfo').style.display = 'none';
  document.getElementById('btnAmbilLokasi').disabled = true;
  document.getElementById('resDiterima').style.display = 'none';
  document.getElementById('resDitolak').style.display = 'none';
  document.getElementById('btnScanLagi').classList.add('d-none');
  setScanStatus('Scanning…', 'secondary');
  startScan();
}

function setScanStatus(msg, type) {
  const el = document.getElementById('scanStatus');
  el.textContent = msg;
  el.className = 'badge bg-' + type;
}

/* ============================================================
   FETCH DATA TOKO by barcode
============================================================ */
function fetchToko(barcode) {
  fetch('/toko/' + encodeURIComponent(barcode), { headers: { Accept: 'application/json' } })
    .then(r => r.json())
    .then(data => {
      if (!data.success) { alert(data.message); return; }
      document.getElementById('tokoNama').textContent    = data.nama_toko;
      document.getElementById('tokoBarcode').textContent = data.barcode;
      document.getElementById('tokoLat').textContent     = data.latitude;
      document.getElementById('tokoLng').textContent     = data.longitude;
      document.getElementById('tokoAcc').textContent     = data.accuracy;
      document.getElementById('tokoInfo').style.display  = 'block';
      document.getElementById('btnAmbilLokasi').disabled = false;
    })
    .catch(() => alert('Gagal mengambil data toko.'));
}

/* ============================================================
   AMBIL LOKASI SALES & KIRIM KUNJUNGAN
============================================================ */
document.getElementById('btnAmbilLokasi').addEventListener('click', async () => {
  if (!scannedBarcode) { alert('Scan barcode toko terlebih dahulu.'); return; }
  const loader = document.getElementById('gps2load');
  loader.style.display = 'block';
  document.getElementById('btnAmbilLokasi').disabled = true;
  document.getElementById('resDiterima').style.display = 'none';
  document.getElementById('resDitolak').style.display = 'none';
  try {
    const pos = await getAccuratePosition();
    const payload = {
      barcode:   scannedBarcode,
      lat_sales: pos.coords.latitude,
      lng_sales: pos.coords.longitude,
      acc_sales: pos.coords.accuracy,
    };
    const resp = await fetch('/kunjungan', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' },
      body: JSON.stringify(payload),
    });
    const data = await resp.json();
    if (!data.success) { alert(data.message || 'Terjadi kesalahan.'); return; }
    showResult(data);
  } catch (e) {
    alert('GPS Error: ' + (e.message || 'Izin GPS ditolak.'));
  } finally {
    loader.style.display = 'none';
    document.getElementById('btnAmbilLokasi').disabled = false;
  }
});

function showResult(d) {
  const diterima = d.status === 'DITERIMA';
  const box  = document.getElementById(diterima ? 'resDiterima' : 'resDitolak');
  const suf  = diterima ? '' : '2';
  document.getElementById('r_jarak'     + suf).textContent = d.jarak_meter;
  document.getElementById('r_threshold' + suf).textContent = d.threshold_efektif;
  document.getElementById('r_acc_toko'  + suf).textContent = d.acc_toko;
  document.getElementById('r_acc_sales' + suf).textContent = parseFloat(d.acc_sales).toFixed(2);
  document.getElementById('r_lat'       + suf).textContent = d.lat_sales;
  document.getElementById('r_lng'       + suf).textContent = d.lng_sales;
  box.style.display = 'block';
  box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/* ============================================================
   CETAK BARCODE (print QR via qrserver.com)
============================================================ */
document.querySelectorAll('.btn-cetak').forEach(btn => {
  btn.addEventListener('click', () => {
    const bc   = btn.dataset.barcode;
    const nama = btn.dataset.nama;
    const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(bc);
    const html = `<html><head><title>Barcode ${bc}</title>
      <style>body{font-family:sans-serif;text-align:center;padding:20px}
      img{display:block;margin:0 auto 10px}h3{margin:0}p{color:#666;font-size:13px}</style></head>
      <body onload="window.print()">
        <img src="${qrUrl}" width="200" height="200">
        <h3>${nama}</h3><p>Barcode: ${bc}</p>
      </body></html>`;
    const fr = document.getElementById('printFrame');
    fr.srcdoc = html;
  });
});

/* ============================================================
   INIT
============================================================ */
document.addEventListener('DOMContentLoaded', startScan);
</script>
@endpush
