<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class LandingController extends Controller
{
    /**
     * Halaman publik (landing page) SIPKL.
     *
     * Tidak memakai middleware auth maupun role: halaman ini harus bisa
     * dibuka tamu. seluruh CTA mengarah ke route yang sudah ada
     * (route('login') / route('redirect')).
     */
    public function index(): View
    {
        return view('landing.index');
    }
}
