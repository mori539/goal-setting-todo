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
            class="checkbox checkbox-success checkbox-md rounded-full mt-1 border-2 border-gray-400"
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
                <div class="relative flex items-center gap-1">
                    <span class="text-gray-500">期限:</span>

                    {{-- 表示モード --}}
                    <span
                        x-show="!isEditingDate"
                        @click="isEditingDate = true; $nextTick(() => $refs.dateInput.showPicker())"
                        class="cursor-pointer hover:bg-yellow-200 hover:text-gray-700 px-1 rounded transition-colors"
                    >
                        @if($goal->due_at)
                            {{ $goal->due_at->format('Y-m-d') }}
                        @else
                            <span class="text-gray-400 opacity-50">---</span>
                        @endif
                    </span>

                    {{-- 編集モード --}}
                    <input
                        x-show="isEditingDate"
                        x-cloak
                        x-ref="dateInput"
                        type="date"
                        wire:model="editingDueAt"
                        wire:change="updateDueAt"
                        @change="isEditingDate = false"
                        @click.outside="isEditingDate = false"
                        class="input input-bordered input-xs h-6 w-[110px] px-1 bg-white"
                    />
                </div>

                <x-date-label label="完了" :date="$goal->completed_at" />
            </div>
        </div>

        {{-- ④ 削除ボタン --}}
        {{-- btn-ghost btn-circle で丸いホバーエフェクト付きボタンに --}}
        <button
            wire:click="delete"
            wire:confirm="『{{ $goal->title }}』を削除しますか？"
            class="btn btn-ghost btn-circle btn-xs text-gray-400 hover:text-error hover:bg-red-50"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
            </svg>
        </button>
    </div>

    {{-- ⑤ 進捗バー --}}
    {{-- 複雑なdiv構成をやめて progress コンポーネントを使用 --}}
    <div class="w-5/6 mt-3 flex items-center gap-2">
        <progress
            class="progress progress-success w-full bg-gray-200"
            value="0"
            max="100">
        </progress>
        <span class="text-xs font-bold text-gray-600 whitespace-nowrap">0% 完了</span>
    </div>
</div>