<x-layouts.dashboard title="الاعتراف بالإيرادات">
    @php
        $monthLabel = $period->format('Y/m');
    @endphp

  <div class="flex h-full flex-col">
    {{-- header --}}
    <div data-animate="auth-card" class="flex shrink-0 flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('company.dashboard') }}" class="transition hover:text-brand">الرئيسية</a>
                <span>/</span>
                <span class="text-slate-500 dark:text-slate-300">الاعتراف بالإيرادات</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-brand-navy sm:text-3xl dark:text-white">الاعتراف بالإيرادات</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">حوّل الإيرادات المؤجلة إلى إيرادات فعلية عند انتهاء فترة الخدمة (محاكاة إقفال نهاية الشهر).</p>
        </div>

        <div class="flex shrink-0 flex-wrap items-center gap-2">
            <button type="button" data-revenue-recognize @disabled($kpis['pending_count'] === 0)
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-l from-brand to-brand-blue px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand/25 transition hover:scale-[1.02] hover:shadow-brand/40 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:scale-100">
                <x-icon name="check-double" class="h-5 w-5" />
                تشغيل الاعتراف للشهر
            </button>
        </div>
    </div>

    {{-- toasts --}}
    @if (session('status'))
        <div data-toast class="mt-4 flex shrink-0 items-center gap-3 rounded-2xl border border-brand/20 bg-brand/10 px-4 py-3 text-sm font-medium text-brand">
            <x-icon name="check-double" class="h-5 w-5" />
            {{ session('status') }}
        </div>
    @endif
    @if (session('error'))
        <div data-toast class="mt-4 flex shrink-0 items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-600 dark:border-rose-500/20 dark:bg-rose-500/10">
            <x-icon name="bell" class="h-5 w-5" />
            {{ session('error') }}
        </div>
    @endif

    {{-- compact KPIs --}}
    <div data-animate="auth-card" class="mt-4 grid shrink-0 grid-cols-2 gap-3 lg:grid-cols-4">
        @php
            $cards = [
                ['الإيراد المؤجل الحالي', number_format($kpis['deferred_balance'], 2).' ر.س', 'invoices', 'text-amber-600 bg-amber-50 dark:bg-amber-500/10', null],
                ['إجمالي الإيراد المعترف به', number_format($kpis['recognized_total'], 2).' ر.س', 'revenue', 'text-emerald-600 bg-emerald-50 dark:bg-emerald-500/10', null],
                ['بانتظار الاعتراف ('.$monthLabel.')', number_format($kpis['pending_amount'], 2).' ر.س', 'subscriptions', 'text-blue-600 bg-blue-50 dark:bg-blue-500/10', $kpis['pending_count'].' فاتورة'],
                ['تم الاعتراف ('.$monthLabel.')', number_format($kpis['recognized_month_amount'], 2).' ر.س', 'check-double', 'text-brand bg-brand/10', $kpis['recognized_month_count'].' فاتورة'],
            ];
        @endphp
        @foreach ($cards as $card)
            <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm dark:border-white/10 dark:bg-slate-900">
                <div class="flex items-center gap-2.5">
                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg {{ $card[3] }}">
                        <x-icon name="{{ $card[2] }}" class="h-4 w-4" />
                    </span>
                    <div class="min-w-0">
                        <p class="truncate text-[11px] font-medium text-slate-500 dark:text-slate-400">{{ $card[0] }}</p>
                        <p class="truncate text-base font-bold text-brand-navy dark:text-white">{{ $card[1] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- table card --}}
    <div data-animate="auth-card" data-revenue
         data-url="{{ route('company.revenue-recognition') }}"
         class="mt-4 flex min-h-0 flex-1 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">

        {{-- toolbar: search + month --}}
        <div class="flex shrink-0 flex-col gap-3 border-b border-slate-100 px-4 py-3 lg:flex-row lg:items-center lg:justify-between sm:px-6 dark:border-white/10">
            <div class="flex items-center gap-2">
                <h2 class="text-sm font-bold text-brand-navy dark:text-white">فواتير الاعتراف بالإيراد</h2>
                <span data-revenue-loading class="hidden h-4 w-4 animate-spin rounded-full border-2 border-brand/30 border-t-brand"></span>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
                <div class="relative w-full sm:w-auto">
                    <span class="pointer-events-none absolute inset-y-0 start-0 grid w-10 place-items-center text-slate-400">
                        <x-icon name="search" class="h-4 w-4" />
                    </span>
                    <input type="search" data-revenue-search value="{{ $filters['q'] ?? '' }}"
                           placeholder="بحث برقم الفاتورة أو العميل..."
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pe-3 ps-10 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 sm:w-56 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                </div>
                <input type="month" data-revenue-month value="{{ $period->format('Y-m') }}" title="الشهر"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 sm:w-auto dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
            </div>
        </div>

        {{-- tabs (filter) --}}
        <div class="flex shrink-0 flex-wrap items-center gap-1.5 border-b border-slate-100 px-4 py-2.5 sm:px-6 dark:border-white/10">
            <button type="button" data-revenue-view="pending"
                    @class([
                        'rounded-lg px-3.5 py-1.5 text-sm font-medium transition',
                        'bg-brand text-white shadow shadow-brand/25' => $view === 'pending',
                        'text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-white/5' => $view !== 'pending',
                    ])>
                بانتظار الاعتراف <span class="ms-1 text-xs opacity-80">({{ $kpis['pending_count'] }})</span>
            </button>
            <button type="button" data-revenue-view="recognized"
                    @class([
                        'rounded-lg px-3.5 py-1.5 text-sm font-medium transition',
                        'bg-brand text-white shadow shadow-brand/25' => $view === 'recognized',
                        'text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-white/5' => $view !== 'recognized',
                    ])>
                تم الاعتراف بها <span class="ms-1 text-xs opacity-80">({{ $kpis['recognized_count'] }})</span>
            </button>
        </div>

        {{-- results (table + pagination) get swapped via AJAX --}}
        <div data-revenue-results class="flex min-h-0 flex-1 flex-col">
            @include('company.revenue-recognition._results')
        </div>
    </div>
  </div>

    {{-- ===================== RECOGNIZE CONFIRM MODAL ===================== --}}
    <div data-modal="revenue-recognize" class="fixed inset-0 z-50 hidden">
        <div data-modal-overlay class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div data-modal-dismiss class="absolute inset-0 flex items-center justify-center p-4">
            <div data-modal-panel class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-white/10 dark:bg-slate-900">
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-brand/10 text-brand">
                    <x-icon name="check-double" class="h-6 w-6" />
                </span>
                <h3 class="mt-4 text-lg font-bold text-brand-navy dark:text-white">تأكيد الاعتراف بالإيراد</h3>
                <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">
                    سيتم ترحيل قيد (مدين الإيرادات المؤجلة / دائن إيرادات الاشتراكات) لكل فاتورة مؤهلة في فترة <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $monthLabel }}</span>.
                </p>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-center dark:border-white/10 dark:bg-white/5">
                        <p class="text-xs text-slate-400">عدد الفواتير</p>
                        <p class="mt-1 text-lg font-bold text-brand-navy dark:text-white">{{ $kpis['pending_count'] }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-center dark:border-white/10 dark:bg-white/5">
                        <p class="text-xs text-slate-400">إجمالي المبلغ</p>
                        <p class="mt-1 text-lg font-bold text-brand-navy dark:text-white">{{ number_format($kpis['pending_amount'], 2) }} <span class="text-xs text-slate-400">ر.س</span></p>
                    </div>
                </div>

                <form method="POST" action="{{ route('company.revenue-recognition.recognize') }}" class="mt-6 flex items-center justify-end gap-3">
                    @csrf
                    <input type="hidden" name="month" value="{{ $period->format('Y-m') }}">
                    <button type="button" data-modal-close class="rounded-xl px-4 py-2.5 text-sm font-medium text-slate-500 transition hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-white/5">إلغاء</button>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-l from-brand to-brand-blue px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand/25 transition hover:scale-[1.02]">
                        <x-icon name="check-double" class="h-4 w-4" /> اعتراف الآن
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
