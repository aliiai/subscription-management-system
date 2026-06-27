<x-layouts.dashboard title="الميزانية العمومية">
  <div class="flex h-full flex-col">
    {{-- header --}}
    <div data-animate="auth-card" class="flex shrink-0 flex-col gap-4 lg:flex-row lg:items-end lg:justify-between print:hidden">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('company.dashboard') }}" class="transition hover:text-brand">الرئيسية</a>
                <span>/</span>
                <span class="text-slate-500 dark:text-slate-300">الميزانية العمومية</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-brand-navy sm:text-3xl dark:text-white">الميزانية العمومية</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">أرصدة الأصول والالتزامات وحقوق الملكية كما في التاريخ المحدد.</p>
        </div>

        <div data-balance data-url="{{ route('company.balance-sheet') }}" class="flex shrink-0 flex-wrap items-end gap-2">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">كما في تاريخ</label>
                <input type="date" data-balance-asof value="{{ $filters['as_of'] }}"
                       class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/20 sm:w-auto dark:border-white/10 dark:bg-slate-900 dark:text-slate-100">
            </div>
            <span data-balance-loading class="mb-2.5 hidden h-4 w-4 animate-spin rounded-full border-2 border-brand/30 border-t-brand"></span>
            <button type="button" onclick="window.print()"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-brand-navy shadow-sm transition hover:border-brand/30 hover:bg-brand/5 dark:border-white/10 dark:bg-slate-900 dark:text-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/></svg>
                طباعة
            </button>
        </div>
    </div>

    {{-- results (as-of label + KPIs + columns + balance check) get swapped via AJAX --}}
    <div data-balance-results data-animate="auth-card" class="mt-4 flex min-h-0 flex-1 flex-col gap-3">
        @include('company.reports._balance-sheet')
    </div>
  </div>
</x-layouts.dashboard>
