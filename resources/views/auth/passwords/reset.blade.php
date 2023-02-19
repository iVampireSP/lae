@extends('layouts.app')

@section('content')

    <h2>{{ __('Reset Password') }}</h2>


    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="form-group">
            <label for="email" class="text-left ml-0">邮箱</label>
            <input id="email" type="text"
                   class="form-control @error('email') is-invalid @enderror" name="email"
                   value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>

            @error('email')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>

        <div class="form-group mt-2">
            <label for="password" class="text-left ml-0">{{ __('Password') }}</label>


            <input id="password" type="password"
                   class="form-control @error('password') is-invalid @enderror" name="password"
                   required autocomplete="new-password">

            @error('password')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror

        </div>

        <div class="form-group mt-2">
            <label for="password-confirm" class="text-left ml-0">{{ __('Confirm Password') }}</label>

            <input id="password-confirm" type="password" class="form-control"
                   name="password_confirmation" required autocomplete="new-password">
        </div>

        <button type="submit" class="btn btn-primary mt-3">
            {{ __('Reset Password') }}
        </button>

    </form>

@endsection
