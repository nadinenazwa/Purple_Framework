@extends('layouts.master')

@section('content')
<div class="container">
  <h2>Demo Plain Table - Tambah Barang</h2>

  <div class="row mb-3">
    <div class="col-md-6">
      <form id="formBarangPlain" novalidate>
        <div class="mb-2 row align-items-center">
          <label class="col-sm-3 col-form-label">Nama barang:</label>
          <div class="col-sm-9">
            <input type="text" name="nama" class="form-control" required>
          </div>
        </div>
        <div class="mb-2 row align-items-center">
          <label class="col-sm-3 col-form-label">Harga barang:</label>
          <div class="col-sm-9">
            <input type="number" name="harga" class="form-control" required>
          </div>
        </div>
      </form>
      <div class="mt-2">
        <!-- submit button intentionally outside the form -->
        <button type="button" id="btnTambahPlain" class="btn btn-success">Tambahkan</button>
      </div>
    </div>
  </div>

  <h4>Daftar Barang (Plain)</h4>
  <table class="table table-bordered" id="tabelBarangPlain">
    <thead>
      <tr>
        <th>ID barang</th>
        <th>Nama</th>
        <th>Harga</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

@push('js-page')
<script>
  $(function(){
    // Provide a small fallback implementation if resources/js/app.js is not compiled/loaded
    if (typeof window.bindBarangForm !== 'function') {
      window.bindBarangForm = function(formSelector, tableSelector, submitBtnSelector) {
        const $form = $(formSelector);
        const $table = $(tableSelector);
        if (!$form.length || !$table.length) return;
        $form.on('submit', function(e){
          e.preventDefault();
          const formEl = this;
          if (typeof formEl.checkValidity === 'function' && !formEl.checkValidity()) {
            if (typeof formEl.reportValidity === 'function') formEl.reportValidity();
            return;
          }
          const $btn = submitBtnSelector ? ($(submitBtnSelector).length ? $(submitBtnSelector) : $form.find(submitBtnSelector)) : $form.find('button');
          if ($btn.length) { $btn.data('orig', $btn.html()).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>'); }
          const nama = $form.find('input[name="nama"]').val();
          const harga = $form.find('input[name="harga"]').val();
          let nextId = $table.data('next-id') || 1; const id = nextId; $table.data('next-id', id+1);
          const $tbody = $table.find('tbody').length ? $table.find('tbody') : $('<tbody/>').appendTo($table);
          $tbody.append('<tr data-id="'+id+'"><td>'+id+'</td><td>'+$('<div/>').text(nama).html()+'</td><td>'+$('<div/>').text(harga).html()+'</td></tr>');
          $form[0].reset();
          if ($btn.length) setTimeout(function(){ $btn.prop('disabled', false).html($btn.data('orig')); }, 700);
        });
      };
    }

    // bind form to plain table; button outside triggers form submit
    window.bindBarangForm('#formBarangPlain', '#tabelBarangPlain', '#btnTambahPlain');
    $('#btnTambahPlain').on('click', function(){
      $('#formBarangPlain').trigger('submit');
    });
  });
</script>
@endpush

@endsection
