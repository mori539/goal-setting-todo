@props([
    'mode' => 'goal', // 'goal' または 'task'
    'filter' => 'all', // 現在選択中のフィルター（親から受け取る）
])

<div class="w-full md:w-64 flex-shrink-0 flex flex-col gap-6">

    {{-- ▼▼▼▼▼ エリア1: 目標検索 & フィルター ▼▼▼▼▼ --}}
    <div class="bg-base-100 rounded-box p-4 shadow-sm border border-gray-200">

            <div class=" border-l-4 border-yellow-500">
                <h3 class="font-bold mb-2 pl-2">目標検索</h3>
            </div>

        {{-- 目標検索ボックス --}}
        @if($mode === 'goal')
            {{-- Goal画面: リアルタイム検索 --}}
            <label class="input input-bordered input-sm flex items-center gap-2 mb-4">
                <input wire:model.live.debounce.300ms="search" type="text" class="grow" placeholder="検索..." />
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4 opacity-70"><path fill-rule="evenodd" d="M9.965 11.026a5 5 0 1 1 1.06-1.06l2.755 2.754a.75.75 0 1 1-1.06 1.06l-2.755-2.754ZM10.5 7a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z" clip-rule="evenodd" /></svg>
            </label>
        @else
            {{-- Task画面: 目標一覧へ検索しにいくフォーム --}}
            <form action="{{ route('goals.index') }}" method="GET" class="mb-4">
                <label class="input input-bordered input-sm flex items-center gap-2">
                    <input name="search" type="text" class="grow" placeholder="検索..." />
                    <button type="submit"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4 opacity-70"><path fill-rule="evenodd" d="M9.965 11.026a5 5 0 1 1 1.06-1.06l2.755 2.754a.75.75 0 1 1-1.06 1.06l-2.755-2.754ZM10.5 7a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z" clip-rule="evenodd" /></svg></button>
                </label>
            </form>
        @endif

        <ul class="menu w-full gap-1 p-0 [&_li>*]:rounded-md">
            {{-- Goal画面なら wire:click, Task画面なら リンク(aタグ) に切り替えるロジック --}}
            @php
                $goalLinks = [
                    'all' => ['label' => 'すべての目標一覧', 'icon' => 'M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z'],
                    'uncompleted_over_due' => ['label' => '未完了期限切れ', 'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z'],
                    'due_soon' => ['label' => '期限日間近', 'icon' => 'M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0M3.124 7.5A8.969 8.969 0 0 1 5.292 3m13.416 0a8.969 8.969 0 0 1 2.168 4.5'],
                    'uncompleted' => ['label' => '進行中', 'icon' => 'm12.75 15 3-3m0 0-3-3m3 3h-7.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                    'completed' => ['label' => '完了済み', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                ];
            @endphp

            @foreach($goalLinks as $key => $item)
                <li>
                    @if($mode === 'goal')
                        {{-- Goal画面: 自分のプロパティを変更 --}}
                        <button wire:click="$set('filter', '{{ $key }}')" class="{{ $filter === $key ? 'bg-orange-200' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" /></svg>
                            {{ $item['label'] }}
                        </button>
                    @else
                        {{-- Task画面: 目標一覧ページへ遷移 --}}
                        <a href="{{ route('goals.index', ['filter' => $key]) }}" wire:navigate class="hover:bg-gray-100">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" /></svg>
                            {{ $item['label'] }}
                        </a>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>


    {{-- ▼▼▼▼▼ エリア2: タスク検索 & フィルター (Taskモードのみ表示) ▼▼▼▼▼ --}}
    @if($mode === 'task')
    <div class="bg-base-100 rounded-box p-4 shadow-sm border border-gray-200">

        <div class=" border-l-4 border-blue-500">
            <h3 class="font-bold mb-2 pl-2">タスク検索</h3>
        </div>

        {{-- タスク検索ボックス --}}
        <label class="input input-bordered input-sm flex items-center gap-2 mb-4">
            <input wire:model.live.debounce.300ms="search" type="text" class="grow" placeholder="現在の目標内..." />
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4 opacity-70"><path fill-rule="evenodd" d="M9.965 11.026a5 5 0 1 1 1.06-1.06l2.755 2.754a.75.75 0 1 1-1.06 1.06l-2.755-2.754ZM10.5 7a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z" clip-rule="evenodd" /></svg>
        </label>

        <ul class="menu w-full gap-1 p-0 [&_li>*]:rounded-md">
            @php
                $taskLinks = [
                    'all' => 'すべてのタスク',
                    'uncompleted_over_due' => '未完了期限切れ',
                    'due_soon' => '期限間近',
                    'uncompleted' => '進行中',
                    'completed' => '完了済み',
                ];
            @endphp

            @foreach($taskLinks as $key => $label)
            <li>
                <button wire:click="$set('filter', '{{ $key }}')" class="{{ $filter === $key ? 'bg-sky-200' : '' }}">
                    {{-- アイコンは適宜変更可 --}}
                    @switch($key)
                        @case('completed')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            @break
                        @case('uncompleted_over_due')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                            </svg>
                            @break
                        @case('due_soon')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0M3.124 7.5A8.969 8.969 0 0 1 5.292 3m13.416 0a8.969 8.969 0 0 1 2.168 4.5" />
                            </svg>
                            @break
                        @case('uncompleted')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m12.75 15 3-3m0 0-3-3m3 3h-7.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            @break
                        @default
                            {{-- all --}}
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                    @endswitch
                    {{ $label }}
                </button>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

</div>