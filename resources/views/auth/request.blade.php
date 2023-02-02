@extends('layouts.app')

@section('title', '认证')

@section('content')

    <h3><code>{{ $data['module']['name'] }}</code> 想要获取你的用户信息。</h3>

    <p>{{ $data['description'] }}</p>

    @auth('web')

        <form method="POST" action="{{ route('auth_request.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $data['token'] }}">
            <button type="submit" class="btn btn-primary">同意</button>
        </form>

    @endauth

@endsection
