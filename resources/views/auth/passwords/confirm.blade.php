@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-center align-items-center h-screen" style="height:60vh">
        <div class="text-center">
            <span style="font-size: 10rem">
                <i class="bi bi-key"></i>
            </span>
            <h2>{{ __('Confirm Password') }}</h2>
            {{ __('Please confirm your password before continuing.') }}
            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <div class="form-group mt-2">
                    <input id="password" type="password"
                           class="form-control text-center @error('password') is-invalid @enderror" name="password"
                           value="{{ old('password') }}" required autofocus placeholder="密码" aria-label="密码">

                    @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <button class="btn btn-primary btn-block mt-3" type="submit">
                    {{ __('Confirm Password') }}
                </button>

            </form>
        </div>
    </div>

@endsection
