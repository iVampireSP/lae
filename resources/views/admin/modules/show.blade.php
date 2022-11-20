@extends('layouts.admin')

@section('title', '模块:' . $module->name)

@section('content')
    <h3>{{ $module->name }}</h3>
    <a class="mt-3" href="{{ route('admin.modules.edit', $module) }}">编辑</a>
    <h4 class="mt-2">收益</h4>
    <div>
        <x-module-earning :module="$module" />
    </div>
@endsection
