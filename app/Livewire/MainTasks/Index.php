<?php

namespace App\Livewire\MainTasks;

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

#[Layout('components.layouts.app')]
class Index extends Component
{
    // ページネーションを使用
    use WithPagination;


    public Goal $goal;

    // 新規登録用のプロパティ
    public bool $isCreating = false;

    // タスクのタイトル（バリデーション：必須、文字列、max255まで）
    #[Validate('required|string|max:255')]
    public string $newTitle = '';

    // タスクのメモ（バリデーション：NULL許可、文字列、max1000まで）
    #[Validate('nullable|string|max:1000')]
    public string $newMemo = '';

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


    // 初期化
    // URLのID (例: /goals/1) に対応するGoalデータを、
    // Laravelが自動でDBから取得して $goal に渡してくれる（ルートモデル結合）
    public function mount(Goal $goal)
    {
        // 取得したGoalデータを、このコンポーネントのプロパティにセットして
        // Bladeや他のメソッドで使えるようにする
        $this->goal = $goal;
    }

    // フォームの開閉トグル（View側で使う）
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
        // タスク側だけ更新しても、親である $this->goal は古い情報のまま（キャッシュされた状態）になっている。
        // 特に期限日変更時など、即座に画面へ反映させるために refresh() でDBから最新情報を再取得して同期させる。
        // (以前、Flatpickr編集時に反映されない事象があったため、この処理を追加した)
        // $this->goal->refresh();

        // 解説:
        // このメソッドの中身は空でOK。
        // Livewireは「メソッドが呼ばれる」→「render()が再実行される」という仕様のため、
        // 単にここを通るだけで、自動的にDBから最新データを取得しなおして画面が更新される。
    }

    // 保存処理
    public function store()
    {
        try{

            // バリデーション処理
            $this->validate();

            // goalに紐づくメインタスクを作成
            $this->goal->mainTasks()->create([
                'user_id' => Auth::id(),
                'title'   => $this->newTitle,
                'memo'    => $this->newMemo ?: null,
                'due_at'  => $this->newDueAt ?: null,
            ]);

            // フォームの入力欄をリセットする。
            $this->reset(['newTitle', 'newMemo', 'newDueAt']);

            // トーストで通知をする（成功通知）
            $this->dispatch('notify', message: 'タスクを追加しました');

        } catch (ValidationException $e) {
            // 保存失敗時の処理

            // バリデーターから発生したエラーメッセージの「最初の1つ」を取り出す
            $errorMessage = $e->validator->errors()->first();

            // トーストで更新失敗の通知をする
            $this->dispatch('notify', message: $errorMessage, type: 'error');
        }
    }


    // 描画のためのデータ取得ロジックをここ（Computed）に集約
    // この書き方をすることで、画面を表示する間、取得結果が一時的にキャッシュされるためDB負荷が減る
    #[Computed]
    public function mainTasksData()
    {
        // この目標に紐づくタスクのクエリを作成（まだgetしない）
        $query = $this->goal->mainTasks()->getQuery();

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
                     ->where('due_at', '<', now()->startOfDay());
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

        // 3. 並び替え（Sort）の適用
        switch ($this->sort) {
            case 'due_asc':
                // 期限が近い順（期限なしは後ろにする工夫が必要ですが、一旦単純に）
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

        // データ返却
        // withを使うことで目標に紐づくサブタスクもすべて取得してreturn
        return $query->with('subTasks')->paginate(50);
    }

    // 表示
    public function render()
    {
        return view('livewire.main-tasks.index', [
            // mainTasksData()で取得したデータをView側に渡して描画
            'mainTasks' => $this->mainTasksData,
        ]);

    }

    // フィルターが切り替わったらページを1ページ目に戻す（これがないとバグりやすい）
    public function updatedFilter()
    {
        $this->resetPage();
    }
}