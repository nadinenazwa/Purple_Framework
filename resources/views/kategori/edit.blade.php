@extends('layouts.master')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-gradient-primary text-white">Edit Kategori</div>
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

                    @php
                        $katKey = data_get($kategori, 'id_kategori') ?? data_get($kategori, 'id') ?? (is_object($kategori) && method_exists($kategori, 'getKey') ? $kategori->getKey() : null);
                    @endphp
                    @if($katKey)
                        <form action="{{ route('kategori.update', $katKey) }}" method="POST">
                    @else
                        <form>
                    @endif
                        @csrf
                        @method('PUT')
                        <div class="form-group mb-3">
                            <label>Nama</label>
                            <input type="text" name="nama_kategori" class="form-control" value="{{ old('nama_kategori', $kategori->nama_kategori) }}" required>
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
