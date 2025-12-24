<div class="max-w-6xl mx-auto py-6 px-4">
    <div class="flex flex-col md:flex-row gap-6 w-full">
        {{-- サイドバー（フィルターメニュー） --}}
        <x-filter-sidebar mode="task" :filter="$filter" />

        {{-- メインコンテンツ --}}
        <div class="flex-1">
            {{-- パンくずリスト --}}
            <div class="text-sm breadcrumbs mb-4 text-gray-500">
                <ul>
                    <li><a href="{{ route('goals.index') }}" wire:navigate class="hover:text-blue-600">目標一覧</a></li>
                    <li class="font-bold text-gray-700">{{ $goal->title }}</li>
                </ul>
            </div>

            {{-- 目標タイトルヘッダー --}}
            <div class="bg-amber-50 border rounded-xl border-gray-200 p-4 mb-4">
                <p class="text-sm text-gray-700">目標：</p>
                <h2 class="text-xl font-bold text-gray-800">{{ $goal->title }}</h2>
                <div class="mt-2 flex items-center gap-4 text-xs text-gray-500">
                    <x-date-label label="作成" :date="$goal->created_at" />
                    <x-date-label label="期限" :date="$goal->due_at" />
                    <x-date-label label="完了" :date="$goal->completed_at" />
                </div>

                {{-- 進捗バー（目標の全体進捗） --}}
                <x-progress-bar :progress="$goal->progress" />
            </div>

            {{-- 目標追加エリア（daisyUIのアコーディオンを使用） --}}
            {{-- collapse: アコーディオンの基本クラス --}}
            {{-- collapse-arrow: 右端に自動で矢印アイコンを追加・回転してくれる --}}
            {{-- collapse-open: Livewireの$isCreatingフラグがtrueの時だけ開く --}}
            <div class="collapse collapse-arrow border border-blue-200 bg-blue-50 mb-10 rounded-lg overflow-hidden {{ $isCreating ? 'collapse-open' : '' }}">

                {{-- トリガー部分 --}}
                {{-- collapse-title: この領域がクリック可能なヘッダー --}}
                {{-- toggleCreateForm でアコーディオンを手動で閉じたり開いたりの制御をする --}}
                <div
                    wire:click="toggleCreateForm"
                    class="collapse-title flex items-center gap-2 font-semibold text-gray-700 hover:bg-blue-100 transition-colors cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    タスク追加
                </div>

                {{-- フォーム部分 --}}
                {{-- collapse-content: 開いたときに表示される中身 --}}
                <div class="collapse-content bg-white border-t border-blue-100 cursor-auto">
                    <form wire:submit="store" class="pt-4 space-y-4">

                        {{-- タイトル --}}
                        <div class="form-control w-full">
                            <div><span class="label-text font-medium text-gray-700">タスク名</span></div>
                            <input wire:model="newTitle" type="text" placeholder="例：参考書を購入する" class="input input-bordered w-full focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-1 rounded" />
                            {{-- エラー通知 --}}
                            @error('newTitle') <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div> @enderror
                        </div>

                        {{-- 期限 --}}
                        <div class="form-control w-full">
                            <div><span class="label-text font-medium text-gray-700">期限（任意）</span></div>
                            <div class="w-[200px]">
                                <x-date-picker class="focus:outline-none focus:border-blue-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-1 rounded" wire:model="newDueAt" />
                            </div>
                            {{-- エラー通知 --}}
                            @error('newDueAt') <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div> @enderror
                        </div>

                        {{-- メモ（Textarea） --}}
                        <div class="form-control w-full">
                            <div><span class="label-text font-medium text-gray-700">メモ（任意）</span></div>
                            <textarea wire:model="newMemo" class="textarea textarea-bordered h-24 w-full focus:outline-none focus:border-blue-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-1 rounded" placeholder="詳細な手順やURLなど"></textarea>
                            {{-- エラー通知 --}}
                            @error('newMemo') <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div> @enderror
                        </div>

                        {{-- ボタン --}}
                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-primary text-white">追加する</button>
                        </div>
                    </form>
                </div>
            </div>


            {{-- ソート --}}
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <h3 class="text-xl font-bold text-gray-800">
                    {{-- 動的にタイトル変更する --}}
                    {{ match($filter) {
                    'uncompleted' => '進行中 のタスク一覧',
                    'uncompleted_over_due' => '未完了期限切れ のタスク一覧',
                    'due_soon' => '期限間近（2週間以内） のタスク一覧',
                    'completed' => '完了済み のタスク一覧',
                    default => 'すべて のタスク一覧'
                    } }}
                </h3>

                {{-- ソート選択プルダウン --}}
                <select wire:model.live="sort" class="select select-bordered select-sm w-full sm:w-auto focus:outline-none focus:border-blue-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-1 rounded">
                    <option value="created_desc">作成日が新しい順</option>
                    <option value="created_asc">作成日が古い順</option>
                    <option value="due_asc">期限が近い順</option>
                    <option value="due_desc">期限が遠い順</option>
                </select>
            </div>

            {{-- メインタスク一覧リスト --}}
            <div class="space-y-4">
                @forelse($mainTasks as $task)
                {{-- main-task-item のコンポーネント呼び出し --}}
                <livewire:main-tasks.main-task-item :task="$task" wire:key="task-item-{{ $task->id }}" />
                @empty
                {{-- データがない時の表示 --}}
                <div class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-200">
                    <p class="text-gray-500 flex justify-center items-center">
                        @if($filter === 'all' && $search === '' )
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                        </svg>
                        まだタスクがありません。「タスク追加」から登録しましょう！
                        @else
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 15.75-2.489-2.489m0 0a3.375 3.375 0 1 0-4.773-4.773 3.375 3.375 0 0 0 4.774 4.774ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        条件に一致するタスクはありません。
                        @endif
                    </p>
                </div>
                @endforelse

                {{-- ページネーション --}}
                <div class="mt-[20px]">
                    {{ $mainTasks->links() }}
                </div>

            </div>

        </div>
    </div>
</div>