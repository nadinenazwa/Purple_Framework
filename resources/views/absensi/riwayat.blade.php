@extends('layouts.master')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="page-header">
      <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
          <i class="mdi mdi-history"></i>
        </span>
        Riwayat Absensi
      </h3>
      <nav aria-label="breadcrumb">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
          <li class="breadcrumb-item active">Riwayat Absensi</li>
        </ul>
      </nav>
    </div>
  </div>
</div>

{{-- Summary --}}
<div class="row mb-4">
  <div class="col-md-4 col-sm-6 mb-3">
    <div class="card shadow-sm border-0">
      <div class="card-body text-center py-3">
        <h2 class="fw-bold text-primary mb-0">{{ $totalAbsensi }}</h2>
        <small class="text-muted">Total Absensi</small>
      </div>
    </div>
  </div>
</div>

{{-- Filter --}}
<div class="row mb-4">
  <div class="col-12">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <h5 class="fw-bold mb-3"><i class="mdi mdi-filter-variant text-primary me-2"></i>Filter Absensi</h5>
        <form method="GET" action="{{ route('absensi.riwayat') }}">
          <div class="row g-3 align-items-end">
            <div class="col-md-6 col-sm-6">
              <label class="form-label fw-semibold">Tanggal</label>
              <input type="date" name="tanggal" class="form-control" value="{{ request('tanggal') }}">
            </div>
            <div class="col-md-6 col-sm-6">
              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-gradient-primary flex-fill">
                  <i class="mdi mdi-magnify me-1"></i> Filter
                </button>
                <a href="{{ route('absensi.riwayat') }}" class="btn btn-light">
                  <i class="mdi mdi-refresh"></i>
                </a>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- Table --}}
<div class="row">
  <div class="col-12">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        @if($absensis->count() > 0)
        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>NIM</th>
                <th>Nama</th>
                <th>Waktu</th>
              </tr>
            </thead>
            <tbody>
              @foreach($absensis as $i => $absensi)
              <tr>
                <td class="text-muted">{{ $i + 1 }}</td>
                <td><code class="fw-bold">{{ $absensi->mahasiswa->nim ?? '-' }}</code></td>
                <td>{{ $absensi->mahasiswa->nama ?? '-' }}</td>
                <td>
                  <span>{{ $absensi->waktu->format('d M Y') }}</span>
                  <br>
                  <small class="text-muted">{{ $absensi->waktu->format('H:i:s') }}</small>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <div class="text-center py-5 text-muted">
          <i class="mdi mdi-inbox-arrow-down" style="font-size: 2.5rem; opacity: 0.4;"></i>
          <p class="mt-2 mb-0">Belum ada data absensi</p>
          <small>Data akan muncul setelah mahasiswa melakukan scan NFC</small>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
