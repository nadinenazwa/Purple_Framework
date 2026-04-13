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
                    <h4 class="mb-0">Daftar Buku</h4>
                    <a href="{{ route('buku.create') }}" class="btn btn-light">Tambah Buku</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Kode</th>
                                    <th>Judul</th>
                                    <th>Pengarang</th>
                                    <th>Kategori</th>
                                    <th style="width:180px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bukus as $buku)
                                    <tr>
                                        <td>{{ $buku->idbuku }}</td>
                                        <td>{{ $buku->kode }}</td>
                                        <td>{{ $buku->judul }}</td>
                                        <td>{{ $buku->pengarang }}</td>
                                        <td>{{ optional($buku->kategori)->nama_kategori }}</td>
                                        <td>
                                            <a href="{{ route('buku.edit', $buku) }}" class="btn btn-sm btn-gradient-primary">Edit</a>
                                            <form action="{{ route('buku.destroy', $buku) }}" method="POST" style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus buku ini?')">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Belum ada data buku.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center">
                        {{ $bukus->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
