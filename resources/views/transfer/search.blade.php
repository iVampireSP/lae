@extends('layouts.app')

@section('title', '转账')

@section('content')
    <h2>转账</h2>
    <p>将您的余额转入到其他莱云账号，并且无需对方确认。</p>
    <p>您有: {{ $balance }} 元。 </p>

    <form method="post" action="{{ route('transfer') }}" onsubmit="return beforeContinue()">
        @csrf
        <div class="form-group">
            <label for="to">转入账号(输入对方的邮箱)</label>
            <input type="text" class="form-control" id="to" name="to" placeholder="请输入对方的邮箱">
        </div>

        <div class="form-group">
            <div class="form-group">
                <label for="amount">金额</label>
                <input type="number" class="form-control" id="amount" name="amount" placeholder="请输入转账金额" min="1"
                       max="100" value="1">
            </div>

            <div class="form-group">
                <label for="description">备注</label>
                <input type="text" class="form-control" id="description" name="description" placeholder="请输入备注"
                       maxlength="100">
            </div>

            <button type="submit" class="btn btn-primary mt-3">转账</button>
        </div>
    </form>

    <script>
        function beforeContinue() {
            return true;
            if (confirm('您确定要转账吗？')) {
                if (confirm('当您确定后，您将无法取消。')) {
                    if (confirm('钱款会直接汇入到对方账户，您将无法退回。')) {
                        if (confirm('您再次确认转账给这个账号吗？')) {
                            return true;
                        }
                    }
                }
            }

            return false;
        }
    </script>

@endsection
