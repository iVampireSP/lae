@extends('layouts.app')

@section('title', '余额')

@section('content')
    <h2>余额</h2>


    <p>您的余额: {{ $balance }} 元 </p>
    <p>Drops: {{ $drops }} </p>

    <form name="charge" method="POST" target="_blank" action="{{ route('balances.store') }}">
        @csrf
        <input type="number" name="amount" value="1" min="1" max="1000"/>
        <button type="submit" class="btn btn-primary">充值</button>
    </form>


@endsection
