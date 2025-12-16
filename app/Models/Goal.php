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
}
