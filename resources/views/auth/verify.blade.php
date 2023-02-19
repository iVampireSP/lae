@extends('layouts.app')

@section('content')
    <h2>验证邮件地址</h2>

    @if (session('resent'))
        {{ __('A fresh verification link has been sent to your email address.') }}
    @else
        {{ __('Before proceeding, please check your email for a verification link.') }}
        {{ __('If you did not receive the email') }},
        <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
            @csrf
            <button type="submit"
                    class="btn btn-link p-0 m-0 align-baseline">{{ __('click here to request another') }}</button>
            。
        </form>

        <p>我们将会删除超过 3 天没有验证邮箱的用户。</p>
    @endif
@endsection
