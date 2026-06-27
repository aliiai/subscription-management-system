<x-layouts.dashboard title="سجل النشاط">
  <div class="flex h-full flex-col">
    {{-- header --}}
    <div data-animate="auth-card" class="shrink-0">
        <nav class="mb-2 flex items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('company.dashboard') }}" class="transition hover:text-brand">الرئيسية</a>
            <span>/</span>
            <span class="text-slate-500 dark:text-slate-300">سجل النشاط</span>
        </nav>
        <h1 class="text-2xl font-bold tracking-tight text-brand-navy sm:text-3xl dark:text-white">سجل النشاط</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">تتبّع كل الأحداث المهمة التي تمت في حساب شركتك على خط زمني واحد.</p>
    </div>

    {{-- KPI cards --}}
    <div data-animate="auth-card" class="mt-5 grid shrink-0 grid-cols-2 gap-3 lg:grid-cols-4">
        @php
            $cards = [
                ['label' => 'إجمالي الأحداث', 'value' => $stats['total'], 'icon' => 'activity', 'color' => 'bg-brand/10 text-brand'],
                ['label' => 'اليوم', 'value' => $stats['today'], 'icon' => 'calendar', 'color' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400'],
                ['label' => 'آخر ٧ أيام', 'value' => $stats['week'], 'icon' => 'subscriptions', 'color' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'],
                ['label' => 'آخر ٣٠ يومًا', 'value' => $stats['month'], 'icon' => 'revenue', 'color' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400'],
            ];
        @endphp
        @foreach ($cards as $card)
            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm dark:border-white/10 dark:bg-slate-900">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl {{ $card['color'] }}">
                    <x-icon :name="$card['icon']" class="h-5 w-5" />
                </span>
                <div class="min-w-0">
                    <p class="text-xl font-bold leading-none text-brand-navy dark:text-white">{{ number_format($card['value']) }}</p>
                    <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ $card['label'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- timeline card --}}
    <div data-animate="auth-card" data-activity
         data-url="{{ route('company.activity-log') }}"
         class="mt-5 flex min-h-0 flex-1 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">

        {{-- toolbar --}}
        <div class="flex shrink-0 flex-col gap-3 border-b border-slate-100 px-4 py-4 md:flex-row md:items-center md:justify-between sm:px-6 dark:border-white/10">
            <div class="flex items-center gap-2">
                <h2 class="text-sm font-bold text-brand-navy dark:text-white">الخط الزمني</h2>
                <span data-activity-loading class="hidden h-4 w-4 animate-spin rounded-full border-2 border-brand/30 border-t-brand"></span>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
                {{-- search --}}
                <div class="relative w-full sm:w-auto">
                    <span class="pointer-events-none absolute inset-y-0 start-0 grid w-10 place-items-center text-slate-400">
                        <x-icon name="search" class="h-4 w-4" />
                    </span>
                    <input type="search" data-activity-search value="{{ $filters['q'] ?? '' }}"
                           placeholder="ابحث في الأحداث..."
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pe-3 ps-10 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 sm:w-60 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                </div>

                {{-- type filter --}}
                <div class="relative w-full sm:w-auto">
                    <select data-activity-type title="النوع"
                            class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 py-2 pe-9 ps-3 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 sm:w-40 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                        <option value="">كل الأنواع</option>
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 left-0 grid w-9 place-items-center text-slate-400"><x-icon name="chevron-down" class="h-4 w-4" /></span>
                </div>

                {{-- range filter --}}
                <div class="relative w-full sm:w-auto">
                    <select data-activity-range title="الفترة"
                            class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 py-2 pe-9 ps-3 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 sm:w-36 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                        <option value="">كل الفترات</option>
                        <option value="today" @selected(($filters['range'] ?? '') === 'today')>اليوم</option>
                        <option value="week" @selected(($filters['range'] ?? '') === 'week')>آخر ٧ أيام</option>
                        <option value="month" @selected(($filters['range'] ?? '') === 'month')>آخر ٣٠ يومًا</option>
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 left-0 grid w-9 place-items-center text-slate-400"><x-icon name="chevron-down" class="h-4 w-4" /></span>
                </div>
            </div>
        </div>

        {{-- results get swapped via AJAX --}}
        <div data-activity-results class="flex min-h-0 flex-1 flex-col">
            @include('company.activity-log._results')
        </div>
    </div>
  </div>
</x-layouts.dashboard>
