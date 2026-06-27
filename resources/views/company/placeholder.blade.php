<x-layouts.dashboard :title="$title">
    <div data-animate="auth-card">
        <nav class="mb-2 flex items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('company.dashboard') }}" class="transition hover:text-brand">الرئيسية</a>
            <span>/</span>
            <span class="text-slate-500 dark:text-slate-300">{{ $title }}</span>
        </nav>

        <h1 class="text-2xl font-bold tracking-tight text-brand-navy sm:text-3xl dark:text-white">
            {{ $title }}
        </h1>
    </div>
</x-layouts.dashboard>
