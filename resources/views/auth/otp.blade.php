@extends('layouts.auth')

@section('content')
<div class="card mx-auto" style="max-width:420px;">
  <div class="card-body p-4">
    <div class="text-center mb-4">
      <img src="{{ asset('assets/images/logo.svg') }}" alt="logo" style="height:40px;" />
    </div>

    <form method="POST" action="{{ route('otp.verify') }}">
      @csrf
      <div class="form-group mb-3">
        <label for="otp">Masukkan Kode OTP (6 Digit)</label>
        <input id="otp" name="otp" type="text" class="form-control" maxlength="6" required autofocus>
        @error('otp')<div class="text-danger mt-2">{{ $message }}</div>@enderror
      </div>
      <button type="submit" class="btn btn-gradient-primary w-100">Verifikasi</button>
    </form>
  </div>
</div>
@endsection
