@extends('layouts.master')
@section('content')
<style>
.aq-tiket-wrapper{display:flex;align-items:center;justify-content:center;min-height:60vh;padding:20px}
.aq-ticket{background:#fff;border-radius:20px;max-width:380px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.15);overflow:hidden;text-align:center}
.aq-ticket-top{background:linear-gradient(135deg,#7c4dff,#448aff);padding:28px 20px}
.aq-ticket-top h2{color:#fff;font-size:14px;font-weight:600;opacity:.85;margin-bottom:4px}
.aq-ticket-nomor{font-size:6rem;font-weight:800;color:#fff;line-height:1;letter-spacing:-2px}
.aq-ticket-body{padding:24px 28px}
.aq-ticket-nama{font-size:1.25rem;font-weight:700;color:#333;margin-bottom:8px}
.aq-ticket-info{font-size:13px;color:#aaa;margin-bottom:20px}
.aq-status-pill{display:inline-block;padding:6px 18px;border-radius:20px;font-size:12px;font-weight:700}
.aq-status-pill.menunggu{background:#e3f2fd;color:#1565c0}
.aq-status-pill.dipanggil{background:#e8f5e9;color:#2e7d32}
.aq-status-pill.terlambat{background:#fff8e1;color:#f57f17}
.aq-status-pill.selesai{background:#f5f5f5;color:#757575}
.aq-divider{border:none;border-top:2px dashed #eee;margin:20px 0}
.aq-footer-note{font-size:12px;color:#bbb;padding-bottom:8px}
.aq-btn-back{display:inline-block;margin-top:10px;padding:10px 24px;background:linear-gradient(135deg,#7c4dff,#448aff);color:#fff;text-decoration:none;border-radius:8px;font-weight:600;font-size:13px;transition:opacity .2s}
.aq-btn-back:hover{opacity:.85;color:#fff}
.aq-btn-papan{background:linear-gradient(135deg,#00c853,#69f0ae)}
</style>

<div class="row">
  <div class="col-12">
    <div class="page-header">
      <h3 class="page-title"><span class="page-title-icon bg-gradient-primary text-white me-2"><i class="mdi mdi-ticket-confirmation"></i></span>Tiket Antrian</h3>
      <nav aria-label="breadcrumb">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('antrian.admin') }}">Antrian</a></li>
          <li class="breadcrumb-item active" aria-current="page">Tiket #{{ str_pad($antrian->nomor, 3, '0', STR_PAD_LEFT) }}</li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<div class="aq-tiket-wrapper">
  <div class="aq-ticket">
    <div class="aq-ticket-top">
      <h2>NOMOR ANTRIAN ANDA</h2>
      <div class="aq-ticket-nomor">{{ str_pad($antrian->nomor, 3, '0', STR_PAD_LEFT) }}</div>
    </div>
    <div class="aq-ticket-body">
      <div class="aq-ticket-nama">{{ $antrian->nama }}</div>
      <div class="aq-ticket-info">Terdaftar: {{ $antrian->created_at->format('d M Y, H:i') }}</div>
      <span class="aq-status-pill {{ $antrian->status }}">
        {{ strtoupper($antrian->status) }}
      </span>
      <hr class="aq-divider">
      <p class="aq-footer-note">Harap perhatikan papan antrian dan tetap berada di area tunggu.</p>
      <a href="{{ route('antrian.guest') }}" class="aq-btn-back">← Daftar Lagi</a>
      &nbsp;
      <a href="{{ route('antrian.papan') }}" target="_blank" class="aq-btn-back aq-btn-papan">📺 Papan</a>
    </div>
  </div>
</div>
@endsection
