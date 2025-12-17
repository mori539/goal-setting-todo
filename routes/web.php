<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;
use App\Livewire\Goals\Index as GoalsIndex;
use App\Livewire\MainTasks\Index as MainTasksIndex;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    // 目標一覧画面
    Route::get('/goals', GoalsIndex::class)->name('goals.index');

    // メインタスク一覧画面 {goal} はidが動的に入る。
    Route::get('/goals/{goal}/main-tasks', MainTasksIndex::class)->name('goals.main-tasks');
});

require __DIR__.'/auth.php';
