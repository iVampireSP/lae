@extends('layouts.admin')

@section('title', '模块:' . $module->name)

@section('content')
    <h3>{{ $module->name }}</h3>
    <a class="mt-3" href="{{ route('admin.modules.edit', $module) }}">编辑</a>
    <h4>收益</h4>
    <div>
        <table class="table table-hover">
            <thead>
            <th>年 / 月</th>

            @for ($i = 1; $i < 13; $i++)
                <th>{{ $i }} 月</th>
            @endfor
            </thead>
            <tbody>

            @foreach ($years as $year => $months)
                <tr>
                    <td>{{ $year }}</td>
                    @for ($i = 1; $i < 13; $i++)

                        <td @if ($months[$i]['should_balance'] ?? 0 > 0) class="text-danger" @endif>{{ $months[$i]['should_balance'] ?? 0 }}
                            元
                        </td>

                    @endfor
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
