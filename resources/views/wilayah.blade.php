@extends('layouts.master')

@section('content')
<div class="container">
    <h3>Dependent selects - Ajax (fetch) and Axios</h3>

    <div class="row">
        <div class="col-md-6 section">
            <h5>AJAX (fetch) version</h5>
            <div class="select-box">
                <label>Provinsi :</label>
                <select id="ajaxProvinsi" class="form-select mb-2">
                    <option value="0">Pilih Provinsi</option>
                </select>

                <label>Kota :</label>
                <select id="ajaxKota" class="form-select mb-2">
                    <option value="0">Pilih Kota</option>
                </select>

                <label>Kecamatan :</label>
                <select id="ajaxKecamatan" class="form-select mb-2">
                    <option value="0">Pilih Kecamatan</option>
                </select>

                <label>Kelurahan :</label>
                <select id="ajaxKelurahan" class="form-select mb-2">
                    <option value="0">Pilih Kelurahan</option>
                </select>
                <div id="ajaxSelected" class="mt-3" style="display:none;"></div>
            </div>
        </div>

        <div class="col-md-6 section">
            <h5>Axios version</h5>
            <div class="select-box">
                <label>Provinsi :</label>
                <select id="axiosProvinsi" class="form-select mb-2">
                    <option value="0">Pilih Provinsi</option>
                </select>

                <label>Kota :</label>
                <select id="axiosKota" class="form-select mb-2">
                    <option value="0">Pilih Kota</option>
                </select>

                <label>Kecamatan :</label>
                <select id="axiosKecamatan" class="form-select mb-2">
                    <option value="0">Pilih Kecamatan</option>
                </select>

                <label>Kelurahan :</label>
                <select id="axiosKelurahan" class="form-select mb-2">
                    <option value="0">Pilih Kelurahan</option>
                </select>
                <div id="axiosSelected" class="mt-3" style="display:none;"></div>
            </div>
        </div>
    </div>

    <hr>
    <p class="text-muted">Notes: change a higher-level select to clear lower-level selects. Both sections are independent.</p>
</div>

<style>
    .select-box { max-width:520px; }
    .section { margin-top:2rem; }
</style>
@endsection

@push('js-page')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    // Utility to fill options
    function fillSelect(el, items, placeholder) {
        el.innerHTML = '';
        const opt0 = document.createElement('option');
        opt0.value = '0';
        opt0.textContent = placeholder;
        el.appendChild(opt0);
        items.forEach(i => {
            const o = document.createElement('option');
            o.value = i.id ?? i.code ?? i['id'];
            o.textContent = i.name ?? i['name'];
            el.appendChild(o);
        });
    }

    // ----------------- AJAX (fetch) implementation -----------------
    const ajaxProv = document.getElementById('ajaxProvinsi');
    const ajaxKota = document.getElementById('ajaxKota');
    const ajaxKec = document.getElementById('ajaxKecamatan');
    const ajaxKel = document.getElementById('ajaxKelurahan');

    function loadProvincesAjax() {
        fetch('/api/wilayah/provinces').then(r => r.json()).then(data => {
            fillSelect(ajaxProv, data, 'Pilih Provinsi');
            updateAjaxSelected();
        });
    }

    ajaxProv.addEventListener('change', function () {
        const pid = this.value;
        // Clear lower levels
        fillSelect(ajaxKota, [], 'Pilih Kota');
        fillSelect(ajaxKec, [], 'Pilih Kecamatan');
        fillSelect(ajaxKel, [], 'Pilih Kelurahan');
        if (pid === '0') return;
        fetch('/api/wilayah/regencies/' + pid).then(r => r.json()).then(data => {
            fillSelect(ajaxKota, data, 'Pilih Kota');
            updateAjaxSelected();
        });
    });

    ajaxKota.addEventListener('change', function () {
        const rid = this.value;
        fillSelect(ajaxKec, [], 'Pilih Kecamatan');
        fillSelect(ajaxKel, [], 'Pilih Kelurahan');
        if (rid === '0') return;
        fetch('/api/wilayah/districts/' + rid).then(r => r.json()).then(data => {
            fillSelect(ajaxKec, data, 'Pilih Kecamatan');
            updateAjaxSelected();
        });
    });

    ajaxKec.addEventListener('change', function () {
        const did = this.value;
        fillSelect(ajaxKel, [], 'Pilih Kelurahan');
        if (did === '0') return;
        fetch('/api/wilayah/villages/' + did).then(r => r.json()).then(data => {
            fillSelect(ajaxKel, data, 'Pilih Kelurahan');
            updateAjaxSelected();
        });
    });

    // when user selects kelurahan, update summary
    ajaxKel.addEventListener('change', function(){ updateAjaxSelected(); });

    function getSelectedText(sel){
        const v = sel.value;
        if (!v || v === '0') return null;
        const opt = sel.options[sel.selectedIndex];
        return opt ? opt.textContent.trim() : null;
    }

    function updateAjaxSelected(){
        const p = getSelectedText(ajaxProv);
        const k = getSelectedText(ajaxKota);
        const c = getSelectedText(ajaxKec);
        const l = getSelectedText(ajaxKel);
        const target = document.getElementById('ajaxSelected');
        if (p && k && c && l) {
            target.style.display = 'block';
            target.innerHTML = `<div class="alert alert-success"><strong>Wilayah Terpilih:</strong> ${p} → ${k} → ${c} → ${l}</div>`;
        } else {
            target.style.display = 'none';
            target.innerHTML = '';
        }
    }

    // ----------------- Axios implementation -----------------
    const aProv = document.getElementById('axiosProvinsi');
    const aKota = document.getElementById('axiosKota');
    const aKec = document.getElementById('axiosKecamatan');
    const aKel = document.getElementById('axiosKelurahan');

    function loadProvincesAxios() {
        axios.get('/api/wilayah/provinces').then(resp => {
            fillSelect(aProv, resp.data, 'Pilih Provinsi');
            updateAxiosSelected();
        });
    }

    aProv.addEventListener('change', function () {
        const pid = this.value;
        fillSelect(aKota, [], 'Pilih Kota');
        fillSelect(aKec, [], 'Pilih Kecamatan');
        fillSelect(aKel, [], 'Pilih Kelurahan');
        if (pid === '0') return;
        axios.get('/api/wilayah/regencies/' + pid).then(r => {
            fillSelect(aKota, r.data, 'Pilih Kota');
            updateAxiosSelected();
        });
    });

    aKota.addEventListener('change', function () {
        const rid = this.value;
        fillSelect(aKec, [], 'Pilih Kecamatan');
        fillSelect(aKel, [], 'Pilih Kelurahan');
        if (rid === '0') return;
        axios.get('/api/wilayah/districts/' + rid).then(r => {
            fillSelect(aKec, r.data, 'Pilih Kecamatan');
            updateAxiosSelected();
        });
    });

    aKec.addEventListener('change', function () {
        const did = this.value;
        fillSelect(aKel, [], 'Pilih Kelurahan');
        if (did === '0') return;
        axios.get('/api/wilayah/villages/' + did).then(r => {
            fillSelect(aKel, r.data, 'Pilih Kelurahan');
            updateAxiosSelected();
        });
    });

    // when user selects kelurahan, update summary
    aKel.addEventListener('change', function(){ updateAxiosSelected(); });

    function updateAxiosSelected(){
        const p = getSelectedText(aProv);
        const k = getSelectedText(aKota);
        const c = getSelectedText(aKec);
        const l = getSelectedText(aKel);
        const target = document.getElementById('axiosSelected');
        if (p && k && c && l) {
            target.style.display = 'block';
            target.innerHTML = `<div class="alert alert-success"><strong>Wilayah Terpilih:</strong> ${p} → ${k} → ${c} → ${l}</div>`;
        } else {
            target.style.display = 'none';
            target.innerHTML = '';
        }
    }

    // initial load
    loadProvincesAjax();
    loadProvincesAxios();
</script>
@endpush
