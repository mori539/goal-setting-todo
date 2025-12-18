<?php

namespace App\Livewire\MainTasks;

use App\Models\Goal;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;


    public Goal $goal;

    // ▼ 新規登録用のプロパティ
    public bool $isCreating = false;

    #[Validate('required|string|max:255')]
    public string $newTitle = '';

    #[Validate('nullable|string|max:1000')]
    public string $newMemo = ''; // ワイヤーフレームにある「メモ」用

    #[Validate('nullable|date|after:yesterday')]
    public string $newDueAt = '';


    // フィルター用プロパティ
    #[Url]
    public string $filter = 'all';

    // 検索ワード用（URLにも反映されるように #[Url] をつける）
    #[Url]
    public string $search = '';

    // ソート用プロパティ
    #[Url]
    public string $sort = 'created_desc';



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

        $this->reset(['newTitle', 'newMemo', 'newDueAt']);

        session()->flash('status', 'メインタスクを作成しました！');
    }

    public function render()
    {
        // // この目標に紐づくメインタスクを50件のページネーションで取得（作成順）
        // // ※リレーション設定がまだの場合は Model側で設定が必要ですが、
        // // 　引継ぎ情報によると「Goal -< MainTask」設定済みとのことなのでそのまま使います。
        // $mainTasks = $this->goal->mainTasks()
        //     ->orderBy('created_at', 'desc')
        //     ->paginate(50);

        // return view('livewire.main-tasks.index', [
        //     'mainTasks' => $mainTasks,
        // ]);
        // 1. この目標に紐づくタスクのクエリを作成
        $query = $this->goal->mainTasks()->getQuery();

        // 2. 絞り込み（Filter）

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

        // 4. データ取得
        // // withを使うことで目標に紐づくサブタスクもすべて取得する
        $mainTasks = $query->with('subTasks')->paginate(50);

        return view('livewire.main-tasks.index', [
            'mainTasks' => $mainTasks,
        ]);
    }

    // フィルターが切り替わったらページを1ページ目に戻す（これがないとバグりやすい）
    public function updatedFilter()
    {
        $this->resetPage();
    }
}