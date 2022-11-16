<?php

namespace App\View\Components;

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
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {

        $this->payment = match ($this->payment) {
            'alipay' => '支付宝',
            'wechat', 'wepay' => '微信支付',
            'drops' => 'Drops',
            'balance' => '余额',
            'unfreeze' => '解冻',
            'freeze' => '冻结',
            'console' => '控制台',
            default => $this->payment,
        };

        return view('components.payment', ['payment' => $this->payment]);
    }
}
