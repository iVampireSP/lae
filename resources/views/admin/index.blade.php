@extends('layouts.admin')

@section('title', '首页')

@section('content')
    <h2>今年的收益</h2>

    @foreach($modules as $module)
        @php($years = $module->calculate())

        <h3 class="mb-3">{{ $module->name }}</h3>
        <div class="mt-3">
            <x-module-earning :module="$module"/>
        </div>

    @endforeach


    {{ $modules->links() }}
@endsection
