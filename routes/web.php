<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Livewire\Goals\Index as GoalsIndex;
use App\Livewire\MainTasks\Index as MainTasksIndex;


Route::get('/', function () {
    // ログイン済みなら、目標一覧へ転送
    if (Auth::check()) {
        return redirect()->route('goals.index');
    }
    // 未ログインなら、ログインページへ転送
    return redirect()->route('login');
})->name('home');

Route::middleware(['auth'])->group(function () {

    // 現状はいらないが、ユーザー設定変更画面を作る場合に必要になりそうなため、コメントアウトしておく
    // Route::redirect('settings', 'settings/profile');

    // 現状はいらないが、ユーザー設定変更画面を作る場合に必要になりそうなため、コメントアウトしておく
    // Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    // Volt::route('settings/password', 'settings.password')->name('settings.password');
    // Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    // 目標一覧画面
    Route::get('/goals', GoalsIndex::class)->name('goals.index');

    // メインタスク一覧画面 {goal} はidが動的に入る。
    Route::get('/goals/{goal}/main-tasks', MainTasksIndex::class)->name('goals.main-tasks');
});

require __DIR__.'/auth.php';
