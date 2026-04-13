@extends('layouts.master')

@section('content')
<div class="row justify-content-center">
  <div class="col-lg-4 col-md-6 d-flex align-items-center min-vh-100">
    <div class="card w-100">
      <div class="card-body p-4">
        <div class="text-center mb-4">
          <img src="{{ asset('assets/images/logo.svg') }}" alt="logo" style="height:40px;" />
        </div>

        <form method="POST" action="{{ route('register') }}">
          @csrf
          <div class="form-group mb-3">
            <label for="name">Name</label>
            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="form-group mb-3">
            <label for="email">Email</label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="form-group mb-3">
            <label for="password">Password</label>
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="form-group mb-3">
            <label for="password-confirm">Confirm Password</label>
            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
          </div>

          <button type="submit" class="btn btn-gradient-primary w-100">{{ __('Register') }}</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
