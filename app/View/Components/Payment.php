<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Payment extends Component
{
    public ?string $payment = null;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(string|null $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return string
     */
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
            default => $this->payment,
        };

        return $this->payment;
    }
}
