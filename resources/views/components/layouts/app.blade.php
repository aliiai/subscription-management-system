@props(['title' => null])

<!DOCTYPE html>
<html lang="ar" dir="rtl" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Accrual Hub') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logoicon.png') }}">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased selection:bg-indigo-500/30">
    <div class="pointer-events-none fixed -top-40 right-1/2 h-[420px] w-[420px] translate-x-1/2 rounded-full bg-gradient-to-tr from-indigo-600/20 via-fuchsia-500/10 to-cyan-400/10 blur-3xl"></div>

    <header class="sticky top-0 z-50 border-b border-white/5 bg-slate-950/70 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-semibold tracking-tight">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-indigo-500 to-fuchsia-500 text-sm font-bold">A</span>
                {{ config('app.name', 'Accrual Hub') }}
            </a>

            <div class="flex items-center gap-4">
                <div class="hidden text-left sm:block">
                    <p class="text-sm font-medium text-slate-100">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-400">{{ auth()->user()->role->label() }}</p>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-200 transition hover:border-rose-400/40 hover:bg-rose-500/10 hover:text-rose-200">
                        تسجيل الخروج
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="relative mx-auto max-w-6xl px-6 py-10">
        {{ $slot }}
    </main>
</body>
</html>
