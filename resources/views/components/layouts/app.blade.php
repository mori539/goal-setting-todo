<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-gray-50 dark:bg-zinc-800 text-gray-900 dark:text-gray-100 antialiased">

        {{-- ▼▼▼ ヘッダー（Navbar）ここから ▼▼▼ --}}
        <header class="bg-white dark:bg-zinc-900 border-b border-gray-200 dark:border-zinc-700 sticky top-0 z-50">
            <div class="navbar max-w-7xl mx-auto px-4">

                {{-- ① 左側：ロゴとタイトル --}}
                <div class="flex-1">
                    <a href="{{ route('goals.index') }}" class="btn btn-ghost text-xl font-bold gap-2">
                        {{-- ロゴアイコン --}}
                        <svg width="30" height="30" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <linearGradient id="bgGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#60A5FA;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#2563EB;stop-opacity:1" />
                                </linearGradient>
                                <linearGradient id="starGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#FDE047;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#F59E0B;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                            <rect x="0" y="0" width="200" height="200" rx="50" fill="url(#bgGradient)" />
                            <path d="M45 160 H85 V125 H125 V90 H165" stroke="white" stroke-width="16" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M165 30 L172 50 L193 50 L176 62 L183 82 L165 70 L147 82 L154 62 L137 50 L158 50 Z" fill="url(#starGradient)" />
                        </svg>
                        Goal Setting Todo
                    </a>
                </div>

                {{-- ② 右側：アカウントメニュー --}}
                <div class="flex-none gap-2">
                    <div class="dropdown dropdown-end">

                        {{-- トリガーアイコン（人型） --}}
                        <div tabindex="0" role="button" class="btn btn-ghost btn-circle border border-gray-300 dark:border-zinc-600">
                            {{-- ▼ h-10 を追加して、幅と高さを揃えて正円にします --}}
                            <div class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-100 dark:bg-zinc-700">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </div>
                        </div>

                        {{-- ドロップダウンの中身 --}}
                        <ul tabindex="0" class="mt-3 z-[1] p-2 shadow menu menu-sm dropdown-content bg-base-100 rounded-box w-52 border border-gray-100 dark:border-zinc-700 dark:bg-zinc-800">

                            @auth
                                {{-- ▼ ログイン中のみ表示 --}}
                                <li class="menu-title px-4 py-2 text-gray-500 border-b border-gray-100 dark:border-zinc-700 mb-2">
                                    {{ Auth::user()->name }} さん
                                </li>

                                <li>
                                    {{-- ログアウト --}}
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="text-error font-medium w-full text-left">
                                            ログアウト
                                        </button>
                                    </form>
                                </li>
                            @else
                                {{-- ▼ ログアウト中のみ表示（ゲスト用） --}}
                                <li><a href="{{ route('login') }}" class="font-medium">ログイン</a></li>
                                <li><a href="{{ route('register') }}">ユーザー登録</a></li>
                            @endauth

                        </ul>
                    </div>
                </div>
            </div>
        </header>
        {{-- ▲▲▲ ヘッダーここまで ▲▲▲ --}}

        {{-- メインコンテンツ --}}
        <main class="w-full">
            {{ $slot }}
        </main>

        @fluxScripts
    </body>
</html>