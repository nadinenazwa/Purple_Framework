@extends('absensi.layout')

@section('title', 'Daftar Kartu NFC')

@section('content')
<div class="page-header">
    <h1>💳 Daftarkan Kartu NFC</h1>
    <p>Scan dan tautkan kartu NFC ke profil mahasiswa</p>
</div>

{{-- Select Mahasiswa --}}
<div class="glass-card">
    <div class="card-title">
        <span class="icon blue">👤</span>
        Pilih Mahasiswa
    </div>
    <div class="form-group">
        <label class="form-label" for="mahasiswa">Mahasiswa</label>
        <select id="mahasiswa" class="form-select" required>
            <option value="" disabled selected>— Pilih mahasiswa —</option>
            @foreach($mahasiswas as $mhs)
                <option value="{{ $mhs['id'] }}" data-nfc="{{ $mhs['nfc_serial'] }}">
                    {{ $mhs['nim'] }} — {{ $mhs['nama'] }}
                    @if($mhs['nfc_serial'])
                        (NFC: {{ $mhs['nfc_serial'] }})
                    @else
                        (Belum ada kartu)
                    @endif
                </option>
            @endforeach
        </select>
    </div>
</div>

{{-- NFC Scan --}}
<div class="glass-card">
    <div class="card-title">
        <span class="icon violet">📡</span>
        Scan Kartu NFC
    </div>

    <div class="nfc-visual" id="nfcVisual">
        <div class="nfc-ring" id="nfcRing">
            <span class="ring"></span>
            <span class="ring"></span>
            <span class="ring"></span>
            <span class="nfc-icon">💳</span>
        </div>
        <p class="nfc-text" id="nfcText">Tekan tombol untuk scan kartu NFC baru</p>
    </div>

    <button class="btn btn-primary btn-block btn-lg" id="btnScanKartu" onclick="scanKartu()">
        📡 Scan Kartu NFC
    </button>

    <div class="form-group" style="margin-top: 20px;">
        <label class="form-label" for="nfcSerial">NFC Serial Number</label>
        <input type="text" id="nfcSerial" class="form-input" readonly placeholder="Scan kartu untuk mengisi otomatis...">
    </div>
</div>

{{-- Status & Save --}}
<div class="status-box" id="statusBox" role="alert"></div>

<button class="btn btn-success btn-block btn-lg" id="btnSimpan" onclick="simpanKartu()" disabled>
    💾 Simpan Kartu NFC
</button>

{{-- Result Card --}}
<div class="result-card" id="resultCard" style="margin-top: 20px;">
    <div class="result-header">
        <div class="result-avatar hadir">✓</div>
        <div>
            <div class="result-name" id="resultNama">—</div>
            <div class="result-nim" id="resultNim">—</div>
        </div>
    </div>
    <div class="result-details">
        <div class="result-item" style="grid-column: 1 / -1;">
            <div class="result-item-label">NFC Serial Number</div>
            <div class="result-item-value" id="resultSerial" style="font-family: monospace; letter-spacing: 0.1em;">—</div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function updateStatus(message, type = 'info') {
        const box = document.getElementById('statusBox');
        box.className = 'status-box show ' + type;
        const icons = { info: 'ℹ️', success: '✅', error: '❌', warning: '⚠️' };
        box.innerHTML = (icons[type] || '') + ' ' + message;
    }

    function hideStatus() {
        document.getElementById('statusBox').className = 'status-box';
    }

    async function scanKartu() {
        if (!('NDEFReader' in window)) {
            updateStatus('Browser tidak mendukung Web NFC. Gunakan Android Chrome (v89+) via HTTPS.', 'error');
            return;
        }

        const mhsSelect = document.getElementById('mahasiswa');
        if (!mhsSelect.value) {
            updateStatus('Pilih mahasiswa terlebih dahulu!', 'warning');
            mhsSelect.focus();
            return;
        }

        const btn = document.getElementById('btnScanKartu');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Scanning...';

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

                btn.innerHTML = '✅ Kartu Terbaca';
                btn.className = 'btn btn-success btn-block btn-lg';

                document.getElementById('nfcText').textContent = 'Kartu berhasil dibaca!';

                updateStatus('Serial: ' + serialNumber + ' — Klik Simpan untuk mendaftarkan.', 'success');

                if (navigator.vibrate) navigator.vibrate(200);
            });

            ndef.addEventListener('readingerror', () => {
                updateStatus('Gagal membaca kartu NFC. Coba dekatkan lagi.', 'warning');
            });

        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = '📡 Scan Kartu NFC';
            btn.className = 'btn btn-primary btn-block btn-lg';

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
        const mahasiswaId = document.getElementById('mahasiswa').value;
        const nfcSerial = document.getElementById('nfcSerial').value;

        if (!mahasiswaId || !nfcSerial) {
            updateStatus('Data belum lengkap!', 'warning');
            return;
        }

        const btn = document.getElementById('btnSimpan');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Menyimpan...';

        try {
            const res = await fetch('/daftar-kartu', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    mahasiswa_id: mahasiswaId,
                    nfc_serial: nfcSerial
                })
            });

            const data = await res.json();

            if (data.success) {
                updateStatus(data.message, 'success');

                // Show result
                const card = document.getElementById('resultCard');
                card.className = 'result-card show';
                document.getElementById('resultNama').textContent = data.nama;
                document.getElementById('resultNim').textContent = 'NIM: ' + data.nim;
                document.getElementById('resultSerial').textContent = data.nfc_serial;

                if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
            } else {
                updateStatus(data.message, 'error');
            }
        } catch (err) {
            updateStatus('Gagal menyimpan: ' + err.message, 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '💾 Simpan Kartu NFC';
        }
    }
</script>
@endsection
