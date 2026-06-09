@extends('absensi.layout')

@section('title', 'Scan Absensi NFC')

@section('content')
<div class="page-header">
    <h1>📲 Scan Absensi NFC</h1>
    <p>Dekatkan kartu NFC mahasiswa untuk mencatat kehadiran</p>
</div>

{{-- Pilih Matakuliah --}}
<div class="glass-card">
    <div class="card-title">
        <span class="icon blue">📚</span>
        Pilih Matakuliah
    </div>
    <div class="form-group">
        <label class="form-label" for="matakuliah">Matakuliah</label>
        <select id="matakuliah" class="form-select" required>
            <option value="" disabled selected>— Pilih matakuliah —</option>
            @foreach($matakuliahs as $mk)
                <option value="{{ $mk['id'] }}">{{ $mk['kode'] }} — {{ $mk['nama'] }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- NFC Scanner --}}
<div class="glass-card">
    <div class="card-title">
        <span class="icon emerald">📡</span>
        NFC Scanner
    </div>

    <div class="nfc-visual" id="nfcVisual">
        <div class="nfc-ring" id="nfcRing">
            <span class="ring"></span>
            <span class="ring"></span>
            <span class="ring"></span>
            <span class="nfc-icon">📱</span>
        </div>
        <p class="nfc-text" id="nfcText">Tekan tombol di bawah untuk mengaktifkan NFC</p>
    </div>

    <button class="btn btn-primary btn-block btn-lg" id="btnScan" onclick="startScan()">
        📡 Aktifkan NFC
    </button>
</div>

{{-- Status Messages --}}
<div class="status-box" id="statusBox" role="alert"></div>

{{-- Result Card --}}
<div class="result-card" id="resultCard">
    <div class="result-header">
        <div class="result-avatar" id="resultAvatar">✓</div>
        <div>
            <div class="result-name" id="resultNama">—</div>
            <div class="result-nim" id="resultNim">—</div>
        </div>
    </div>
    <div class="result-details">
        <div class="result-item">
            <div class="result-item-label">Matakuliah</div>
            <div class="result-item-value" id="resultMk">—</div>
        </div>
        <div class="result-item">
            <div class="result-item-label">Status</div>
            <div class="result-item-value" id="resultStatus">—</div>
        </div>
        <div class="result-item">
            <div class="result-item-label">Waktu</div>
            <div class="result-item-value" id="resultWaktu">—</div>
        </div>
        <div class="result-item">
            <div class="result-item-label">Tanggal</div>
            <div class="result-item-value" id="resultTanggal">—</div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let isScanning = false;

    function updateStatus(message, type = 'info') {
        const box = document.getElementById('statusBox');
        box.className = 'status-box show ' + type;
        const icons = { info: 'ℹ️', success: '✅', error: '❌', warning: '⚠️' };
        box.innerHTML = (icons[type] || '') + ' ' + message;
    }

    function hideStatus() {
        document.getElementById('statusBox').className = 'status-box';
    }

    function tampilkanHasil(data) {
        if (!data.success) {
            updateStatus(data.message, 'error');
            return;
        }

        hideStatus();
        const card = document.getElementById('resultCard');
        card.className = 'result-card show';

        // Avatar
        const avatar = document.getElementById('resultAvatar');
        avatar.className = 'result-avatar ' + data.status;
        avatar.textContent = data.status === 'hadir' ? '✓' : '!';

        // Details
        document.getElementById('resultNama').textContent = data.nama;
        document.getElementById('resultNim').textContent = 'NIM: ' + data.nim;
        document.getElementById('resultMk').textContent = data.matakuliah;
        document.getElementById('resultWaktu').textContent = data.waktu;
        document.getElementById('resultTanggal').textContent = data.tanggal;

        // Status badge
        const statusEl = document.getElementById('resultStatus');
        const badgeClass = data.status === 'hadir' ? 'badge-hadir' : 'badge-terlambat';
        statusEl.innerHTML = '<span class="badge ' + badgeClass + '">' + data.status.toUpperCase() + '</span>';

        // Vibrate for feedback
        if (navigator.vibrate) navigator.vibrate(200);
    }

    async function startScan() {
        if (!('NDEFReader' in window)) {
            updateStatus('Browser tidak mendukung Web NFC. Gunakan Android Chrome (v89+) via HTTPS.', 'error');
            return;
        }

        const mkSelect = document.getElementById('matakuliah');
        if (!mkSelect.value) {
            updateStatus('Pilih matakuliah terlebih dahulu!', 'warning');
            mkSelect.focus();
            return;
        }

        const btn = document.getElementById('btnScan');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Mengaktifkan NFC...';

        try {
            const ndef = new NDEFReader();
            await ndef.scan();

            isScanning = true;
            btn.innerHTML = '🟢 NFC Aktif — Menunggu Kartu...';
            btn.className = 'btn btn-success btn-block btn-lg';

            document.getElementById('nfcRing').classList.add('scanning');
            document.getElementById('nfcText').textContent = 'NFC aktif. Dekatkan kartu mahasiswa...';

            updateStatus('NFC aktif. Dekatkan kartu mahasiswa ke perangkat.', 'info');

            ndef.addEventListener('reading', async ({ serialNumber, message }) => {
                let isi = '';
                for (const record of message.records) {
                    isi += new TextDecoder().decode(record.data);
                }
                console.log('Serial:', serialNumber);
                console.log('Isi:', isi);

                updateStatus('Memproses absensi...', 'info');
                await kirimAbsensi(serialNumber, isi);
            });

            ndef.addEventListener('readingerror', () => {
                updateStatus('Gagal membaca kartu NFC. Coba dekatkan lagi.', 'warning');
            });

        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = '📡 Aktifkan NFC';
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

    async function kirimAbsensi(serialNumber, isiKartu) {
        const matakuliahId = document.getElementById('matakuliah').value;
        try {
            const res = await fetch('/absensi', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    nfc_serial: serialNumber,
                    matakuliah_id: matakuliahId
                })
            });
            const data = await res.json();
            tampilkanHasil(data);
        } catch (err) {
            updateStatus('Gagal mengirim data: ' + err.message, 'error');
        }
    }
</script>
@endsection
