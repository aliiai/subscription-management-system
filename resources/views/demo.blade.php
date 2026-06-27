<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Accrual Hub') }} · Tailwind + GSAP</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased selection:bg-indigo-500/30">

    <header class="fixed inset-x-0 top-0 z-50 border-b border-white/5 bg-slate-950/70 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
            <a href="#top" class="flex items-center gap-2 font-semibold tracking-tight">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-gradient-to-br from-indigo-500 to-fuchsia-500 text-sm font-bold">A</span>
                Accrual Hub
            </a>
            <nav class="hidden items-center gap-8 text-sm text-slate-300 sm:flex">
                <a href="#features" class="transition hover:text-white">Features</a>
                <a href="#stats" class="transition hover:text-white">Stats</a>
                <a href="#cta" class="transition hover:text-white">Get started</a>
            </nav>
        </div>
    </header>

    <main id="top">
        {{-- Hero --}}
        <section class="relative overflow-hidden px-6 pt-40 pb-28">
            <div data-animate="orb" class="pointer-events-none absolute -top-24 left-1/2 h-[480px] w-[480px] -translate-x-1/2 rounded-full bg-gradient-to-tr from-indigo-600/30 via-fuchsia-500/20 to-cyan-400/20 blur-3xl"></div>

            <div class="relative mx-auto max-w-3xl text-center">
                <span data-animate="hero-badge" class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-1.5 text-xs font-medium text-slate-300">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                    Powered by Laravel Boost · Tailwind v4 · GSAP
                </span>

                <h1 data-animate="hero-title" class="mt-6 bg-gradient-to-br from-white via-slate-200 to-slate-400 bg-clip-text text-5xl font-bold tracking-tight text-transparent sm:text-6xl">
                    Animations that feel alive
                </h1>

                <p data-animate="hero-subtitle" class="mx-auto mt-6 max-w-xl text-lg text-slate-300">
                    A starter page wired with Tailwind CSS for styling and GSAP for smooth, scroll-driven motion. Everything below animates as you scroll.
                </p>

                <div class="mt-10 flex items-center justify-center gap-4">
                    <a data-animate="hero-cta" href="#features" class="rounded-xl bg-gradient-to-r from-indigo-500 to-fuchsia-500 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:scale-[1.03] hover:shadow-indigo-500/40">
                        Explore features
                    </a>
                    <a data-animate="hero-cta" href="#stats" class="rounded-xl border border-white/10 bg-white/5 px-6 py-3 text-sm font-semibold text-slate-200 transition hover:bg-white/10">
                        View stats
                    </a>
                </div>
            </div>
        </section>

        {{-- Features --}}
        <section id="features" class="px-6 py-20">
            <div class="mx-auto max-w-6xl">
                <h2 class="text-center text-3xl font-semibold tracking-tight">Built for motion</h2>
                <p class="mx-auto mt-3 max-w-md text-center text-slate-400">Each card reveals itself with a GSAP ScrollTrigger animation.</p>

                <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @php
                        $features = [
                            ['title' => 'Tailwind v4', 'desc' => 'Utility-first styling with the new high-performance engine.', 'icon' => 'M4 4h16v16H4z'],
                            ['title' => 'GSAP timeline', 'desc' => 'Sequenced entrance animations with precise control.', 'icon' => 'M12 2v20M2 12h20'],
                            ['title' => 'ScrollTrigger', 'desc' => 'Reveal elements smoothly as they enter the viewport.', 'icon' => 'M4 12l8 8 8-8'],
                            ['title' => 'Laravel Boost', 'desc' => 'AI guidelines, skills and MCP wired into the project.', 'icon' => 'M13 2L3 14h7l-1 8 10-12h-7z'],
                            ['title' => 'Vite HMR', 'desc' => 'Instant feedback while you build the interface.', 'icon' => 'M3 3h18v18H3z'],
                            ['title' => 'Responsive', 'desc' => 'Looks great from mobile to ultra-wide displays.', 'icon' => 'M2 6h20v12H2z'],
                        ];
                    @endphp

                    @foreach ($features as $feature)
                        <article data-animate="card" class="group rounded-2xl border border-white/10 bg-white/5 p-6 transition hover:border-indigo-400/40 hover:bg-white/[0.07]">
                            <div class="grid h-11 w-11 place-items-center rounded-xl bg-gradient-to-br from-indigo-500/20 to-fuchsia-500/20 text-indigo-300 ring-1 ring-inset ring-white/10">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}" />
                                </svg>
                            </div>
                            <h3 class="mt-5 text-lg font-semibold">{{ $feature['title'] }}</h3>
                            <p class="mt-2 text-sm text-slate-400">{{ $feature['desc'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Stats --}}
        <section id="stats" class="px-6 py-20">
            <div class="mx-auto grid max-w-4xl gap-8 rounded-3xl border border-white/10 bg-white/5 p-10 sm:grid-cols-3">
                @php
                    $stats = [
                        ['value' => 60, 'suffix' => 'fps', 'label' => 'Smooth animation'],
                        ['value' => 1240, 'suffix' => '+', 'label' => 'Lines saved'],
                        ['value' => 100, 'suffix' => '%', 'label' => 'Utility-driven'],
                    ];
                @endphp

                @foreach ($stats as $stat)
                    <div data-animate="card" class="text-center">
                        <div class="text-4xl font-bold text-white">
                            <span data-counter="{{ $stat['value'] }}">0</span><span class="text-indigo-400">{{ $stat['suffix'] }}</span>
                        </div>
                        <p class="mt-2 text-sm text-slate-400">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- CTA --}}
        
        <section id="cta" class="px-6 py-24">
            <div data-animate="card" class="mx-auto max-w-3xl rounded-3xl border border-white/10 bg-gradient-to-br from-indigo-600/20 to-fuchsia-600/10 p-12 text-center">
                <h2 class="text-3xl font-semibold tracking-tight">Ready to build?</h2>
                <p class="mx-auto mt-3 max-w-md text-slate-300">Edit <code class="rounded bg-black/30 px-1.5 py-0.5 text-indigo-300">resources/js/app.js</code> and <code class="rounded bg-black/30 px-1.5 py-0.5 text-indigo-300">resources/views/demo.blade.php</code> to make it yours.</p>
                <a href="#top" class="mt-8 inline-block rounded-xl bg-white px-6 py-3 text-sm font-semibold text-slate-900 transition hover:scale-[1.03]">Back to top</a>
            </div>
        </section>
    </main>

    <footer class="border-t border-white/5 px-6 py-10 text-center text-sm text-slate-500">
        Accrual Hub — Tailwind CSS + GSAP demo
    </footer>
</body>
</html>
