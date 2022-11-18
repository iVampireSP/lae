<?php

namespace App\View\Components;

use Illuminate\View\Component;

class WorkOrderStatus extends Component
{
    public $status = null;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($status)
    {
        //

        $this->status = $status;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $this->status = match ($this->status) {
            'pending' => '推送中',
            'open' => '开启',
            'user_read' => '用户已读',
            'user_replied' => '用户已回复',
            'replied' => '已回复',
            'read' => '已读',
            'on_hold' => '挂起',
            'in_progress' => '处理中',
            'closed' => '结单',
            default => $this->status,
        };

        return view('components.work-order-status', ['status' => $this->status]);
    }
}
