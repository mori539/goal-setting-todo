{{-- type も受け取れるようにする（デフォルトは success） --}}
@props([
    'message' => session('status'),
    'type' => session('type', 'success'),
])

<div
    x-data="{
        show: false,
        message: '{{ $message }}',
        type: '{{ $type }}', // 初期タイプ
        timeout: null,

        // 第2引数で type を受け取る（指定がなければ success）
        showToast(msg, type = 'success') {
            this.message = msg;
            this.type = type;
            this.show = true;

            clearTimeout(this.timeout);
            this.timeout = setTimeout(() => { this.show = false }, 3000);
        }
    }"
    x-init="@if($message) showToast('{{ $message }}', '{{ $type }}') @endif"

    {{-- イベントから message と type 両方を受け取る --}}
    @notify.window="showToast($event.detail.message, $event.detail.type)"

    class="toast toast-bottom toast-end z-50 fixed"
    style="display: none;"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-2"
>
    {{-- クラスを条件によって切り替え --}}
    <div class="alert shadow-lg"
         :class="{
            'alert-success text-white': type === 'success',
            'alert-error alert-soft': type === 'del_success'
         }">

        {{-- 成功時（チェックマーク） --}}
        <template x-if="type === 'success'">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </template>

        {{-- エラー/削除時（バツマーク） --}}
        <template x-if="type === 'del_success'">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </template>

        <span x-text="message" class="font-bold"></span>
    </div>
</div>