<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BasicCard extends Component
{

    public string $title;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($title)
    {
        //
        $this->title = $title;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.basic-card', [
            'title' => $this->title,
        ]);
    }
}
