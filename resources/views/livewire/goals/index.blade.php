<div class="max-w-4xl mx-auto py-6 px-4">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">目標一覧</h2>

    {{-- ▼▼▼ 目標追加エリア（daisyUIのアコーディオンを使用） ▼▼▼ --}}
    {{-- collapse: アコーディオンの基本クラス --}}
    {{-- collapse-arrow: 右端に自動で矢印アイコンを追加・回転してくれる --}}
    {{-- collapse-open: Livewireのフラグがtrueの時だけ強制的に開く --}}
    <div class="collapse collapse-arrow border border-blue-200 bg-blue-50 mb-8 rounded-lg overflow-hidden {{ $isCreating ? 'collapse-open' : '' }}">

        {{-- トリガー部分 --}}
        {{-- collapse-title: この領域がクリック可能なヘッダー --}}
        <div
            wire:click="toggleCreateForm"
            class="collapse-title flex items-center gap-2 font-semibold text-gray-700 hover:bg-blue-100 transition-colors cursor-pointer"
        >
            {{-- プラスアイコン（左側） --}}
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
                <label class="form-control w-full">
                    <div class="label pt-0">
                        <span class="label-text font-medium text-gray-700">目標タイトル</span>
                    </div>
                    <input
                        wire:model="newTitle"
                        type="text"
                        placeholder="例：英語の資格を取得する"
                        class="input input-bordered w-full"
                    />
                    @error('newTitle') <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div> @enderror
                </label>

                {{-- 期限 --}}
                <label class="form-control w-full">
                    <div class="label">
                        <span class="label-text font-medium text-gray-700">期限（任意）</span>
                    </div>
                    <input
                        wire:model="newDueAt"
                        type="date"
                        class="input input-bordered w-full"
                    />
                    @error('newDueAt') <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div> @enderror
                </label>

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
    <div class="space-y-4">
        @foreach($goals as $goal)
            <livewire:goals.goal-item :goal="$goal" wire:key="goal-item-{{ $goal->id }}" />
        @endforeach
    </div>
</div>