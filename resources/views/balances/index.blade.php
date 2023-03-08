@extends('layouts.app')

@section('title', '余额')

@section('content')
    <div class="d-flex justify-content-center align-items-center h-screen" style="height: 85vh">
        <div class="text-center">
            <h1 class="display-1">
                <small class="fs-4">余额</small>

                {{ bcadd($balance, 0, 2) }}

                <small class="fs-4">元</small>
            </h1>
            <p><small class="text-danger"><i class="bi bi-exclamation-circle"></i> 余额不可用于提现</small></p>

            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#chargeModal">
                添加余额
            </button>
        </div>


    </div>

    <!-- Modal -->
    <div class="modal fade" id="chargeModal" tabindex="-1" aria-labelledby="chargeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="chargeModalLabel">充值</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form name="charge" id="charge" method="POST" target="_blank" class="form-horizontal"
                          action="{{ route('balances.store') }}"
                          onsubmit="return confirm('请注意: 由于计费方式的特殊性和虚拟商品的特性，如果非人为的质量问题，我们不提供退款。请合理充值。')">
                        @csrf

                        <div class="form-floating mb-3">
                            <input type="number" id="amount" name="amount" value="10" min="1" max="1000"
                                   class="form-control" placeholder="输入一个整数金额">
                            <label for="amount">金额</label>
                        </div>

                        <input type="radio" name="payment" id="wechat" value="wechat" checked>
                        <label for="wechat"> <i class="bi bi-wechat"></i> 微信支付</label>

                        <input type="radio" name="payment" id="alipay" value="alipay">
                        <label for="alipay"> <i class="bi bi-alipay"></i> 支付宝</label>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal"
                            onclick="document.getElementById('charge').submit()">充值
                    </button>
                </div>
            </div>
        </div>
    </div>




    <div style="margin-top: 200px;padding-top: 50px" id="chargeLogs">
        <h2 class="mt-3">充值记录</h2>
        <div class="overflow-auto">
            <table class="table table-hover">
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
    </div>

    <script>
        @if (Request::has('page'))
            window.location.href = '#chargeLogs';
        @endif
    </script>

@endsection
