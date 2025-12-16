<div class="max-w-4xl mx-auto py-6 px-4">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">目標一覧</h2>

    {{-- 目標追加のバー --}}
    <div class="mb-8 border border-blue-200 rounded-lg bg-blue-50 overflow-hidden">
        <button
            wire:click="toggleCreateForm"
            class="w-full text-left px-4 py-3 flex items-center justify-between hover:bg-blue-100 transition-colors"
        >
            <div class="flex items-center text-gray-700 font-semibold">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                目標追加
            </div>
            {{-- 下矢印アイコン（展開、非展開で180度回転） --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform transition-transform {{ $isCreating ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        {{-- 目標追加フォームを展開したときに表示 --}}
        @if($isCreating)
            <form wire:submit="store" class="p-4 bg-white border-t border-blue-100">
                <div class="space-y-4">

                    {{-- 目標タイトル --}}
                    <div>
                        <label for="newTitle" class="block text-sm font-medium text-gray-700">目標タイトル</label>
                        <input
                            wire:model="newTitle"
                            type="text"
                            id="newTitle"
                            class="h-[30px] mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:outline-none focus:ring focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            placeholder="例：英語の資格を取得する"
                        >
                        @error('newTitle') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- 期限 --}}
                    <div>
                        <label for="newDueAt" class="block text-sm font-medium text-gray-700">期限（任意）</label>
                        <input
                            wire:model="newDueAt"
                            type="date"
                            id="newDueAt"
                            class="h-[30px] mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:outline-none focus:ring focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        >
                        @error('newDueAt') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- 作成ボタン --}}
                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow transition-colors"
                        >
                            作成する
                        </button>
                    </div>
                </div>
            </form>
        @endif
    </div>

    <div class="space-y-4">
        {{-- 目標を表示ある分表示 --}}
        @foreach($goals as $goal)
            <div class="border border-yellow-200 bg-yellow-50 rounded-lg p-4 shadow-sm">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 flex items-center">
                            <span class="mr-2">○</span>
                            {{ $goal->title }}
                        </h3>
                            <p class="text-xs text-gray-500 mt-1">
                                {{-- date-label.blade.phpのコンポーネントを使用 --}}
                                <x-date-label label="作成" :date="$goal->created_at" />
                                <x-date-label label="期限" :date="$goal->due_at" />
                                <x-date-label label="完了" :date="$goal->completed_at" />
                            </p>

                    </div>

                    <button class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                        </svg>
                    </button>
                </div>

                <div class="mt-3 flex items-center">
                    <div class="w-5/6 bg-gray-200 rounded-full h-4 mr-2">
                        <div class="bg-green-400 h-4 rounded-full" style="width: 0%"></div>
                    </div>
                    <span class="ml-[5px] text-sm font-bold text-gray-600">0% 完了</span>
                </div>
            </div>
        @endforeach
    </div>
</div>