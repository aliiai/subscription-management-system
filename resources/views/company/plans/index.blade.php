<x-layouts.dashboard title="خطط الاشتراك">
    @php
        $currencies = [
            'SAR' => ['cc' => 'sa', 'symbol' => 'ر.س', 'name' => 'السعودية'],
            'AED' => ['cc' => 'ae', 'symbol' => 'د.إ', 'name' => 'الإمارات'],
            'QAR' => ['cc' => 'qa', 'symbol' => 'ر.ق', 'name' => 'قطر'],
            'KWD' => ['cc' => 'kw', 'symbol' => 'د.ك', 'name' => 'الكويت'],
            'BHD' => ['cc' => 'bh', 'symbol' => 'د.ب', 'name' => 'البحرين'],
            'OMR' => ['cc' => 'om', 'symbol' => 'ر.ع', 'name' => 'عُمان'],
            'EGP' => ['cc' => 'eg', 'symbol' => 'ج.م', 'name' => 'مصر'],
            'USD' => ['cc' => 'us', 'symbol' => '$', 'name' => 'أمريكا'],
            'EUR' => ['cc' => 'eu', 'symbol' => '€', 'name' => 'اليورو'],
        ];
        $flagBase = 'https://flagcdn.com';
    @endphp

  <div class="flex h-full flex-col">
    {{-- header --}}
    <div data-animate="auth-card" class="flex shrink-0 flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('company.dashboard') }}" class="transition hover:text-brand">الرئيسية</a>
                <span>/</span>
                <span class="text-slate-500 dark:text-slate-300">خطط الاشتراك</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-brand-navy sm:text-3xl dark:text-white">خطط الاشتراك</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">أنشئ وأدر خطط الاشتراك التي تقدّمها لعملائك.</p>
        </div>

        <button type="button" data-plan-create
                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-gradient-to-l from-brand to-brand-blue px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand/25 transition hover:scale-[1.02] hover:shadow-brand/40 active:scale-[0.99]">
            <x-icon name="plus" class="h-5 w-5" />
            خطة جديدة
        </button>
    </div>

    {{-- status toast --}}
    @if (session('status'))
        <div data-toast class="mt-5 flex shrink-0 items-center gap-3 rounded-2xl border border-brand/20 bg-brand/10 px-4 py-3 text-sm font-medium text-brand">
            <x-icon name="check-double" class="h-5 w-5" />
            {{ session('status') }}
        </div>
    @endif

    {{-- table card --}}
    <div data-animate="auth-card" data-plans
         data-url="{{ route('company.plans') }}"
         class="mt-6 flex min-h-0 flex-1 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">

        {{-- toolbar: search + filters --}}
        <div class="flex shrink-0 flex-col gap-3 border-b border-slate-100 px-4 py-4 lg:flex-row lg:items-center lg:justify-between sm:px-6 dark:border-white/10">
            <div class="flex items-center gap-2">
                <h2 class="text-sm font-bold text-brand-navy dark:text-white">الخطط الحالية</h2>
                <span data-plans-loading class="hidden h-4 w-4 animate-spin rounded-full border-2 border-brand/30 border-t-brand"></span>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
                {{-- search --}}
                <div class="relative w-full sm:w-auto">
                    <span class="pointer-events-none absolute inset-y-0 start-0 grid w-10 place-items-center text-slate-400">
                        <x-icon name="search" class="h-4 w-4" />
                    </span>
                    <input type="search" data-plans-search value="{{ $filters['q'] ?? '' }}"
                           placeholder="ابحث باسم الخطة..."
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pe-3 ps-10 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 sm:w-60 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                </div>

                {{-- status filter --}}
                <div class="relative w-full sm:w-auto">
                    <select data-plans-status
                            class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 py-2 pe-9 ps-3 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 sm:w-36 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                        <option value="">كل الحالات</option>
                        <option value="active" @selected(($filters['status'] ?? '') === 'active')>نشطة</option>
                        <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>متوقفة</option>
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 left-0 grid w-9 place-items-center text-slate-400">
                        <x-icon name="chevron-down" class="h-4 w-4" />
                    </span>
                </div>

                {{-- billing cycle filter --}}
                <div class="relative w-full sm:w-auto">
                    <select data-plans-cycle
                            class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 py-2 pe-9 ps-3 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 sm:w-40 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                        <option value="">كل الدورات</option>
                        @foreach (\App\Enums\BillingCycle::cases() as $cycle)
                            <option value="{{ $cycle->value }}" @selected(($filters['cycle'] ?? '') === $cycle->value)>{{ $cycle->label() }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 left-0 grid w-9 place-items-center text-slate-400">
                        <x-icon name="chevron-down" class="h-4 w-4" />
                    </span>
                </div>

                {{-- sort --}}
                <div class="relative w-full sm:w-auto">
                    <select data-plans-sort
                            class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 py-2 pe-9 ps-3 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 sm:w-44 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                        <option value="">الأحدث أولاً</option>
                        <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>الأقدم أولاً</option>
                        <option value="most_subscribers" @selected(($filters['sort'] ?? '') === 'most_subscribers')>الأكثر اشتراكًا</option>
                        <option value="least_subscribers" @selected(($filters['sort'] ?? '') === 'least_subscribers')>الأقل اشتراكًا</option>
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 left-0 grid w-9 place-items-center text-slate-400">
                        <x-icon name="chevron-down" class="h-4 w-4" />
                    </span>
                </div>
            </div>
        </div>

        {{-- results (table + pagination) get swapped via AJAX --}}
        <div data-plans-results class="flex min-h-0 flex-1 flex-col">
            @include('company.plans._results')
        </div>
    </div>
  </div>

    {{-- ===================== CREATE / EDIT MODAL ===================== --}}
    <div data-modal="plan" class="fixed inset-0 z-50 hidden">
        <div data-modal-overlay class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div data-modal-dismiss class="absolute inset-0 flex items-center justify-center p-4">
            <div data-modal-panel class="max-h-[95vh] w-full max-w-xl overflow-y-auto rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-white/10 dark:bg-slate-900">
                {{-- header with brand accent --}}
                <div class="relative flex items-center gap-3 border-b border-slate-100 px-6 py-4 dark:border-white/10">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-brand to-brand-blue text-white shadow-lg shadow-brand/25">
                        <x-icon name="plans" class="h-5 w-5" />
                    </span>
                    <div class="flex-1">
                        <h3 class="text-base font-bold text-brand-navy dark:text-white" data-plan-modal-title>إنشاء خطة جديدة</h3>
                        <p class="text-xs text-slate-400">عرّف تفاصيل خطة الاشتراك التي ستقدّمها لعملائك.</p>
                    </div>
                    <button type="button" data-modal-close class="grid h-9 w-9 shrink-0 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-white/5">
                        <x-icon name="close" class="h-5 w-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('company.plans.store') }}" data-plan-form data-base="{{ url('company/plans') }}" class="grid grid-cols-2 gap-x-4 gap-y-3.5 px-6 py-5">
                    @csrf
                    <input type="hidden" name="_method" value="POST" data-method>
                    <input type="hidden" name="plan_id" value="{{ old('plan_id') }}" data-plan-id>

                    {{-- name --}}
                    <div class="col-span-2">
                        <x-input-label for="plan_name" value="اسم الخطة" />
                        <div class="relative">
                            <x-auth-icon name="building" />
                            <x-text-input id="plan_name" name="name" value="{{ old('name') }}" required placeholder="مثال: الباقة الاحترافية" class="ps-11!" />
                        </div>
                        <x-input-error :messages="$errors->get('name')" />
                    </div>

                    {{-- price + currency group --}}
                    @php $selectedCur = old('currency', 'SAR'); @endphp
                    <div class="col-span-2 sm:col-span-1">
                        <x-input-label value="السعر" />
                        <div class="flex rounded-xl border border-slate-200 bg-slate-50 transition focus-within:border-brand focus-within:bg-white focus-within:ring-2 focus-within:ring-brand/25 dark:border-white/10 dark:bg-white/5 dark:focus-within:bg-white/10">
                            <input id="plan_price" name="price" type="number" step="0.01" min="0" value="{{ old('price') }}" required placeholder="0.00" dir="ltr"
                                   class="w-full border-0 bg-transparent px-4 py-2.5 text-right text-sm text-slate-900 placeholder-slate-400 outline-none dark:text-slate-100">

                            <div class="relative" data-currency>
                                <input type="hidden" name="currency" value="{{ $selectedCur }}" data-currency-input>
                                <button type="button" data-currency-button
                                        class="flex h-full items-center gap-1.5 rounded-l-xl border-0 border-r border-slate-200 bg-slate-100/70 px-2.5 text-xs font-medium text-slate-700 outline-none transition hover:bg-slate-200/70 dark:border-white/10 dark:bg-white/5 dark:text-slate-200 dark:hover:bg-white/10">
                                    <img data-currency-flag src="{{ $flagBase }}/{{ $currencies[$selectedCur]['cc'] }}.svg" alt="" class="h-3.5 w-5 shrink-0 rounded-[2px] object-cover ring-1 ring-black/5">
                                    <span data-currency-symbol>{{ $currencies[$selectedCur]['symbol'] }}</span>
                                    <x-icon name="chevron-down" class="h-3 w-3 text-slate-400" />
                                </button>
                                <div data-currency-menu class="absolute left-0 top-full z-20 mt-1.5 hidden max-h-56 w-60 overflow-auto rounded-xl border border-slate-200 bg-white p-1 shadow-xl shadow-slate-300/40 dark:border-white/10 dark:bg-slate-800 dark:shadow-black/40">
                                    @foreach ($currencies as $code => $cur)
                                        <button type="button" data-currency-option data-code="{{ $code }}" data-symbol="{{ $cur['symbol'] }}" data-flag="{{ $flagBase }}/{{ $cur['cc'] }}.svg"
                                                class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-2 text-right transition hover:bg-slate-100 dark:hover:bg-white/5">
                                            <img src="{{ $flagBase }}/{{ $cur['cc'] }}.svg" alt="" class="h-4 w-6 shrink-0 rounded-[2px] object-cover ring-1 ring-black/5">
                                            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $cur['symbol'] }}</span>
                                            <span class="mr-auto text-xs text-slate-400">{{ $cur['name'] }} · {{ $code }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('price')" />
                        <x-input-error :messages="$errors->get('currency')" />
                    </div>

                    {{-- status toggle --}}
                    <div class="col-span-2 sm:col-span-1">
                        <x-input-label value="حالة الخطة" />
                        <label class="flex h-[42px] cursor-pointer items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 dark:border-white/10 dark:bg-white/5">
                            <span class="text-sm font-medium text-slate-600 dark:text-slate-300" data-active-label>نشطة</span>
                            <span class="relative">
                                <input type="checkbox" name="is_active" value="1" class="peer sr-only" data-plan-active @checked($errors->any() ? old('is_active') : true)>
                                <span class="block h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-brand dark:bg-white/15"></span>
                                <span class="absolute right-1 top-1 h-4 w-4 rounded-full bg-white shadow transition peer-checked:-translate-x-5"></span>
                            </span>
                        </label>
                    </div>

                    {{-- billing cycle segmented --}}
                    <div class="col-span-2">
                        <x-input-label value="دورة الفوترة" />
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                            @foreach (\App\Enums\BillingCycle::cases() as $cycle)
                                <label class="cursor-pointer">
                                    <input type="radio" name="billing_cycle" value="{{ $cycle->value }}" class="peer sr-only" @checked(old('billing_cycle', 'monthly') === $cycle->value)>
                                    <span class="block rounded-xl border border-slate-200 bg-slate-50 py-2.5 text-center text-sm font-medium text-slate-600 transition hover:border-brand/40 peer-checked:border-brand peer-checked:bg-brand/10 peer-checked:text-brand dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:peer-checked:bg-brand/15">
                                        {{ $cycle->label() }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('billing_cycle')" />
                    </div>

                    {{-- description --}}
                    <div class="col-span-2">
                        <x-input-label for="plan_description" value="الوصف (اختياري)" />
                        <textarea id="plan_description" name="description" rows="2" placeholder="وصف مختصر للخطة"
                                  class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 dark:border-white/10 dark:bg-white/5 dark:text-slate-100 dark:placeholder-slate-500">{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" />
                    </div>

                    {{-- features --}}
                    <div class="col-span-2">
                        <x-input-label for="plan_features" value="المميزات (ميزة في كل سطر)" />
                        <textarea id="plan_features" name="features" rows="3" placeholder="عملاء غير محدودين&#10;تقارير لحظية&#10;دعم على مدار الساعة"
                                  class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 dark:border-white/10 dark:bg-white/5 dark:text-slate-100 dark:placeholder-slate-500">{{ old('features') }}</textarea>
                        <x-input-error :messages="$errors->get('features')" />
                    </div>

                    {{-- actions --}}
                    <div class="col-span-2 mt-1 flex items-center justify-end gap-3 border-t border-slate-100 pt-4 dark:border-white/10">
                        <button type="button" data-modal-close class="rounded-xl px-4 py-2.5 text-sm font-medium text-slate-500 transition hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-white/5">إلغاء</button>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-l from-brand to-brand-blue px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand/25 transition hover:scale-[1.02]" data-plan-submit>
                            <x-icon name="save" class="h-4 w-4" />
                            <span data-plan-submit-text>حفظ الخطة</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===================== DELETE MODAL ===================== --}}
    <div data-modal="delete" class="fixed inset-0 z-50 hidden">
        <div data-modal-overlay class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div data-modal-dismiss class="absolute inset-0 flex items-center justify-center p-4">
            <div data-modal-panel class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-2xl dark:border-white/10 dark:bg-slate-900">
                <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-rose-50 text-rose-600 dark:bg-rose-500/10">
                    <x-icon name="trash" class="h-7 w-7" />
                </span>
                <h3 class="mt-4 text-lg font-bold text-brand-navy dark:text-white">حذف الخطة</h3>
                <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">
                    هل أنت متأكد من حذف خطة "<span class="font-semibold text-slate-700 dark:text-slate-200" data-delete-name></span>"؟ لا يمكن التراجع عن هذا الإجراء.
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

    @if ($errors->any())
        <script>
            window.__openPlanModal = {
                mode: '{{ old('_method') === 'PUT' ? 'edit' : 'create' }}',
                id: '{{ old('plan_id') }}',
            };
        </script>
    @endif
</x-layouts.dashboard>
