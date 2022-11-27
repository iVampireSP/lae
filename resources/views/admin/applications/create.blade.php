@extends('layouts.admin')

@section('title', '新建应用')

@section('content')
    <h3>新建应用</h3>

    <form method="POST" action="{{ route('admin.applications.store')}}">
        @csrf

        <div class="form-group">
            <label for="name">名称</label>
            <input type="text" class="form-control" id="name" name="name">
        </div>

        <div class="form-group mt-1">
            <label for="description">描述</label>
            <input type="text" class="form-control" id="description" name="description">
        </div>

        <div class="form-group mt-1">
            <label for="api_token">密钥</label>
            <input type="text" class="form-control" id="api_token" name="api_token" required autocomplete="off">
            <span class="form-text text-muted">密钥应该保密，并且还应该是唯一的。</span>

        </div>

        {{--  随机密钥生成   --}}
        <div class="form-group mt-1">
            <button type="button" class="btn btn-primary" id="generate-api-token" onclick="fillApiToken()">生成密钥
            </button>
        </div>


        <button type="submit" class="btn btn-primary mt-3">提交</button>
    </form>


    <script>
        function randomString(length) {
            let chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            let result = '';
            for (let i = length; i > 0; --i) result += chars[Math.floor(Math.random() * chars.length)];
            return result;
        }

        function fillApiToken() {

            document.getElementById('api_token').value = randomString(32);
        }
    </script>

@endsection
