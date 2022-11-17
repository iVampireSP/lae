@extends('layouts.app')

@section('title', '交易记录')

@section('content')
    <h2>交易记录</h2>

    <a href="?type=income">收入</a>
    <a href="?type=payout">支出</a>
    <a href="?payment=transfer">转账记录</a>

    <div class="overflow-auto">
        <table class="table">
            <thead>
            <tr>
                <th scope="col">类型与模块</th>
                <th scope="col">支付方式</th>
                <th scope="col">说明</th>
                <th scope="col">入账</th>
                <th scope="col">支出</th>
                <th scope="col">余额</th>
                <th scope="col">交易时间</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($transactions as $t)
                <tr>

		    <td>
                        @if ($t->type === 'payout')
                            <span class="text-danger">
                                支出
                            </span>
                        @elseif($t->type === 'income')
                            <span class="text-success">
                                收入
                            </span>
                        @endif
                        &nbsp;
                        <span class="module_name" module="{{ $t->module_id }}">{{ $t->module_id }}</span>

                    </td>
                    <td>
                        <x-payment :payment="$t->payment"></x-payment>
                    </td>
                    <td>
                        {{ $t->description }}
                    </td>
                    <td class="text-success">
                        {{ $t->income }} 元
                        <br/>
                        {{ $t->income_drops }} Drops
                    </td>

                    <td class="text-danger">
                        {{ $t->outcome }} 元
                        <br/>
                        {{ $t->outcome_drops }} Drops
                    </td>

                    <td>
                        {{ $t->balance ?? $t->balances }} 元
                        <br/>
                        {{ $t->drops }} Drops
                    </td>
                    <td>
                        {{ $t->created_at }}
                    </td>
                </tr>
            @endforeach

            </tbody>
        </table>
    </div>

    {{ $transactions->links() }}


    <script>
        let modules = {!! $modules !!},
            display_name = "{{ config('app.display_name') }}"

        let m = {}
        modules.forEach((module) => {
            //    转换成 key value
            m[module.id] = module.name

        })

        window.onload = () => {
            document.querySelectorAll('.module_name').forEach((node) => {
                let module = node.getAttribute('module')

                if (module == null || module === "") {
                    node.innerText = display_name
                } else {
                    console.log(module)
                    node.innerText = m[module] ?? '模块'
                }
            })
        }
    </script>


@endsection
