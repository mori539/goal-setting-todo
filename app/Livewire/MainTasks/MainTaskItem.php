<?php

namespace App\Livewire\MainTasks;

use Livewire\Component;
use App\Models\MainTask;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

class MainTaskItem extends Component
{
    public MainTask $task;

    #[Validate('required|string|max:255')]
    public string $editingTitle;

    #[Validate('nullable|string|max:1000')]
    public string $editingMemo;

    #[Validate('nullable|date|after:yesterday')]
    public string $editingDueAt = '';


    #[Validate('required|string|max:255')]
    public string $newSubTaskTitle = '';

    // 初期化
    public function mount(MainTask $task)
    {
        $this->task = $task;
        $this->editingTitle = $task->title;
        $this->editingMemo  = $task->memo ?? ''; // nullなら空文字
        $this->editingDueAt = $task->due_at ? $task->due_at->format('Y-m-d') : '';
    }

    // タイトル更新
    public function updateTitle()
    {
        $this->validateOnly('editingTitle');
        $this->task->update(['title' => $this->editingTitle]);
    }

    // メモ更新
    public function updateMemo()
    {
        $this->validateOnly('editingMemo');
        $this->task->update(['memo' => $this->editingMemo ?: null]);
    }

    // 期限日更新
    public function updateDueAt()
    {
        $this->validateOnly('editingDueAt');
        $this->task->update(['due_at' => $this->editingDueAt ?: null]);
    }

    // 完了切り替え
    public function toggleCompletion()
    {
        if ($this->task->completed_at) {
            $this->task->update(['completed_at' => null]);
        } else {
            $this->task->update(['completed_at' => now()]);
        }

        // ★重要: 親コンポーネントに進捗再計算を依頼するイベント（後で使います）
        $this->dispatch('task-updated');
    }

    // 削除
    public function delete()
    {
        $this->task->delete();
        $this->dispatch('task-updated'); // 削除時も進捗が変わるので通知

        $this->dispatch('notify', message: 'タスクを削除しました', type: 'del_success');
    }

    public function render()
    {
        return view('livewire.main-tasks.main-task-item');
    }



    // サブタスク保存
    public function storeSubTask()
    {
        $this->validateOnly('newSubTaskTitle');

        $this->task->subTasks()->create([
            'title' => $this->newSubTaskTitle,
        ]);

        $this->reset('newSubTaskTitle');
        $this->dispatch('subtask-updated'); // 進捗計算のために自分自身に通知
        $this->dispatch('notify', message: 'サブタスクを追加しました');
    }

    // SubTaskItemコンポーネントで更新・削除が行われたら実行されるリスナー
    #[On('subtask-updated')]
    public function refreshSubTaskList()
    {
        // データベースから最新のメインタスク情報（リレーション含む）を再読み込み
        $this->task->refresh();
    }
}