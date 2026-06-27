<x-layouts.dashboard title="الاشتراكات">
    @php
        $currencySymbols = [
            'SAR' => 'ر.س', 'AED' => 'د.إ', 'QAR' => 'ر.ق', 'KWD' => 'د.ك',
            'BHD' => 'د.ب', 'OMR' => 'ر.ع', 'EGP' => 'ج.م', 'USD' => '$', 'EUR' => '€',
        ];
        $statusStyles = [
            'active' => ['bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400', 'bg-emerald-500'],
            'canceled' => ['bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400', 'bg-rose-500'],
            'expired' => ['bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400', 'bg-amber-500'],
        ];
    @endphp

  <div class="flex h-full flex-col">
    {{-- header --}}
    <div data-animate="auth-card" class="flex shrink-0 flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('company.dashboard') }}" class="transition hover:text-brand">الرئيسية</a>
                <span>/</span>
                <span class="text-slate-500 dark:text-slate-300">الاشتراكات</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-brand-navy sm:text-3xl dark:text-white">الاشتراكات</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">اربط عملاءك بخطط الاشتراك وتابع حالتها.</p>
        </div>

        @if ($customers->isNotEmpty() && $plans->isNotEmpty())
            <button type="button" data-subscription-create
                    class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-gradient-to-l from-brand to-brand-blue px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand/25 transition hover:scale-[1.02] hover:shadow-brand/40 active:scale-[0.99]">
                <x-icon name="plus" class="h-5 w-5" />
                اشتراك جديد
            </button>
        @endif
    </div>

    {{-- status toast --}}
    @if (session('status'))
        <div data-toast class="mt-5 flex shrink-0 items-center gap-3 rounded-2xl border border-brand/20 bg-brand/10 px-4 py-3 text-sm font-medium text-brand">
            <x-icon name="check-double" class="h-5 w-5" />
            {{ session('status') }}
        </div>
    @endif

    {{-- prerequisites notice --}}
    @if ($customers->isEmpty() || $plans->isEmpty())
        <div class="mt-5 flex shrink-0 items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-400">
            <x-icon name="bell" class="mt-0.5 h-5 w-5 shrink-0" />
            <span>
                لإنشاء اشتراك تحتاج إلى
                @if ($customers->isEmpty())<a href="{{ route('company.customers') }}" class="font-semibold underline">عميل واحد على الأقل</a>@endif
                @if ($customers->isEmpty() && $plans->isEmpty()) و @endif
                @if ($plans->isEmpty())<a href="{{ route('company.plans') }}" class="font-semibold underline">خطة اشتراك واحدة على الأقل</a>@endif.
            </span>
        </div>
    @endif

    {{-- table card --}}
    <div data-animate="auth-card" data-subscriptions
         data-url="{{ route('company.subscriptions') }}"
         class="mt-6 flex min-h-0 flex-1 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">

        {{-- toolbar: search + filters --}}
        <div class="flex shrink-0 flex-col gap-3 border-b border-slate-100 px-4 py-4 lg:flex-row lg:items-center lg:justify-between sm:px-6 dark:border-white/10">
            <div class="flex items-center gap-2">
                <h2 class="text-sm font-bold text-brand-navy dark:text-white">قائمة الاشتراكات</h2>
                <span data-subscriptions-loading class="hidden h-4 w-4 animate-spin rounded-full border-2 border-brand/30 border-t-brand"></span>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                {{-- search --}}
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 start-0 grid w-10 place-items-center text-slate-400">
                        <x-icon name="search" class="h-4 w-4" />
                    </span>
                    <input type="search" data-subscriptions-search value="{{ $filters['q'] ?? '' }}"
                           placeholder="ابحث بالعميل أو الخطة..."
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pe-3 ps-10 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 sm:w-56 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                </div>

                {{-- status filter --}}
                <div class="relative">
                    <select data-subscriptions-status
                            class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 py-2 pe-9 ps-3 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 sm:w-40 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                        <option value="">كل الحالات</option>
                        @foreach (\App\Enums\SubscriptionStatus::cases() as $case)
                            <option value="{{ $case->value }}" @selected(($filters['status'] ?? '') === $case->value)>{{ $case->label() }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 left-0 grid w-9 place-items-center text-slate-400">
                        <x-icon name="chevron-down" class="h-4 w-4" />
                    </span>
                </div>

                {{-- plan filter --}}
                <div class="relative">
                    <select data-subscriptions-plan
                            class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 py-2 pe-9 ps-3 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 sm:w-44 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                        <option value="">كل الخطط</option>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}" @selected(($filters['plan'] ?? '') == $plan->id)>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 left-0 grid w-9 place-items-center text-slate-400">
                        <x-icon name="chevron-down" class="h-4 w-4" />
                    </span>
                </div>
            </div>
        </div>

        {{-- results (table + pagination) get swapped via AJAX --}}
        <div data-subscriptions-results class="flex min-h-0 flex-1 flex-col">
            @include('company.subscriptions._results')
        </div>
    </div>
  </div>

    {{-- ===================== CREATE / EDIT MODAL ===================== --}}
    <div data-modal="subscription" class="fixed inset-0 z-50 hidden">
        <div data-modal-overlay class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div data-modal-dismiss class="absolute inset-0 flex items-center justify-center p-4">
            <div data-modal-panel class="w-full max-w-lg overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-white/10 dark:bg-slate-900">
                <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4 dark:border-white/10">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-brand to-brand-blue text-white shadow-lg shadow-brand/25">
                        <x-icon name="subscriptions" class="h-5 w-5" />
                    </span>
                    <div class="flex-1">
                        <h3 class="text-base font-bold text-brand-navy dark:text-white" data-subscription-modal-title>اشتراك جديد</h3>
                        <p class="text-xs text-slate-400">اربط عميلاً بخطة اشتراك وحدّد تاريخ البدء.</p>
                    </div>
                    <button type="button" data-modal-close class="grid h-9 w-9 shrink-0 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-white/5">
                        <x-icon name="close" class="h-5 w-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('company.subscriptions.store') }}" data-subscription-form data-base="{{ url('company/subscriptions') }}" class="grid grid-cols-2 gap-x-4 gap-y-3.5 px-6 py-5">
                    @csrf
                    <input type="hidden" name="_method" value="POST" data-method>
                    <input type="hidden" name="subscription_id" value="{{ old('subscription_id') }}" data-subscription-id>

                    {{-- customer --}}
                    <div class="col-span-2">
                        <x-input-label for="sub_customer" value="العميل" />
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 start-0 grid w-11 place-items-center text-slate-400">
                                <x-icon name="customers" class="h-5 w-5" />
                            </span>
                            <select id="sub_customer" name="customer_id" required
                                    class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 py-2.5 pe-4 ps-11 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                                <option value="">اختر العميل</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-slate-400">
                                <x-icon name="chevron-down" class="h-4 w-4" />
                            </span>
                        </div>
                        <x-input-error :messages="$errors->get('customer_id')" />
                    </div>

                    {{-- plan --}}
                    <div class="col-span-2">
                        <x-input-label for="sub_plan" value="خطة الاشتراك" />
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 start-0 grid w-11 place-items-center text-slate-400">
                                <x-icon name="plans" class="h-5 w-5" />
                            </span>
                            <select id="sub_plan" name="plan_id" required
                                    class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 py-2.5 pe-4 ps-11 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                                <option value="">اختر الخطة</option>
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>{{ $plan->name }} — {{ number_format((float) $plan->price, 2) }} {{ $currencySymbols[$plan->currency] ?? $plan->currency }} / {{ $plan->billing_cycle->label() }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-slate-400">
                                <x-icon name="chevron-down" class="h-4 w-4" />
                            </span>
                        </div>
                        <x-input-error :messages="$errors->get('plan_id')" />
                    </div>

                    {{-- start date --}}
                    <div class="col-span-2 sm:col-span-1">
                        <x-input-label for="sub_start" value="تاريخ البدء" />
                        <input id="sub_start" type="date" name="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" required
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                        <x-input-error :messages="$errors->get('start_date')" />
                    </div>

                    {{-- status --}}
                    <div class="col-span-2 sm:col-span-1">
                        <x-input-label for="sub_status" value="الحالة" />
                        <div class="relative">
                            <select id="sub_status" name="status" required
                                    class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                                @foreach (\App\Enums\SubscriptionStatus::cases() as $case)
                                    <option value="{{ $case->value }}" @selected(old('status', 'active') === $case->value)>{{ $case->label() }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-slate-400">
                                <x-icon name="chevron-down" class="h-4 w-4" />
                            </span>
                        </div>
                        <x-input-error :messages="$errors->get('status')" />
                    </div>

                    <div class="col-span-2 mt-1 flex items-center justify-end gap-3 border-t border-slate-100 pt-4 dark:border-white/10">
                        <button type="button" data-modal-close class="rounded-xl px-4 py-2.5 text-sm font-medium text-slate-500 transition hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-white/5">إلغاء</button>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-l from-brand to-brand-blue px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand/25 transition hover:scale-[1.02]">
                            <x-icon name="save" class="h-4 w-4" />
                            <span data-subscription-submit-text>حفظ الاشتراك</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===================== DELETE MODAL ===================== --}}
    <div data-modal="subscription-delete" class="fixed inset-0 z-50 hidden">
        <div data-modal-overlay class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div data-modal-dismiss class="absolute inset-0 flex items-center justify-center p-4">
            <div data-modal-panel class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-2xl dark:border-white/10 dark:bg-slate-900">
                <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-rose-50 text-rose-600 dark:bg-rose-500/10">
                    <x-icon name="trash" class="h-7 w-7" />
                </span>
                <h3 class="mt-4 text-lg font-bold text-brand-navy dark:text-white">حذف الاشتراك</h3>
                <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">
                    هل أنت متأكد من حذف اشتراك "<span class="font-semibold text-slate-700 dark:text-slate-200" data-delete-name></span>"؟ لا يمكن التراجع عن هذا الإجراء.
                </p>
                <form method="POST" data-delete-form class="mt-6 flex items-center justify-center gap-3">
                    @csrf
                    @method('DELETE')
                    <button type="button" data-modal-close class="rounded-xl px-4 py-2.5 text-sm font-medium text-slate-500 transition hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-white/5">إلغاء</button>
                    <button type="submit" class="rounded-xl bg-rose-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rose-500/25 transition hover:bg-rose-700">نعم، احذف</button>
                </form>
            </div>
        </div>
    </div>

    @if ($errors->any() && old('subscription_id') !== null)
        <script>
            window.__openSubscriptionModal = {
                mode: '{{ old('_method') === 'PUT' ? 'edit' : 'create' }}',
                id: '{{ old('subscription_id') }}',
            };
        </script>
    @elseif ($errors->any())
        <script>
            window.__openSubscriptionModal = { mode: 'create', id: '' };
        </script>
    @endif
</x-layouts.dashboard>
