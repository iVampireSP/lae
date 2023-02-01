@if ($data['balance']->paid_at !== null)
    {{ $data['user']->name }} 在 {{ $data['balance']->paid_at->toDateTimeString() }} 充值了 {{ $data['balance']->amount }} 元，使用了
    <x-payment :payment="$data['balance']->payment"/>
@endif
