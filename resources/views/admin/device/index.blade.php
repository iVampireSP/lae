@extends('layouts.admin')

@section('title', '设备')

@section('content')
    <h3>物联设备</h3>
    <p>这里列出了当前连接到 MQTT 的设备。</p>

    <table class="table table-hover">
        <thead>
        <th>
            Client ID
        </th>
        <th>
            用户 ID
        </th>
        <th>
            节点
        </th>
        <th>
            协议
        </th>
        <th>
            IP 地址
        </th>
        <th>
            参数
        </th>
        <th>
            订阅数
        </th>
        <th>
            踢出
        </th>
        </thead>
        <tbody>

        @foreach($clients as $c)
            <tr>
                <td>
                    {{ $c['clientid'] }}
                </td>
                <td>
                    <a href="?username={{ $c['username'] }}">{{ $c['username'] }}</a>
                </td>
                <td>
                    {{ $c['node'] }}
                </td>
                <td>
                    {{ $c['proto_name'] . ' v' . $c['proto_ver'] }}
                </td>
                <td>
                    {{ $c['ip_address'] }}
                </td>
                <td>
                    @if ($c['clean_start'])
                        <span class="badge text-success">干净启动</span>
                    @endif
                    @if ($c['recv_oct'])
                        <br/>
                        <span class="badge text-success">接收字节: {{ $c['recv_oct'] }}</span>
                    @endif
                    @if ($c['send_oct'])
                        <br/>
                        <span class="badge text-success">发送字节: {{ $c['send_oct'] }}</span>
                    @endif
                </td>
                <td>
                    @if ($c['subscriptions_cnt'] > 0)
                        <span class="text-success">{{ $c['subscriptions_cnt'] }} 个</span>
                    @else
                        <span class="text-danger">没有订阅</span>
                    @endif
                </td>
                <td>
                    <form action="{{ route('admin.devices.destroy') }}" method="post">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="client_id" value="{{ $c['clientid'] }}">
                        <button type="submit" class="btn btn-danger btn-sm">踢出</button>
                    </form>

                    <form class="mt-2" action="{{ route('admin.devices.destroy') }}" method="post"
                          onsubmit="return confirm('将踢出此模块以及它的所有客户端。')">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="username" value="{{ $c['username'] }}">
                        <input type="hidden" name="like_username" value="1"/>
                        <button type="submit" class="btn btn-danger btn-sm">踢出所有</button>
                    </form>

                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $clients->links() }}

@endsection
