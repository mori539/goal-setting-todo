<div
    {{-- alpain.jsで使用する変数を定義 --}}
    x-data="{ isEditing: false, isEditingDate: false }"
    class="card bg-yellow-50 border border-yellow-200 shadow-sm rounded-lg p-4"
>
    <div class="flex items-start gap-3">

        {{-- 完了チェックボックス --}}
        <input
            type="checkbox"
            wire:click="toggleCompletion"
            @checked($goal->completed_at)
            class="checkbox checkbox-success checkbox-md rounded-full border-2 border-gray-400"
        />

        {{-- 真ん中のエリア（目標タイトル・日付） --}}
        <div class="w-full min-w-0 grid gap-1">

            {{-- 目標タイトル表示エリア --}}
            <div class="relative group">

                {{-- 表示モード --}}
                <h3
                    x-show="!isEditing"
                    @click="isEditing = true; $nextTick(() => $refs.titleInput.focus())"
                    class="text-lg font-bold text-gray-800 cursor-pointer hover:bg-yellow-100/50 rounded px-1 -ml-1 transition-colors break-words
                        {{ $goal->completed_at ? 'line-through text-gray-400' : '' }}"
                >
                    {{ $goal->title }}
                </h3>

                {{-- 編集モード --}}
                <input
                    x-show="isEditing"
                    x-cloak
                    x-ref="titleInput"
                    wire:model="editingTitle"
                    wire:blur="updateTitle; isEditing = false"
                    @keydown.enter.prevent="$event.target.blur()"
                    @keydown.escape.prevent="isEditing = false; $wire.resetTitle()"
                    type="text"
                    class="input input-sm input-ghost w-full text-lg font-bold text-gray-800 px-1 -ml-1 h-auto focus:bg-white"
                />
            </div>
            @error('editingTitle') <span class="text-error text-xs">{{ $message }}</span> @enderror

            {{-- 日付ラベル --}}
            <div class="text-xs text-gray-500 flex flex-wrap gap-2 items-center">
                <x-date-label label="作成" :date="$goal->created_at" />

                {{-- 期限日時 --}}
                {{-- ▼ inline-block をやめて flex に変更。items-center で「期限:」と「日付」の高さを揃えます --}}
                <div class="flex items-center gap-1">
                    <span class="text-gray-500 shrink-0">期限:</span> {{-- shrink-0: 縮こまらないように固定 --}}

                    <div class="w-[85px]"> {{-- 幅を少し調整 --}}
                        <x-date-picker
                            wire:model.live="editingDueAt"
                            placeholder="---"
                            {{--
                                ▼ ポイント:
                                1. h-[15px] → h-5 (20px): text-xs の行の高さに合わせる
                                2. text-xs: 文字サイズを周りと合わせる
                                3. p-0: 余計なパディングを消して文字位置を安定させる
                                4. leading-none: 行間を詰めて垂直位置を合わせやすくする
                            --}}
                            class="bg-yellow-50 input input-ghost input-sm h-5 px-0 py-0 text-xs leading-none text-gray-500 hover:bg-yellow-200 focus:bg-white focus:text-gray-900 w-full"
                        />
                    </div>
                </div>

                <x-date-label label="完了" :date="$goal->completed_at" />
            </div>
        </div>

        {{-- ボタンエリア --}}
        <div class="flex items-center gap-2 ml-4 flex-shrink-0">

            {{-- ▼▼▼ 詳細画面への遷移ボタン ▼▼▼ --}}
            {{-- wire:navigate をつけると、SPAのように高速に画面遷移する --}}
            <a
                href="{{ route('goals.main-tasks', $goal) }}"
                wire:navigate
                class="btn btn-ghost btn-circle btn-xs text-gray-400 hover:text-blue-600 hover:bg-blue-50"
                title="詳細・タスク管理へ"
            >
                {{-- 右矢印アイコン --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </a>

            {{-- ④ 削除ボタン --}}
            {{-- btn-ghost btn-circle で丸いホバーエフェクト付きボタンに --}}
            <button
                wire:click="delete"
                wire:confirm="『{{ $goal->title }}』を削除しますか？"
                class="btn btn-ghost btn-circle btn-xs text-gray-400 hover:text-error hover:bg-red-50"
                title="目標を削除"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
            </button>
        </div>
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
                $progress < 35 => 'bg-warning',
                $progress < 75 => 'bg-info',
                default => 'bg-success',
            };

            // テキストの色も合わせる
            $textColorClass = match(true) {
                $progress < 35 => 'text-orange-500',
                $progress < 75 => 'text-blue-500',
                default => 'text-green-600',
            };
        @endphp

        <div class="mt-3 px-1">
            {{-- <progress> ではなく 2つのdivで実装 --}}
            <div class="flex items-center gap-2 w-9/10">
                <div class="w-full bg-gray-100 h-3 rounded-full overflow-hidden">
                    <div
                        class="h-full rounded-full transition-all duration-500 ease-out {{ $colorClass }}"
                        style="width: {{ $progress }}%"
                    ></div>
                </div>
                {{-- 数字表示（お好みで） --}}
                <span class="text-[12px] font-bold whitespace-nowrap {{ $textColorClass }} w-8 text-right">
                    {{ $progress }}%完了
                </span>
            </div>
        </div>

</div>