<div class="max-w-6xl mx-auto py-6 px-4">

    <div class="flex flex-col md:flex-row gap-6">

        {{-- サイドバー（フィルターメニュー） --}}
        <x-filter-sidebar mode="goal" :filter="$filter" />

        {{-- メインコンテンツ --}}
        <div class="flex-1">

            {{-- ソート --}}
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <h2 class="text-2xl font-bold text-gray-800">
                    {{-- 動的にタイトル変更する --}}
                    {{ match($filter) {
                    'uncompleted' => '進行中 の目標一覧',
                    'uncompleted_over_due' => '未完了期限切れ の目標一覧',
                    'due_soon' => '期限間近（2週間以内） の目標一覧',
                    'completed' => '完了済み の目標一覧',
                    default => 'すべて の目標一覧'
                    } }}
                </h2>

                {{-- ソート選択プルダウン --}}
                <select wire:model.live="sort" class="select select-bordered select-sm w-full sm:w-auto">
                    <option value="created_desc">作成日が新しい順</option>
                    <option value="created_asc">作成日が古い順</option>
                    <option value="due_asc">期限が近い順</option>
                    <option value="due_desc">期限が遠い順</option>
                </select>
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
                    目標追加
                </div>

                {{-- フォーム部分 --}}
                {{-- collapse-content: 開いたときに表示される中身 --}}
                <div class="collapse-content bg-white border-t border-blue-100 cursor-auto">
                    <form wire:submit="store" class="pt-4 space-y-4">

                        {{-- 目標タイトル --}}
                        <div class="form-control w-full">
                            <div>
                                <span class="label-text font-medium text-gray-700">目標タイトル</span>
                            </div>
                            <input
                                wire:model="newTitle"
                                type="text"
                                placeholder="例：英語の資格を取得する"
                                class="input input-bordered w-full focus:outline-none focus:border-blue-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-1 rounded" />
                            {{-- エラー通知 --}}
                            @error('newTitle') <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div> @enderror
                        </div>

                        {{-- 期限 --}}
                        <div class="form-control w-full">
                            <div>
                                <span class="label-text font-medium text-gray-700">期限（任意）</span>
                            </div>
                            <div class="w-[200px]">
                                <x-date-picker class="focus:outline-none focus:border-blue-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-1 rounded" wire:model="newDueAt" />
                            </div>
                            {{-- エラー通知 --}}
                            @error('newDueAt') <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div> @enderror
                        </div>

                        {{-- 作成ボタン --}}
                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-primary text-white">
                                作成する
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 目標一覧リスト --}}
            <div class="space-y-4 mt-8">
                @forelse($goals as $goal)
                {{-- goal-itemのコンポーネントを呼ぶ --}}
                <livewire:goals.goal-item :goal="$goal" wire:key="goal-item-{{ $goal->id }}" />
                @empty
                {{-- データがない時の表示 --}}
                <div class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-200">
                    <p class="text-gray-500">
                        @if($filter === 'all')
                        目標がまだありません。新しい目標を追加しましょう！
                        @else
                        条件に一致する目標はありません。
                        @endif
                    </p>
                </div>
                @endforelse
            </div>

        </div>

        {{-- ページネーション --}}
        <div class="mt-[20px]">
            {{ $goals->links() }}
        </div>

    </div>

</div>