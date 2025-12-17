<?php

namespace App\Livewire\Goals;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use App\Models\Goal;
use Livewire\Attributes\On;

class Index extends Component
{
    // Livewireのスターターキット標準のレイアウトを使用
    #[Layout('components.layouts.app')]

    // フォームの開閉フラグ
    public bool $isCreating = false;

    // タイトル
    #[Validate('required|string|max:255')]
    public string $newTitle = '';

    // 期限日（任意）
    #[Validate('nullable|date|after:yesterday')]
    public string $newDueAt = '';

    // 保存処理
    public function store()
    {
        $this->validate();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // ログインユーザーに紐づけて作成
        $user->goals()->create([
            'title' => $this->newTitle,
            'due_at' => $this->newDueAt ?: null, // 空文字ならnullにする
        ]);

        // フォームをリセットして閉じる
        $this->reset(['newTitle', 'newDueAt', 'isCreating']);

        // オプション: 完了メッセージなどのFlash通知
        session()->flash('status', '目標を作成しました！');
    }

    // フォームの開閉トグル
    public function toggleCreateForm()
    {
        $this->isCreating = !$this->isCreating;
        if (!$this->isCreating) {
            $this->reset(['newTitle', 'newDueAt']);
        }
    }

    // GoalItemコンポーネントで削除が行われたら実行されるリスナー
    #[On('goal-deleted')]
    public function refreshGoalsList()
    {
        // コンポーネントが再レンダリングされ、最新のリストが表示される。
    }

    // 表示
    public function render()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // ログインユーザーの目標を、作成日が新しい順に取得
        $goals = $user->goals()->orderBy('created_at', 'desc')->get();

        return view('livewire.goals.index', [
            'goals' => $goals,
        ]);
    }
}