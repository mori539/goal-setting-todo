<div class="max-w-6xl mx-auto py-6 px-4">
    <div class="flex flex-col md:flex-row gap-6 w-full">
        {{-- ▼▼▼ 左サイドバー（フィルターメニュー） ▼▼▼ --}}
        <x-filter-sidebar mode="task" :filter="$filter" />

        {{-- ▼▼▼ 右メインコンテンツ ▼▼▼ --}}
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
                    <x-date-label label="期限" :date="$goal->due_at" />
                    <x-date-label label="作成" :date="$goal->created_at" />
                    <x-date-label label="完了" :date="$goal->completed_at" />
                </div>
                {{-- 進捗バー（目標の全体進捗） --}}
                @php
                // 進捗率を取得
                $progress = $goal->progress;

                // 色の決定ロジック（daisyUIのクラスを切り替え）
                // 0-34%: warning (オレンジ/黄色系)
                // 35-74%: info (青系) ※ワイヤーでは黄色ですが、daisyUI標準色だとinfoかaccentが見やすいかも
                // 75-100%: success (緑)
                $colorClass = match(true) {
                $progress < 35=> 'bg-warning',
                    $progress < 75=> 'bg-info',
                        default => 'bg-success',
                        };

                        // テキストの色も合わせる
                        $textColorClass = match(true) {
                        $progress < 35=> 'text-orange-500',
                            $progress < 75=> 'text-blue-500',
                                default => 'text-green-600',
                                };
                                @endphp

                                <div class="mt-3 px-1">
                                    {{-- <progress> ではなく 2つのdivで実装 --}}
                                        <div class="flex items-center gap-2 w-9/10">
                                            <div class="w-full bg-gray-100 h-3 rounded-full overflow-hidden">
                                                <div
                                                    class="h-full rounded-full transition-all duration-500 ease-out {{ $colorClass }}"
                                                    style="width: {{ $progress }}%"></div>
                                            </div>
                                            {{-- 数字表示（お好みで） --}}
                                            <span class="text-[12px] font-bold whitespace-nowrap {{ $textColorClass }} w-8 text-right">
                                                {{ $progress }}%完了
                                            </span>
                                        </div>
                                </div>
            </div>

            {{-- ▼▼▼ メインタスク追加フォーム（アコーディオン） ▼▼▼ --}}
            <div class="collapse collapse-arrow border border-blue-200 bg-blue-50 mb-10 rounded-lg overflow-hidden {{ $isCreating ? 'collapse-open' : '' }}">

                <div
                    wire:click="toggleCreateForm"
                    class="collapse-title flex items-center gap-2 font-semibold text-gray-700 hover:bg-blue-100 transition-colors cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    タスク追加
                </div>

                <div class="collapse-content bg-white border-t border-blue-100 cursor-auto">
                    <form wire:submit="store" class="pt-4 space-y-4">

                        {{-- タイトル --}}
                        <div class="form-control w-full">
                            <div><span class="label-text font-medium text-gray-700">タスク名</span></div>
                            <input wire:model="newTitle" type="text" placeholder="例：参考書を購入する" class="input input-bordered w-full" />
                            @error('newTitle') <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div> @enderror
                        </div>

                        {{-- 期限 --}}
                        <div class="form-control w-full">
                            <div><span class="label-text font-medium text-gray-700">期限（任意）</span></div>
                            <input wire:model="newDueAt" type="date" class="input input-bordered w-1/3" />
                            @error('newDueAt') <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div> @enderror
                        </div>

                        {{-- メモ（Textarea） --}}
                        <div class="form-control w-full">
                            <div><span class="label-text font-medium text-gray-700">メモ（任意）</span></div>
                            <textarea wire:model="newMemo" class="textarea textarea-bordered h-24 w-full" placeholder="詳細な手順やURLなど"></textarea>
                            @error('newMemo') <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div> @enderror
                        </div>

                        {{-- ボタン --}}
                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-primary text-white">追加する</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ▼▼▼ メインタスク一覧リスト ▼▼▼ --}}
            {{-- <h3 class="text-xl font-bold text-gray-800 mb-4">タスク一覧</h3> --}}
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <h3 class="text-xl font-bold text-gray-800">
                    {{ match($filter) {
                    'uncompleted' => '進行中 のタスク一覧',
                    'uncompleted_over_due' => '未完了期限切れ のタスク一覧',
                    'due_soon' => '期限間近（2週間以内） のタスク一覧',
                    'completed' => '完了済み のタスク一覧',
                    default => 'すべて のタスク一覧'
                    } }}
                </h3>

                {{-- ソート選択プルダウン --}}
                <select wire:model.live="sort" class="select select-bordered select-sm w-full sm:w-auto">
                    <option value="created_desc">作成日が新しい順</option>
                    <option value="created_asc">作成日が古い順</option>
                    <option value="due_asc">期限が近い順</option>
                    <option value="due_desc">期限が遠い順</option>
                </select>
            </div>
            <div class="space-y-4">
                @forelse($mainTasks as $task)
                {{-- コンポーネント呼び出し --}}
                <livewire:main-tasks.main-task-item :task="$task" wire:key="task-item-{{ $task->id }}" />
                @empty
                <div class="text-center py-10 text-gray-400 bg-gray-50 rounded-lg border-dashed border-2 border-gray-200">
                    まだタスクがありません。「タスク追加」から登録してください。
                </div>
                @endforelse
            </div>

            <div class="mt-[20px]">
                {{ $mainTasks->links() }}
            </div>
        </div>
    </div>
</div>