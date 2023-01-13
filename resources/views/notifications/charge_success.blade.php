@if ($data['balance']->paid_at !== null)
    {{ $data['user']->name }} 在 {{ $data['balance']->paid_at->toDateTimeString() }} 通过 <x-payment :payment="$data['balance']->payment" /> 充值了 {{ $data['balance']->amount }} 元。
@endif
