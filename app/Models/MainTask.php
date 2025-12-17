<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MainTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'goal_id',
        'title',
        'memo',
        'due_at',
        'completed_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    // リレーション：メインタスクは一人のユーザーに所属する
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // リレーション：メインタスクはひとつの目標に所属する
    public function goal()
    {
        return $this->belongsTo(Goal::class);
    }

    // リレーション：メインタスクはたくさんのサブタスクを持つ
    public function subTasks()
    {
        return $this->hasMany(SubTask::class)->orderBy('created_at', 'asc');
    }

    public function getProgressAttribute(): int
    {
        // サブタスクの総数
        $total = $this->subTasks->count();

        // サブタスクが0個の場合は0%
        if ($total === 0) {
            return 0;
        }

        // 完了しているサブタスクの数
        $completed = $this->subTasks->whereNotNull('completed_at')->count();

        // パーセント計算
        return round(($completed / $total) * 100);
    }
}
