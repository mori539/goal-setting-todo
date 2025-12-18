<div
    x-data="{ isEditingSubTaskTitle: false, isEditingSubTaskMemo: false }"
    class="flex items-start gap-2 py-2 border-b last:border-0 border-gray-100"
>
    {{-- 完了チェックボックス（小さめ） --}}
    <input
        type="checkbox"
        wire:click="toggleCompletion"
        @checked($subTask->completed_at)
        class="checkbox checkbox-xs checkbox-success rounded-full mt-1.5"
    />

    <div class="w-full min-w-0 grid gap-0.5">
        {{-- タイトル --}}
        <div class="relative">
            <div
                x-show="!isEditingSubTaskTitle"
                @click="isEditingSubTaskTitle = true; $nextTick(() => $refs.titleInput.focus())"
                class="text-sm text-gray-700 hover:bg-gray-100 rounded px-1 -ml-1 cursor-text break-words {{ $subTask->completed_at ? 'line-through text-gray-400' : '' }}"
            >
                {{ $subTask->title }}
            </div>
            <input
                x-show="isEditingSubTaskTitle"
                x-cloak
                x-ref="titleInput"
                wire:model="editingTitle"
                wire:blur="updateTitle"
                @blur="isEditingSubTaskTitle = false"
                @keydown.enter.prevent="$event.target.blur()"
                type="text"
                class="input input-xs input-ghost w-full text-sm px-1 -ml-1 h-auto focus:bg-white border-b border-blue-300 rounded-none"
            />
        </div>
        @error('editingTitle') <span class="text-error text-xs">{{ $message }}</span> @enderror

        {{-- メモ --}}
        <div class="relative">
            <div
                x-show="!isEditingSubTaskMemo"
                @click="isEditingSubTaskMemo = true; $nextTick(() => $refs.memoInput.focus())"
                class="text-xs text-gray-800 hover:bg-gray-50 rounded pl-5 -ml-1 cursor-text min-h-[1.5rem] {{ $subTask->completed_at ? 'opacity-50' : '' }}"
            >
                @if($subTask->memo)
                    {!! nl2br(e($subTask->memo)) !!}
                @else
                    <span class="opacity-0 hover:opacity-100 text-gray-800">メモを入力...</span>
                @endif
            </div>
            <textarea
                x-show="isEditingSubTaskMemo"
                x-cloak
                x-ref="memoInput"
                wire:model="editingMemo"
                wire:blur="updateMemo"
                @blur="isEditingSubTaskMemo = false"
                class="textarea textarea-bordered textarea-xs w-full h-16 leading-normal"
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