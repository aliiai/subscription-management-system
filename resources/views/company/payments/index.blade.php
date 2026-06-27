<x-layouts.dashboard title="المدفوعات">
    @php
        $methodStyles = [
            'cash' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
            'transfer' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
            'card' => 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400',
        ];
    @endphp

  <div class="flex h-full flex-col">
    {{-- header --}}
    <div data-animate="auth-card" class="flex shrink-0 flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('company.dashboard') }}" class="transition hover:text-brand">الرئيسية</a>
                <span>/</span>
                <span class="text-slate-500 dark:text-slate-300">المدفوعات</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-brand-navy sm:text-3xl dark:text-white">المدفوعات</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">سجّل وتابع الدفعات المُحصّلة من العملاء مقابل الفواتير.</p>
        </div>

        @if ($openInvoices->isNotEmpty())
            <button type="button" data-payment-create
                    class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-gradient-to-l from-emerald-500 to-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/25 transition hover:scale-[1.02] active:scale-[0.99]">
                <x-icon name="plus" class="h-5 w-5" /> تسجيل دفعة
            </button>
        @endif
    </div>

    @if (session('status'))
        <div data-toast class="mt-4 flex shrink-0 items-center gap-3 rounded-2xl border border-brand/20 bg-brand/10 px-4 py-3 text-sm font-medium text-brand">
            <x-icon name="check-double" class="h-5 w-5" /> {{ session('status') }}
        </div>
    @endif

    @if ($openInvoices->isEmpty() && $payments->isEmpty())
        <div class="mt-4 flex shrink-0 items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-400">
            <x-icon name="bell" class="mt-0.5 h-5 w-5 shrink-0" />
            <span>لا توجد فواتير مفتوحة لتسجيل دفعات عليها. أنشئ <a href="{{ route('company.invoices') }}" class="font-semibold underline">فاتورة</a> أولاً.</span>
        </div>
    @endif

    {{-- compact KPIs --}}
    <div data-animate="auth-card" class="mt-4 grid shrink-0 grid-cols-2 gap-3 lg:grid-cols-4">
        @php
            $cards = [
                ['إجمالي المُحصّل', number_format($kpis['total_collected'], 2).' ر.س', 'payments', 'text-emerald-600 bg-emerald-50 dark:bg-emerald-500/10'],
                ['المُحصّل هذا الشهر', number_format($kpis['collected_this_month'], 2).' ر.س', 'revenue', 'text-blue-600 bg-blue-50 dark:bg-blue-500/10'],
                ['عدد الدفعات', $kpis['count'], 'journal', 'text-brand bg-brand/10'],
                ['متوسط الدفعة', number_format($kpis['average'], 2).' ر.س', 'balance', 'text-violet-600 bg-violet-50 dark:bg-violet-500/10'],
            ];
        @endphp
        @foreach ($cards as $card)
            <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm dark:border-white/10 dark:bg-slate-900">
                <div class="flex items-center gap-2.5">
                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg {{ $card[3] }}"><x-icon name="{{ $card[2] }}" class="h-4 w-4" /></span>
                    <div class="min-w-0">
                        <p class="truncate text-[11px] font-medium text-slate-500 dark:text-slate-400">{{ $card[0] }}</p>
                        <p class="truncate text-base font-bold text-brand-navy dark:text-white">{{ $card[1] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- table card --}}
    <div data-animate="auth-card" data-payments
         data-url="{{ route('company.payments') }}"
         class="mt-4 flex min-h-0 flex-1 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">

        {{-- toolbar: search + method + date filters --}}
        <div class="flex shrink-0 flex-col gap-3 border-b border-slate-100 px-4 py-3 lg:flex-row lg:items-center lg:justify-between sm:px-6 dark:border-white/10">
            <div class="flex items-center gap-2">
                <h2 class="text-sm font-bold text-brand-navy dark:text-white">قائمة المدفوعات</h2>
                <span data-payments-loading class="hidden h-4 w-4 animate-spin rounded-full border-2 border-brand/30 border-t-brand"></span>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
                <div class="relative w-full sm:w-auto">
                    <span class="pointer-events-none absolute inset-y-0 start-0 grid w-10 place-items-center text-slate-400">
                        <x-icon name="search" class="h-4 w-4" />
                    </span>
                    <input type="search" data-payments-search value="{{ $filters['q'] ?? '' }}"
                           placeholder="بحث بالعميل أو رقم الفاتورة..."
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pe-3 ps-10 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 sm:w-56 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                </div>

                <div class="relative w-full sm:w-auto">
                    <select data-payments-method
                            class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 py-2 pe-9 ps-3 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 sm:w-36 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                        <option value="">كل الطرق</option>
                        @foreach (\App\Enums\PaymentMethod::cases() as $method)
                            <option value="{{ $method->value }}" @selected(($filters['method'] ?? '') === $method->value)>{{ $method->label() }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 left-0 grid w-9 place-items-center text-slate-400"><x-icon name="chevron-down" class="h-4 w-4" /></span>
                </div>

                <input type="date" data-payments-from value="{{ $filters['from'] ?? '' }}" title="من تاريخ"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 sm:w-auto dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                <input type="date" data-payments-to value="{{ $filters['to'] ?? '' }}" title="إلى تاريخ"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 sm:w-auto dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
            </div>
        </div>

        {{-- results (table + pagination) get swapped via AJAX --}}
        <div data-payments-results class="flex min-h-0 flex-1 flex-col">
            @include('company.payments._results')
        </div>
    </div>
  </div>

    {{-- record payment modal --}}
    @include('company.payments._modal')

    @if ($errors->any())
        <script>window.__openPaymentModal = true;</script>
    @endif
</x-layouts.dashboard>
