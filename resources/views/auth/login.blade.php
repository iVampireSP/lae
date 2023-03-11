@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-center align-items-center h-screen" style="height: 60vh">
        <div class="text-center">
            <span style="font-size: 10rem">
                <i class="bi bi-person-circle" id="main-icon"></i>

            </span>

            <h2 id="form-title">使用 LoliArt Account 登录。</h2>

            <a class="btn btn-primary" href="{{route('login')}}">使用 LoliArt Account 登录</a>



        </div>
    </div>



@endsection
