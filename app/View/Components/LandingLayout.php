<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class LandingLayout extends Component
{
    /**
     * Judul halaman (dipakai untuk <title> dan share metadata).
     */
    public ?string $title;

    /**
     * Deskripsi halaman (dipakai untuk meta description).
     */
    public ?string $description;

    public function __construct(?string $title = null, ?string $description = null)
    {
        $this->title = $title;
        $this->description = $description;
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.landing');
    }
}
