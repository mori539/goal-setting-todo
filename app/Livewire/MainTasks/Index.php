<?php

namespace App\Livewire\MainTasks;

use App\Models\Goal;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public Goal $goal;

    // ▼ 新規登録用のプロパティ
    public bool $isCreating = false;

    #[Validate('required|string|max:255')]
    public string $newTitle = '';

    #[Validate('nullable|string|max:1000')]
    public string $newMemo = ''; // ワイヤーフレームにある「メモ」用

    #[Validate('nullable|date|after:yesterday')]
    public string $newDueAt = '';

    public function mount(Goal $goal)
    {
        $this->goal = $goal;
    }

    // ▼ フォーム開閉トグル
    public function toggleCreateForm()
    {
        $this->isCreating = !$this->isCreating;
        if (!$this->isCreating) {
            $this->reset(['newTitle', 'newMemo', 'newDueAt']);
        }
    }

    // MainTaskItemコンポーネントで更新・削除が行われたら実行されるリスナー
    #[On('task-updated')]
    public function refreshTaskList()
    {
        // データベースから最新のGoal情報（リレーション含む）を再読み込み
        $this->goal->refresh();
    }

    // ▼ 保存処理
    public function store()
    {
        $this->validate();

        // Goalに紐づくMainTaskを作成
        // ER図によると user_id も必要なので、Auth::id() をセットします
        $this->goal->mainTasks()->create([
            'user_id' => Auth::id(),
            'title'   => $this->newTitle,
            'memo'    => $this->newMemo ?: null,
            'due_at'  => $this->newDueAt ?: null,
        ]);

        $this->reset(['newTitle', 'newMemo', 'newDueAt', 'isCreating']);

        session()->flash('status', 'メインタスクを作成しました！');
    }

    public function render()
    {
        // この目標に紐づくメインタスクを50件のページネーションで取得（作成順）
        // ※リレーション設定がまだの場合は Model側で設定が必要ですが、
        // 　引継ぎ情報によると「Goal -< MainTask」設定済みとのことなのでそのまま使います。
        $mainTasks = $this->goal->mainTasks()
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('livewire.main-tasks.index', [
            'mainTasks' => $mainTasks,
        ]);
    }
}