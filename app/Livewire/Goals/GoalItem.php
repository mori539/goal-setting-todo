<?php
// 目標の編集、削除用のコンポーネント

namespace App\Livewire\Goals;

use App\Models\Goal;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Validation\ValidationException;

use function PHPSTORM_META\type;

class GoalItem extends Component
{
    // 親から受け取る目標モデル
    public Goal $goal;

    // 編集用のプロパティ（インライン編集用）
    #[Validate('required|string|max:255')]
    public string $editingTitle;

    // 期限日編集用プロパティ
    #[Validate('nullable|date|after:yesterday')]
    public string $editingDueAt = '';

    // 初期化処理
    public function mount(Goal $goal)
    {
        $this->goal = $goal;

        // 編集用プロパティに初期値をセット
        $this->editingTitle = $goal->title;

        // 期限日の初期値をセット（Y-m-d形式）
        $this->editingDueAt = $goal->due_at ? $goal->due_at->format('Y-m-d') : '';
    }

    // タイトルの更新処理（フォーカスが外れた時などに実行）
    public function updateTitle()
    {
        // バリデーション実行
        $this->validateOnly('editingTitle');

        // 値に変更がなければ何もしない
        if ($this->goal->title === $this->editingTitle) {
            return;
        }

        // DB更新
        $this->goal->update(['title' => $this->editingTitle]);
    }

    // 編集キャンセル時のリセット処理（ESCキー用）
    public function resetTitle()
    {
        $this->editingTitle = $this->goal->title;
    }

    // 期限日の更新処理（フォーカスが外れた時などに実行）
    public function updatedEditingDueAt()
    {
        try{
            $this->validateOnly('editingDueAt');

            // 空文字なら NULL に変換して保存
            $dueAt = $this->editingDueAt === '' ? null : $this->editingDueAt;

            // 更新処理
            $this->goal->update(['due_at' => $dueAt]);

            // blade側に更新成功の合図を送る
            $this->dispatch('goal-updated');

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
            $this->editingDueAt = $this->goal->due_at ? $this->goal->due_at->format('Y-m-d') : '';

        }
    }

    // 編集キャンセル時のリセット処理（ESCキー用）
    public function resetDueAt()
    {
        $this->editingDueAt = $this->goal->DueAt;
    }

    // 完了状態の切り替え（トグル）
    public function toggleCompletion()
    {
        // 既に完了日時が入っていれば NULL に、入っていなければ現在日時をセット
        if ($this->goal->completed_at) {
            $this->goal->update(['completed_at' => null]);
        } else {
            $this->goal->update(['completed_at' => now()]);
        }
    }

    // 削除処理
    public function delete()
    {
        // モデルを削除（カスケード設定により関連タスクも削除される）
        $this->goal->delete();

        // 親コンポーネント(Goals\Index)に削除されたことを通知してリストを更新させる
        $this->dispatch('goal-deleted');

        $this->dispatch('notify', message: '目標を削除しました');
    }

    // 表示
    public function render()
    {
        return view('livewire.goals.goal-item');
    }
}