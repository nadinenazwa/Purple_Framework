@extends('absensi.layout')

@section('title', 'Riwayat Absensi')

@section('content')
<div class="page-header">
    <h1>📋 Riwayat Absensi</h1>
    <p>Lihat seluruh catatan kehadiran mahasiswa</p>
</div>

{{-- Summary Cards --}}
@if($totalHadir->count() > 0)
<div class="summary-grid">
    @foreach($totalHadir as $th)
    <div class="summary-card">
        <div class="count">{{ $th['total'] }}</div>
        <div class="label">Hadir — {{ $th['nama'] }}</div>
    </div>
    @endforeach
</div>
@endif

{{-- Filter --}}
<div class="glass-card">
    <div class="card-title">
        <span class="icon blue">🔍</span>
        Filter Absensi
    </div>
    <form method="GET" action="{{ route('absensi.riwayat') }}">
        <div class="filter-bar">
            <select name="matakuliah_id" class="form-select">
                <option value="">Semua Matakuliah</option>
                @foreach($matakuliahs as $mk)
                    <option value="{{ $mk['id'] }}" {{ request('matakuliah_id') == $mk['id'] ? 'selected' : '' }}>
                        {{ $mk['kode'] }} — {{ $mk['nama'] }}
                    </option>
                @endforeach
            </select>
            <input type="date" name="tanggal" class="form-input" value="{{ request('tanggal') }}" placeholder="Tanggal">
            <select name="status" class="form-select">
                <option value="">Semua Status</option>
                <option value="hadir" {{ request('status') == 'hadir' ? 'selected' : '' }}>Hadir</option>
                <option value="terlambat" {{ request('status') == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
            </select>
            <button type="submit" class="btn btn-primary">🔍 Filter</button>
            <a href="{{ route('absensi.riwayat') }}" class="btn btn-outline">↺ Reset</a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="glass-card" style="padding: 0; overflow: hidden;">
    @if($absensis->count() > 0)
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>NIM</th>
                    <th>Nama</th>
                    <th>Matakuliah</th>
                    <th>Waktu</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($absensis as $i => $absensi)
                <tr>
                    <td style="color: var(--text-muted);">{{ $i + 1 }}</td>
                    <td style="font-family: monospace; font-weight: 600;">{{ $absensi['nim'] }}</td>
                    <td>{{ $absensi['nama_mahasiswa'] }}</td>
                    <td>
                        <span style="color: var(--accent-cyan);">{{ $absensi['kode_matakuliah'] }}</span>
                        <br>
                        <small style="color: var(--text-muted);">{{ $absensi['nama_matakuliah'] }}</small>
                    </td>
                    <td>
                        <span>{{ $absensi['waktu_carbon']->format('d M Y') }}</span>
                        <br>
                        <small style="color: var(--text-muted);">{{ $absensi['waktu_carbon']->format('H:i:s') }}</small>
                    </td>
                    <td>
                        @if($absensi['status'] === 'hadir')
                            <span class="badge badge-hadir">HADIR</span>
                        @else
                            <span class="badge badge-terlambat">TERLAMBAT</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty-state">
        <div class="icon">📭</div>
        <p>Belum ada data absensi</p>
        <p style="font-size: 0.8rem; margin-top: 4px;">Data akan muncul setelah mahasiswa melakukan scan NFC</p>
    </div>
    @endif
</div>
@endsection
