@extends('layouts.app')

@section('content')
    <h2>{{ __('Reset Password') }}</h2>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="form-group">
            <label for="email" class="text-left ml-0">邮箱</label>
            <input id="email" type="text"
                   class="form-control @error('email') is-invalid @enderror" name="email"
                   value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="您的邮箱地址">

            @error('email')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>

        <button class="btn btn-primary btn-block mt-3" type="submit">
            {{ __('Send Password Reset Link') }}
        </button>

    </form>

    @auth
        <script>
            const input = document.getElementById('email');
            input.value = "{{ auth()->user()->email }}";
            input.readOnly = true;
        </script>
    @endauth
@endsection
