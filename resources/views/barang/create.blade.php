@extends('layouts.master')

@section('content')
<div class="container">
  <h1>Tambah Barang</h1>
  <form method="POST" action="{{ route('barang.store') }}">
    @csrf
    <div class="form-group">
      <label>Nama</label>
      <input type="text" name="nama" class="form-control" value="{{ old('nama') }}">
    </div>
    <div class="form-group">
      <label>Harga</label>
      <input type="number" name="harga" class="form-control" value="{{ old('harga',0) }}">
    </div>
    <button class="btn btn-primary mt-2">Simpan</button>
  </form>
</div>
@endsection
