<x-layouts.dashboard :title="$invoice->invoice_number">
    @php
        $currencySymbols = [
            'SAR' => 'ر.س', 'AED' => 'د.إ', 'QAR' => 'ر.ق', 'KWD' => 'د.ك',
            'BHD' => 'د.ب', 'OMR' => 'ر.ع', 'EGP' => 'ج.م', 'USD' => '$', 'EUR' => '€',
        ];
        $symbol = $currencySymbols[$invoice->currency] ?? $invoice->currency;
        $statusStyles = [
            'unpaid' => ['badge' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400', 'dot' => 'bg-amber-500'],
            'partially_paid' => ['badge' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400', 'dot' => 'bg-blue-500'],
            'paid' => ['badge' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400', 'dot' => 'bg-emerald-500'],
            'void' => ['badge' => 'bg-slate-100 text-slate-500 dark:bg-white/5 dark:text-slate-400', 'dot' => 'bg-slate-400'],
        ];
        $status = $statusStyles[$invoice->status->value] ?? $statusStyles['unpaid'];
        $total = (float) $invoice->amount;
        $paid = (float) $invoice->amount_paid;
        $percent = $total > 0 ? min(100, max(0, round($paid / $total * 100))) : ($invoice->status->value === 'paid' ? 100 : 0);
        $initials = collect(explode(' ', trim((string) $invoice->customer?->name)))
            ->filter()->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode('');
        $tenant = auth()->user()?->tenant;
        $sellerInitials = collect(explode(' ', trim((string) $tenant?->name)))
            ->filter()->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode('');
    @endphp

    <style>
        @media print {
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            @page { margin: 1.4cm; size: A4; }
            .invoice-print { break-inside: avoid; }
            table { break-inside: auto; }
            tr { break-inside: avoid; }
        }
    </style>

    {{-- ============ ON-SCREEN VIEW (hidden when printing) ============ --}}
    <div class="print:hidden">
    {{-- header --}}
    <div data-animate="auth-card" class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0">
            <nav class="mb-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-400">
                <a href="{{ route('company.dashboard') }}" class="transition hover:text-brand">الرئيسية</a>
                <span>/</span>
                <a href="{{ route('company.invoices') }}" class="transition hover:text-brand">الفواتير</a>
                <span>/</span>
                <span class="text-slate-500 dark:text-slate-300">{{ $invoice->invoice_number }}</span>
            </nav>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="font-mono text-2xl font-bold tracking-tight text-brand-navy sm:text-3xl dark:text-white" dir="ltr">{{ $invoice->invoice_number }}</h1>
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium {{ $status['badge'] }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $status['dot'] }}"></span>
                    {{ $invoice->status->label() }}
                </span>
                @if ($invoice->isOverdue())
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-3 py-1 text-xs font-medium text-rose-600 dark:bg-rose-500/10 dark:text-rose-400">
                        <x-icon name="bell" class="h-3.5 w-3.5" /> متأخرة
                    </span>
                @endif
            </div>
        </div>

        <div class="flex w-full flex-col gap-2 sm:flex-row lg:w-auto lg:shrink-0 print:hidden">
            @if ($invoice->status->value !== 'paid' && $invoice->status->value !== 'void')
                <button type="button" data-payment-create data-invoice="{{ $invoice->id }}" data-balance="{{ $invoice->balance() }}"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-l from-emerald-500 to-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/25 transition hover:scale-[1.02] sm:w-auto">
                    <x-icon name="payments" class="h-5 w-5" /> تسجيل دفعة
                </button>
            @endif
            <button type="button" onclick="window.print()"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-brand-navy shadow-sm transition hover:border-brand/30 hover:bg-brand/5 sm:w-auto dark:border-white/10 dark:bg-slate-900 dark:text-white dark:hover:bg-white/5">
                <x-icon name="invoices" class="h-5 w-5" /> طباعة الفاتورة
            </button>
            @if ($invoice->status->value !== 'void' && $invoice->payments->isEmpty())
                <form method="POST" action="{{ route('company.invoices.void', $invoice) }}" onsubmit="return confirm('تأكيد إلغاء الفاتورة؟');" class="w-full sm:w-auto">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50 sm:w-auto dark:border-rose-500/20 dark:bg-slate-900 dark:hover:bg-rose-500/10">
                        <x-icon name="close" class="h-5 w-5" /> إلغاء الفاتورة
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if (session('status'))
        <div data-toast class="mt-5 flex items-center gap-3 rounded-2xl border border-brand/20 bg-brand/10 px-4 py-3 text-sm font-medium text-brand">
            <x-icon name="check-double" class="h-5 w-5 shrink-0" /> {{ session('status') }}
        </div>
    @endif
    @if (session('error'))
        <div data-toast class="mt-5 flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-600 dark:border-rose-500/20 dark:bg-rose-500/10">
            <x-icon name="bell" class="h-5 w-5 shrink-0" /> {{ session('error') }}
        </div>
    @endif

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- main column --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- summary / hero card --}}
            <div data-animate="auth-card" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">
                <div class="flex flex-col gap-5 p-5 sm:p-6 md:flex-row md:items-center md:justify-between">
                    <div class="flex items-center gap-3.5">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-brand to-brand-blue text-base font-bold text-white shadow-lg shadow-brand/25">
                            {{ $initials !== '' ? $initials : '—' }}
                        </span>
                        <div class="min-w-0">
                            <p class="text-[11px] font-medium uppercase tracking-wider text-slate-400">العميل</p>
                            <p class="truncate text-base font-bold text-brand-navy dark:text-white">{{ $invoice->customer?->name ?? '—' }}</p>
                            @if ($invoice->customer?->email)
                                <p class="truncate text-xs text-slate-400" dir="ltr">{{ $invoice->customer->email }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="shrink-0 border-t border-slate-100 pt-4 md:border-t-0 md:border-r md:pe-0 md:ps-6 md:pt-0 dark:border-white/10">
                        <p class="text-[11px] font-medium uppercase tracking-wider text-slate-400">المتبقي للتحصيل</p>
                        <p class="mt-0.5 text-2xl font-bold text-brand-navy sm:text-3xl dark:text-white">
                            {{ number_format($invoice->balance(), 2) }} <span class="text-sm font-medium text-slate-400">{{ $symbol }}</span>
                        </p>
                    </div>
                </div>

                {{-- progress --}}
                <div class="px-5 pb-2 sm:px-6">
                    <div class="mb-1.5 flex items-center justify-between text-xs">
                        <span class="font-medium text-slate-500 dark:text-slate-400">نسبة التحصيل</span>
                        <span class="font-semibold text-brand-navy dark:text-white">{{ $percent }}%</span>
                    </div>
                    <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-white/10">
                        <div class="h-full rounded-full bg-gradient-to-l from-emerald-500 to-emerald-600 transition-all" style="width: {{ $percent }}%"></div>
                    </div>
                </div>

                {{-- amount tiles --}}
                <div class="grid grid-cols-1 gap-3 p-5 sm:grid-cols-3 sm:p-6">
                    <div class="rounded-xl bg-slate-50 p-4 dark:bg-white/5">
                        <p class="text-xs text-slate-400">الإجمالي</p>
                        <p class="mt-1 text-lg font-bold text-brand-navy dark:text-white">{{ number_format($total, 2) }} <span class="text-xs text-slate-400">{{ $symbol }}</span></p>
                    </div>
                    <div class="rounded-xl bg-emerald-50 p-4 dark:bg-emerald-500/10">
                        <p class="text-xs text-emerald-600/70">المدفوع</p>
                        <p class="mt-1 text-lg font-bold text-emerald-600">{{ number_format($paid, 2) }} <span class="text-xs">{{ $symbol }}</span></p>
                    </div>
                    <div class="rounded-xl bg-amber-50 p-4 dark:bg-amber-500/10">
                        <p class="text-xs text-amber-600/70">المتبقي</p>
                        <p class="mt-1 text-lg font-bold text-amber-600">{{ number_format($invoice->balance(), 2) }} <span class="text-xs">{{ $symbol }}</span></p>
                    </div>
                </div>
            </div>

            {{-- invoice details --}}
            <div data-animate="auth-card" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 dark:border-white/10 dark:bg-slate-900">
                <div class="mb-4 flex items-center gap-2">
                    <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand/10 text-brand"><x-icon name="invoices" class="h-4 w-4" /></span>
                    <h2 class="text-sm font-bold text-brand-navy dark:text-white">تفاصيل الفاتورة</h2>
                </div>
                <dl class="grid grid-cols-2 gap-x-4 gap-y-5 sm:grid-cols-3">
                    <div>
                        <dt class="text-xs text-slate-400">الخطة / الاشتراك</dt>
                        <dd class="mt-1 text-sm font-semibold text-brand-navy dark:text-white">{{ $invoice->subscription?->plan?->name ?? 'فاتورة يدوية' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">الفترة</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-700 dark:text-slate-200" dir="ltr">{{ $invoice->period_start?->format('Y/m/d') }} — {{ $invoice->period_end?->format('Y/m/d') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">تاريخ الإصدار</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-700 dark:text-slate-200">{{ $invoice->issue_date?->format('Y/m/d') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">تاريخ الاستحقاق</dt>
                        <dd class="mt-1 text-sm font-medium {{ $invoice->isOverdue() ? 'text-rose-600' : 'text-slate-700 dark:text-slate-200' }}">{{ $invoice->due_date?->format('Y/m/d') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">العملة</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-700 dark:text-slate-200">{{ $invoice->currency }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">الاعتراف بالإيراد</dt>
                        <dd class="mt-1 text-sm font-medium {{ $invoice->isRecognized() ? 'text-emerald-600' : 'text-slate-500 dark:text-slate-300' }}">
                            {{ $invoice->isRecognized() ? $invoice->revenue_recognized_at?->format('Y/m/d') : 'لم يُعترف بعد' }}
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- payments history --}}
            <div data-animate="auth-card" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">
                <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-5 py-4 sm:px-6 dark:border-white/10">
                    <h2 class="flex items-center gap-2 text-sm font-bold text-brand-navy dark:text-white">
                        <span class="grid h-8 w-8 place-items-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10"><x-icon name="payments" class="h-4 w-4" /></span>
                        سجل الدفعات
                    </h2>
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500 dark:bg-white/5 dark:text-slate-400">{{ $invoice->payments->count() }}</span>
                </div>

                @if ($invoice->payments->isEmpty())
                    <div class="px-6 py-12 text-center">
                        <span class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-slate-50 text-slate-300 dark:bg-white/5"><x-icon name="payments" class="h-6 w-6" /></span>
                        <p class="mt-3 text-sm text-slate-400">لا توجد دفعات على هذه الفاتورة بعد.</p>
                    </div>
                @else
                    {{-- mobile: cards --}}
                    <ul class="divide-y divide-slate-100 sm:hidden dark:divide-white/5">
                        @foreach ($invoice->payments as $payment)
                            <li class="flex items-start justify-between gap-3 px-5 py-4">
                                <div class="min-w-0">
                                    <p class="text-base font-bold text-brand-navy dark:text-white">{{ number_format((float) $payment->amount, 2) }} {{ $symbol }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $payment->method->label() }} · {{ $payment->paid_at?->format('Y/m/d') }}</p>
                                    @if ($payment->reference)
                                        <p class="mt-0.5 text-xs text-slate-400" dir="ltr">{{ $payment->reference }}</p>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('company.payments.destroy', $payment) }}" onsubmit="return confirm('حذف الدفعة وعكس قيدها؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="grid h-8 w-8 shrink-0 place-items-center rounded-lg text-rose-500 transition hover:bg-rose-50 dark:hover:bg-rose-500/10">
                                        <x-icon name="trash" class="h-4 w-4" />
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>

                    {{-- sm+: table --}}
                    <div class="hidden overflow-x-auto sm:block">
                        <table class="w-full min-w-[34rem] border-collapse text-right">
                            <thead>
                                <tr class="bg-slate-50/70 text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:bg-white/[0.02]">
                                    <th class="px-6 py-3 font-semibold">التاريخ</th>
                                    <th class="px-6 py-3 font-semibold">المبلغ</th>
                                    <th class="px-6 py-3 font-semibold">الطريقة</th>
                                    <th class="px-6 py-3 font-semibold">المرجع</th>
                                    <th class="px-6 py-3 text-left font-semibold"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                                @foreach ($invoice->payments as $payment)
                                    <tr class="transition hover:bg-slate-50/60 dark:hover:bg-white/[0.02]">
                                        <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $payment->paid_at?->format('Y/m/d') }}</td>
                                        <td class="px-6 py-3 text-sm font-semibold text-brand-navy dark:text-white">{{ number_format((float) $payment->amount, 2) }} {{ $symbol }}</td>
                                        <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $payment->method->label() }}</td>
                                        <td class="px-6 py-3 text-sm text-slate-400" dir="ltr">{{ $payment->reference ?? '—' }}</td>
                                        <td class="px-6 py-3 text-left">
                                            <form method="POST" action="{{ route('company.payments.destroy', $payment) }}" onsubmit="return confirm('حذف الدفعة وعكس قيدها؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-medium text-rose-600 hover:underline">حذف</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- accounting entries sidebar --}}
        <div data-animate="auth-card" class="lg:col-span-1">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:sticky lg:top-6 dark:border-white/10 dark:bg-slate-900">
                <div class="flex items-center gap-2">
                    <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand/10 text-brand"><x-icon name="journal" class="h-4 w-4" /></span>
                    <h2 class="text-sm font-bold text-brand-navy dark:text-white">القيود المحاسبية المرتبطة</h2>
                </div>
                <p class="mt-1 text-xs text-slate-400">قيود القيد المزدوج المُرحّلة آلياً لهذه الفاتورة.</p>

                <div class="mt-4 space-y-4">
                    @forelse ($invoice->journalEntries as $entry)
                        <div class="rounded-xl border border-slate-100 p-3 dark:border-white/10">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-xs font-semibold text-brand-navy dark:text-white">{{ $entry->description }}</p>
                                <span class="shrink-0 text-[11px] text-slate-400">{{ $entry->entry_date?->format('Y/m/d') }}</span>
                            </div>
                            <div class="mt-2 overflow-x-auto">
                                <table class="w-full text-right text-xs">
                                    <thead>
                                        <tr class="text-slate-400">
                                            <th class="py-1 font-medium">الحساب</th>
                                            <th class="py-1 text-left font-medium">مدين</th>
                                            <th class="py-1 text-left font-medium">دائن</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($entry->lines as $line)
                                            <tr class="border-t border-slate-50 dark:border-white/5">
                                                <td class="py-1.5 text-slate-700 dark:text-slate-200">{{ $line->account?->name }}</td>
                                                <td class="py-1.5 text-left font-mono text-slate-700 dark:text-slate-200" dir="ltr">{{ (float) $line->debit > 0 ? number_format((float) $line->debit, 2) : '—' }}</td>
                                                <td class="py-1.5 text-left font-mono text-slate-700 dark:text-slate-200" dir="ltr">{{ (float) $line->credit > 0 ? number_format((float) $line->credit, 2) : '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-200 py-8 text-center dark:border-white/10">
                            <p class="text-xs text-slate-400">لا توجد قيود.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    </div>
    {{-- ============ END ON-SCREEN VIEW ============ --}}

    {{-- ============ PRINT DOCUMENT (visible only when printing) ============ --}}
    <div class="hidden bg-white text-slate-800 print:block" dir="rtl">
        <div class="invoice-print mx-auto max-w-3xl">

            {{-- letterhead --}}
            <div class="flex items-start justify-between gap-6 border-b-2 border-brand pb-6">
                <div class="flex items-center gap-4">
                    @if ($tenant?->logo_url)
                        <img src="{{ $tenant->logo_url }}" alt="" class="h-16 w-auto object-contain">
                    @else
                        <span class="grid h-16 w-16 place-items-center rounded-2xl bg-brand text-2xl font-bold text-white">{{ $sellerInitials !== '' ? $sellerInitials : 'A' }}</span>
                    @endif
                    <div>
                        <p class="text-xl font-bold text-brand-navy">{{ $tenant?->name ?? config('app.name') }}</p>
                        @if ($tenant?->email)
                            <p class="text-sm text-slate-500" dir="ltr">{{ $tenant->email }}</p>
                        @endif
                        @if ($tenant?->phone)
                            <p class="text-sm text-slate-500" dir="ltr">{{ $tenant->phone }}</p>
                        @endif
                    </div>
                </div>
                <div class="text-left">
                    <h2 class="text-3xl font-extrabold uppercase tracking-tight text-brand">فاتورة</h2>
                    <p class="mt-1 font-mono text-lg font-bold text-brand-navy" dir="ltr">{{ $invoice->invoice_number }}</p>
                    <p class="mt-1 inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold {{ $status['badge'] }}">{{ $invoice->status->label() }}</p>
                </div>
            </div>

            {{-- parties + meta --}}
            <div class="mt-6 grid grid-cols-2 gap-6">
                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">فاتورة إلى</p>
                    <p class="mt-1 text-base font-bold text-brand-navy">{{ $invoice->customer?->name ?? '—' }}</p>
                    @if ($invoice->customer?->email)
                        <p class="text-sm text-slate-500" dir="ltr">{{ $invoice->customer->email }}</p>
                    @endif
                    @if ($invoice->customer?->phone)
                        <p class="text-sm text-slate-500" dir="ltr">{{ $invoice->customer->phone }}</p>
                    @endif
                </div>
                <div class="space-y-1.5 text-sm">
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-400">تاريخ الإصدار</span>
                        <span class="font-semibold text-brand-navy">{{ $invoice->issue_date?->format('Y/m/d') }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-400">تاريخ الاستحقاق</span>
                        <span class="font-semibold {{ $invoice->isOverdue() ? 'text-rose-600' : 'text-brand-navy' }}">{{ $invoice->due_date?->format('Y/m/d') }}</span>
                    </div>
                    @if ($invoice->period_start || $invoice->period_end)
                        <div class="flex justify-between gap-4">
                            <span class="text-slate-400">فترة الخدمة</span>
                            <span class="font-semibold text-brand-navy" dir="ltr">{{ $invoice->period_start?->format('Y/m/d') }} — {{ $invoice->period_end?->format('Y/m/d') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-400">العملة</span>
                        <span class="font-semibold text-brand-navy">{{ $invoice->currency }}</span>
                    </div>
                </div>
            </div>

            {{-- line items --}}
            <table class="mt-8 w-full border-collapse text-right text-sm">
                <thead>
                    <tr class="bg-brand text-white">
                        <th class="rounded-r-lg px-4 py-3 text-right font-semibold">الوصف</th>
                        <th class="px-4 py-3 text-center font-semibold">الفترة</th>
                        <th class="rounded-l-lg px-4 py-3 text-left font-semibold">المبلغ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-slate-100">
                        <td class="px-4 py-4 align-top">
                            <p class="font-semibold text-brand-navy">{{ $invoice->subscription?->plan?->name ?? 'فاتورة خدمات' }}</p>
                            @if ($invoice->subscription?->plan)
                                <p class="mt-0.5 text-xs text-slate-400">اشتراك في خطة {{ $invoice->subscription->plan->name }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center text-slate-500" dir="ltr">
                            {{ $invoice->period_start?->format('Y/m/d') ?? '—' }}<br>{{ $invoice->period_end?->format('Y/m/d') ?? '' }}
                        </td>
                        <td class="px-4 py-4 text-left font-semibold text-brand-navy" dir="ltr">{{ number_format($total, 2) }} {{ $symbol }}</td>
                    </tr>
                </tbody>
            </table>

            {{-- totals --}}
            <div class="mt-6 flex">
                <div class="ms-auto w-72 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-500">الإجمالي</span>
                        <span class="font-semibold text-brand-navy" dir="ltr">{{ number_format($total, 2) }} {{ $symbol }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">المدفوع</span>
                        <span class="font-semibold text-emerald-600" dir="ltr">{{ number_format($paid, 2) }} {{ $symbol }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-brand/10 px-3 py-2.5">
                        <span class="font-bold text-brand-navy">المبلغ المستحق</span>
                        <span class="text-base font-extrabold text-brand" dir="ltr">{{ number_format($invoice->balance(), 2) }} {{ $symbol }}</span>
                    </div>
                </div>
            </div>

            {{-- payments history --}}
            @if ($invoice->payments->isNotEmpty())
                <div class="mt-8">
                    <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">سجل الدفعات</p>
                    <table class="w-full border-collapse text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-400">
                                <th class="py-2 text-right font-semibold">التاريخ</th>
                                <th class="py-2 font-semibold">الطريقة</th>
                                <th class="py-2 font-semibold">المرجع</th>
                                <th class="py-2 text-left font-semibold">المبلغ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoice->payments as $payment)
                                <tr class="border-b border-slate-100">
                                    <td class="py-2 text-slate-600">{{ $payment->paid_at?->format('Y/m/d') }}</td>
                                    <td class="py-2 text-slate-600">{{ $payment->method->label() }}</td>
                                    <td class="py-2 text-slate-400" dir="ltr">{{ $payment->reference ?? '—' }}</td>
                                    <td class="py-2 text-left font-semibold text-brand-navy" dir="ltr">{{ number_format((float) $payment->amount, 2) }} {{ $symbol }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- footer --}}
            <div class="mt-12 border-t border-slate-200 pt-5 text-center">
                <p class="text-sm font-semibold text-brand-navy">شكراً لتعاملكم معنا</p>
                <p class="mt-1 text-xs text-slate-400">{{ $tenant?->name ?? config('app.name') }} · هذه فاتورة صادرة إلكترونياً ولا تحتاج إلى توقيع.</p>
            </div>
        </div>
    </div>
    {{-- ============ END PRINT DOCUMENT ============ --}}

    {{-- record payment modal scoped to this invoice --}}
    @include('company.payments._modal', ['openInvoices' => $invoice->status->value !== 'paid' && $invoice->status->value !== 'void' ? collect([$invoice]) : collect()])
</x-layouts.dashboard>
