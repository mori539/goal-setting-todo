<div
        {{-- alpain.jsで使用する変数を定義 --}}
    x-data="{ isEditingSubTaskTitle: false, isEditingSubTaskMemo: false }"
    class="flex items-start gap-2 py-2 border-b last:border-0 border-gray-100"
>

    {{-- 完了チェックボックス --}}
    <input
        type="checkbox"
        wire:click="toggleCompletion"
        @checked($subTask->completed_at)
        class="checkbox checkbox-xs checkbox-success rounded-full mt-1.5"
    />

    {{-- タイトル、メモエリア --}}
    <div class="w-full min-w-0 grid gap-0.5">
        {{-- タイトル --}}
        <div class="relative">
            {{-- 表示モード --}}
            <div
                x-show="!isEditingSubTaskTitle"         {{-- タイトル編集フラグがfalseの際にこのinput要素が表示される --}}
                @click="isEditingSubTaskTitle = true; $nextTick(() => $refs.titleInput.focus())"
                class="text-sm text-gray-700 hover:bg-gray-100 rounded px-1 -ml-1 cursor-text break-words {{ $subTask->completed_at ? 'line-through text-gray-400' : '' }}"
            >
                {{ $subTask->title }}
            </div>

            {{-- 編集モード --}}
            <input
                x-show="isEditingSubTaskTitle"                      {{-- タイトル編集フラグがtrueの際にこのinput要素が表示される --}}
                x-cloak                                             {{-- app.cssに定義。display:noneしている --}}
                x-ref="titleInput"                                  {{-- $refs.titleInput.focus()の関係先。 --}}
                wire:model="editingTitle"                           {{-- PHP処理側に渡す変数値。 --}}
                wire:blur="updateTitle"                             {{-- フォーカスアウトした際に updateTitle()の処理が実行される --}}
                @blur="isEditingSubTaskTitle = false"               {{-- フォーカスアウトした際にタイトル編集フラグをオフにする --}}
                @keydown.enter.prevent="$event.target.blur()"       {{-- エンターキーを押した際にフォーカスアウトする --}}
                type="text"
                class="input input-xs input-ghost w-full text-sm px-1 -ml-1 h-auto focus:outline-none focus:border-blue-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-1 rounded"
            />
        </div>
        {{-- エラー通知 --}}
        @error('editingTitle') <span class="text-error text-xs">{{ $message }}</span> @enderror

        {{-- メモ --}}
        <div class="relative">
            {{-- 表示モード --}}
            <div
                x-show="!isEditingSubTaskMemo"          {{-- メモ編集フラグがfalseの際にこのinput要素が表示される --}}
                @click="isEditingSubTaskMemo = true; $nextTick(() => $refs.memoInput.focus())"
                class="text-xs text-gray-800 hover:bg-gray-50 rounded pl-5 -ml-1 cursor-text min-h-[1.5rem] {{ $subTask->completed_at ? 'opacity-50' : '' }}"
            >
                @if($subTask->memo)
                    {{-- メモが存在した場合はDB保存値を表示 --}}
                    {!! nl2br(e($subTask->memo)) !!}
                @else
                    {{-- 存在しない場合は入力欄をわかりやすく表示 --}}
                    <span class="opacity-0 hover:opacity-100 text-gray-800">メモを入力...</span>
                @endif
            </div>

            {{-- 編集モード --}}
            <textarea
                x-show="isEditingSubTaskMemo"                   {{-- メモ編集フラグがtrueの際にこのinput要素が表示される --}}
                x-cloak                                         {{-- app.cssに定義。display:noneしている --}}
                x-ref="memoInput"                               {{-- $refs.memoInput.focus()の関係先。 --}}
                wire:model="editingMemo"                        {{-- PHP処理側に渡す変数値。 --}}
                wire:blur="updateMemo"                          {{-- フォーカスアウトした際に updateMemo()の処理が実行される --}}
                @blur="isEditingSubTaskMemo = false"            {{-- フォーカスアウトした際にメモ編集フラグをオフにする --}}
                class="textarea textarea-bordered textarea-xs w-full h-16 leading-normal focus:outline-none focus:border-blue-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-1 rounded"
                placeholder="サブタスクのメモ"
            ></textarea>
        </div>
    </div>

    {{-- 削除ボタン --}}
    <button
        wire:click="delete"
        wire:confirm="削除しますか？"
        class="btn btn-ghost btn-circle btn-xs text-gray-300 hover:text-error"
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>