@extends('layouts.master')

@section('content')
<div class="container">
  <h1>Edit Barang</h1>
  <form method="POST" action="{{ route('barang.update', ['id_barang' => $barang->id_barang ?? null]) }}">
    @csrf
    @method('PUT')
    <div class="form-group">
      <label>Nama</label>
      <input type="text" name="nama" class="form-control" value="{{ old('nama',$barang->nama) }}">
    </div>
    <div class="form-group">
      <label>Harga</label>
      <input type="number" name="harga" class="form-control" value="{{ old('harga',$barang->harga) }}">
    </div>
    <button type="submit" class="btn btn-primary mt-2">Simpan</button>
  </form>
</div>
@endsection
