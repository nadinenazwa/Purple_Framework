@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4>Tag Harga UMKM</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('barangs.print') }}" id="print-form">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Start X (Kolom 1-5)</label>
                        <input type="number" name="start_x" class="form-control" min="1" max="5" value="1" required>
                    </div>
                    <div class="col-md-3">
                        <label>Start Y (Baris 1-8)</label>
                        <input type="number" name="start_y" class="form-control" min="1" max="8" value="1" required>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <a href="{{ route('barang.create') }}" class="btn btn-success mr-2" id="add-button">Tambah Barang</a>
                        <button type="submit" class="btn btn-primary" id="print-button">Cetak Label</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped" id="barangs-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Harga</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($barangs as $barang)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $barang->id_barang }}"></td>
                                <td>{{ $barang->id_barang }}</td>
                                <td>{{ $barang->nama }}</td>
                                <td>{{ number_format($barang->harga, 0, ',', '.') }}</td>
                                <td>
                                    <a href="{{ route('barang.edit', $barang->id_barang) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <button type="button" class="btn btn-sm btn-danger delete-button" data-url="{{ route('barang.destroy', $barang->id_barang) }}" data-name="{{ $barang->nama }}">Hapus</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.getElementById('select-all').addEventListener('change', function(e){
        const checked = e.target.checked;
        document.querySelectorAll('input[name="selected_ids[]"]').forEach(cb => cb.checked = checked);
    });

    // Optional: initialize datatables if present
    if (window.jQuery && $.fn.dataTable) {
        $('#barangs-table').DataTable();
    }

    // Delete button handler: create and submit a DELETE form dynamically
    (function(){
        const csrf = '{{ csrf_token() }}';
        document.addEventListener('click', function(e){
            const btn = e.target.closest('.delete-button');
            if (!btn) return;
            const url = btn.getAttribute('data-url');
            const name = btn.getAttribute('data-name') || 'item';
            if (!url) return;
            if (!confirm('Hapus "' + name + '"? Data yang dihapus tidak dapat dikembalikan.')) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            form.style.display = 'none';

            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = csrf;
            form.appendChild(tokenInput);

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        });
    })();
</script>
@endsection
