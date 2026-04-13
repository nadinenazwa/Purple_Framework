@extends('layouts.master')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-4 col-md-6 d-flex align-items-center min-vh-100">
        <div class="card w-100">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <img src="{{ asset('assets/images/logo.svg') }}" alt="logo" style="height:40px;" />
                </div>

                <p>{{ __('Please confirm your password before continuing.') }}</p>

                <form method="POST" action="{{ route('password.confirm') }}">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="password">Password</label>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-gradient-primary w-100">{{ __('Confirm Password') }}</button>

                    @if (Route::has('password.request'))
                        <div class="text-center mt-3"><a href="{{ route('password.request') }}">{{ __('Forgot Your Password?') }}</a></div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
