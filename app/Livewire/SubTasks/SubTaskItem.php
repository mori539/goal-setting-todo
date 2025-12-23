<?php

namespace App\Livewire\SubTasks;

use App\Models\SubTask;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Validation\ValidationException;

class SubTaskItem extends Component
{
    // 親から受け取るサブタスクモデル
    public SubTask $subTask;

    // インライン編集用 サブタスクタイトル（バリデーション：必須、文字列、max255まで）
    #[Validate('required|string|max:255')]
    public string $editingTitle;

    // インライン編集用 メモ（バリデーション：NULL許可、文字列、max1000まで）
    #[Validate('nullable|string|max:1000')]
    public string $editingMemo;

    // 初期化
    public function mount(SubTask $subTask)
    {
        $this->subTask = $subTask;
        $this->editingTitle = $subTask->title;
        $this->editingMemo  = $subTask->memo ?? '';
    }

    // サブタスク タイトル更新
    public function updateTitle()
    {
        try{

            // バリデーション処理
            $this->validateOnly('editingTitle');

            // 値に変更がなければ何もしない
            if ($this->subTask->title === $this->editingTitle) {
                return;
            }

            // タイトル更新
            $this->subTask->update(['title' => $this->editingTitle]);

            // トーストで更新通知をする
            $this->dispatch('notify', message: 'サブタスクを更新しました');

        } catch (ValidationException $e) {

            // バリデーターから発生したエラーメッセージの「最初の1つ」を取り出す
            $errorMessage = $e->validator->errors()->first();

            // トーストで更新失敗の通知をする
            $this->dispatch('notify', message: $errorMessage, type: 'error');

        }

    }

    // メモ更新
    public function updateMemo()
    {
        try{

            // バリデーション処理
            $this->validateOnly('editingMemo');

            // 値に変更がなければ何もしない
            // （バリデーションの兼ね合いもあるため、DBのNullは空文字に変換してから比較）
            if (($this->subTask->memo ?: '') === $this->editingMemo) {
                return;
            }

            // メモ更新
            $this->subTask->update(['memo' => $this->editingMemo ?: null]);

            // トーストで更新通知をする
            $this->dispatch('notify', message: 'メモを更新しました');

        } catch (ValidationException $e) {

            // バリデーターから発生したエラーメッセージの「最初の1つ」を取り出す
            $errorMessage = $e->validator->errors()->first();

            // トーストで更新失敗の通知をする
            $this->dispatch('notify', message: $errorMessage, type: 'error');

        }
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
        try{

            // 削除処理
            $this->subTask->delete();

            // 削除時も進捗が変わるため、blade側に進捗バーの再計算を依頼
            $this->dispatch('subtask-updated');

            // トーストで削除成功の通知をする
            $this->dispatch('notify', message: 'サブタスクを削除しました');

        } catch (ValidationException $e) {

            // バリデーターから発生したエラーメッセージの「最初の1つ」を取り出す
            $errorMessage = $e->validator->errors()->first();

            // トーストで更新失敗の通知をする
            $this->dispatch('notify', message: $errorMessage, type: 'error');

        }
    }

    // 表示
    public function render()
    {
        return view('livewire.sub-tasks.sub-task-item');
    }
}