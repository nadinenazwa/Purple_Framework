@extends('layouts.master')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-gradient-primary text-white">Tambah Kategori</div>
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

                    <form action="{{ route('kategori.store') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label>Nama</label>
                            <input type="text" name="nama_kategori" class="form-control" value="{{ old('nama_kategori') }}" required>
                        </div>
                        <button class="btn btn-gradient-primary">Simpan</button>
                        <a href="{{ route('kategori.index') }}" class="btn btn-light">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
