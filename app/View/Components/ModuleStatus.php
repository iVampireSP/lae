<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ModuleStatus extends Component
{
    public ?string $status = null;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($status)
    {
        $this->status = $status;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.module-status', ['status' => $this->status]);
    }
}
