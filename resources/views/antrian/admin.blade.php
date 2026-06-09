@extends('layouts.master')
@section('content')
<style>
/* ── Admin Antrian — Polished UI ─────────────────────────── */

/* Dipanggil box */
.aq-dipanggil{border-radius:20px;padding:36px 28px;text-align:center;min-height:200px;display:flex;flex-direction:column;align-items:center;justify-content:center;transition:background .4s ease;position:relative;overflow:hidden}
.aq-dipanggil::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 30% 20%,rgba(255,255,255,.18),transparent 70%);pointer-events:none}
.aq-dipanggil.aktif{background:linear-gradient(135deg,#00c853,#69f0ae)}
.aq-dipanggil.kosong{background:linear-gradient(135deg,#bdbdbd,#e0e0e0)}
.aq-dipanggil-label{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:3px;color:rgba(255,255,255,.7);margin-bottom:8px}
.aq-dipanggil-nomor{font-size:5.5rem;font-weight:900;color:#fff;line-height:1;letter-spacing:-3px;text-shadow:0 4px 20px rgba(0,0,0,.15)}
.aq-dipanggil-nama{font-size:1.15rem;font-weight:600;color:rgba(255,255,255,.9);margin-top:8px}

/* Cards */
.aq-card{border:none;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,.07);overflow:hidden}
.aq-card .card-header{color:#fff;font-weight:700;padding:14px 20px;border:none;font-size:.9rem;display:flex;align-items:center;gap:8px}
.aq-card-menunggu .card-header{background:linear-gradient(135deg,#7c4dff,#448aff)}
.aq-card-terlambat .card-header{background:linear-gradient(135deg,#ff9800,#ffc107)}
.aq-card .card-body{padding:0}
.aq-card .table{margin-bottom:0;font-size:.85rem}
.aq-card .table th{font-size:.7rem;text-transform:uppercase;letter-spacing:1px;color:#999;font-weight:600;border-top:none;padding:10px 16px}
.aq-card .table td{padding:10px 16px;vertical-align:middle;border-color:#f5f5f5}
.aq-card .table tbody tr:hover{background:#fafafa}

/* Badges */
.aq-badge{display:inline-block;padding:4px 14px;border-radius:20px;font-size:.8rem;font-weight:700;min-width:50px;text-align:center}
.aq-badge-blue{background:#e8eaf6;color:#5c6bc0}
.aq-badge-orange{background:#fff3e0;color:#ef6c00}

/* Action buttons */
.aq-btn{display:inline-flex;align-items:center;gap:4px;padding:5px 14px;border-radius:8px;font-size:.75rem;font-weight:600;cursor:pointer;border:none;transition:all .15s ease;white-space:nowrap}
.aq-btn:hover{transform:translateY(-1px);box-shadow:0 2px 8px rgba(0,0,0,.15)}
.aq-btn:active{transform:translateY(0)}
.aq-btn-panggil{background:linear-gradient(135deg,#7c4dff,#448aff);color:#fff}
.aq-btn-terlambat{background:#fff8e1;color:#f57f17;border:1px solid #ffe082}
.aq-btn-terlambat:hover{background:#fff3e0}
.aq-btn-recall{background:linear-gradient(135deg,#ff9800,#ffc107);color:#fff}
.aq-btn-group{display:flex;gap:6px;flex-wrap:nowrap}

/* Panggil berikutnya button */
.aq-btn-next{display:inline-flex;align-items:center;gap:8px;padding:12px 28px;border-radius:12px;font-size:.95rem;font-weight:700;cursor:pointer;border:none;background:linear-gradient(135deg,#7c4dff,#448aff);color:#fff;transition:all .2s ease;box-shadow:0 4px 16px rgba(124,77,255,.3)}
.aq-btn-next:hover{transform:translateY(-2px);box-shadow:0 6px 24px rgba(124,77,255,.4)}
.aq-btn-next:active{transform:translateY(0)}
.aq-btn-next:disabled{opacity:.6;cursor:not-allowed;transform:none}

/* Notification */
#notification{position:fixed;top:20px;right:20px;padding:14px 24px;border-radius:12px;font-weight:600;font-size:14px;display:none;z-index:9999;box-shadow:0 8px 30px rgba(0,0,0,.15);backdrop-filter:blur(8px);max-width:400px}
.notif-ok{background:rgba(232,245,233,.95);color:#2e7d32;border-left:4px solid #00c853}
.notif-err{background:rgba(252,228,236,.95);color:#c62828;border-left:4px solid #f44336}

/* Live dot */
.aq-live-dot{width:10px;height:10px;border-radius:50%;background:#00c853;display:inline-block;animation:aq-pulse 1.5s ease infinite}
@keyframes aq-pulse{0%,100%{opacity:1}50%{opacity:.3}}

/* Empty state */
.aq-empty{color:#bbb;font-size:.85rem;padding:28px 16px!important;text-align:center}
.aq-empty i{display:block;font-size:1.6rem;margin-bottom:6px;opacity:.4}

/* Scrollable table body */
.aq-scroll{max-height:320px;overflow-y:auto}
.aq-scroll::-webkit-scrollbar{width:4px}
.aq-scroll::-webkit-scrollbar-thumb{background:#ddd;border-radius:4px}
</style>

<div id="notification"></div>

{{-- Hidden audio for admin page sound --}}
<audio id="adminAudio" src="/sounds/dingdong.mp3" preload="auto" style="display:none"></audio>

<div class="row">
  <div class="col-12">
    <div class="page-header">
      <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2"><i class="mdi mdi-ticket-account"></i></span>Admin Antrian
      </h3>
      <div class="ms-auto d-flex align-items-center gap-2">
        <span class="aq-live-dot" id="sseDot"></span>
        <small class="text-muted">Real-time</small>
        <a href="{{ route('antrian.papan') }}" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
          <i class="mdi mdi-television-play me-1"></i>Papan
        </a>
      </div>
    </div>
  </div>
</div>

{{-- Row 1: Sedang Dipanggil + Panggil Berikutnya --}}
<div class="row mb-4">
  <div class="col-12">
    <div class="card aq-card">
      <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
          <div class="d-flex align-items-center gap-4">
            <div class="aq-dipanggil kosong" id="dipanggilBox" style="min-width:180px;min-height:140px;border-radius:16px;padding:24px 32px">
              <div class="aq-dipanggil-label">Sedang Dipanggil</div>
              <div class="aq-dipanggil-nomor" id="dipanggilNomor">—</div>
              <div class="aq-dipanggil-nama" id="dipanggilNama">Belum ada panggilan</div>
            </div>
            <div>
              <h5 class="fw-bold mb-1" style="color:#333">Panggilan Saat Ini</h5>
              <p class="text-muted mb-3" style="font-size:.85rem">Tekan tombol untuk memanggil antrian berikutnya secara urut,<br>atau pilih nomor tertentu dari daftar menunggu.</p>
              <button class="aq-btn-next" id="btnPanggil" onclick="panggil()">
                <i class="mdi mdi-bullhorn"></i> Panggil Berikutnya
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Row 2: Menunggu + Terlambat --}}
<div class="row g-4">
  {{-- Daftar Menunggu --}}
  <div class="col-lg-6">
    <div class="card aq-card aq-card-menunggu">
      <div class="card-header">
        <i class="mdi mdi-account-clock" style="font-size:1.2rem"></i>
        Menunggu
        <span class="ms-auto" style="background:rgba(255,255,255,.25);padding:2px 12px;border-radius:12px;font-size:.8rem" id="countMenunggu">0</span>
      </div>
      <div class="card-body">
        <div class="aq-scroll">
          <table class="table">
            <thead><tr><th style="width:70px">No</th><th>Nama</th><th style="width:170px">Aksi</th></tr></thead>
            <tbody id="tbodyMenunggu">
              <tr><td colspan="3" class="aq-empty"><i class="mdi mdi-account-clock-outline"></i>Tidak ada antrian</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  {{-- Daftar Terlambat --}}
  <div class="col-lg-6">
    <div class="card aq-card aq-card-terlambat">
      <div class="card-header">
        <i class="mdi mdi-account-alert" style="font-size:1.2rem"></i>
        Terlambat
        <span class="ms-auto" style="background:rgba(255,255,255,.25);padding:2px 12px;border-radius:12px;font-size:.8rem" id="countTerlambat">0</span>
      </div>
      <div class="card-body">
        <div class="aq-scroll">
          <table class="table">
            <thead><tr><th style="width:70px">No</th><th>Nama</th><th style="width:140px">Aksi</th></tr></thead>
            <tbody id="tbodyTerlambat">
              <tr><td colspan="3" class="aq-empty"><i class="mdi mdi-account-alert-outline"></i>Tidak ada yang terlambat</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('js-page')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

/* ============================================================
   Sound System (mirrors papan — plays on admin page directly)
============================================================ */
let adminSoundReady = false;
const adminAudioEl  = document.getElementById('adminAudio');

// Unlock audio on first user interaction
function unlockAdminAudio() {
  if (adminSoundReady) return;
  adminAudioEl.volume = 0.001;
  adminAudioEl.play()
    .then(() => { adminAudioEl.pause(); adminAudioEl.currentTime = 0; adminAudioEl.volume = 1; adminSoundReady = true; })
    .catch(() => { adminSoundReady = true; });
}
document.addEventListener('click',      unlockAdminAudio, { once: true });
document.addEventListener('touchstart', unlockAdminAudio, { once: true });
document.addEventListener('keydown',    unlockAdminAudio, { once: true });

function playAdminSound(nomor, nama) {
  try {
    adminAudioEl.currentTime = 0;
    adminAudioEl.volume = 1;
    adminAudioEl.play().then(() => {
      adminAudioEl.onended = function() {
        if (!('speechSynthesis' in window)) return;
        window.speechSynthesis.cancel();
        const u = new SpeechSynthesisUtterance(`Nomor antrian ${nomor}. ${nama}, silakan masuk.`);
        u.lang = 'id-ID'; u.rate = 0.85; u.pitch = 1.0; u.volume = 1.0;
        setTimeout(() => window.speechSynthesis.speak(u), 200);
      };
    }).catch(() => {
      // Audio blocked — fallback to TTS only
      if (!('speechSynthesis' in window)) return;
      window.speechSynthesis.cancel();
      const u = new SpeechSynthesisUtterance(`Nomor antrian ${nomor}. ${nama}, silakan masuk.`);
      u.lang = 'id-ID'; u.rate = 0.85; u.pitch = 1.0; u.volume = 1.0;
      window.speechSynthesis.speak(u);
    });
  } catch(e) {}
}

/* ============================================================
   AJAX Polling
============================================================ */
let pollOk = true;

function pollAntrian() {
  fetch('/api/antrian/poll')
    .then(r => r.json())
    .then(data => {
      if (!pollOk) {
        pollOk = true;
        document.getElementById('sseDot').style.background = '#00c853';
      }
      updateUI(data);
    })
    .catch(() => {
      pollOk = false;
      document.getElementById('sseDot').style.background = '#f44336';
    });
}

/* ============================================================
   UI UPDATE
============================================================ */
function updateUI(data) {
  // Sedang dipanggil
  const box = document.getElementById('dipanggilBox');
  if (data.sedang_dipanggil) {
    const d = data.sedang_dipanggil;
    document.getElementById('dipanggilNomor').textContent = String(d.nomor).padStart(3,'0');
    document.getElementById('dipanggilNama').textContent  = d.nama;
    box.classList.remove('kosong');
    box.classList.add('aktif');
  } else {
    document.getElementById('dipanggilNomor').textContent = '—';
    document.getElementById('dipanggilNama').textContent  = 'Belum ada panggilan';
    box.classList.remove('aktif');
    box.classList.add('kosong');
  }

  // Menunggu
  const tM = document.getElementById('tbodyMenunggu');
  document.getElementById('countMenunggu').textContent = (data.menunggu||[]).length;
  if (!data.menunggu || !data.menunggu.length) {
    tM.innerHTML = '<tr><td colspan="3" class="aq-empty"><i class="mdi mdi-account-clock-outline"></i>Tidak ada antrian</td></tr>';
  } else {
    tM.innerHTML = data.menunggu.map(m => `
      <tr>
        <td><span class="aq-badge aq-badge-blue">${String(m.nomor).padStart(3,'0')}</span></td>
        <td class="fw-semibold">${m.nama}</td>
        <td>
          <div class="aq-btn-group">
            <button class="aq-btn aq-btn-panggil" onclick="panggilById(${m.id})"><i class="mdi mdi-bullhorn"></i> Panggil</button>
            <button class="aq-btn aq-btn-terlambat" onclick="tandaiTerlambat(${m.id})"><i class="mdi mdi-clock-alert-outline"></i> Skip</button>
          </div>
        </td>
      </tr>`).join('');
  }

  // Terlambat
  const tT = document.getElementById('tbodyTerlambat');
  document.getElementById('countTerlambat').textContent = (data.terlambat||[]).length;
  if (!data.terlambat || !data.terlambat.length) {
    tT.innerHTML = '<tr><td colspan="3" class="aq-empty"><i class="mdi mdi-account-alert-outline"></i>Tidak ada yang terlambat</td></tr>';
  } else {
    tT.innerHTML = data.terlambat.map(t => `
      <tr>
        <td><span class="aq-badge aq-badge-orange">${String(t.nomor).padStart(3,'0')}</span></td>
        <td class="fw-semibold">${t.nama}</td>
        <td>
          <button class="aq-btn aq-btn-recall" onclick="panggilTerlambat(${t.id})"><i class="mdi mdi-phone-return"></i> Panggil Ulang</button>
        </td>
      </tr>`).join('');
  }
}

/* ============================================================
   ACTIONS
============================================================ */
function post(url) {
  return fetch(url, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
  }).then(r => r.json());
}

function notify(msg, ok = true) {
  const el = document.getElementById('notification');
  el.textContent = msg;
  el.className = ok ? 'notif-ok' : 'notif-err';
  el.style.display = 'block';
  setTimeout(() => el.style.display = 'none', 3000);
}

function panggil() {
  document.getElementById('btnPanggil').disabled = true;
  post('/admin/panggil')
    .then(d => {
      if (d.success) {
        notify(`✓ Memanggil nomor ${String(d.antrian.nomor).padStart(3,'0')} — ${d.antrian.nama}`);
        playAdminSound(d.antrian.nomor, d.antrian.nama);
      } else notify(d.message, false);
    })
    .catch(() => notify('Gagal terhubung ke server.', false))
    .finally(() => document.getElementById('btnPanggil').disabled = false);
}

function panggilById(id) {
  post('/admin/panggil/' + id)
    .then(d => {
      if (d.success) {
        notify(`✓ Memanggil nomor ${String(d.antrian.nomor).padStart(3,'0')} — ${d.antrian.nama}`);
        playAdminSound(d.antrian.nomor, d.antrian.nama);
      } else notify(d.message, false);
    })
    .catch(() => notify('Gagal.', false));
}

function tandaiTerlambat(id) {
  post('/admin/terlambat/' + id)
    .then(d => d.success ? notify('Dipindah ke daftar terlambat.') : notify(d.message, false))
    .catch(() => notify('Gagal.', false));
}

function panggilTerlambat(id) {
  post('/admin/panggil-terlambat/' + id)
    .then(d => {
      if (d.success) {
        notify(`✓ Memanggil ulang nomor ${String(d.antrian.nomor).padStart(3,'0')} — ${d.antrian.nama}`);
        playAdminSound(d.antrian.nomor, d.antrian.nama);
      } else notify(d.message, false);
    })
    .catch(() => notify('Gagal.', false));
}

document.addEventListener('DOMContentLoaded', () => {
  pollAntrian();
  setInterval(pollAntrian, 2000);
});
</script>
@endpush
