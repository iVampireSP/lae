<?php

namespace App\View\Components;

use App\Models\Module;
use Illuminate\View\Component;

class ModuleScript extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $modules = Module::all();
        return view('components.module-script', compact('modules'));
    }
}
