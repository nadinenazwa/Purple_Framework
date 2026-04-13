@extends('layouts.master')

@section('content')
<div class="container">
    <h1>Daftar Barang</h1>
    <a href="{{ route('barang.create') }}" class="btn btn-success mb-2">Tambah Barang</a>
    <button id="print-selected" class="btn btn-primary mb-2">Cetak Label Terpilih</button>

    <table id="table-barang" class="table table-bordered">
        <thead>
            <tr>
              <th></th>
              <th>ID Barang</th>
              <th>Nama</th>
              <th>Harga</th>
              <th>Aksi</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <!-- Modal for start coordinates -->
    <div class="modal fade" id="printModal" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header"><h5 class="modal-title">Cetak Label</h5></div>
          <div class="modal-body">
            <div class="form-group">
              <label>Start X (1-5)</label>
              <input type="number" id="start_x" class="form-control" value="1" min="1" max="5">
            </div>
            <div class="form-group">
              <label>Start Y (1-8)</label>
              <input type="number" id="start_y" class="form-control" value="1" min="1" max="8">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button id="do-print" type="button" class="btn btn-primary">Cetak</button>
          </div>
        </div>
      </div>
    </div>

</div>

@endsection

@push('js-page')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(function(){
  var table = $('#table-barang').DataTable({
    ajax: '{{ route("barang.data") }}', 
    columns: [
      { data: 'checkbox', orderable:false, searchable:false },
      { data: 'id_barang' },
      { data: 'nama' },
      { data: 'harga' },
      { data: 'actions', orderable:false, searchable:false }
    ],
    createdRow: function(row, data, dataIndex){
      $(row).find('td').eq(0).addClass('text-center');
      $(row).find('td').eq(4).addClass('text-center');
    }
  });

  // open modal
  $('#print-selected').on('click', function(){
    $('#printModal').modal('show');
  });

  $('#do-print').on('click', function(){
    var ids = [];
    $('input.select-item:checked').each(function(){ ids.push($(this).val()); });
    if(ids.length === 0){ alert('Pilih minimal 1 barang'); return; }
    var start_x = $('#start_x').val();
    var start_y = $('#start_y').val();

    // submit form to print endpoint
    var form = $('<form method="POST" action="{{ route('barangs.print') }}" target="_blank"></form>');
    form.append('{{ csrf_field() }}');
    ids.forEach(function(i){ form.append('<input type="hidden" name="ids[]" value="'+i+'">'); });
    form.append('<input type="hidden" name="start_x" value="'+start_x+'">');
    form.append('<input type="hidden" name="start_y" value="'+start_y+'">');
    $('body').append(form);
    form.submit();
    $('#printModal').modal('hide');
    form.remove();
  });
});
</script>

@endpush
