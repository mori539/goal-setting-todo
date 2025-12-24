<?php
// apiのルーティングを行うファイル。


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GoalController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// http://localhost:8000/api/goals（開発環境の場合） にアクセスすると、GoalControllerのindexが実行。
Route::get('/goals', [GoalController::class, 'index']);