@extends('layouts.admin')

@section('title', '事件广播')

@section('content')
    <h3>事件广播</h3>
    <p>对集群广播命令。</p>


    <h4>重启</h4>

    <form method="POST" action="{{ route('admin.cluster.events.send') }}" class="d-inline">
        @csrf
        <input type="hidden" name="restart" value="web" />
        <button type="submit" class="btn btn-primary">Web 服务</button>
    </form>

    <form method="POST" action="{{ route('admin.cluster.events.send') }}" class="d-inline">
        @csrf
        <input type="hidden" name="restart" value="queue" />
        <button type="submit" class="btn btn-primary">队列服务</button>
    </form>

@endsection
