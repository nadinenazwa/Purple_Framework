@extends('layouts.master')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center bg-gradient-primary text-white">
                    <h4 class="mb-0">Daftar Kategori</h4>
                    <a href="{{ route('kategori.create') }}" class="btn btn-light">Tambah Kategori</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width:60px;">#</th>
                                    <th>Nama</th>
                                    <th style="width:180px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kategoris as $kategori)
                                    <tr>
                                        <td>{{ $loop->iteration + ($kategoris->firstItem() ? $kategoris->firstItem() - 1 : 0) }}</td>
                                        <td>{{ data_get($kategori, 'nama_kategori') ?? data_get($kategori, 'nama') }}</td>
                                        <td>
                                            @php
                                                $katKey = data_get($kategori, 'id_kategori') ?? data_get($kategori, 'id') ?? (is_object($kategori) && method_exists($kategori, 'getKey') ? $kategori->getKey() : null);
                                            @endphp
                                            @if($katKey)
                                                <a href="{{ route('kategori.edit', $katKey) }}" class="btn btn-sm btn-gradient-primary">Edit</a>
                                                <form action="{{ route('kategori.destroy', $katKey) }}" method="POST" style="display:inline-block;">
                                            @else
                                                <a href="#" class="btn btn-sm btn-primary disabled" onclick="return false;">Edit</a>
                                                <form style="display:inline-block;">
                                            @endif
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus kategori ini?')">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Belum ada data kategori.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center">
                        {{ $kategoris->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
