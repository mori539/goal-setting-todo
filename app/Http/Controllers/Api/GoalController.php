<?php

namespace App\Http\Controllers\Api;

use App\Models\Goal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\GoalResource;

class GoalController extends Controller
{
    public function index()
    {
        // データの取得（Livewireと同じ要領）
        // 今回はシンプルに全件取得してみます（実務ではuser_idで絞る）
        $goals = Goal::with('mainTasks')->get();

        // レスポンス
        // GoalResourceを使用し、成型後の配列をreturn
        return GoalResource::collection($goals);
    }
}
