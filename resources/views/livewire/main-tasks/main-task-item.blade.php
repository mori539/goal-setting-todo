<div
    x-data="{
        isEditingTitle: false,
        isEditingMemo: false,
        isEditingDate: false,
        isEditingSubTask: false,
        showSubTasks: false
    }"
    class="card bg-base-100 border border-gray-200 shadow-sm p-4"
>
    <div class="flex items-start gap-3">

        {{-- ① 完了チェックボックス --}}
        <input
            type="checkbox"
            wire:click="toggleCompletion"
            @checked($task->completed_at)
            class="checkbox checkbox-success checkbox-md rounded-full mt-1 border-2 border-gray-400"
        />

        {{-- 真ん中のエリア --}}
        <div class="w-full min-w-0 grid gap-1">

            {{-- ②-A タイトルエリア --}}
            <div class="relative">
                <h3
                    x-show="!isEditingTitle"
                    @click="isEditingTitle = true; $nextTick(() => $refs.titleInput.focus())"
                    class="text-lg font-bold text-gray-800 hover:bg-gray-100 rounded px-1 -ml-1 transition-colors break-words cursor-text {{ $task->completed_at ? 'line-through text-gray-400' : '' }}"
                >
                    {{ $task->title }}
                </h3>
                <input
                    x-show="isEditingTitle"
                    x-cloak
                    x-ref="titleInput"
                    wire:model="editingTitle"
                    wire:blur="updateTitle"
                    @blur="isEditingTitle = false"
                    @keydown.enter.prevent="$event.target.blur()"
                    @keydown.escape.prevent="isEditingTitle = false; $wire.editingTitle = '{{ $task->title }}'"
                    type="text"
                    class="input input-sm input-ghost w-full text-lg font-bold text-gray-800 px-1 -ml-1 focus:bg-white border-b border-blue-400 rounded-sm h-auto focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-1 rounded"
                />
            </div>
            @error('editingTitle') <span class="text-error text-xs">{{ $message }}</span> @enderror

            {{-- ②-B メモエリア --}}
            <div class="relative mt-1">
                <div
                    x-show="!isEditingMemo"
                    @click="isEditingMemo = true; $nextTick(() => $refs.memoInput.focus())"
                    class="cursor-text group min-h-[1.5rem]"
                >
                    @if($task->memo)
                        <p class="text-sm text-gray-700 bg-gray-50 p-2 rounded hover:bg-gray-100 transition-colors {{ $task->completed_at ? 'opacity-50' : '' }}">
                            {!! nl2br(e($task->memo)) !!}

                        </p>
                    @else
                        <div class="text-xs text-gray-500 flex items-center gap-1 opacity-70 group-hover:opacity-100 transition-opacity p-1">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            メモを追加
                        </div>
                    @endif
                </div>
                <textarea
                    x-show="isEditingMemo"
                    x-cloak
                    x-ref="memoInput"
                    wire:model="editingMemo"
                    wire:blur="updateMemo"
                    @blur="isEditingMemo = false"
                    @keydown.escape.prevent="isEditingMemo = false; $wire.editingMemo = '{{ $task->memo }}'"
                    class="textarea textarea-bordered textarea-sm w-full h-24 text-sm leading-normal focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-1 rounded"
                    placeholder="メモを入力..."
                ></textarea>
            </div>
            @error('editingMemo') <span class="text-error text-xs">{{ $message }}</span> @enderror

            {{-- ③ 日付情報 --}}
            <div class="text-xs text-gray-500 flex flex-wrap gap-2 items-center mt-1">
                <x-date-label label="作成" :date="$task->created_at" />

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
                            class="input input-ghost input-sm h-5 px-0 py-0 text-xs leading-none text-gray-500 hover:bg-gray-100 focus:bg-white focus:text-gray-900 w-full focus:outline-none focus:border-blue-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-1 rounded"
                        />
                    </div>
                </div>

                <x-date-label label="完了" :date="$task->completed_at" />
            </div>
        </div>

        {{-- ④ 右側アクションエリア --}}
        <div class="flex flex-col items-center gap-1">
            {{-- 削除ボタン --}}
            <button
                wire:click="delete"
                wire:confirm="タスク『{{ $task->title }}』を削除しますか？"
                class="btn btn-ghost btn-circle btn-xs text-gray-400 hover:text-error hover:bg-red-50"
                title="タスクの削除"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
            </button>

            {{-- ▼▼▼ 追加：サブタスク開閉ボタン ▼▼▼ --}}
            <div class="relative inline-flex mt-1">
                <button
                    @click="showSubTasks = !showSubTasks"
                    class="btn btn-ghost btn-circle btn-xs text-gray-400"
                    :class="{'text-blue-500 bg-blue-50': showSubTasks}"
                    title="サブタスクを表示/非表示"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 17.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                </button>
                {{-- バッジ（サブタスクがある時だけ） --}}
                @if($task->subTasks->count() > 0)
                    <span class="absolute -top-1 -right-3 bg-blue-500 text-white text-[10px] w-4 h-4 rounded-full flex items-center justify-center pointer-events-none">
                        {{ $task->subTasks->count() }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- ▼▼▼ 修正：アニメーション付き進捗バー ▼▼▼ --}}
    @if($task->subTasks->count() > 0)
        @php
            $progress = $task->progress;
            $colorClass = match(true) {
                $progress < 35 => 'bg-warning',
                $progress < 75 => 'bg-info',
                default => 'bg-success',
            };
            $textColorClass = match(true) {
                $progress < 35 => 'text-orange-500',
                $progress < 75 => 'text-blue-500',
                default => 'text-green-600',
            };
        @endphp

        <div class="mt-3 px-1">
            {{-- <progress> ではなく 2つのdivで実装 --}}
            <div class="flex items-center gap-2 w-9/10">
                <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden">
                    <div
                        class="h-full rounded-full transition-all duration-500 ease-out {{ $colorClass }}"
                        style="width: {{ $progress }}%"
                    ></div>
                </div>
                {{-- 数字表示（お好みで） --}}
                <span class="text-[10px] font-bold whitespace-nowrap {{ $textColorClass }} w-8 text-right">
                    {{ $progress }}%
                </span>
            </div>
        </div>
    @endif


    {{-- ▼▼▼ 修正：サブタスク一覧エリア（開閉式） ▼▼▼ --}}
    {{-- x-show="showSubTasks" で開閉を制御 --}}
    <div x-show="showSubTasks" x-cloak class="mt-4 pl-8 border-l-2 border-gray-100 space-y-2">

        {{-- サブタスク一覧 --}}
        <div class="flex flex-col gap-1">
            @foreach($task->subTasks as $subTask)
                <livewire:sub-tasks.sub-task-item :subTask="$subTask" wire:key="sub-{{ $subTask->id }}" />
            @endforeach
        </div>

        {{-- サブタスク追加フォーム --}}
        <div
            x-show="!isEditingSubTask"
            @click="isEditingSubTask = true; $nextTick(() => $refs.subTaskInput.focus())"
            class="cursor-text group min-h-[1.5rem] mt-2"
        >
            <div class="text-xs text-gray-500 flex items-center gap-1 opacity-70 group-hover:opacity-100 transition-opacity p-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                サブタスクを追加
            </div>
        </div>

        <input
            x-show="isEditingSubTask"
            x-cloak
            x-ref="subTaskInput"
            wire:model="newSubTaskTitle"
            wire:keydown.enter="storeSubTask"
            @blur="isEditingSubTask = false"
            type="text"
            placeholder="サブタスクを追加 (Enterで保存)"
            class="input input-sm input-bordered w-full focus:outline-none focus:border-blue-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-1 rounded"
        />
    </div>
</div>