<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return $this->hasMany(SubTask::class);
    }
}
