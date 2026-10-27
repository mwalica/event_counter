<?php

use Illuminate\Support\Facades\Route;

Route::livewire('login', 'pages::auth.login')->name('login');

Route::middleware(['auth'])->group(function () {
    Route::livewire('/', 'pages::event.index')->name('event.index');
    Route::livewire('type', 'pages::type.index')->name('type.index');
    Route::livewire('archive', 'pages::archive.index')->name('archive.index');

    Route::get('logout', function() {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});



