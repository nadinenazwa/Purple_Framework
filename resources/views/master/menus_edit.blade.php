@extends('layouts.master')

@section('content')
<div class="container">
    <h3>Edit Menu</h3>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('master.menus.update', $menu->id) }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Nama</label>
            <input type="text" name="nama" class="form-control" value="{{ old('nama', $menu->nama) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Harga</label>
            <input type="number" step="0.01" name="harga" class="form-control" value="{{ old('harga', $menu->harga) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea name="deskripsi" class="form-control">{{ old('deskripsi', $menu->deskripsi) }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Vendor</label>
            <select name="vendor_id" class="form-select">
                <option value="">-- Pilih Vendor --</option>
                @foreach($vendors as $v)
                    <option value="{{ $v->id }}" @if(old('vendor_id', $menu->vendor_id) == $v->id) selected @endif>{{ $v->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Gambar (opsional)</label>
            @if($menu->gambar)
                <div class="mb-2"><img src="{{ asset('storage/' . $menu->gambar) }}" alt="gambar" style="max-width:150px"></div>
            @endif
            <input type="file" name="gambar" class="form-control">
        </div>

        <div class="mb-3">
            <button class="btn btn-primary">Simpan Perubahan</button>
            <a href="{{ route('master.menus') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
