<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class MarkdownEditor extends Component
{
    public string $name;

    public ?string $placeholder;

    public ?string $value;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($name, $placeholder = null, $value = null)
    {
        $this->name = $name;
        $this->placeholder = $placeholder;
        $this->value = $value;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.markdown-editor');
    }
}
