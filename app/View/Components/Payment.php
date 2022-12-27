<?php

namespace App\View\Components;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Payment extends Component
{
    public $payment = null;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($payment)
    {
        //
        $this->payment = $payment;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return Application|Factory|View
     */
    public function render()
    {

        $this->payment = match ($this->payment) {
            'alipay' => '支付宝',
            'wechat', 'wepay' => '微信支付',
            'drops' => 'Drops',
            'balance', 'balances' => '余额',
            'unfreeze' => '解冻',
            'freeze' => '冻结',
            'console' => '控制台',
            'transfer' => '转账',
            default => $this->payment,
        };

        return view('components.payment', ['payment' => $this->payment]);
    }
}
