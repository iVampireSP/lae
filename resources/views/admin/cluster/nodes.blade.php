@php use Illuminate\Support\Carbon; @endphp
@extends('layouts.admin')

@section('title', '节点')

@section('content')
    <h3>Cluster Ready!</h3>
    <p>节点管理</p>

    <table class="table table-hover">
        <thead>
        <th>
            类型
        </th>
        <th>
            标识
        </th>
        <th>
            对端地址
        </th>
        <th>
            权重（可更改）
        </th>
        <th>
            上次心跳
        </th>
{{--        <th>--}}
        {{--            管理--}}
        {{--        </th>--}}
        </thead>
        <tbody>

        @foreach($nodes as $node)
            <tr>
                <td>
                    @if ($node['type'] == 'master')
                        <span class="text-success">主节点</span>
                    @elseif ($node['type'] == 'slave')
                        <span class="text-secondary">工作节点</span>
                    @elseif ($node['type'] == 'edge')
                        <span class="text-warning">边缘节点</span>
                    @endif
                </td>
                <td>
                    {{ $node['id'] }}
                </td>
                <td>
                    {{ $node['ip'] }}
                </td>
                <td>
                    <span class="editable" node-id="{{ $node['id'] }}" value=" {{ $node['weight'] ?? '' }}"></span>
                </td>
                <td>
                    @php($time = Carbon::createFromTimestamp($node['last_heartbeat']))
                    @if ($time->diffInMinutes() > 1)
                        <span class="text-danger">{{ $time->diffForHumans() }}</span>
                    @else
                        <span class="text-success">{{ $time->diffForHumans() }}</span>
                    @endif
                </td>
{{--                <td>--}}
{{--                    <a>--}}
{{--                        清除数据--}}
{{--                    </a>--}}
{{--                </td>--}}
            </tr>
        @endforeach
        </tbody>
    </table>

    <p>注意: 权重为 <span class="text-danger">0</span> 则不调度。</p>

    <script>
        let editables = document.querySelectorAll('.editable');
        editables.forEach(function (editable) {
            // fill :value
            editable.innerText = editable.getAttribute('value');

            editable.addEventListener('click', function () {
                let input = document.createElement('input');
                input.value = editable.innerText;
                input.classList.add('form-control')
                editable.innerText = '';
                editable.appendChild(input);
                input.focus();

                input.addEventListener('blur', function () {
                    editable.innerText = input.value;
                    input.remove();

                    // 不能为空，负数
                    if (input.value === '' || input.value < 0) {
                        editable.innerText = editable.getAttribute('value');
                    } else {
                        let node_id = editable.getAttribute('node-id');

                        axios.patch('nodes/' + node_id, {
                            weight: input.value
                        }).then(function () {
                            editable.setAttribute('value', input.value);
                        }).catch(function (error) {
                            editable.innerText = editable.getAttribute('value');

                            if (error.response.status === 422) {
                                let errors = error.response.data.errors;
                                let message = '';
                                for (let key in errors) {
                                    message += errors[key][0] + '\n';
                                }
                                alert(message);
                            } else {
                                alert('服务器错误');
                            }
                        });
                    }

                });

                input.addEventListener('keyup', function (e) {
                    if (e.key === 'Enter') {
                        input.blur();
                    }
                });
            });
        });

    </script>

    <style>
        .editable {
            cursor: pointer;
        }

        .editable input {
            width: 10rem;
        }
    </style>

@endsection
