<?php

namespace App\Livewire\Goals;

use App\Models\Goal;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;


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


    // 絞り込み条件（URLにも反映されるように #[Url] をつける）
    #[Url]
    public string $filter = 'all'; // all（すべて）, not_started（）, uncompleted（未完了）, completed（完了）

    // 検索ワード用（URLにも反映されるように #[Url] をつける）
    #[Url]
    public string $search = '';

    // 並び替え条件（URLにも反映されるように #[Url] をつける）
    #[Url]
    public string $sort = 'created_desc';


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

        // 1. クエリの準備（まだgetしない）
        $query = Goal::query()
            ->where('user_id', Auth::id()); // 自分のデータのみ

        // 2. 絞り込み（Filter）の適用
        $query->when($this->filter === 'uncompleted', function ($q) {
            // 未完了（完了日がnull）
            return $q->whereNull('completed_at');
        });

        $query->when($this->filter === 'completed', function ($q) {
            // 完了済み
            return $q->whereNotNull('completed_at');
        });

        $query->when($this->filter === 'uncompleted_over_due', function ($q) {
            // 未完了期限切れ（未完了 かつ 期限が今日よりも過去）
            return $q->whereNull('completed_at')
                     ->whereNotNull('due_at')
                     ->where('due_at', '<', now());
        });

        $query->when($this->filter === 'due_soon', function ($q) {
            // 期限間近（期限日が今日の00:00～2週間後の23:59）
            return $q->whereNotNull('due_at')
                     ->whereBetween('due_at', [now()->startOfDay(), now()->addWeeks(2)->endOfDay()]);
        });

        $query->when($this->search, function ($q) {
            // 検索文字がある場合、タイトルであいまい検索
            return $q->where('title', 'like', '%' . $this->search . '%');
        });

        // 3. 並び替え（Sort）の適用
        switch ($this->sort) {
            case 'due_asc':
                // 期限が近い順（期限なしは後ろにする工夫が必要ですが、一旦単純に）
                $query->orderBy('due_at', 'asc');
                break;
            case 'due_desc':
                $query->orderBy('due_at', 'desc');
                break;
            case 'created_asc':
                $query->orderBy('created_at', 'asc');
                break;
            default: // created_desc
                $query->orderBy('created_at', 'desc');
                break;
        }

        // 4. データ取得（ページネーション付き）
        // withを使うことで目標に紐づくメインタスクもすべて取得する
        $goals = $query->with('mainTasks')->paginate(10);

        return view('livewire.goals.index', [
            'goals' => $goals,
        ]);
    }

    // フィルターが切り替わったらページを1ページ目に戻す（これがないとバグりやすい）
    public function updatedFilter()
    {
        $this->resetPage();
    }
}