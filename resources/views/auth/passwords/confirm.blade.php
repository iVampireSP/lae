@extends('layouts.app')

@section('content')
    <h2>{{ __('Confirm Password') }}</h2>

    {{ __('Please confirm your password before continuing.') }}

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="form-group mt-3">
            <label for="password" class="text-left ml-0">{{ __('Password') }}</label>
            <input id="password" type="password"
                   class="form-control @error('password') is-invalid @enderror" name="password"
                   value="{{ old('password') }}" required autofocus placeholder="密码">

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

@endsection
