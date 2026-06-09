@extends('layouts.master')
@section('content')
<style>
.aq-guest-wrapper{display:flex;align-items:center;justify-content:center;min-height:60vh;padding:20px}
.aq-guest-card{background:#fff;border-radius:20px;padding:40px;max-width:420px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.12)}
.aq-guest-card .logo{text-align:center;margin-bottom:28px}
.aq-guest-card .logo h1{font-size:2rem;font-weight:800;color:#5c35cc;margin-bottom:4px}
.aq-guest-card .logo p{color:#888;font-size:14px}
.aq-guest-card .flash{padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:14px;font-weight:600}
.aq-guest-card .flash.success{background:#e8f5e9;color:#2e7d32;border-left:4px solid #00c853}
.aq-guest-card .flash.error{background:#fce4ec;color:#c62828;border-left:4px solid #f44336}
.aq-guest-card label{display:block;font-weight:600;margin-bottom:6px;color:#444;font-size:14px}
.aq-guest-card input[type="text"]{width:100%;padding:12px 16px;border:2px solid #e0e0e0;border-radius:10px;font-size:15px;font-family:inherit;transition:border .2s;outline:none}
.aq-guest-card input[type="text"]:focus{border-color:#7c4dff}
.aq-guest-card .err{color:#c62828;font-size:12px;margin-top:4px}
.aq-guest-card .btn-antrian{width:100%;padding:14px;background:linear-gradient(135deg,#7c4dff,#448aff);color:#fff;border:none;border-radius:10px;font-size:16px;font-weight:700;cursor:pointer;margin-top:16px;transition:opacity .2s}
.aq-guest-card .btn-antrian:hover{opacity:.88}
.aq-guest-card .links{text-align:center;margin-top:20px;font-size:13px;color:#888}
.aq-guest-card .links a{color:#7c4dff;text-decoration:none;font-weight:600}
</style>

<div class="row">
  <div class="col-12">
    <div class="page-header">
      <h3 class="page-title"><span class="page-title-icon bg-gradient-primary text-white me-2"><i class="mdi mdi-ticket-account"></i></span>Daftar Antrian</h3>
      <nav aria-label="breadcrumb">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('antrian.admin') }}">Antrian</a></li>
          <li class="breadcrumb-item active" aria-current="page">Daftar</li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<div class="aq-guest-wrapper">
  <div class="aq-guest-card">
    <div class="logo">
      <h1>🎫 Antrian</h1>
      <p>Daftarkan nama Anda untuk mendapat nomor antrian</p>
    </div>

    @if(session('success'))
      <div class="flash success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
      <div class="flash error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('antrian.guest.store') }}">
      @csrf
      <div style="margin-bottom:16px">
        <label for="nama">Nama Lengkap</label>
        <input type="text" id="nama" name="nama" value="{{ old('nama') }}"
               placeholder="Masukkan nama Anda" required autofocus>
        @error('nama')<p class="err">{{ $message }}</p>@enderror
      </div>
      <button type="submit" class="btn-antrian">Ambil Nomor Antrian →</button>
    </form>

    <div class="links">
      <a href="{{ route('antrian.papan') }}" target="_blank">📺 Lihat Papan Antrian</a>
    </div>
  </div>
</div>
@endsection
