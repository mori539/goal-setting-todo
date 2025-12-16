<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'main_task_id',
        'title',
        'memo',
        'completed_at',
    ];

    // リレーション：サブタスクはあるメインタスクに所属する
    public function mainTask()
    {
        return $this->belongsTo(MainTask::class);
    }
}
