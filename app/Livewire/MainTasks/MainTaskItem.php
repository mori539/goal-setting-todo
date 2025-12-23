<?php

namespace App\Livewire\MainTasks;

use Livewire\Component;
use App\Models\MainTask;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Illuminate\Validation\ValidationException;

class MainTaskItem extends Component
{
    // 親から受け取るメインタスクモデル
    public MainTask $task;

    // インライン編集用 メインタスクタイトル（バリデーション：必須、文字列、max255まで）
    #[Validate('required|string|max:255')]
    public string $editingTitle;

    // インライン編集用 メモ（バリデーション：NULL許可、文字列、max1000まで）
    #[Validate('nullable|string|max:1000')]
    public string $editingMemo;

    // インライン編集用 期限日時（バリデーション：NULL許可、日付形式、今日以降のみ許可）
    #[Validate('nullable|date|after:yesterday')]
    public string $editingDueAt = '';

    //インライン編集用 サブタスクタイトル（バリデーション：必須、文字列、max255まで）
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

    // メインタスクタイトル更新
    public function updateTitle()
    {
        try{

            // バリデーション処理
            $this->validateOnly('editingTitle');

            // 値に変更がなければ何もしない
            if ($this->task->title === $this->editingTitle) {
                return;
            }

            // タイトルを更新
            $this->task->update(['title' => $this->editingTitle]);

            // トーストで更新通知をする
            $this->dispatch('notify', message: 'タスクを更新しました');

        } catch (ValidationException $e) {

            // バリデーターから発生したエラーメッセージの「最初の1つ」を取り出す
            $errorMessage = $e->validator->errors()->first();

            // トーストで更新失敗の通知をする
            $this->dispatch('notify', message: $errorMessage, type: 'error');

        }
    }

    // メインタスクメモ更新
    public function updateMemo()
    {
        try{

            // バリデーション処理
            $this->validateOnly('editingMemo');

            // 値に変更がなければ何もしない
            // （バリデーションの兼ね合いもあるため、DBのNullは空文字に変換してから比較）
            if (($this->task->memo ?: '') === $this->editingMemo) {
                return;
            }

            // メインタスク メモ更新
            $this->task->update(['memo' => $this->editingMemo ?: null]);

            // トーストで更新通知をする
            $this->dispatch('notify', message: 'メモを更新しました');

         } catch (ValidationException $e) {

            // バリデーターから発生したエラーメッセージの「最初の1つ」を取り出す
            $errorMessage = $e->validator->errors()->first();

            // トーストで更新失敗の通知をする
            $this->dispatch('notify', message: $errorMessage, type: 'error');

        }
    }

    // 期限日時更新
    public function updatedEditingDueAt()
    {
        try{
            // バリデーション処理
            $this->validateOnly('editingDueAt');

            // 値に変更がなければ何もしない
            if ($this->task->due_at === $this->editingDueAt) {
                return;
            }

            // 期限日時更新
            $this->task->update(['due_at' => $this->editingDueAt ?: null]);

            // blade側に更新成功の合図を送る
            $this->dispatch('task-updated');

            // トーストで更新通知をする
            $this->dispatch('notify', message: '期限日を更新しました');

            // blade側にフォーカスを外す合図を送る
            $this->dispatch('blur-picker');

        } catch (ValidationException $e) {

            // バリデーターから発生したエラーメッセージの「最初の1つ」を取り出す
            $errorMessage = $e->validator->errors()->first();

            // トーストで更新失敗の通知をする
            $this->dispatch('notify', message: $errorMessage, type: 'error');

            // input要素の日付をもとの日付に戻す
            // Null回避もしておく
            $this->editingDueAt = $this->task->due_at ? $this->task->due_at->format('Y-m-d') : '';

        }
    }

    // 完了切り替え
    public function toggleCompletion()
    {
        if ($this->task->completed_at) {
            $this->task->update(['completed_at' => null]);
        } else {
            $this->task->update(['completed_at' => now()]);
        }

        // 親コンポーネントのblade側に進捗バーの再計算を依頼するイベント
        $this->dispatch('task-updated');
    }

    // 削除
    public function delete()
    {
        try{

            // 削除処理
            $this->task->delete();

            // 削除時も進捗が変わるため、blade側に進捗バーの再計算を依頼
            $this->dispatch('task-updated');

            // トーストで削除成功の通知をする
            $this->dispatch('notify', message: 'タスクを削除しました');

        } catch (ValidationException $e) {

            // バリデーターから発生したエラーメッセージの「最初の1つ」を取り出す
            $errorMessage = $e->validator->errors()->first();

            // トーストで削除失敗の通知をする
            $this->dispatch('notify', message: $errorMessage, type: 'error');

        }
    }

    // 表示
    public function render()
    {
        return view('livewire.main-tasks.main-task-item');
    }


    // 編集キャンセル時のリセット処理（ESCキー用）
    public function resetTitle()
    {
        $this->editingTitle = $this->task->title;
    }


    // 編集キャンセル時のリセット処理（ESCキー用）
    public function reseMemo()
    {
        $this->editingMemo = $this->task->memo;
    }



    // サブタスク保存
    public function storeSubTask()
    {
        try{

            // バリデーション処理
            $this->validateOnly('newSubTaskTitle');

            // サブタスク保存処理
            $this->task->subTasks()->create([
                'title' => $this->newSubTaskTitle,
            ]);

            // サブタスクタイトルをリセット
            $this->reset('newSubTaskTitle');

            // blade側に進捗バーの再計算を依頼
            $this->dispatch('subtask-updated');

            // トーストで作成成功の通知をする
            $this->dispatch('notify', message: 'サブタスクを追加しました');

         } catch (ValidationException $e) {

            // バリデーターから発生したエラーメッセージの「最初の1つ」を取り出す
            $errorMessage = $e->validator->errors()->first();

            // トーストで更新失敗の通知をする
            $this->dispatch('notify', message: $errorMessage, type: 'error');

        }
    }

    // SubTaskItemコンポーネントで更新・削除が行われたら実行されるリスナー
    #[On('subtask-updated')]
    public function refreshSubTaskList()
    {
        // データベースから最新のメインタスク情報（リレーション含む）を再読み込み
        $this->task->refresh();
    }
}