@props([
    'progress' => 0,                    // 進捗率（必須）
    'height' => 'h-3',                  // バーの高さ（デフォルトは目標用の太め）
    'textSize' => 'text-[12px]',        // 文字サイズ
    'showLabel' => true,                // 「完了」という文字をつけるかどうか
])

@php
    // 色の決定ロジック（共通化）
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

{{-- 二つのdiv要素で進捗バーを表現（アニメーションを付けるため） --}}
<div class="mt-3 px-1">
    <div class="flex items-center gap-2 w-9/10">
        {{-- バー本体 --}}
        <div class="w-full bg-gray-100 {{ $height }} rounded-full overflow-hidden">
            <div
                class="h-full rounded-full transition-all duration-500 ease-out {{ $colorClass }}"
                style="width: {{ $progress }}%"
            ></div>
        </div>

        {{-- 数字表示 --}}
        <span class="{{ $textSize }} font-bold whitespace-nowrap {{ $textColorClass }} w-auto min-w-[3rem] text-right">
            {{ $progress }}%{{ $showLabel ? '完了' : '' }}
        </span>
    </div>
</div>