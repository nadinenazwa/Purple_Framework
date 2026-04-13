@extends('layouts.master')

@section('content')
<div class="container mt-4">
  <h3>Kota - Demo Select / Select2</h3>

  <div class="row">
    <div class="col-md-12">
      <div class="card mb-4">
        <div class="card-header">Select</div>
        <div class="card-body">
          <form id="formKotaPlain" class="d-flex align-items-center" novalidate>
            <label class="me-3" style="min-width:80px">Kota:</label>
            <input id="kotaInput" name="kota" class="form-control me-3" style="max-width:600px;" required>
            <button id="btnTambahPlain" type="button" class="btn btn-success">Tambahkan</button>
          </form>

          <div class="mt-4">
            <label class="me-2">Select Kota:</label>
            <select id="normalSelect" class="form-select" style="width:100%"></select>
          </div>

          <div class="mt-3">
            <strong>Kota Terpilih:</strong>
            <span id="kotaTerpilihPlain"></span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-12">
      <div class="card">
        <div class="card-header">Select2</div>
        <div class="card-body">
          <form id="formKotaSelect2" class="d-flex align-items-center" novalidate>
            <label class="me-3" style="min-width:80px">Kota:</label>
            <input id="kotaInput2" name="kota2" class="form-control me-3" style="max-width:600px;" required>
            <button id="btnTambahSelect2" type="button" class="btn btn-success">Tambahkan</button>
          </form>

          <div class="mt-4">
            <label class="me-2">Select Kota:</label>
            <select id="select2Select" class="form-select" style="width:100%"></select>
          </div>

          <div class="mt-3">
            <strong>Kota Terpilih:</strong>
            <span id="kotaTerpilih2"></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('js-page')
  {{-- Include jQuery + Select2 from CDN for demo; if your layout already includes them, these will be ignored by the browser cache --}}
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script>
  (function($){
    function addOptionTo(select, value, text){
      // avoid duplicates by value
      if(select.find('option[value="'+value+'"]').length) return;
      var opt = $('<option>').val(value).text(text);
      select.append(opt);
    }

    $(function(){
      // Plain select handlers
      $('#btnTambahPlain').on('click', function(){
        var form = document.getElementById('formKotaPlain');
        if(!form.checkValidity()){
          form.reportValidity();
          return;
        }
        var kota = $('#kotaInput').val().trim();
        if(!kota) return;
        addOptionTo($('#normalSelect'), kota, kota);
        addOptionTo($('#select2Select'), kota, kota);
        $('#kotaInput').val('');
      });

      $('#normalSelect').on('change', function(){
        $('#kotaTerpilihPlain').text($(this).val() || '');
      });

      // Select2 handlers
      $('#select2Select').select2({placeholder:'Pilih kota'});

      $('#btnTambahSelect2').on('click', function(){
        var form = document.getElementById('formKotaSelect2');
        if(!form.checkValidity()){
          form.reportValidity();
          return;
        }
        var kota = $('#kotaInput2').val().trim();
        if(!kota) return;
        addOptionTo($('#select2Select'), kota, kota);
        addOptionTo($('#normalSelect'), kota, kota);
        // refresh select2 to pick up new option
        $('#select2Select').trigger('change.select2');
        $('#kotaInput2').val('');
      });

      $('#select2Select').on('change', function(){
        $('#kotaTerpilih2').text($(this).val() || '');
      });
    });
  })(jQuery);
  </script>

@endpush
