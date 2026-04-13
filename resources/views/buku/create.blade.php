@extends('layouts.master')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-gradient-primary text-white">Tambah Buku</div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('buku.store') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label>Kode</label>
                            <input type="text" name="kode" class="form-control" value="{{ old('kode') }}" required>
                        </div>
                        <div class="form-group mb-3">
                            <label>Judul</label>
                            <input type="text" name="judul" class="form-control" value="{{ old('judul') }}" required>
                        </div>
                        <div class="form-group mb-3">
                            <label>Pengarang</label>
                            <input type="text" name="pengarang" class="form-control" value="{{ old('pengarang') }}" required>
                        </div>
                        <div class="form-group mb-3">
                            <label>Kategori</label>
                            <select name="idkategori" class="form-control" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($kategoris as $kat)
                                    <option value="{{ $kat->idkategori }}">{{ $kat->nama_kategori }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-gradient-primary">Simpan</button>
                        <a href="{{ route('buku.index') }}" class="btn btn-light">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
