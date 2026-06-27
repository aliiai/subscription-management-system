<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : view('landing');
});

Route::get('/demo', function () {
    return view('demo');
});

require __DIR__.'/auth.php';
