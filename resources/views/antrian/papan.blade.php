@extends('layouts.master')

@section('content')
<style>
.papan-wrapper{background:#0d0d1a;border-radius:16px;min-height:70vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px;text-align:center;position:relative;overflow:hidden;color:#fff}
.papan-header{margin-bottom:24px}
.papan-header h1{font-size:1.4rem;font-weight:700;color:rgba(255,255,255,.6);letter-spacing:4px;text-transform:uppercase}
.now-serving{background:rgba(255,255,255,.04);border:2px solid rgba(124,77,255,.3);border-radius:28px;padding:48px 80px;margin-bottom:32px;min-width:400px;position:relative;overflow:hidden}
.now-serving::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(124,77,255,.1),rgba(68,138,255,.05));pointer-events:none}
.now-label{font-size:1rem;font-weight:700;color:rgba(255,255,255,.5);letter-spacing:3px;text-transform:uppercase;margin-bottom:12px}
.now-nomor{font-size:8rem;font-weight:900;line-height:1;letter-spacing:-4px;background:linear-gradient(135deg,#7c4dff,#448aff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;transition:all .5s ease}
.now-nomor.animate{animation:papan-flash .4s ease}
@keyframes papan-flash{0%,100%{opacity:1}50%{opacity:.1}}
.now-nama{font-size:1.8rem;font-weight:700;color:#fff;margin-top:8px;transition:all .4s ease}
.waiting-section{display:flex;gap:16px;flex-wrap:wrap;justify-content:center;max-width:700px}
.waiting-card{background:rgba(255,255,255,.06);border-radius:14px;padding:12px 20px;min-width:110px;text-align:center;border:1px solid rgba(255,255,255,.08)}
.waiting-num{font-size:1.8rem;font-weight:800;color:#7c4dff}
.waiting-name{font-size:0.7rem;color:rgba(255,255,255,.5);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100px}
.waiting-empty{color:rgba(255,255,255,.3);font-size:0.85rem}
.papan-live{position:absolute;top:16px;right:20px;display:flex;align-items:center;gap:6px;font-size:12px;color:rgba(255,255,255,.4)}
.papan-dot{width:8px;height:8px;border-radius:50%;background:#00c853;animation:papan-pulse 1.5s infinite}
@keyframes papan-pulse{0%,100%{opacity:1}50%{opacity:.2}}
.papan-sound-badge{position:absolute;top:16px;left:20px;font-size:12px;color:rgba(255,255,255,.5);background:rgba(255,255,255,.06);padding:4px 12px;border-radius:20px;cursor:pointer;transition:all .3s}
.papan-sound-badge:hover{background:rgba(255,255,255,.12)}
.papan-time{position:absolute;bottom:20px;left:20px;font-size:1.8rem;font-weight:800;color:rgba(255,255,255,.15);font-variant-numeric:tabular-nums}
@media(max-width:600px){.now-serving{padding:28px 24px;min-width:auto}.now-nomor{font-size:5rem}.now-nama{font-size:1.2rem}}
</style>

<audio id="papanAudio" src="/sounds/dingdong.mp3" preload="auto"></audio>

<div class="row">
  <div class="col-12">
    <div class="page-header">
      <h3 class="page-title"><span class="page-title-icon bg-gradient-primary text-white me-2"><i class="mdi mdi-television-play"></i></span>Papan Antrian</h3>
      <nav aria-label="breadcrumb">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('antrian.admin') }}">Antrian</a></li>
          <li class="breadcrumb-item active" aria-current="page">Papan</li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="papan-wrapper" id="papanWrapper">
      <div class="papan-sound-badge" id="soundBadge" onclick="manualUnlock()">🔇 Klik di sini untuk aktifkan suara</div>
      <div class="papan-live"><span class="papan-dot" id="dot"></span><span>Live</span></div>
      <div class="papan-header"><h1>🎫 Antrian Digital</h1></div>
      <div class="now-serving">
        <div class="now-label">Sedang Dipanggil</div>
        <div class="now-nomor" id="nowNomor">—</div>
        <div class="now-nama" id="nowNama">Menunggu panggilan pertama…</div>
      </div>
      <div>
        <p style="color:rgba(255,255,255,.4);font-size:.8rem;letter-spacing:2px;text-transform:uppercase;margin-bottom:12px">Menunggu</p>
        <div class="waiting-section" id="waitingSection">
          <span class="waiting-empty">Belum ada antrian</span>
        </div>
      </div>
      <div class="papan-time" id="clock">--:--:--</div>
    </div>
  </div>
</div>
@endsection

@push('js-page')
<script>
(function(){
  /* ── Clock ─────────────────────────────────────────────────── */
  function updateClock(){
    document.getElementById('clock').textContent =
      new Date().toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
  }
  setInterval(updateClock,1000);
  updateClock();

  /* ── Sound ─────────────────────────────────────────────────── */
  var audioEl     = document.getElementById('papanAudio');
  var soundReady  = false;
  var lastCalled   = null;
  var pendingCall  = null;  // queued for after audio unlock

  // Called on ANY user click/touch/key
  function tryUnlock(){
    if(soundReady) return;
    audioEl.volume = 0.001;
    var p = audioEl.play();
    if(p && p.then){
      p.then(function(){
        audioEl.pause();
        audioEl.currentTime = 0;
        audioEl.volume = 1;
        soundReady = true;
        onSoundReady();
      }).catch(function(){
        // some browsers still block — mark TTS-only
        soundReady = true;
        onSoundReady();
      });
    } else {
      soundReady = true;
      onSoundReady();
    }
  }

  function onSoundReady(){
    document.getElementById('soundBadge').innerHTML = '🔊 Suara Aktif';
    document.getElementById('soundBadge').style.color = '#69f0ae';
    // Play any call that arrived before audio was unlocked
    if(pendingCall){
      playDingThenTTS(pendingCall.nomor, pendingCall.nama);
      pendingCall = null;
    }
  }

  // Manual click on badge
  window.manualUnlock = tryUnlock;

  // Auto-unlock on any interaction (capture phase)
  document.addEventListener('click',      tryUnlock, true);
  document.addEventListener('touchstart', tryUnlock, true);
  document.addEventListener('keydown',    tryUnlock, true);

  /* ── Play ding-dong then TTS ───────────────────────────────── */
  function playDingThenTTS(nomor, nama){
    if(!soundReady){
      pendingCall = {nomor:nomor, nama:nama};
      return;
    }
    try {
      audioEl.currentTime = 0;
      audioEl.volume = 1;
      audioEl.onended = function(){ speakTTS(nomor, nama); };
      var p = audioEl.play();
      if(p && p.catch) p.catch(function(){ speakTTS(nomor, nama); });
    } catch(e){
      speakTTS(nomor, nama);
    }
  }

  function speakTTS(nomor, nama){
    if(!window.speechSynthesis) return;
    speechSynthesis.cancel();
    var u = new SpeechSynthesisUtterance('Nomor antrian '+nomor+'. '+nama+', silakan masuk.');
    u.lang='id-ID'; u.rate=0.85; u.pitch=1; u.volume=1;
    setTimeout(function(){ speechSynthesis.speak(u); }, 250);
  }

  /* ── Polling ───────────────────────────────────────────────── */
  var pollOk = true;

  function poll(){
    fetch('/api/antrian/poll')
      .then(function(r){ return r.json(); })
      .then(function(data){
        if(!pollOk){ pollOk=true; document.getElementById('dot').style.background='#00c853'; }
        update(data);
      })
      .catch(function(){
        pollOk=false; document.getElementById('dot').style.background='#f44336';
      });
  }

  function update(data){
    var nomEl  = document.getElementById('nowNomor');
    var namaEl = document.getElementById('nowNama');

    if(data.sedang_dipanggil){
      var d = data.sedang_dipanggil;
      var nomorStr = String(d.nomor).padStart(3,'0');

      if(lastCalled !== d.id){
        lastCalled = d.id;
        nomEl.classList.add('animate');
        setTimeout(function(){ nomEl.classList.remove('animate'); }, 400);
        playDingThenTTS(d.nomor, d.nama);
      }

      nomEl.textContent  = nomorStr;
      namaEl.textContent = d.nama;
    } else {
      if(firstLoad) firstLoad = false;
      nomEl.textContent  = '—';
      namaEl.textContent = 'Menunggu panggilan…';
    }

    // Waiting list
    var ws   = document.getElementById('waitingSection');
    var list = data.menunggu || [];
    if(!list.length){
      ws.innerHTML = '<span class="waiting-empty">Tidak ada antrian</span>';
    } else {
      ws.innerHTML = list.slice(0,8).map(function(m){
        return '<div class="waiting-card"><div class="waiting-num">'+String(m.nomor).padStart(3,'0')+'</div><div class="waiting-name" title="'+m.nama+'">'+m.nama+'</div></div>';
      }).join('') + (list.length>8 ? '<div class="waiting-card"><div class="waiting-num">+'+(list.length-8)+'</div><div class="waiting-name">lainnya</div></div>' : '');
    }
  }

  poll();
  setInterval(poll, 2000);
})();
</script>
@endpush
