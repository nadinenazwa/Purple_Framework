@extends('layouts.master')

@section('content')
<div class="container">
  <h3>Edit Menu</h3>
  <form method="POST" action="{{ route('vendor.menus.update', $menu->id) }}">
    @csrf
    @method('PUT')
    <div class="mb-3">
      <label class="form-label">Nama</label>
      <input name="name" class="form-control" value="{{ $menu->name }}" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Deskripsi</label>
      <textarea name="description" class="form-control">{{ $menu->description }}</textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Harga</label>
      <input name="price" type="number" min="0" class="form-control" value="{{ $menu->price }}" required>
    </div>
    <button class="btn btn-primary">Simpan</button>
    <a href="{{ route('vendor.menus.index') }}" class="btn btn-secondary">Batal</a>
  </form>
</div>
@endsection
