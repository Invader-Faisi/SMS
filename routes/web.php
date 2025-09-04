<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


Route::middleware(['auth'])->group(function () {
    Route::redirect('admin', 'admin/teacher');

    // User Management
    Volt::route('admin/teacher', 'admin.teacher')->name('admin.teacher');
    Volt::route('admin/staff', 'admin.staff')->name('admin.staff');
    Volt::route('admin/parent', 'admin.parent')->name('admin.parent');
    Volt::route('admin/student', 'admin.student')->name('admin.student');
    Volt::route('admin/classes', 'admin.classes')->name('admin.classes');
    Volt::route('admin/classes/{id}', 'admin.partials.class')->name('admin.classes.class');

    // Settings
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
