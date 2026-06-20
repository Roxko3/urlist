<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::livewire('/{name}', 'pages::list');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('d/dashboard', 'dashboard')->name('dashboard');
});


require __DIR__.'/settings.php';
