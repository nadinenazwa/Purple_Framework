@extends('layouts.master')

@section('content')
<div class="container py-4">
  <h3 class="mb-4">Tambah Customer (Form lengkap + Kamera)</h3>
  <div class="card">
    <div class="card-body">
      <form id="fullForm" method="post" action="{{ route('customer.store.blob') }}">
        @csrf
        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Nama</label>
              <input name="name" class="form-control" />
            </div>
            <div class="mb-3">
              <label class="form-label">Alamat</label>
              <input name="alamat" class="form-control" />
            </div>
            <div class="mb-3">
              <label class="form-label">Provinsi</label>
              <input name="provinsi" class="form-control" />
            </div>
            <div class="mb-3">
              <label class="form-label">Kota</label>
              <input name="kota" class="form-control" />
            </div>
            <div class="mb-3">
              <label class="form-label">Kecamatan</label>
              <input name="kecamatan" class="form-control" />
            </div>
            <div class="mb-3">
              <label class="form-label">Kodepos - Kelurahan</label>
              <input name="kodepos" class="form-control" />
            </div>
          </div>

          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Foto</label>
              <div style="width:220px;height:220px;border:2px solid #c8e6c9;display:flex;align-items:center;justify-content:center;background:#fff" id="photoBox">
                <img id="photoPreview" src="" alt="Foto" style="max-width:100%;max-height:100%;display:none" />
                <span id="photoPlaceholder" style="color:#777">Foto</span>
              </div>
            </div>
            <div class="mb-3">
              <button id="openCamera" class="btn btn-primary">Ambil Foto</button>
              <button type="submit" class="btn btn-success">Simpan Data</button>
            </div>
          </div>
        </div>

        <input type="hidden" name="photo_data" id="photo_data_full" />
      </form>
    </div>
  </div>
</div>

<!-- Modal ambil foto -->
<div class="modal fade" id="cameraModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Modal ambil Foto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <div style="border:1px solid #c8e6c9; height:300px; display:flex; align-items:center; justify-content:center;">
              <video id="camVideo" autoplay playsinline style="max-width:100%; max-height:100%;"></video>
            </div>
          </div>
          <div class="col-md-6">
            <div style="border:1px solid #c8e6c9; height:300px; display:flex; align-items:center; justify-content:center;">
              <canvas id="camCanvas" style="max-width:100%; max-height:100%;"></canvas>
            </div>
          </div>
        </div>
        <div class="mt-3 d-flex gap-2">
          <button id="takeSnapshot" class="btn btn-primary">Ambil Foto</button>
          <button id="saveSnapshot" class="btn btn-success">Simpan Foto</button>
          <select id="cameraSelect" class="form-select" style="max-width:300px"></select>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('js-page')
<script>
(async function(){
  const openBtn = document.getElementById('openCamera');
  const modalEl = document.getElementById('cameraModal');
  const camVideo = document.getElementById('camVideo');
  const camCanvas = document.getElementById('camCanvas');
  const takeBtn = document.getElementById('takeSnapshot');
  const saveBtn = document.getElementById('saveSnapshot');
  const preview = document.getElementById('photoPreview');
  const placeholder = document.getElementById('photoPlaceholder');
  const photoDataInput = document.getElementById('photo_data_full');
  const cameraSelect = document.getElementById('cameraSelect');
  let stream = null;

  function populateCameras(devices){
    cameraSelect.innerHTML = '';
    devices.forEach(d=>{
      const opt = document.createElement('option');
      opt.value = d.deviceId;
      opt.textContent = d.label || ('Camera ' + (cameraSelect.length+1));
      cameraSelect.appendChild(opt);
    });
  }

  async function startCamera(deviceId){
    if (stream) {
      stream.getTracks().forEach(t=>t.stop());
      stream = null;
    }
    try {
      stream = await navigator.mediaDevices.getUserMedia({ video: deviceId ? { deviceId: { exact: deviceId } } : { facingMode: 'user' }, audio: false });
      camVideo.srcObject = stream;
    } catch(e){ alert('Gagal membuka kamera: '+e.message); }
  }

  openBtn.addEventListener('click', function(e){
    e.preventDefault();
    var b = new bootstrap.Modal(modalEl);
    b.show();
    // enumerate devices and start camera
    navigator.mediaDevices.enumerateDevices().then(devs=>{
      const cams = devs.filter(d=>d.kind==='videoinput');
      populateCameras(cams);
      startCamera(cams[0] ? cams[0].deviceId : null);
    });
  });

  cameraSelect.addEventListener('change', function(){ startCamera(this.value); });

  takeBtn.addEventListener('click', function(e){
    e.preventDefault();
    const w = camVideo.videoWidth;
    const h = camVideo.videoHeight;
    camCanvas.width = w;
    camCanvas.height = h;
    const ctx = camCanvas.getContext('2d');
    ctx.drawImage(camVideo, 0, 0, w, h);
  });

  saveBtn.addEventListener('click', function(e){
    e.preventDefault();
    const dataUrl = camCanvas.toDataURL('image/png');
    photoDataInput.value = dataUrl;
    preview.src = dataUrl;
    preview.style.display = 'block';
    placeholder.style.display = 'none';
    // hide modal
    var m = bootstrap.Modal.getInstance(modalEl);
    if (m) m.hide();
  });

})();
</script>
@endpush
