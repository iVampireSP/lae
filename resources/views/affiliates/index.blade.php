@extends('layouts.app')

@section('title', '推介计划')

@section('content')

    <h3>推介计划</h3>

    <p>
        访问量：{{ $affiliate->visits }}
    </p>
    <p>
        盈利：{{ $affiliate->revenue }} 元
    </p>

    <p>推介 URL: {{ \Illuminate\Support\Facades\URL::route('affiliates.show', $affiliate->uuid) }}</p>

    <h3>用户列表</h3>
    @php($count = $affiliateUsers->count())
    @if ($count)
        <table class="table table-striped">
            <thead>
            <tr>
                <th scope="col">用户名</th>
                <th scope="col">盈利</th>
                <th scope="col">注册时间</th>
            </tr>
            </thead>
            <tbody>
            @foreach($affiliateUsers as $user)
                <tr>
                    <td>{{ $user->user->name }}</td>
                    <td>{{ $user->revenue }} 元</td>
                    <td>{{ $user->created_at }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $affiliateUsers->links() }}
    @else
        <p>您还没有推介用户。</p>
    @endif

    <h4>离开推介计划</h4>
    <form method="post" action="{{ route('affiliates.destroy', $affiliate->id) }}"
          onclick="return confirm('删除后将不会获得收益，推介数据也会被删除。')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">删除推介计划</button>
    </form>

@endsection
