@extends('layouts.master')

@section('content')
<div class="container">
  <h2>Demo DataTables - Tambah Barang</h2>

  <div class="row mb-3">
    <div class="col-md-6">
      <form id="formBarangDT" novalidate>
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
        <!-- submit button outside the form -->
        <button type="button" id="btnTambahDT" class="btn btn-success">Tambahkan</button>
      </div>
    </div>
  </div>

  <h4>Daftar Barang (DataTables)</h4>
  <table class="table table-striped" id="tabelBarangDT">
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
  <!-- DataTables CSS/JS from CDN (only for this demo page) -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script>
    $(function(){
      // initialize DataTable
      const dt = $('#tabelBarangDT').DataTable({
        columns: [ { title: 'ID' }, { title: 'Nama' }, { title: 'Harga' } ]
      });

      // fallback bind if main script not loaded
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
            // If DataTable is available use API
            if (typeof $.fn.DataTable !== 'undefined' && $.fn.DataTable.isDataTable($table.get(0))) {
              $table.DataTable().row.add([id, nama, harga]).draw(false);
            } else {
              const $tbody = $table.find('tbody').length ? $table.find('tbody') : $('<tbody/>').appendTo($table);
              $tbody.append('<tr data-id="'+id+'"><td>'+id+'</td><td>'+$('<div/>').text(nama).html()+'</td><td>'+$('<div/>').text(harga).html()+'</td></tr>');
            }
            $form[0].reset();
            if ($btn.length) setTimeout(function(){ $btn.prop('disabled', false).html($btn.data('orig')); }, 700);
          });
        };
      }

      // bind form and button to DataTable-enabled table
      window.bindBarangForm('#formBarangDT', '#tabelBarangDT', '#btnTambahDT');

      $('#btnTambahDT').on('click', function(){
        $('#formBarangDT').trigger('submit');
      });
    });
  </script>
@endpush

@endsection
