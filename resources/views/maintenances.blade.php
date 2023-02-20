@extends('layouts.app')

@section('title', '维护计划')

@section('content')
    <h3>维护计划</h3>
    @if (count($maintenances) > 0)
        <div class="overflow-auto">
            <table class="table table-hover">
                <thead>
                <th>名称</th>
                <th>内容</th>
                <th>模块</th>
                <th>开始于</th>
                <th>结束于</th>
                </thead>

                <tbody>
                @foreach ($maintenances as $m)
                    <tr>
                        <td>
                            {{ $m->name }}
                        </td>

                        <td>
                            <textarea class="form-control border-0" cols="3" readonly
                                      aria-label="维护内容">{{ $m->content }}</textarea>
                        </td>

                        <td>
                            {{ $m->module?->name }}
                        </td>

                        <td>
                            {{ $m->start_at }}
                        </td>

                        <td>
                            {{ $m->end_at }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p>
            暂无维护计划。
        </p>
    @endif
@endsection
