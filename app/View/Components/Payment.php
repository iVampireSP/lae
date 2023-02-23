<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Payment extends Component
{
    public ?string $payment = null;

    public function __construct(string|null $payment)
    {
        $this->payment = $payment;
    }

    public function render(): string
    {
        $this->payment = match ($this->payment) {
            'alipay' => '支付宝',
            'wechat', 'wepay' => '微信支付',
            'drops' => 'Drops',
            'balance', 'balances' => '余额',
            'module_balance', => '模块余额',
            'unfreeze' => '解冻',
            'freeze' => '冻结',
            'console' => '控制台',
            'transfer' => '转账',
            'affiliate' => '推介',
            default => $this->payment,
        };

        return $this->payment;
    }
}
