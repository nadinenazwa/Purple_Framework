@extends('layouts.master')

@section('content')
<div class="container py-4">
  <h3 class="mb-4">Tambah Customer 1 (simpan sebagai BLOB)</h3>
  <div class="row">
    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <form id="blobForm" method="post" action="{{ route('customer.store.blob') }}">
            @csrf
            <div class="mb-3">
              <label class="form-label">Nama</label>
              <input name="name" class="form-control" />
            </div>
            <div class="mb-3">
              <label class="form-label">Alamat</label>
              <textarea name="alamat" class="form-control" rows="3" placeholder="Alamat lengkap"></textarea>
            </div>
            <div class="row">
              <div class="col-md-4 mb-3">
                <label class="form-label">Provinsi</label>
                <select id="provinceSel" name="province_id" class="form-control">
                  <option value="">Pilih Provinsi</option>
                </select>
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Kota / Kabupaten</label>
                <select id="regencySel" name="regency_id" class="form-control">
                  <option value="">Pilih Kota / Kabupaten</option>
                </select>
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Kecamatan</label>
                <select id="districtSel" name="district_id" class="form-control">
                  <option value="">Pilih Kecamatan</option>
                </select>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Kodepos / Kelurahan</label>
              <input name="kodepos" class="form-control" placeholder="Contoh: 60115 / Sukolilo" />
              <input type="hidden" name="province_name" id="province_name_input" />
              <input type="hidden" name="regency_name" id="regency_name_input" />
              <input type="hidden" name="district_name" id="district_name_input" />
            </div>
            <div class="mb-3">
              <video id="video" autoplay playsinline style="width:100%;border:1px solid #ddd"></video>
            </div>
            <div class="mb-3">
              <canvas id="canvas" style="display:none"></canvas>
            </div>
            <input type="hidden" name="photo_data" id="photo_data" />
            <div>
              <button id="captureBtn" class="btn btn-primary">Ambil Foto</button>
              <button id="saveBtn" class="btn btn-success" type="submit">Simpan</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <h5>Preview</h5>
          <img id="preview" src="" style="max-width:100%;border:1px solid #ccc" />
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('js-page')
<script>
(async function(){
  const video = document.getElementById('video');
  const canvas = document.getElementById('canvas');
  const preview = document.getElementById('preview');
  const captureBtn = document.getElementById('captureBtn');
  const photoInput = document.getElementById('photo_data');

  try {
    const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
    video.srcObject = stream;
  } catch (e) {
    alert('Gagal mengakses kamera: ' + e.message);
  }

  captureBtn.addEventListener('click', function(e){
    e.preventDefault();
    const w = video.videoWidth;
    const h = video.videoHeight;
    canvas.width = w;
    canvas.height = h;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, w, h);
    const dataUrl = canvas.toDataURL('image/png');
    preview.src = dataUrl;
    photoInput.value = dataUrl;
  });
})();
</script>
<script>
function populateSelect(sel, items){
  sel.innerHTML = '<option value="">Pilih</option>';
  items.forEach(it=>{
    const opt = document.createElement('option');
    opt.value = it.id ?? it.ID ?? '';
    opt.textContent = it.name ?? it.NAMA ?? opt.value;
    sel.appendChild(opt);
  });
}

document.addEventListener('DOMContentLoaded', function(){
  const pSel = document.getElementById('provinceSel');
  const rSel = document.getElementById('regencySel');
  const dSel = document.getElementById('districtSel');
  if (!pSel) return;

  fetch('/api/wilayah/provinces').then(r=>r.json()).then(data=>{ populateSelect(pSel, data); }).catch(()=>{});

  pSel.addEventListener('change', function(){
    const pid = pSel.value;
    document.getElementById('province_name_input').value = pSel.options[pSel.selectedIndex]?.text || '';
    document.getElementById('regency_name_input').value = '';
    document.getElementById('district_name_input').value = '';
    dSel.innerHTML = '<option value="">Pilih Kecamatan</option>';
    if (!pid) { rSel.innerHTML = '<option value="">Pilih Kota / Kabupaten</option>'; return; }
    fetch('/api/wilayah/regencies/' + encodeURIComponent(pid)).then(r=>r.json()).then(data=>{ populateSelect(rSel, data); }).catch(()=>{ rSel.innerHTML = '<option value="">Pilih Kota / Kabupaten</option>'; });
  });

  rSel.addEventListener('change', function(){
    const rid = rSel.value;
    document.getElementById('regency_name_input').value = rSel.options[rSel.selectedIndex]?.text || '';
    document.getElementById('district_name_input').value = '';
    if (!rid) { dSel.innerHTML = '<option value="">Pilih Kecamatan</option>'; return; }
    fetch('/api/wilayah/districts/' + encodeURIComponent(rid)).then(r=>r.json()).then(data=>{ populateSelect(dSel, data); }).catch(()=>{ dSel.innerHTML = '<option value="">Pilih Kecamatan</option>'; });
  });

  dSel.addEventListener('change', function(){
    document.getElementById('district_name_input').value = dSel.options[dSel.selectedIndex]?.text || '';
  });
});
</script>
@endpush
