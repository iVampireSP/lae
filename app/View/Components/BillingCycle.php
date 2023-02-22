<?php

namespace App\View\Components;

use Illuminate\View\Component;

class BillingCycle extends Component
{
    public string $cycle = 'dynamic';

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(null|string $cycle = 'dynamic')
    {
        $this->cycle = $cycle ?? 'dynamic';
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): string
    {
        return trans('hosts.'.$this->cycle);
    }
}
