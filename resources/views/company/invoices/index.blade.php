<x-layouts.dashboard title="الفواتير">
    @php
        $currencySymbols = [
            'SAR' => 'ر.س', 'AED' => 'د.إ', 'QAR' => 'ر.ق', 'KWD' => 'د.ك',
            'BHD' => 'د.ب', 'OMR' => 'ر.ع', 'EGP' => 'ج.م', 'USD' => '$', 'EUR' => '€',
        ];
        $statusStyles = [
            'unpaid' => ['bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400', 'bg-amber-500'],
            'partially_paid' => ['bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400', 'bg-blue-500'],
            'paid' => ['bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400', 'bg-emerald-500'],
            'void' => ['bg-slate-100 text-slate-500 dark:bg-white/5 dark:text-slate-400', 'bg-slate-400'],
        ];
        $tabs = ['' => 'الكل'] + collect(\App\Enums\InvoiceStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    @endphp

  <div class="flex h-full flex-col">
    {{-- header --}}
    <div data-animate="auth-card" class="flex shrink-0 flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('company.dashboard') }}" class="transition hover:text-brand">الرئيسية</a>
                <span>/</span>
                <span class="text-slate-500 dark:text-slate-300">الفواتير</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-brand-navy sm:text-3xl dark:text-white">الفواتير</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">أنشئ الفواتير يدوياً أو ولّدها آلياً للاشتراكات النشطة وتابع تحصيلها.</p>
        </div>

        <div class="flex shrink-0 items-center gap-2">
            <button type="button" data-invoice-generate
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-brand-navy shadow-sm transition hover:border-brand/30 hover:bg-brand/5 dark:border-white/10 dark:bg-slate-900 dark:text-white">
                <x-icon name="subscriptions" class="h-5 w-5" />
                <span class="hidden sm:inline">توليد فواتير الفترة</span>
                <span class="sm:hidden">توليد</span>
            </button>
            @if ($customers->isNotEmpty())
                <button type="button" data-invoice-create
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-l from-brand to-brand-blue px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand/25 transition hover:scale-[1.02] hover:shadow-brand/40 active:scale-[0.99]">
                    <x-icon name="plus" class="h-5 w-5" />
                    فاتورة جديدة
                </button>
            @endif
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
                ['إجمالي المستحق', number_format($kpis['outstanding'], 2).' ر.س', 'invoices', 'text-amber-600 bg-amber-50 dark:bg-amber-500/10'],
                ['المُحصّل هذا الشهر', number_format($kpis['collected_this_month'], 2).' ر.س', 'payments', 'text-emerald-600 bg-emerald-50 dark:bg-emerald-500/10'],
                ['فواتير غير مدفوعة', $kpis['unpaid_count'], 'revenue', 'text-blue-600 bg-blue-50 dark:bg-blue-500/10'],
                ['فواتير متأخرة', $kpis['overdue_count'], 'bell', 'text-rose-600 bg-rose-50 dark:bg-rose-500/10'],
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
    <div data-animate="auth-card" data-invoices
         data-url="{{ route('company.invoices') }}"
         class="mt-4 flex min-h-0 flex-1 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">

        {{-- toolbar: search + date filters --}}
        <div class="flex shrink-0 flex-col gap-3 border-b border-slate-100 px-4 py-3 lg:flex-row lg:items-center lg:justify-between sm:px-6 dark:border-white/10">
            <div class="flex items-center gap-2">
                <h2 class="text-sm font-bold text-brand-navy dark:text-white">قائمة الفواتير</h2>
                <span data-invoices-loading class="hidden h-4 w-4 animate-spin rounded-full border-2 border-brand/30 border-t-brand"></span>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
                <div class="relative w-full sm:w-auto">
                    <span class="pointer-events-none absolute inset-y-0 start-0 grid w-10 place-items-center text-slate-400">
                        <x-icon name="search" class="h-4 w-4" />
                    </span>
                    <input type="search" data-invoices-search value="{{ $filters['q'] ?? '' }}"
                           placeholder="بحث برقم الفاتورة أو العميل..."
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pe-3 ps-10 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 sm:w-56 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                </div>
                <div class="relative w-full sm:w-auto">
                    <select data-invoices-status title="الحالة"
                            class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 py-2 pe-9 ps-3 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 sm:w-auto dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                        @foreach ($tabs as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 left-0 grid w-9 place-items-center text-slate-400"><x-icon name="chevron-down" class="h-4 w-4" /></span>
                </div>
                <input type="date" data-invoices-from value="{{ $filters['from'] ?? '' }}" title="من تاريخ"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 sm:w-auto dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                <input type="date" data-invoices-to value="{{ $filters['to'] ?? '' }}" title="إلى تاريخ"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 sm:w-auto dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
            </div>
        </div>

        {{-- results (table + pagination) get swapped via AJAX --}}
        <div data-invoices-results class="flex min-h-0 flex-1 flex-col">
            @include('company.invoices._results')
        </div>
    </div>
  </div>

    {{-- ===================== CREATE INVOICE MODAL ===================== --}}
    <div data-modal="invoice" class="fixed inset-0 z-50 hidden">
        <div data-modal-overlay class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div data-modal-dismiss class="absolute inset-0 flex items-center justify-center p-4">
            <div data-modal-panel class="w-full max-w-lg overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-white/10 dark:bg-slate-900">
                <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4 dark:border-white/10">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-brand to-brand-blue text-white shadow-lg shadow-brand/25">
                        <x-icon name="invoices" class="h-5 w-5" />
                    </span>
                    <div class="flex-1">
                        <h3 class="text-base font-bold text-brand-navy dark:text-white">فاتورة جديدة</h3>
                        <p class="text-xs text-slate-400">أنشئ فاتورة يدوية لعميل، مع ربطها باشتراك اختيارياً.</p>
                    </div>
                    <button type="button" data-modal-close class="grid h-9 w-9 shrink-0 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-white/5">
                        <x-icon name="close" class="h-5 w-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('company.invoices.store') }}" data-invoice-form class="grid grid-cols-2 gap-x-4 gap-y-3.5 px-6 py-5">
                    @csrf

                    <div class="col-span-2">
                        <x-input-label for="inv_customer" value="العميل" />
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 start-0 grid w-11 place-items-center text-slate-400"><x-icon name="customers" class="h-5 w-5" /></span>
                            <select id="inv_customer" name="customer_id" required
                                    class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 py-2.5 pe-4 ps-11 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                                <option value="">اختر العميل</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-slate-400"><x-icon name="chevron-down" class="h-4 w-4" /></span>
                        </div>
                        <x-input-error :messages="$errors->get('customer_id')" />
                    </div>

                    <div class="col-span-2">
                        <x-input-label for="inv_subscription" value="الاشتراك (اختياري)" />
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 start-0 grid w-11 place-items-center text-slate-400"><x-icon name="subscriptions" class="h-5 w-5" /></span>
                            <select id="inv_subscription" name="subscription_id"
                                    class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 py-2.5 pe-4 ps-11 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                                <option value="">بدون اشتراك</option>
                                @foreach ($subscriptions as $subscription)
                                    <option value="{{ $subscription->id }}" data-customer="{{ $subscription->customer_id }}" data-amount="{{ $subscription->price ?? $subscription->plan?->price }}" @selected(old('subscription_id') == $subscription->id)>
                                        {{ $subscription->plan?->name }} — {{ number_format((float) ($subscription->price ?? $subscription->plan?->price), 2) }} {{ $currencySymbols[$subscription->plan?->currency] ?? $subscription->plan?->currency }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-slate-400"><x-icon name="chevron-down" class="h-4 w-4" /></span>
                        </div>
                        <x-input-error :messages="$errors->get('subscription_id')" />
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <x-input-label for="inv_issue" value="تاريخ الإصدار" />
                        <input id="inv_issue" type="date" name="issue_date" value="{{ old('issue_date', now()->format('Y-m-d')) }}" required
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                        <x-input-error :messages="$errors->get('issue_date')" />
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <x-input-label for="inv_due" value="تاريخ الاستحقاق" />
                        <input id="inv_due" type="date" name="due_date" value="{{ old('due_date', now()->addDays(14)->format('Y-m-d')) }}" required
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                        <x-input-error :messages="$errors->get('due_date')" />
                    </div>

                    <div class="col-span-2">
                        <x-input-label for="inv_amount" value="المبلغ" />
                        <input id="inv_amount" type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" required placeholder="0.00"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                        <x-input-error :messages="$errors->get('amount')" />
                    </div>

                    <div class="col-span-2 mt-1 flex items-center justify-end gap-3 border-t border-slate-100 pt-4 dark:border-white/10">
                        <button type="button" data-modal-close class="rounded-xl px-4 py-2.5 text-sm font-medium text-slate-500 transition hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-white/5">إلغاء</button>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-l from-brand to-brand-blue px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand/25 transition hover:scale-[1.02]">
                            <x-icon name="save" class="h-4 w-4" /> حفظ الفاتورة
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===================== GENERATE MODAL ===================== --}}
    <div data-modal="invoice-generate" class="fixed inset-0 z-50 hidden">
        <div data-modal-overlay class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div data-modal-dismiss class="absolute inset-0 flex items-center justify-center p-4">
            <div data-modal-panel class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-white/10 dark:bg-slate-900">
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-brand/10 text-brand">
                    <x-icon name="subscriptions" class="h-6 w-6" />
                </span>
                <h3 class="mt-4 text-lg font-bold text-brand-navy dark:text-white">توليد فواتير الفترة</h3>
                <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">سيتم إنشاء فاتورة لكل اشتراك نشط لم تُصدر له فاتورة في الشهر المحدد.</p>
                <form method="POST" action="{{ route('company.invoices.generate') }}" class="mt-5">
                    @csrf
                    <x-input-label for="gen_month" value="الشهر" />
                    <input id="gen_month" type="month" name="month" value="{{ now()->format('Y-m') }}"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                    <x-input-error :messages="$errors->get('month')" />
                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button type="button" data-modal-close class="rounded-xl px-4 py-2.5 text-sm font-medium text-slate-500 transition hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-white/5">إلغاء</button>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-l from-brand to-brand-blue px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand/25 transition hover:scale-[1.02]">
                            <x-icon name="check-double" class="h-4 w-4" /> توليد الآن
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===================== VOID MODAL ===================== --}}
    <div data-modal="invoice-void" class="fixed inset-0 z-50 hidden">
        <div data-modal-overlay class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div data-modal-dismiss class="absolute inset-0 flex items-center justify-center p-4">
            <div data-modal-panel class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-2xl dark:border-white/10 dark:bg-slate-900">
                <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-rose-50 text-rose-600 dark:bg-rose-500/10"><x-icon name="close" class="h-7 w-7" /></span>
                <h3 class="mt-4 text-lg font-bold text-brand-navy dark:text-white">إلغاء الفاتورة</h3>
                <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">
                    سيتم إلغاء الفاتورة "<span class="font-semibold text-slate-700 dark:text-slate-200" data-void-name></span>" وترحيل قيد عكسي. لا يمكن التراجع.
                </p>
                <form method="POST" data-void-form data-base="{{ url('company/invoices') }}" class="mt-6 flex items-center justify-center gap-3">
                    @csrf
                    @method('DELETE')
                    <button type="button" data-modal-close class="rounded-xl px-4 py-2.5 text-sm font-medium text-slate-500 transition hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-white/5">تراجع</button>
                    <button type="submit" class="rounded-xl bg-rose-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rose-500/25 transition hover:bg-rose-700">نعم، إلغاء</button>
                </form>
            </div>
        </div>
    </div>

    {{-- ===================== RECORD PAYMENT MODAL (shared) ===================== --}}
    @include('company.payments._modal')

    @if ($errors->any())
        <script>window.__openInvoiceModal = true;</script>
    @endif
</x-layouts.dashboard>
