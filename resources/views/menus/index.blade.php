@extends('layouts.master')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Menu Saya</h3>
    <a href="{{ route('vendor.menus.create') }}" class="btn btn-primary">Tambah Menu</a>
  </div>

  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

  <div class="table-responsive">
    <table class="table table-striped">
      <thead><tr><th>Nama</th><th>Harga</th><th>Aksi</th></tr></thead>
      <tbody>
        @foreach($menus as $m)
          <tr>
            <td>{{ $m->name }}</td>
            <td>{{ number_format($m->price,0,',','.') }}</td>
            <td>
              <a href="{{ route('vendor.menus.edit', $m) }}" class="btn btn-sm btn-secondary">Edit</a>
              <form action="{{ route('vendor.menus.destroy', $m) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Hapus menu ini?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-danger">Hapus</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
