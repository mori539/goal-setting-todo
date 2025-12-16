{{-- 「ラベル：yyyy/mm/dd」という形式で出力する部品。 --}}
{{--  Nullの時は何も出力しない。 --}}
@props(['label', 'date'])

@if($date)
    @php
        // ここで Carbon::parse() を使って、確実に Carbon インスタンスに変換する
        $carbonDate = is_string($date) ? \Carbon\Carbon::parse($date) : $date;
    @endphp

    <span {{ $attributes->merge(['class' => 'mx-[5px]']) }}>
        {{ $label }}: {{ $carbonDate->format('Y-m-d') }}
    </span>
@endif