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
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    // MainTaskItemコンポーネントで更新・削除が行われたら実行されるリスナー
    #[On('goal-updated')]
    public function refreshGoalList()
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


    // ユーザーに紐づく目標・タスク・サブタスクのCSVダウンロード
    public function exportAllDataCsv(): StreamedResponse
    {
        // ファイル名
        $fileName = 'all_data_' . now()->format('Y-m-d') . '.csv';

        // streamDownloadを使用することで、少しずつファイルを作ってブラウザに渡すことができる
        return response()->streamDownload(function () {

            // 出力用のバッファを開く
            $handle = fopen('php://output', 'w');

            // 文字化け対策（BOM付気にする）
            fputs($handle, "\xEF\xBB\xBF");

            // ヘッダー行
            fputcsv($handle, [
                '目標ID', '目標タイトル', '目標期限','目標完了日時',            //目標
                'タスクID', 'タスクタイトル', 'タスク期限','タスク完了日時',    //タスク
                'サブタスクID', 'サブタスクタイトル', 'サブタスク完了日時'      //サブタスク
            ]);

            // データの取得
            // Goalを親として、子供(mainTasks)と孫(subTasks)を道連れにして取得
            $goals = Goal::query()
                ->where('user_id', Auth::id())
                ->with(['mainTasks.subTasks'])  // 孫まで一気に取得
                ->lazyById(200);                //getだといっぺんにすべて取得するためメモリを食う。lazyById(200)にすることで200件ずつ取得してメモリ消費を抑える。

            // データを平坦化して書き込み（3重ループ）
            foreach ($goals as $goal) {
                // 無駄なループ処理をしないために、タスクの有無、サブタスクの有無で処理を分岐させる。

                // 目標に紐づくタスクが1つもない場合
                if ($goal->mainTasks->isEmpty()) {
                    // 目標の情報だけ書いて、タスク以降は空欄にする
                    fputcsv($handle, [
                        // '目標ID', '目標タイトル', '目標期限','目標完了日時',
                        $goal->id, $goal->title, $goal->due_at?->format('Y-m-d')?? '', $goal->completed_at?->format('Y-m-d')?? '',

                        // 'タスクID', 'タスクタイトル', 'タスク期限','タスク完了日時',
                        '', '', '', '', // タスク情報なし

                        // 'サブタスクID', 'サブタスクタイトル', 'サブタスク完了日時'
                        '', '', '', // サブタスク情報なし
                    ]);
                    continue; // 次の目標へ
                }

                // 目標に紐づくタスクあり
                foreach ($goal->mainTasks as $task) {

                    // タスクはあるけど、サブタスクがない場合
                    if ($task->subTasks->isEmpty()) {
                        fputcsv($handle, [
                            // '目標ID', '目標タイトル', '目標期限','目標完了日時',
                            $goal->id, $goal->title, $goal->due_at?->format('Y-m-d')?? '', $goal->completed_at?->format('Y-m-d')?? '',

                            // 'タスクID', 'タスクタイトル', 'タスク期限','タスク完了日時',
                            $task->id, $task->title, $task->due_at?->format('Y-m-d')?? '', $task->completed_at?->format('Y-m-d')?? '',

                            // 'サブタスクID', 'サブタスクタイトル', 'サブタスク完了日時'
                            '', '', '', // サブタスク情報なし
                        ]);
                        continue; // 次のタスクへ
                    }

                    // サブタスクまである場合
                    foreach ($task->subTasks as $subTask) {
                        fputcsv($handle, [
                            // '目標ID', '目標タイトル', '目標期限','目標完了日時',
                            $goal->id, $goal->title, $goal->due_at?->format('Y-m-d')?? '', $goal->completed_at?->format('Y-m-d')?? '',

                            // 'タスクID', 'タスクタイトル', 'タスク期限','タスク完了日時',
                            $task->id, $task->title, $task->due_at?->format('Y-m-d')?? '', $task->completed_at?->format('Y-m-d')?? '',

                            // 'サブタスクID', 'サブタスクタイトル', 'サブタスク完了日時'
                            $subTask->id, $subTask->title,  $subTask->completed_at?->format('Y-m-d')?? '',
                        ]);
                    }
                }
            }

            // ファイルを閉じる
            fclose($handle);
        }, $fileName);
    }

}