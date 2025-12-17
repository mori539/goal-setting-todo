<?php

namespace App\Livewire\SubTasks;

use Livewire\Component;
use App\Models\SubTask;
use Livewire\Attributes\Validate;

class SubTaskItem extends Component
{
    public SubTask $subTask;

    #[Validate('required|string|max:255')]
    public string $editingTitle;

    #[Validate('nullable|string|max:1000')]
    public string $editingMemo;

    public function mount(SubTask $subTask)
    {
        $this->subTask = $subTask;
        $this->editingTitle = $subTask->title;
        $this->editingMemo  = $subTask->memo ?? '';
    }

    // タイトル更新
    public function updateTitle()
    {
        $this->validateOnly('editingTitle');
        $this->subTask->update(['title' => $this->editingTitle]);
    }

    // メモ更新
    public function updateMemo()
    {
        $this->validateOnly('editingMemo');
        $this->subTask->update(['memo' => $this->editingMemo ?: null]);
    }

    // 完了切り替え
    public function toggleCompletion()
    {
        if ($this->subTask->completed_at) {
            $this->subTask->update(['completed_at' => null]);
        } else {
            $this->subTask->update(['completed_at' => now()]);
        }

        // 親（MainTaskItem）に進捗再計算を依頼
        $this->dispatch('subtask-updated');
    }

    // 削除
    public function delete()
    {
        $this->subTask->delete();
        $this->dispatch('subtask-updated');
    }

    public function render()
    {
        return view('livewire.sub-tasks.sub-task-item');
    }
}