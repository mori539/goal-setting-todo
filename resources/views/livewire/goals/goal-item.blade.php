<div
    {{-- alpain.jsで使用する変数を定義 --}}
    x-data="{ isEditing: false, isEditingDate: false }"
    class="card bg-yellow-50 border border-yellow-200 shadow-sm rounded-lg p-4"
>

    {{-- 進捗バーよりも上の部分 --}}
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

                {{-- 目標タイトル 表示モード --}}
                <h3
                    x-show="!isEditing"
                    @click="isEditing = true; $nextTick(() => $refs.titleInput.focus())"
                    class="text-lg font-bold text-gray-800 cursor-pointer hover:bg-yellow-200 rounded px-1 -ml-1 transition-colors break-words
                        {{ $goal->completed_at ? 'line-through text-gray-400' : '' }}"
                >
                    {{ $goal->title }}
                </h3>

                {{-- 目標タイトル 編集モード --}}
                <input
                    x-show="isEditing"                                                  {{-- 編集フラグがtrueの際にこのinput要素が表示される --}}
                    x-cloak                                                             {{-- app.cssに定義。display:noneしている --}}
                    x-ref="titleInput"                                                  {{-- $refs.titleInput.focus()の関係先。 --}}
                    wire:model="editingTitle"                                           {{-- PHP処理側(に渡す変数値。 --}}
                    wire:blur="updateTitle;"                                            {{-- フォーカスアウトした際に updateTitle()の処理が実行される --}}
                    @blur="isEditing = false"                                           {{-- フォーカスアウトした際に編集フラグをオフにする --}}
                    @keydown.enter.prevent="$event.target.blur()"                       {{-- エンターキーを押した際にフォーカスアウトする --}}
                    @keydown.escape.prevent="isEditing = false; $wire.resetTitle()"     {{-- Escキーを押した際に編集フラグをオフにし、resetTitle()で目標タイトルをもとに戻す --}}
                    type="text"
                    class="input input-sm input-ghost w-full text-lg font-bold text-gray-800 px-1 -ml-1 h-auto focus:bg-white focus:outline-none focus:border-blue-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-1 rounded"
                />
            </div>
            @error('editingTitle') <span class="text-error text-xs">{{ $message }}</span> @enderror

            {{-- 日付ラベル --}}
            <div class="text-xs text-gray-500 flex flex-wrap gap-2 items-center">

                {{-- 作成日時（date-label.blade.phpのコンポーネントを使用している） --}}
                <x-date-label label="作成" :date="$goal->created_at" />

                {{-- 期限日時 --}}
                <div class="flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                    </svg>
                    <span class="text-gray-500 shrink-0">期限:</span> {{-- shrink-0: 縮こまらないように固定 --}}

                    <div class="w-[85px]">
                        {{-- date-pickerのコンポーネントを使用している --}}
                        {{-- date-pickerコンポーネントはflatpickerを使用している --}}
                        <x-date-picker
                            wire:model.live="editingDueAt"
                            placeholder="---"
                            class="bg-yellow-50 input input-ghost input-sm h-5 px-0 py-0 text-xs leading-none text-gray-500 hover:bg-yellow-200 focus:bg-white focus:text-gray-900 w-full focus:outline-none focus:border-blue-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-1 rounded"
                        />
                    </div>
                </div>

                {{-- 完了日時（date-label.blade.phpのコンポーネントを使用している） --}}
                <x-date-label label="完了" :date="$goal->completed_at" />
            </div>
        </div>

        {{-- ボタンエリア --}}
        <div class="flex items-center gap-2 ml-4 flex-shrink-0">

            {{-- 詳細画面への遷移ボタン --}}
            {{-- wire:navigate をつけるとシングルページアプリケーションのように高速に画面遷移する --}}
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

            {{-- 削除ボタン --}}
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
            // 35-74%: info (青系)
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
                {{-- 数字表示 --}}
                <span class="text-[12px] font-bold whitespace-nowrap {{ $textColorClass }} w-8 text-right">
                    {{ $progress }}%完了
                </span>
            </div>
        </div>

</div>