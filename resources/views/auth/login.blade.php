@extends('layouts.auth')

@section('content')
<div class="card mx-auto" style="max-width:420px;">
  <div class="card-body p-4">
    <div class="text-center mb-4">
      <img src="{{ asset('assets/images/logo.svg') }}" alt="logo" style="height:40px;" />
    </div>

    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
      @csrf
      <div class="form-group mb-3">
        <label for="email">Email</label>
        <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus>
      </div>
      <div class="form-group mb-3">
        <label for="password">Password</label>
        <input id="password" type="password" class="form-control" name="password" required>
      </div>
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="form-check">
          <input type="checkbox" name="remember" id="remember" class="form-check-input">
          <label class="form-check-label" for="remember">Remember me</label>
        </div>
        <div>
          @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="text-muted">Forgot?</a>
          @endif
        </div>
      </div>
      <button type="submit" class="btn btn-gradient-primary w-100">Login</button>
    </form>

    <hr>
    <div class="text-center mt-3">
      <a href="{{ route('auth.google') }}" class="btn btn-outline-secondary">Login with Google</a>
    </div>
  </div>
</div>
@endsection
