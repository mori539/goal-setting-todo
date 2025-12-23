<div
    {{-- alpain.jsで使用する変数を定義 --}}
    x-data="{
        isEditingTitle: false,
        isEditingMemo: false,
        isEditingDate: false,
        isEditingSubTask: false,
        showSubTasks: false
    }"
    class="card bg-base-100 border border-gray-200 shadow-sm p-4"
>

    {{-- 進捗バーよりも上の部分 --}}
    <div class="flex items-start gap-3">

        {{-- 完了チェックボックス --}}
        <input
            type="checkbox"
            wire:click="toggleCompletion"
            @checked($task->completed_at)
            class="checkbox checkbox-success checkbox-md rounded-full mt-1 border-2 border-gray-400"
        />

        {{-- 真ん中のエリア --}}
        <div class="w-full min-w-0 grid gap-1">

            {{-- メインタスク タイトルエリア --}}
            <div class="relative">

                {{-- メインタスクタイトル 表示モード --}}
                <h3
                    x-show="!isEditingTitle"    {{-- タイトル編集フラグがfalseの際にこのinput要素が表示される --}}
                    @click="isEditingTitle = true; $nextTick(() => $refs.titleInput.focus())"   {{-- クリックした際にタイトル編集フラグをtrueにする。その後編集モードへ移行。 --}}
                    class="text-lg font-bold text-gray-800 hover:bg-blue-100 rounded px-1 -ml-1 transition-colors break-words cursor-text {{ $task->completed_at ? 'line-through text-gray-400' : '' }}"
                >
                    {{ $task->title }}
                </h3>

                {{-- メインタスクタイトル 編集モード --}}
                <input
                    x-show="isEditingTitle"                                                                             {{-- タイトル編集フラグがtrueの際にこのinput要素が表示される --}}
                    x-cloak                                                                                             {{-- app.cssに定義。display:noneしている --}}
                    x-ref="titleInput"                                                                                  {{-- $refs.titleInput.focus()の関係先。 --}}
                    wire:model="editingTitle"                                                                           {{-- PHP処理側(に渡す変数値。 --}}
                    wire:blur="updateTitle"                                                                             {{-- フォーカスアウトした際に updateTitle()の処理が実行される --}}
                    @blur="isEditingTitle = false"                                                                      {{-- フォーカスアウトした際にタイトル編集フラグをオフにする --}}
                    @keydown.enter.prevent="$event.target.blur()"                                                       {{-- エンターキーを押した際にフォーカスアウトする --}}
                    @keydown.escape.prevent="isEditingTitle = false; $wire.resetTitle()"                                {{-- Escキーを押した際に編集フラグをオフにし、resetTitle()でメインタスクタイトルをもとに戻す --}}
                    type="text"
                    class="input input-sm input-ghost w-full text-lg font-bold text-gray-800 px-1 -ml-1 focus:bg-white border-b border-blue-400 rounded-sm h-auto focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-1 rounded"
                />
            </div>
            {{-- エラー通知 --}}
            @error('editingTitle') <span class="text-error text-xs">{{ $message }}</span> @enderror

            {{-- メモエリア --}}
            <div class="relative mt-1">
                {{-- メモ 表示モード --}}
                <div
                    x-show="!isEditingMemo"     {{-- メモ編集フラグがfalseの際にこのinput要素が表示される --}}
                    @click="isEditingMemo = true; $nextTick(() => $refs.memoInput.focus())"     {{-- クリックした際にタイトル編集フラグをtrueにする。その後編集モードへ移行。 --}}
                    class="cursor-text group min-h-[1.5rem]"
                >


                    @if($task->memo)
                    {{-- 保存値がある場合は保存値を表示 --}}
                    <p class="text-sm text-gray-700 bg-gray-50 p-2 rounded hover:bg-blue-100 transition-colors {{ $task->completed_at ? 'opacity-50' : '' }}">
                        {!! nl2br(e($task->memo)) !!}

                    </p>
                    @else
                    {{-- 保存値がない場合は「+メモを追加」テキストを表示 --}}
                    <div class="text-xs text-gray-500 flex items-center gap-1 opacity-70 group-hover:opacity-100 hover:bg-blue-50 rounded-sm transition-opacity p-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        メモを追加
                    </div>
                    @endif
                </div>

                {{-- メモ 編集モード --}}
                <textarea
                    x-show="isEditingMemo"                                                                          {{-- メモ編集フラグがtrueの際にこのinput要素が表示される --}}
                    x-cloak                                                                                         {{-- app.cssに定義。display:noneしている --}}
                    x-ref="memoInput"                                                                               {{-- $refs.memoInput.focus()の関係先。 --}}
                    wire:model="editingMemo"                                                                        {{-- PHP処理側に渡す変数値。 --}}
                    wire:blur="updateMemo"                                                                          {{-- フォーカスアウトした際に updateMemo()の処理が実行される --}}
                    @blur="isEditingMemo = false"                                                                   {{-- フォーカスアウトした際にメモ編集フラグをオフにする --}}
                    @keydown.escape.prevent="isEditingMemo = false; $wire.resetMemo()"                              {{-- Escキーを押した際に編集フラグをオフにし、resetMemo()でメモをもとに戻す --}}
                    @keydown.ctrl.enter="$event.target.blur()"                                                      {{-- Ctrl+Enterを押した際にフォーカスアウトする --}}
                    class="textarea textarea-bordered textarea-sm w-full h-24 text-sm leading-normal focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-1 rounded"
                    placeholder="メモを入力(Ctrl + Enter で保存)"
                ></textarea>
            </div>
            {{-- エラー通知 --}}
            @error('editingMemo') <span class="text-error text-xs">{{ $message }}</span> @enderror

            {{-- 日付情報 --}}
            <div class="text-xs text-gray-500 flex flex-wrap gap-2 items-center mt-1">

                {{-- 作成日時（date-label.blade.phpのコンポーネントを使用している） --}}
                <x-date-label label="作成" :date="$task->created_at" />

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
                            class="input input-ghost input-sm h-5 px-0 py-0 text-xs leading-none text-gray-500 hover:bg-blue-100 focus:bg-white focus:text-gray-900 w-full focus:outline-none focus:border-blue-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-1 rounded"
                        />
                    </div>
                </div>

                {{-- 完了日時（date-label.blade.phpのコンポーネントを使用している） --}}
                <x-date-label label="完了" :date="$task->completed_at" />
            </div>
        </div>

        {{-- 右側アクションエリア --}}
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

            {{-- サブタスク開閉ボタン --}}
            <div class="relative inline-flex mt-1">
                <button
                    @click="showSubTasks = !showSubTasks"       {{-- クリックでサブタスク開閉フラグを切り替え --}}
                    class="btn btn-ghost btn-circle btn-xs text-gray-400"
                    :class="{'text-blue-500 bg-blue-50': showSubTasks}"
                    title="サブタスクを表示/非表示"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 17.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                </button>
                {{-- バッジ（サブタスクがある時だけサブタスクの件数を表示する） --}}
                @if($task->subTasks->count() > 0)
                    <span class="absolute -top-1 -right-3 bg-blue-500 text-white text-[10px] w-4 h-4 rounded-full flex items-center justify-center pointer-events-none">
                        {{ $task->subTasks->count() }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- 修正：アニメーション付き進捗バー --}}
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
                {{-- 数字表示 --}}
                <span class="text-[10px] font-bold whitespace-nowrap {{ $textColorClass }} w-8 text-right">
                    {{ $progress }}%
                </span>
            </div>
        </div>
    @endif


    {{-- 修正：サブタスク一覧エリア（開閉式） --}}
    {{-- x-show="showSubTasks" で開閉を制御 --}}
    <div
        x-show="showSubTasks"           {{-- サブタスク表示フラグがオンになったら表示（サブタスク開閉ボタンと連動） --}}
        x-collapse                      {{-- 開閉にアニメーションを付ける --}}
        x-cloak                         {{-- app.cssに定義。display:noneしている --}}
        class="mt-4 pl-8 border-l-2 border-gray-100 space-y-2"
    >

        {{-- サブタスク一覧 --}}
        <div class="flex flex-col gap-1">
            @foreach($task->subTasks as $subTask)
                <livewire:sub-tasks.sub-task-item :subTask="$subTask" wire:key="sub-{{ $subTask->id }}" />
            @endforeach
        </div>

        {{-- サブタスク追加フォーム --}}
        {{-- 表示モード --}}
        <div
            x-show="!isEditingSubTask"      {{-- サブタスク編集フラグがfalseの際にこのdiv要素が表示される --}}
            @click="isEditingSubTask = true; $nextTick(() => $refs.subTaskInput.focus())"
            class="cursor-text group min-h-[1.5rem] mt-2 hover:bg-gray-100 rounded-sm"
        >
            <div class="text-xs text-gray-500 flex items-center gap-1 opacity-70 group-hover:opacity-100 transition-opacity p-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                サブタスクを追加
            </div>
        </div>

        {{-- 編集モード --}}
        <input
            x-show="isEditingSubTask"                               {{-- サブタスク編集フラグがtrueの際にこのinput要素が表示される --}}
            x-cloak                                                 {{-- app.cssに定義。display:noneしている --}}
            x-ref="subTaskInput"                                    {{-- $refs.subTaskInput.focus()の関係先。 --}}
            wire:model="newSubTaskTitle"                            {{-- PHP処理側に渡す変数値。 --}}
            wire:keydown.enter="storeSubTask"                       {{-- フォーカスアウトした際に storeSubTask()の処理が実行される --}}
            @blur="isEditingSubTask = false"                        {{-- フォーカスアウトした際にサブタスク編集フラグをオフにする --}}
            type="text"
            placeholder="サブタスクを追加 (Enterで保存)"
            class="input input-sm input-bordered w-full focus:outline-none focus:border-blue-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-1 rounded"
        />
    </div>
</div>