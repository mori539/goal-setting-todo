<?php

namespace App\Livewire\Goals;

use App\Models\Goal;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Index extends Component
{
    use WithPagination;


    // Livewireのスターターキット標準のレイアウトを使用
    #[Layout('components.layouts.app')]

    // フォームの開閉フラグ（view側で使う）
    public bool $isCreating = false;

    // 目標タイトル（バリデーション：必須、文字列、max255文字まで）
    #[Validate('required|string|max:255')]
    public string $newTitle = '';

    // 期限日時（バリデーション：NULL許可、日付形式、今日以降のみ許可）
    #[Validate('nullable|date|after:yesterday')]
    public string $newDueAt = '';


    // 絞り込み条件（URLにも反映されるように #[Url] をつける）
    // all（すべて）, uncompleted（進行中）, uncompleted_over_due（未完了期限切れ）, due_soon（期限間近）,completed（完了）
    #[Url]
    public string $filter = 'all';

    // 検索ワード用（URLにも反映されるように #[Url] をつける）
    #[Url]
    public string $search = '';

    // ソート条件（URLにも反映されるように #[Url] をつける）
    // created_desc（作成日時が新しい順）,created_asc（作成日時が古い順）,due_asc（期限が近い順）,due_desc（期限が遠い順）
    #[Url]
    public string $sort = 'created_desc';


    // 保存処理
    public function store()
    {
        try{

            // バリデーション処理
            $this->validate();

            // ログインユーザー情報をセット（@var~~~~~~ はVScodeが誤認識しないように書いてあげてるだけ）
            /** @var \App\Models\User $user */
            $user = Auth::user();

            // ログインユーザーに紐づけて作成
            $user->goals()->create([
                'title' => $this->newTitle,
                'due_at' => $this->newDueAt ?: null, // 空文字ならnullにする
            ]);

            // フォームの入力欄をリセットし、isCreatingをfalseに戻してアコーディオンを閉じる
            $this->reset(['newTitle', 'newDueAt', 'isCreating']);

            // トーストで通知をする（成功通知）
            $this->dispatch('notify', message: '目標を追加しました');

        } catch (ValidationException $e) {
            // 保存失敗時の処理

            // バリデーターから発生したエラーメッセージの「最初の1つ」を取り出す
            $errorMessage = $e->validator->errors()->first();

            // トーストで更新失敗の通知をする
            $this->dispatch('notify', message: $errorMessage, type: 'error');
        }
    }

    // フォームの開閉トグル（View側で使う）
    public function toggleCreateForm()
    {
        $this->isCreating = !$this->isCreating;
        if (!$this->isCreating) {
            $this->reset(['newTitle', 'newDueAt']);
        }
    }

    // GoalItemコンポーネントで削除が行われたら実行される
    #[On('goal-deleted')]
    public function refreshGoalsList()
    {
        // 解説:
        // このメソッドの中身は空でOK。
        // Livewireは「メソッドが呼ばれる」→「render()が再実行される」という仕様のため、
        // 単にここを通るだけで、自動的にDBから最新データを取得しなおして画面が更新される。
    }



    // 描画のためのデータ取得ロジックをここ（Computed）に集約
    // この書き方をすることで、画面を表示する間、取得結果が一時的にキャッシュされるためDB負荷が減る
    #[Computed]
    public function goalsData()
    {
        // クエリの準備（まだgetしない）
        $query = Goal::query()
            ->where('user_id', Auth::id()); // 自分のデータのみ

        // フィルターの適用
        $query->when($this->filter === 'uncompleted', function ($q) {
            // フィルター：未完了（完了日がnull）
            return $q->whereNull('completed_at');
        });

        $query->when($this->filter === 'completed', function ($q) {
            // フィルター：完了済み
            return $q->whereNotNull('completed_at');
        });

        $query->when($this->filter === 'uncompleted_over_due', function ($q) {
            // フィルター：未完了期限切れ（未完了 かつ 期限が今日よりも過去）
            return $q->whereNull('completed_at')
                     ->whereNotNull('due_at')
                     ->where('due_at', '<', now());
        });

        $query->when($this->filter === 'due_soon', function ($q) {
            // フィルター：期限間近（期限日が今日の00:00～2週間後の23:59）
            return $q->whereNotNull('due_at')
                     ->whereBetween('due_at', [now()->startOfDay(), now()->addWeeks(2)->endOfDay()]);
        });

        $query->when($this->search, function ($q) {
            // 検索文字がある場合、タイトルであいまい検索
            return $q->where('title', 'like', '%' . $this->search . '%');
        });

        // ソートの適用
        switch ($this->sort) {
            case 'due_asc':
                // 期限が近い順
                $query->orderBy('due_at', 'asc');
                break;
            case 'due_desc':
                // 期限が遠い順
                $query->orderBy('due_at', 'desc');
                break;
            case 'created_asc':
                // 作成日時が古い順
                $query->orderBy('created_at', 'asc');
                break;
            default: // created_desc
                // 作成日時が新しい順
                $query->orderBy('created_at', 'desc');
                break;
        }

        // データ返却（ページネーション付き）
        // withを使うことで目標に紐づくメインタスクもすべて取得し、N+1問題を回避する。
        return $query->with('mainTasks')->paginate(10);

    }


    // 表示
    public function render()
    {
    // goalsData()で取得したデータをView側に渡して描画
        return view('livewire.goals.index', [
            'goals' => $this->goalsData,
        ]);
    }

    // フィルターが切り替わったらページを1ページ目に戻す（これがないとバグりやすい）
    public function updatedFilter()
    {
        $this->resetPage();
    }
}