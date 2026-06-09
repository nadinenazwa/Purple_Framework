@extends('layouts.master')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="page-header">
      <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
          <i class="mdi mdi-card-account-details"></i>
        </span>
        Daftarkan Kartu NFC
      </h3>
      <nav aria-label="breadcrumb">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
          <li class="breadcrumb-item active">Daftar Kartu</li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<div class="row justify-content-center">

  {{-- Form Data Mahasiswa --}}
  <div class="col-lg-6 col-md-8 col-12 mb-4">
    <div class="card shadow-sm border-0">
      <div class="card-body p-4">
        <h5 class="fw-bold mb-3"><i class="mdi mdi-account-plus text-primary me-2"></i>Data Mahasiswa</h5>
        <div class="mb-3">
          <label for="nim" class="form-label fw-semibold">NIM</label>
          <input type="text" id="nim" class="form-control form-control-lg" placeholder="Masukkan NIM mahasiswa..." required>
        </div>
        <div class="mb-3">
          <label for="nama" class="form-label fw-semibold">Nama Lengkap</label>
          <input type="text" id="nama" class="form-control form-control-lg" placeholder="Masukkan nama lengkap..." required>
        </div>
      </div>
    </div>
  </div>

  {{-- NFC Scan --}}
  <div class="col-lg-6 col-md-8 col-12 mb-4">
    <div class="card shadow-sm border-0">
      <div class="card-body p-4">
        <h5 class="fw-bold mb-3"><i class="mdi mdi-nfc text-info me-2"></i>Scan Kartu NFC</h5>

        <div class="text-center py-4">
          <div class="nfc-pulse-wrapper mb-3" id="nfcRing">
            <div class="nfc-ring ring-1"></div>
            <div class="nfc-ring ring-2"></div>
            <div class="nfc-ring ring-3"></div>
            <div class="nfc-center-icon"><i class="mdi mdi-credit-card-wireless"></i></div>
          </div>
          <p class="text-muted" id="nfcText">Tekan tombol untuk scan kartu NFC baru</p>
        </div>

        <button class="btn btn-gradient-primary btn-lg btn-block w-100" id="btnScanKartu" onclick="scanKartu()">
          <i class="mdi mdi-nfc me-2"></i> Scan Kartu NFC
        </button>

        <div class="mt-3">
          <label for="nfcSerial" class="form-label fw-semibold">NFC Serial Number</label>
          <input type="text" id="nfcSerial" class="form-control" readonly placeholder="Scan kartu untuk mengisi otomatis...">
        </div>
      </div>
    </div>
  </div>

  {{-- Status & Save --}}
  <div class="col-lg-8 col-md-10 col-12">
    <div class="alert d-none" id="statusBox" role="alert"></div>
  </div>

  <div class="col-lg-8 col-md-10 col-12 mb-4">
    <button class="btn btn-gradient-success btn-lg btn-block w-100" id="btnSimpan" onclick="simpanKartu()" disabled>
      <i class="mdi mdi-content-save me-2"></i> Simpan Kartu NFC
    </button>
  </div>

  {{-- Result Card --}}
  <div class="col-lg-8 col-md-10 col-12 d-none" id="resultCard">
    <div class="card border-0 shadow-sm" style="border-left: 4px solid #00c853 !important;">
      <div class="card-body p-4">
        <div class="d-flex align-items-center mb-3">
          <div class="result-avatar hadir me-3">
            <i class="mdi mdi-check"></i>
          </div>
          <div>
            <h5 class="fw-bold mb-0" id="resultNama">—</h5>
            <small class="text-muted" id="resultNim">—</small>
          </div>
        </div>
        <div class="p-3 bg-light rounded">
          <small class="text-muted d-block mb-1">NFC Serial Number</small>
          <code id="resultSerial" class="text-dark" style="letter-spacing: 0.1em;">—</code>
        </div>
      </div>
    </div>
  </div>

  {{-- Tabel Kartu Terdaftar --}}
  <div class="col-12 mt-4">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <h5 class="fw-bold mb-3"><i class="mdi mdi-card-bulleted text-success me-2"></i>Kartu Terdaftar</h5>
        @if($mahasiswas->count() > 0)
        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>NIM</th>
                <th>Nama</th>
                <th>NFC Serial</th>
                <th>Terdaftar</th>
              </tr>
            </thead>
            <tbody>
              @foreach($mahasiswas as $i => $mhs)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td><code>{{ $mhs->nim }}</code></td>
                <td>{{ $mhs->nama }}</td>
                <td>
                  @if($mhs->nfc_serial)
                    <code>{{ $mhs->nfc_serial }}</code>
                  @else
                    <span class="text-muted">— Belum ada —</span>
                  @endif
                </td>
                <td><small class="text-muted">{{ $mhs->created_at->format('d M Y H:i') }}</small></td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <div class="text-center py-4 text-muted">
          <i class="mdi mdi-card-off-outline" style="font-size: 2.5rem; opacity: 0.4;"></i>
          <p class="mt-2">Belum ada kartu terdaftar. Scan kartu NFC untuk mendaftarkan mahasiswa baru.</p>
        </div>
        @endif
      </div>
    </div>
  </div>

</div>

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
}
.result-avatar.hadir { background: linear-gradient(135deg, #10b981, #06b6d4); }
</style>
@endsection

@push('js-page')
<script>
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

    async function scanKartu() {
        if (!('NDEFReader' in window)) {
            updateStatus('Browser tidak mendukung Web NFC. Gunakan Android Chrome (v89+) via HTTPS.', 'error');
            return;
        }

        const nimInput = document.getElementById('nim');
        const namaInput = document.getElementById('nama');
        if (!nimInput.value.trim()) {
            updateStatus('Masukkan NIM terlebih dahulu!', 'warning');
            nimInput.focus();
            return;
        }
        if (!namaInput.value.trim()) {
            updateStatus('Masukkan nama terlebih dahulu!', 'warning');
            namaInput.focus();
            return;
        }

        const btn = document.getElementById('btnScanKartu');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Scanning...';

        try {
            const ndef = new NDEFReader();
            await ndef.scan();

            document.getElementById('nfcRing').classList.add('scanning');
            document.getElementById('nfcText').textContent = 'Dekatkan kartu NFC baru...';
            updateStatus('NFC aktif. Dekatkan kartu NFC yang ingin didaftarkan.', 'info');

            ndef.addEventListener('reading', ({ serialNumber }) => {
                console.log('Serial:', serialNumber);

                document.getElementById('nfcSerial').value = serialNumber;
                document.getElementById('btnSimpan').disabled = false;

                btn.innerHTML = '<i class="mdi mdi-check-circle me-2"></i> Kartu Terbaca';
                btn.className = 'btn btn-gradient-success btn-lg btn-block w-100';

                document.getElementById('nfcText').textContent = 'Kartu berhasil dibaca!';

                updateStatus('Serial: ' + serialNumber + ' — Klik Simpan untuk mendaftarkan.', 'success');

                if (navigator.vibrate) navigator.vibrate(200);
            });

            ndef.addEventListener('readingerror', () => {
                updateStatus('Gagal membaca kartu NFC. Coba dekatkan lagi.', 'warning');
            });

        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="mdi mdi-nfc me-2"></i> Scan Kartu NFC';
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

    async function simpanKartu() {
        const nim = document.getElementById('nim').value.trim();
        const nama = document.getElementById('nama').value.trim();
        const nfcSerial = document.getElementById('nfcSerial').value;

        if (!nim || !nama || !nfcSerial) {
            updateStatus('Data belum lengkap!', 'warning');
            return;
        }

        const btn = document.getElementById('btnSimpan');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';

        try {
            const res = await fetch('/daftar-kartu', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    nim: nim,
                    nama: nama,
                    nfc_serial: nfcSerial
                })
            });

            const data = await res.json();

            if (data.success) {
                updateStatus(data.message, 'success');

                // Show result
                const card = document.getElementById('resultCard');
                card.classList.remove('d-none');
                document.getElementById('resultNama').textContent = data.nama;
                document.getElementById('resultNim').textContent = 'NIM: ' + data.nim;
                document.getElementById('resultSerial').textContent = data.nfc_serial;

                if (navigator.vibrate) navigator.vibrate([100, 50, 100]);

                // Reload halaman setelah 2 detik agar tabel terupdate
                setTimeout(() => location.reload(), 2000);
            } else {
                updateStatus(data.message, 'error');
            }
        } catch (err) {
            updateStatus('Gagal menyimpan: ' + err.message, 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="mdi mdi-content-save me-2"></i> Simpan Kartu NFC';
        }
    }
</script>
@endpush
