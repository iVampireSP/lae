<?php

namespace App\View\Components;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class HostStatus extends Component
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
     * @return Application|Factory|View
     */
    public function render()
    {
        return view('components.host-status', ['status' => $this->status]);
    }
}
