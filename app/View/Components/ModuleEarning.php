<?php

namespace App\View\Components;

use App\Models\Module;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ModuleEarning extends Component
{
    private Module $module;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View
     */
    public function render(): View
    {
        $years = $this->module->calculate();
        return view('components.module-earning', compact('years'));
    }
}
