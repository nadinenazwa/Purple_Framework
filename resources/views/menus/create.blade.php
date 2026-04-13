@extends('layouts.master')

@section('content')
<div class="container">
  <h3>Tambah Menu</h3>
  <form method="POST" action="{{ route('vendor.menus.store') }}">
    @csrf
    <div class="mb-3">
      <label class="form-label">Nama</label>
      <input name="name" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Deskripsi</label>
      <textarea name="description" class="form-control"></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Harga</label>
      <input name="price" type="number" min="0" class="form-control" required>
    </div>
    <button class="btn btn-primary">Simpan</button>
    <a href="{{ route('vendor.menus.index') }}" class="btn btn-secondary">Batal</a>
  </form>
</div>
@endsection
