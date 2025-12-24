<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // ここで「Reactに渡したい形」に変換
        return [
            'id' => $this->id,
            '目標タイトル' => $this->title,
            // React側で使いやすいように日付を整える
            '期限日時' => $this->due_at?->format('Y-m-d'),
            '進捗率' => $this->progress . '%', // 単位もつけちゃう
            // リレーションも整形して渡す（必要なら）
            'タスク数' => $this->mainTasks->count(),
        ];
    }
}
