@extends('layouts.master')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-4 col-md-6 d-flex align-items-center min-vh-100">
        <div class="card w-100">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <img src="{{ asset('assets/images/logo.svg') }}" alt="logo" style="height:40px;" />
                </div>

                @if (session('status'))
                    <div class="alert alert-success" role="alert">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="email">Email</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-gradient-primary w-100">{{ __('Send Password Reset Link') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
