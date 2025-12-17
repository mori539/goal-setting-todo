<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'due_at',
        'completed_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    // リレーション：目標は一人のユーザーに所属する
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // リレーション：目標はたくさんのメインタスクを持つ
    public function mainTasks()
    {
        return $this->hasMany(MainTask::class);
    }

    // $goal->progress で進捗率(0~100)が取れるようにする
    public function getProgressAttribute(): int
    {
        // メインタスクの総数
        $total = $this->mainTasks->count();

        // タスクが0個なら0%
        if ($total === 0) {
            return 0;
        }

        // 完了しているタスクの数
        $completed = $this->mainTasks->whereNotNull('completed_at')->count();

        // パーセント計算 (四捨五入)
        return round(($completed / $total) * 100);
    }
}
