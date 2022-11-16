@if ($balances->paid_at !== null)
    {{ $user->name }} 在 {{ $balance->paid_at->toDateTimeString() }} 充值了 {{ $balances->amount }} 元。
@endif
