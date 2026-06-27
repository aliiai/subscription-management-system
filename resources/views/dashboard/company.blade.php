<x-layouts.dashboard title="الرئيسية">
    @php
        $user = auth()->user();
        $symbol = $data['symbol'];
        $kpis = $data['kpis'];
        $charts = $data['charts'];
        $health = $data['health'];
        $alerts = $data['alerts'];

        $invoiceStatusStyles = [
            'unpaid' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
            'partially_paid' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
            'paid' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
            'void' => 'bg-slate-100 text-slate-500 dark:bg-white/5 dark:text-slate-400',
        ];

        $money = fn ($v) => number_format((float) $v, 2).' '.$symbol;

        $kpiCards = [
            ['label' => 'الإيراد الشهري المتكرر', 'value' => $kpis['mrr']['value'], 'icon' => 'subscriptions', 'tone' => 'text-brand bg-brand/10', 'subtitle' => $kpis['mrr']['subtitle'] ?? null, 'delta' => $kpis['mrr']['delta'] ?? null],
            ['label' => 'الإيراد المعترف به (هذا الشهر)', 'value' => $kpis['recognized']['value'], 'icon' => 'revenue', 'tone' => 'text-emerald-600 bg-emerald-50 dark:bg-emerald-500/10', 'subtitle' => $kpis['recognized']['subtitle'] ?? null, 'delta' => $kpis['recognized']['delta'] ?? null],
            ['label' => 'رصيد النقدية', 'value' => $kpis['cash']['value'], 'icon' => 'balance', 'tone' => 'text-violet-600 bg-violet-50 dark:bg-violet-500/10', 'subtitle' => $kpis['cash']['subtitle'] ?? null, 'delta' => $kpis['cash']['delta'] ?? null],
            ['label' => 'إجمالي المستحقات', 'value' => $kpis['receivable']['value'], 'icon' => 'invoices', 'tone' => 'text-amber-600 bg-amber-50 dark:bg-amber-500/10', 'subtitle' => $kpis['receivable']['subtitle'] ?? null, 'delta' => $kpis['receivable']['delta'] ?? null],
        ];

        $quickActions = [
            ['label' => 'عميل جديد', 'route' => 'company.customers', 'icon' => 'customers'],
            ['label' => 'اشتراك جديد', 'route' => 'company.subscriptions', 'icon' => 'subscriptions'],
            ['label' => 'الفواتير', 'route' => 'company.invoices', 'icon' => 'invoices'],
            ['label' => 'تسجيل دفعة', 'route' => 'company.payments', 'icon' => 'payments'],
            ['label' => 'الاعتراف بالإيراد', 'route' => 'company.revenue-recognition', 'icon' => 'revenue'],
        ];
    @endphp

    {{-- ===== header + quick actions ===== --}}
    <div data-animate="auth-card" class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs text-slate-400">
                <span class="text-slate-500 dark:text-slate-300">الرئيسية</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-brand-navy sm:text-3xl dark:text-white">
                أهلاً، {{ $user->name }}
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                نظرة عامة على أداء {{ $user->tenant?->name ?? 'شركتك' }} المالي والتشغيلي · {{ now()->format('Y/m/d') }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @foreach ($quickActions as $action)
                <a href="{{ route($action['route']) }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-brand-navy shadow-sm transition hover:border-brand/30 hover:bg-brand/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand/40 dark:border-white/10 dark:bg-slate-900 dark:text-white dark:hover:bg-white/5">
                    <x-icon name="{{ $action['icon'] }}" class="h-4 w-4 text-slate-400" /> {{ $action['label'] }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- ===== KPI cards ===== --}}
    <div data-animate="auth-card" class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($kpiCards as $card)
            <div class="group flex items-center gap-3.5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-brand/20 hover:shadow-md dark:border-white/10 dark:bg-slate-900">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl {{ $card['tone'] }}">
                    <x-icon name="{{ $card['icon'] }}" class="h-5 w-5" />
                </span>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-2">
                        <p class="truncate text-[11px] font-medium text-slate-400">{{ $card['label'] }}</p>
                        @if (! is_null($card['delta']))
                            <span class="inline-flex shrink-0 items-center gap-0.5 rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $card['delta'] >= 0 ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400' }}">
                                {{ $card['delta'] >= 0 ? '▲' : '▼' }} {{ number_format(abs($card['delta']), 1) }}%
                            </span>
                        @endif
                    </div>
                    <p class="mt-0.5 truncate text-lg font-bold leading-tight text-brand-navy dark:text-white" dir="ltr">
                        {{ number_format((float) $card['value'], 2) }} <span class="text-[11px] font-medium text-slate-400">{{ $symbol }}</span>
                    </p>
                    @if ($card['subtitle'])
                        <p class="mt-0.5 truncate text-[11px] text-slate-400">{{ $card['subtitle'] }}</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- ===== revenue trend + deferred ===== --}}
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div data-animate="auth-card" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2 dark:border-white/10 dark:bg-slate-900">
            <div class="mb-4 flex items-center justify-between gap-2">
                <div>
                    <h2 class="text-sm font-bold text-brand-navy dark:text-white">اتجاه الإيرادات والتحصيل</h2>
                    <p class="text-xs text-slate-400">آخر 6 أشهر · الإيراد المعترف به مقابل النقدية المحصّلة</p>
                </div>
                <span class="grid h-9 w-9 place-items-center rounded-lg bg-brand/10 text-brand"><x-icon name="revenue" class="h-5 w-5" /></span>
            </div>
            <div class="relative h-72"><canvas id="chart-revenue-trend" role="img" aria-label="رسم بياني لاتجاه الإيرادات والتحصيل"></canvas></div>
        </div>

        <div data-animate="auth-card" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900">
            <div class="mb-4 flex items-center justify-between gap-2">
                <div>
                    <h2 class="text-sm font-bold text-brand-navy dark:text-white">الإيراد المؤجل مقابل المعترف</h2>
                    <p class="text-xs text-slate-400">جوهر القيد المحاسبي</p>
                </div>
                <span class="grid h-9 w-9 place-items-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-500/10"><x-icon name="journal" class="h-5 w-5" /></span>
            </div>
            <div class="relative h-72"><canvas id="chart-deferred" role="img" aria-label="رسم بياني للإيراد المؤجل مقابل المعترف به"></canvas></div>
        </div>
    </div>

    {{-- ===== plans + invoice status + financial snapshot ===== --}}
    <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div data-animate="auth-card" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900">
            <h2 class="mb-1 text-sm font-bold text-brand-navy dark:text-white">توزيع الاشتراكات حسب الخطة</h2>
            <p class="mb-4 text-xs text-slate-400">مساهمة كل خطة في الإيراد المتكرر</p>
            @if (! empty($charts['plans']['labels']))
                <div class="relative h-64"><canvas id="chart-plans" role="img" aria-label="رسم بياني لتوزيع الاشتراكات حسب الخطة"></canvas></div>
            @else
                <div class="flex h-64 flex-col items-center justify-center text-center">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-slate-50 text-slate-300 dark:bg-white/5"><x-icon name="subscriptions" class="h-6 w-6" /></span>
                    <p class="mt-3 text-sm text-slate-400">لا توجد اشتراكات نشطة بعد.</p>
                </div>
            @endif
        </div>

        <div data-animate="auth-card" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900">
            <h2 class="mb-1 text-sm font-bold text-brand-navy dark:text-white">حالات الفواتير</h2>
            <p class="mb-4 text-xs text-slate-400">توزيع الفواتير حسب حالة السداد</p>
            <div class="relative h-64"><canvas id="chart-invoice-status" role="img" aria-label="رسم بياني لحالات الفواتير"></canvas></div>
        </div>

        <div data-animate="auth-card" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:col-span-2 lg:col-span-1 dark:border-white/10 dark:bg-slate-900">
            <h2 class="mb-1 text-sm font-bold text-brand-navy dark:text-white">المركز المالي المختصر</h2>
            <p class="mb-4 text-xs text-slate-400">أرصدة الحسابات الرئيسية</p>
            <ul class="space-y-3">
                @php
                    $snapshot = [
                        ['النقدية', $health['cash'], 'text-violet-600 bg-violet-50 dark:bg-violet-500/10', 'balance'],
                        ['الذمم المدينة', $health['receivable'], 'text-amber-600 bg-amber-50 dark:bg-amber-500/10', 'invoices'],
                        ['الإيرادات المؤجلة', $health['deferred'], 'text-blue-600 bg-blue-50 dark:bg-blue-500/10', 'journal'],
                        ['إيرادات الاشتراكات', $health['recognized'], 'text-emerald-600 bg-emerald-50 dark:bg-emerald-500/10', 'revenue'],
                    ];
                @endphp
                @foreach ($snapshot as $row)
                    <li class="flex items-center justify-between gap-3">
                        <span class="flex items-center gap-2.5 text-sm text-slate-600 dark:text-slate-300">
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg {{ $row[2] }}"><x-icon name="{{ $row[3] }}" class="h-4 w-4" /></span>
                            {{ $row[0] }}
                        </span>
                        <span class="font-mono text-sm font-bold text-brand-navy dark:text-white" dir="ltr">{{ number_format((float) $row[1], 2) }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- ===== accounting health strip ===== --}}
    <div data-animate="auth-card" class="mt-6 grid grid-cols-2 gap-4 divide-slate-100 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:grid-cols-4 dark:divide-white/10 dark:border-white/10 dark:bg-slate-900">
        <div class="flex items-center gap-3">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl {{ $health['balanced'] ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10' : 'bg-rose-50 text-rose-600 dark:bg-rose-500/10' }}">
                <x-icon name="check-double" class="h-5 w-5" />
            </span>
            <div>
                <p class="text-xs text-slate-400">حالة الميزانية</p>
                <p class="text-sm font-bold {{ $health['balanced'] ? 'text-emerald-600' : 'text-rose-600' }}">{{ $health['balanced'] ? 'متوازنة' : 'غير متوازنة' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-brand/10 text-brand"><x-icon name="journal" class="h-5 w-5" /></span>
            <div>
                <p class="text-xs text-slate-400">القيود المُرحّلة</p>
                <p class="text-sm font-bold text-brand-navy dark:text-white">{{ number_format($health['journalEntries']) }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-500/10"><x-icon name="balance" class="h-5 w-5" /></span>
            <div>
                <p class="text-xs text-slate-400">إجمالي الأصول</p>
                <p class="text-sm font-bold text-brand-navy dark:text-white">{{ $money($health['totalAssets']) }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/10"><x-icon name="calendar" class="h-5 w-5" /></span>
            <div>
                <p class="text-xs text-slate-400">آخر اعتراف بالإيراد</p>
                <p class="text-sm font-bold text-brand-navy dark:text-white">{{ $health['lastRecognitionAt'] ? \Illuminate\Support\Carbon::parse($health['lastRecognitionAt'])->format('Y/m/d') : '—' }}</p>
            </div>
        </div>
    </div>

    {{-- ===== alerts ===== --}}
    @if ($alerts['overdueCount'] > 0 || $alerts['unrecognizedCount'] > 0)
        <div data-animate="auth-card" class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
            @if ($alerts['unrecognizedCount'] > 0)
                <a href="{{ route('company.revenue-recognition') }}" class="flex items-center justify-between gap-3 rounded-2xl border border-blue-200 bg-blue-50 p-4 transition hover:bg-blue-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-400/50 dark:border-blue-500/20 dark:bg-blue-500/10 dark:hover:bg-blue-500/15">
                    <div class="flex items-center gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-blue-500/15 text-blue-600 dark:text-blue-400"><x-icon name="revenue" class="h-5 w-5" /></span>
                        <div>
                            <p class="text-sm font-bold text-blue-700 dark:text-blue-300">{{ $alerts['unrecognizedCount'] }} فاتورة بانتظار الاعتراف بالإيراد</p>
                            <p class="text-xs text-blue-600/80 dark:text-blue-300/70">انتهت فترة خدمتها — اعترف بإيرادها لإغلاق الشهر.</p>
                        </div>
                    </div>
                    <x-icon name="chevron-down" class="h-5 w-5 -rotate-90 text-blue-500" />
                </a>
            @endif
            @if ($alerts['overdueCount'] > 0)
                <a href="{{ route('company.invoices', ['status' => 'unpaid']) }}" class="flex items-center justify-between gap-3 rounded-2xl border border-rose-200 bg-rose-50 p-4 transition hover:bg-rose-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-400/50 dark:border-rose-500/20 dark:bg-rose-500/10 dark:hover:bg-rose-500/15">
                    <div class="flex items-center gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-rose-500/15 text-rose-600 dark:text-rose-400"><x-icon name="bell" class="h-5 w-5" /></span>
                        <div>
                            <p class="text-sm font-bold text-rose-700 dark:text-rose-300">{{ $alerts['overdueCount'] }} فاتورة متأخرة عن السداد</p>
                            <p class="text-xs text-rose-600/80 dark:text-rose-300/70">تجاوزت تاريخ الاستحقاق — تابع تحصيلها.</p>
                        </div>
                    </div>
                    <x-icon name="chevron-down" class="h-5 w-5 -rotate-90 text-rose-500" />
                </a>
            @endif
        </div>
    @endif

    {{-- ===== recent invoices + payments ===== --}}
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- recent invoices --}}
        <div data-animate="auth-card" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">
            <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-5 py-4 dark:border-white/10">
                <h2 class="flex items-center gap-2 text-sm font-bold text-brand-navy dark:text-white">
                    <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand/10 text-brand"><x-icon name="invoices" class="h-4 w-4" /></span>
                    أحدث الفواتير
                </h2>
                <a href="{{ route('company.invoices') }}" class="text-xs font-semibold text-brand transition hover:underline">عرض الكل</a>
            </div>
            @if ($data['recentInvoices']->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-slate-400">لا توجد فواتير بعد.</p>
            @else
                <ul class="divide-y divide-slate-100 dark:divide-white/5">
                    @foreach ($data['recentInvoices'] as $invoice)
                        <li>
                            <a href="{{ route('company.invoices.show', $invoice) }}" class="flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-slate-50/60 focus-visible:outline-none focus-visible:bg-slate-50 dark:hover:bg-white/[0.02] dark:focus-visible:bg-white/[0.04]">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-brand-navy dark:text-white">{{ $invoice->customer?->name ?? '—' }}</p>
                                    <p class="font-mono text-xs text-slate-400" dir="ltr">{{ $invoice->invoice_number }} · {{ $invoice->issue_date?->format('Y/m/d') }}</p>
                                </div>
                                <div class="flex shrink-0 flex-col items-end gap-1">
                                    <span class="text-sm font-bold text-brand-navy dark:text-white" dir="ltr">{{ $money($invoice->amount) }}</span>
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-medium {{ $invoiceStatusStyles[$invoice->status->value] ?? '' }}">{{ $invoice->status->label() }}</span>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- recent payments --}}
        <div data-animate="auth-card" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">
            <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-5 py-4 dark:border-white/10">
                <h2 class="flex items-center gap-2 text-sm font-bold text-brand-navy dark:text-white">
                    <span class="grid h-8 w-8 place-items-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10"><x-icon name="payments" class="h-4 w-4" /></span>
                    أحدث المدفوعات
                </h2>
                <a href="{{ route('company.payments') }}" class="text-xs font-semibold text-brand transition hover:underline">عرض الكل</a>
            </div>
            @if ($data['recentPayments']->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-slate-400">لا توجد مدفوعات بعد.</p>
            @else
                <ul class="divide-y divide-slate-100 dark:divide-white/5">
                    @foreach ($data['recentPayments'] as $payment)
                        <li class="flex items-center justify-between gap-3 px-5 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-brand-navy dark:text-white">{{ $payment->customer?->name ?? '—' }}</p>
                                <p class="text-xs text-slate-400">{{ $payment->method->label() }} · {{ $payment->paid_at?->format('Y/m/d') }}</p>
                            </div>
                            <span class="shrink-0 text-sm font-bold text-emerald-600" dir="ltr">{{ $money($payment->amount) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- chart data payload --}}
    <script type="application/json" id="dashboard-charts-data">@json(array_merge(['symbol' => $symbol], $charts))</script>
</x-layouts.dashboard>
