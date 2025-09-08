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

    // Class Management
    Volt::route('classes', 'classes.classes')->name('classes');
    Volt::route('classes/class/{id}', 'classes.partials.class')->name('classes.class');
    Volt::route('classes/timetables', 'classes.timetables')->name('classes.timetables');
    Volt::route('classes/timetable/{id}', 'classes.partials.timetable')->name('classes.timetable');
    Volt::route('classes/attendance', 'classes.attendance')->name('classes.attendance');

    // Settings
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
