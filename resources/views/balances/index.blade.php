@extends('layouts.app')

@section('title', '余额')

@section('content')
    <h2>余额</h2>
    <p>您的余额: {{ $balance }} 元 <small class="text-danger"><i class="bi bi-exclamation-circle"></i> 余额不可用于提现</small></p>


    <h2>充值余额</h2>
    <form name="charge" method="POST" target="_blank" action="{{ route('balances.store') }}"
          onsubmit="return confirm('请注意: 由于计费方式的特殊性，我们不支持退款，请合理充值。')">
        @csrf
        <input type="number" id="amount" name="amount" value="10" min="1" max="1000"/>元
        <button type="submit" class="btn btn-primary">充值</button>
    </form>

    <div class="mt-2">
        <div>
            请注意: 由于计费方式的特殊性，我们不支持退款，请合理充值。
            <br/>
            <a
                target="_blank"
                href="https://forum.laecloud.com/d/4-wo-chong-zhi-hou-jin-e-mei-you-li-ji-dao-zhang"
            >
                必看! 充值后金额没有立即到账的原因。
            </a>
        </div>
    </div>


    <h2 class="mt-3">充值记录</h2>
    <div class="overflow-auto">
        <table class="table">
            <thead>
            <tr>
                <th scope="col">订单号</th>
                <th scope="col">支付方式</th>
                <th scope="col">金额</th>
                <th scope="col">完成时间</th>
            </tr>
            </thead>
            <tbody>
            @foreach($balances as $b)
                <tr>
                    <td>{{ $b->order_id }}</td>
                    <td>
                        <x-payment :payment="$b->payment"></x-payment>
                    </td>
                    <td>
                        {{ $b->amount }}
                    </td>
                    <td>
                        {{ $b->paid_at }}
                    </td>
                </tr>
            @endforeach

            </tbody>
        </table>
    </div>

    {{ $balances->links() }}


    {{-- <script>
        let rate = {{ $drops_rate }};
        let to_drops = document.querySelector('#to_drops')
        let amount = document.querySelector('#amount')

        amount.addEventListener('change', (el) => calc(el.target))

        function calc(el) {
            to_drops.innerText = (el.value * rate)
        }

        calc(amount)


    </script> --}}

@endsection
