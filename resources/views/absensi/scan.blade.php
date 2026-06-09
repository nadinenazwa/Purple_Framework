@extends('layouts.master')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="page-header">
      <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
          <i class="mdi mdi-cellphone-nfc"></i>
        </span>
        Scan Absensi NFC
      </h3>
      <nav aria-label="breadcrumb">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
          <li class="breadcrumb-item active">Scan Absensi</li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<div class="row justify-content-center">

  {{-- NFC Scanner --}}
  <div class="col-lg-6 col-md-8 col-12 mb-4">
    <div class="card shadow-sm border-0">
      <div class="card-body p-4">
        <h5 class="fw-bold mb-3"><i class="mdi mdi-nfc text-success me-2"></i>NFC Scanner</h5>

        <div class="text-center py-4">
          <div class="nfc-pulse-wrapper mb-3" id="nfcRing">
            <div class="nfc-ring ring-1"></div>
            <div class="nfc-ring ring-2"></div>
            <div class="nfc-ring ring-3"></div>
            <div class="nfc-center-icon"><i class="mdi mdi-cellphone-nfc"></i></div>
          </div>
          <p class="text-muted" id="nfcText">Tekan tombol di bawah untuk mengaktifkan NFC</p>
        </div>

        <button class="btn btn-gradient-primary btn-lg btn-block w-100" id="btnScan" onclick="startScan()">
          <i class="mdi mdi-nfc me-2"></i> Aktifkan NFC
        </button>
      </div>
    </div>
  </div>

  {{-- Status Messages --}}
  <div class="col-lg-8 col-md-10 col-12">
    <div class="alert d-none" id="statusBox" role="alert"></div>
  </div>

  {{-- Result Card --}}
  <div class="col-lg-8 col-md-10 col-12 d-none" id="resultCard">
    <div class="card border-0 shadow-sm" style="border-left: 4px solid #00c853 !important;">
      <div class="card-body p-4">
        <div class="d-flex align-items-center mb-3">
          <div class="result-avatar me-3">
            <i class="mdi mdi-check"></i>
          </div>
          <div>
            <h5 class="fw-bold mb-0" id="resultNama">—</h5>
            <small class="text-muted" id="resultNim">—</small>
          </div>
        </div>
        <div class="row g-3">
          <div class="col-sm-6">
            <div class="p-3 bg-light rounded">
              <small class="text-muted d-block mb-1">Waktu</small>
              <strong id="resultWaktu">—</strong>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="p-3 bg-light rounded">
              <small class="text-muted d-block mb-1">Tanggal</small>
              <strong id="resultTanggal">—</strong>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

{{-- NFC Pulse CSS --}}
<style>
.nfc-pulse-wrapper {
  position: relative; width: 120px; height: 120px; margin: 0 auto;
  display: flex; align-items: center; justify-content: center;
}
.nfc-ring {
  position: absolute; border: 2px solid rgba(124, 77, 255, 0.3); border-radius: 50%;
  animation: nfcPulse 2s ease-out infinite;
}
.ring-1 { width: 60px; height: 60px; animation-delay: 0s; }
.ring-2 { width: 85px; height: 85px; animation-delay: 0.4s; }
.ring-3 { width: 110px; height: 110px; animation-delay: 0.8s; }
.nfc-center-icon { font-size: 2rem; z-index: 1; color: #7c4dff; }
.nfc-pulse-wrapper.scanning .nfc-ring { border-color: rgba(0, 200, 83, 0.4); }
.nfc-pulse-wrapper.scanning .nfc-center-icon { color: #00c853; }
@keyframes nfcPulse {
  0% { transform: scale(0.8); opacity: 1; }
  100% { transform: scale(1.3); opacity: 0; }
}
.result-avatar {
  width: 48px; height: 48px; border-radius: 50%; display: flex;
  align-items: center; justify-content: center; font-size: 1.3rem;
  font-weight: 700; color: white;
  background: linear-gradient(135deg, #10b981, #06b6d4);
}
</style>
@endsection

@push('js-page')
<script>
    let isScanning = false;

    function updateStatus(message, type = 'info') {
        const box = document.getElementById('statusBox');
        box.className = 'alert';
        const typeMap = { info: 'alert-info', success: 'alert-success', error: 'alert-danger', warning: 'alert-warning' };
        box.classList.add(typeMap[type] || 'alert-info');
        const icons = { info: 'ℹ️', success: '✅', error: '❌', warning: '⚠️' };
        box.innerHTML = (icons[type] || '') + ' ' + message;
    }

    function hideStatus() {
        document.getElementById('statusBox').className = 'alert d-none';
    }

    function tampilkanHasil(data) {
        if (!data.success) {
            updateStatus(data.message, 'error');
            return;
        }

        hideStatus();
        const card = document.getElementById('resultCard');
        card.classList.remove('d-none');

        document.getElementById('resultNama').textContent = data.nama;
        document.getElementById('resultNim').textContent = 'NIM: ' + data.nim;
        document.getElementById('resultWaktu').textContent = data.waktu;
        document.getElementById('resultTanggal').textContent = data.tanggal;

        if (navigator.vibrate) navigator.vibrate(200);
    }

    async function startScan() {
        if (!('NDEFReader' in window)) {
            updateStatus('Browser tidak mendukung Web NFC. Gunakan Android Chrome (v89+) via HTTPS.', 'error');
            return;
        }

        const btn = document.getElementById('btnScan');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Mengaktifkan NFC...';

        try {
            const ndef = new NDEFReader();
            await ndef.scan();

            isScanning = true;
            btn.innerHTML = '<i class="mdi mdi-check-circle me-2"></i> NFC Aktif — Menunggu Kartu...';
            btn.className = 'btn btn-gradient-success btn-lg btn-block w-100';

            document.getElementById('nfcRing').classList.add('scanning');
            document.getElementById('nfcText').textContent = 'NFC aktif. Dekatkan kartu mahasiswa...';

            updateStatus('NFC aktif. Dekatkan kartu mahasiswa ke perangkat.', 'info');

            ndef.addEventListener('reading', async ({ serialNumber }) => {
                console.log('Serial:', serialNumber);
                updateStatus('Memproses absensi...', 'info');
                await kirimAbsensi(serialNumber);
            });

            ndef.addEventListener('readingerror', () => {
                updateStatus('Gagal membaca kartu NFC. Coba dekatkan lagi.', 'warning');
            });

        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="mdi mdi-nfc me-2"></i> Aktifkan NFC';
            btn.className = 'btn btn-gradient-primary btn-lg btn-block w-100';

            if (err.name === 'NotAllowedError') {
                updateStatus('Izin NFC ditolak. Aktifkan NFC di pengaturan perangkat dan izinkan akses di browser.', 'error');
            } else if (err.name === 'NotSupportedError') {
                updateStatus('NFC tidak didukung oleh perangkat ini.', 'error');
            } else {
                updateStatus('Error: ' + err.message, 'error');
            }
        }
    }

    async function kirimAbsensi(serialNumber) {
        try {
            const res = await fetch('/absensi', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ nfc_serial: serialNumber })
            });
            const data = await res.json();
            tampilkanHasil(data);
        } catch (err) {
            updateStatus('Gagal mengirim data: ' + err.message, 'error');
        }
    }
</script>
@endpush
