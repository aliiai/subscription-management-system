<x-layouts.admin title="الشركات">
    {{-- header --}}
    <div data-animate="auth-card" class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('admin.dashboard') }}" class="transition hover:text-brand">لوحة التحكم</a>
                <span>/</span>
                <span class="text-slate-500 dark:text-slate-300">الشركات</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-brand-navy sm:text-3xl dark:text-white">الشركات</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">جميع الشركات المسجّلة في المنصة وبياناتها.</p>
        </div>

        <span class="inline-flex shrink-0 items-center gap-2 self-start rounded-xl bg-brand/10 px-4 py-2.5 text-sm font-semibold text-brand">
            <x-icon name="buildings" class="h-5 w-5" />
            {{ $companies->count() }} شركة
        </span>
    </div>

    {{-- status toast --}}
    @if (session('status'))
        <div data-toast class="mt-5 flex items-center gap-3 rounded-2xl border border-brand/20 bg-brand/10 px-4 py-3 text-sm font-medium text-brand">
            <x-icon name="check-double" class="h-5 w-5" />
            {{ session('status') }}
        </div>
    @endif

    {{-- table / empty state --}}
    <div data-animate="auth-card" class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">
        @if ($companies->isEmpty())
            <div class="flex flex-col items-center justify-center gap-4 px-6 py-16 text-center">
                <span class="grid h-16 w-16 place-items-center rounded-2xl bg-brand/10 text-brand">
                    <x-icon name="buildings" class="h-8 w-8" />
                </span>
                <div>
                    <h3 class="text-lg font-semibold text-brand-navy dark:text-white">لا توجد شركات بعد</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">ستظهر هنا الشركات بمجرد تسجيلها في المنصة.</p>
                </div>
            </div>
        @else
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-6 py-4 dark:border-white/10">
                <div class="flex items-center gap-2">
                    <h2 class="text-sm font-bold text-brand-navy dark:text-white">قائمة الشركات</h2>
                    <span class="rounded-full bg-brand/10 px-2 py-0.5 text-xs font-semibold text-brand">{{ $companies->count() }}</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] border-collapse text-right">
                    <thead>
                        <tr class="bg-slate-50/70 text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:bg-white/[0.02]">
                            <th class="px-6 py-3.5 font-semibold">الشركة</th>
                            <th class="px-6 py-3.5 font-semibold">الهاتف</th>
                            <th class="px-6 py-3.5 font-semibold">المستخدمون</th>
                            <th class="hidden px-6 py-3.5 font-semibold lg:table-cell">الخطط</th>
                            <th class="px-6 py-3.5 font-semibold">الحالة</th>
                            <th class="px-6 py-3.5 text-left font-semibold">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @foreach ($companies as $company)
                            <tr class="group transition hover:bg-slate-50/80 dark:hover:bg-white/[0.03]">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-brand/15 to-brand-blue/10 text-brand ring-1 ring-inset ring-brand/10">
                                            <x-icon name="building" class="h-5 w-5" />
                                        </span>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-brand-navy dark:text-white">{{ $company->name }}</p>
                                            <p class="mt-0.5 truncate text-xs text-slate-400" dir="ltr">{{ $company->email ?? '—' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300" dir="ltr">
                                    <span class="block text-right">{{ $company->phone ?? '—' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 dark:bg-white/5 dark:text-slate-300">
                                        <x-icon name="customers" class="h-3.5 w-3.5" /> {{ $company->users_count }}
                                    </span>
                                </td>
                                <td class="hidden px-6 py-4 lg:table-cell">
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 dark:bg-white/5 dark:text-slate-300">
                                        <x-icon name="plans" class="h-3.5 w-3.5" /> {{ $company->plans_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($company->isActive())
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> {{ $company->status->label() }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> {{ $company->status->label() }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1.5 opacity-70 transition group-hover:opacity-100">
                                        <button type="button"
                                                class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-brand/30 hover:bg-brand/10 hover:text-brand dark:border-white/10 dark:text-slate-400"
                                                data-company-edit
                                                data-id="{{ $company->id }}"
                                                data-name="{{ $company->name }}"
                                                data-email="{{ $company->email }}"
                                                data-phone="{{ $company->phone }}"
                                                data-active="{{ $company->isActive() ? '1' : '0' }}"
                                                title="تعديل">
                                            <x-icon name="pencil" class="h-[18px] w-[18px]" />
                                        </button>
                                        <button type="button"
                                                class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-rose-300 hover:bg-rose-50 hover:text-rose-600 dark:border-white/10 dark:text-slate-400 dark:hover:bg-rose-500/10"
                                                data-company-delete
                                                data-id="{{ $company->id }}"
                                                data-name="{{ $company->name }}"
                                                title="حذف">
                                            <x-icon name="trash" class="h-[18px] w-[18px]" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-6 py-3 text-xs text-slate-400 dark:border-white/10">
                إجمالي الشركات: <span class="font-semibold text-slate-500 dark:text-slate-300">{{ $companies->count() }}</span>
            </div>
        @endif
    </div>

    {{-- ===================== EDIT MODAL ===================== --}}
    <div data-modal="company-edit" class="fixed inset-0 z-50 hidden">
        <div data-modal-overlay class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div data-modal-dismiss class="absolute inset-0 flex items-center justify-center p-4">
            <div data-modal-panel class="w-full max-w-lg overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-white/10 dark:bg-slate-900">
                <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4 dark:border-white/10">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-brand to-brand-blue text-white shadow-lg shadow-brand/25">
                        <x-icon name="building" class="h-5 w-5" />
                    </span>
                    <div class="flex-1">
                        <h3 class="text-base font-bold text-brand-navy dark:text-white">تعديل الشركة</h3>
                        <p class="text-xs text-slate-400">حدّث بيانات الشركة وحالتها في المنصة.</p>
                    </div>
                    <button type="button" data-modal-close class="grid h-9 w-9 shrink-0 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-white/5">
                        <x-icon name="close" class="h-5 w-5" />
                    </button>
                </div>

                <form method="POST" data-company-form data-base="{{ url('admin/companies') }}" class="grid grid-cols-2 gap-x-4 gap-y-3.5 px-6 py-5">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="company_id" value="{{ old('company_id') }}" data-company-id>

                    <div class="col-span-2">
                        <x-input-label for="company_name" value="اسم الشركة" />
                        <div class="relative">
                            <x-auth-icon name="building" />
                            <x-text-input id="company_name" name="name" value="{{ old('name') }}" required placeholder="اسم الشركة" class="ps-11!" />
                        </div>
                        <x-input-error :messages="$errors->get('name')" />
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <x-input-label for="company_email" value="البريد الإلكتروني" />
                        <div class="relative">
                            <x-auth-icon name="mail" />
                            <x-text-input id="company_email" type="email" name="email" value="{{ old('email') }}" placeholder="info@company.com" class="ps-11!" dir="ltr" />
                        </div>
                        <x-input-error :messages="$errors->get('email')" />
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <x-input-label for="company_phone" value="رقم الهاتف" />
                        <div class="relative">
                            <x-auth-icon name="phone" />
                            <x-text-input id="company_phone" name="phone" value="{{ old('phone') }}" placeholder="05xxxxxxxx" class="ps-11!" dir="ltr" />
                        </div>
                        <x-input-error :messages="$errors->get('phone')" />
                    </div>

                    <div class="col-span-2">
                        <x-input-label value="حالة الشركة" />
                        <label class="flex h-[42px] cursor-pointer items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 dark:border-white/10 dark:bg-white/5">
                            <span class="text-sm font-medium text-slate-600 dark:text-slate-300" data-status-label>نشطة</span>
                            <span class="relative">
                                <input type="checkbox" name="is_active" value="1" class="peer sr-only" data-company-active @checked(old('is_active'))>
                                <span class="block h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-brand dark:bg-white/15"></span>
                                <span class="absolute right-1 top-1 h-4 w-4 rounded-full bg-white shadow transition peer-checked:-translate-x-5"></span>
                            </span>
                        </label>
                    </div>

                    <div class="col-span-2 mt-1 flex items-center justify-end gap-3 border-t border-slate-100 pt-4 dark:border-white/10">
                        <button type="button" data-modal-close class="rounded-xl px-4 py-2.5 text-sm font-medium text-slate-500 transition hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-white/5">إلغاء</button>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-l from-brand to-brand-blue px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand/25 transition hover:scale-[1.02]">
                            <x-icon name="save" class="h-4 w-4" />
                            حفظ التعديلات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===================== DELETE MODAL ===================== --}}
    <div data-modal="company-delete" class="fixed inset-0 z-50 hidden">
        <div data-modal-overlay class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div data-modal-dismiss class="absolute inset-0 flex items-center justify-center p-4">
            <div data-modal-panel class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-2xl dark:border-white/10 dark:bg-slate-900">
                <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-rose-50 text-rose-600 dark:bg-rose-500/10">
                    <x-icon name="trash" class="h-7 w-7" />
                </span>
                <h3 class="mt-4 text-lg font-bold text-brand-navy dark:text-white">حذف الشركة</h3>
                <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">
                    سيتم حذف شركة "<span class="font-semibold text-slate-700 dark:text-slate-200" data-delete-name></span>" مع جميع مستخدميها وخططها. لا يمكن التراجع عن هذا الإجراء.
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

    @if ($errors->any() && old('company_id'))
        <script>
            window.__openCompanyModal = { id: '{{ old('company_id') }}' };
        </script>
    @endif
</x-layouts.admin>
