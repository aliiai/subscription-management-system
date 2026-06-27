<x-layouts.admin title="مستخدمو الشركات">
    {{-- header --}}
    <div data-animate="auth-card" class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('admin.dashboard') }}" class="transition hover:text-brand">لوحة التحكم</a>
                <span>/</span>
                <span class="text-slate-500 dark:text-slate-300">مستخدمو الشركات</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-brand-navy sm:text-3xl dark:text-white">مستخدمو الشركات</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">جميع المستخدمين التابعين للشركات المسجّلة في المنصة.</p>
        </div>

        <span class="inline-flex shrink-0 items-center gap-2 self-start rounded-xl bg-brand/10 px-4 py-2.5 text-sm font-semibold text-brand">
            <x-icon name="customers" class="h-5 w-5" />
            {{ $users->count() }} مستخدم
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
        @if ($users->isEmpty())
            <div class="flex flex-col items-center justify-center gap-4 px-6 py-16 text-center">
                <span class="grid h-16 w-16 place-items-center rounded-2xl bg-brand/10 text-brand">
                    <x-icon name="customers" class="h-8 w-8" />
                </span>
                <div>
                    <h3 class="text-lg font-semibold text-brand-navy dark:text-white">لا يوجد مستخدمون بعد</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">سيظهر هنا مستخدمو الشركات بمجرد تسجيلهم.</p>
                </div>
            </div>
        @else
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-6 py-4 dark:border-white/10">
                <div class="flex items-center gap-2">
                    <h2 class="text-sm font-bold text-brand-navy dark:text-white">قائمة المستخدمين</h2>
                    <span class="rounded-full bg-brand/10 px-2 py-0.5 text-xs font-semibold text-brand">{{ $users->count() }}</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] border-collapse text-right">
                    <thead>
                        <tr class="bg-slate-50/70 text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:bg-white/[0.02]">
                            <th class="px-6 py-3.5 font-semibold">المستخدم</th>
                            <th class="px-6 py-3.5 font-semibold">الشركة</th>
                            <th class="px-6 py-3.5 font-semibold">النوع</th>
                            <th class="hidden px-6 py-3.5 font-semibold lg:table-cell">تاريخ الإنشاء</th>
                            <th class="px-6 py-3.5 text-left font-semibold">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @foreach ($users as $companyUser)
                            <tr class="group transition hover:bg-slate-50/80 dark:hover:bg-white/[0.03]">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-brand to-brand-blue text-sm font-bold text-white">
                                            {{ mb_substr($companyUser->name, 0, 1) }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-brand-navy dark:text-white">{{ $companyUser->name }}</p>
                                            <p class="mt-0.5 truncate text-xs text-slate-400" dir="ltr">{{ $companyUser->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 text-sm text-slate-600 dark:text-slate-300">
                                        <x-icon name="building" class="h-4 w-4 text-slate-400" />
                                        {{ $companyUser->tenant?->name ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($companyUser->is_owner)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-brand/10 px-2.5 py-1 text-xs font-medium text-brand">
                                            <x-icon name="shield" class="h-3.5 w-3.5" /> مدير الشركة
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500 dark:bg-white/5 dark:text-slate-400">
                                            <x-icon name="users" class="h-3.5 w-3.5" /> موظف
                                        </span>
                                    @endif
                                </td>
                                <td class="hidden px-6 py-4 text-sm text-slate-500 lg:table-cell dark:text-slate-400">
                                    {{ $companyUser->created_at?->format('Y/m/d') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1.5 opacity-70 transition group-hover:opacity-100">
                                        <button type="button"
                                                class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-brand/30 hover:bg-brand/10 hover:text-brand dark:border-white/10 dark:text-slate-400"
                                                data-user-edit
                                                data-id="{{ $companyUser->id }}"
                                                data-name="{{ $companyUser->name }}"
                                                data-email="{{ $companyUser->email }}"
                                                data-owner="{{ $companyUser->is_owner ? '1' : '0' }}"
                                                data-company="{{ $companyUser->tenant?->name }}"
                                                title="تعديل">
                                            <x-icon name="pencil" class="h-[18px] w-[18px]" />
                                        </button>
                                        <button type="button"
                                                class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-rose-300 hover:bg-rose-50 hover:text-rose-600 dark:border-white/10 dark:text-slate-400 dark:hover:bg-rose-500/10"
                                                data-user-delete
                                                data-id="{{ $companyUser->id }}"
                                                data-name="{{ $companyUser->name }}"
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
                إجمالي المستخدمين: <span class="font-semibold text-slate-500 dark:text-slate-300">{{ $users->count() }}</span>
            </div>
        @endif
    </div>

    {{-- ===================== EDIT MODAL ===================== --}}
    <div data-modal="user-edit" class="fixed inset-0 z-50 hidden">
        <div data-modal-overlay class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div data-modal-dismiss class="absolute inset-0 flex items-center justify-center p-4">
            <div data-modal-panel class="w-full max-w-lg overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-white/10 dark:bg-slate-900">
                <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4 dark:border-white/10">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-brand to-brand-blue text-white shadow-lg shadow-brand/25">
                        <x-icon name="user-cog" class="h-5 w-5" />
                    </span>
                    <div class="flex-1">
                        <h3 class="text-base font-bold text-brand-navy dark:text-white">تعديل المستخدم</h3>
                        <p class="text-xs text-slate-400">الشركة: <span class="font-medium text-slate-500 dark:text-slate-300" data-user-company>—</span></p>
                    </div>
                    <button type="button" data-modal-close class="grid h-9 w-9 shrink-0 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-white/5">
                        <x-icon name="close" class="h-5 w-5" />
                    </button>
                </div>

                <form method="POST" data-user-form data-base="{{ url('admin/company-users') }}" class="grid grid-cols-2 gap-x-4 gap-y-3.5 px-6 py-5">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="user_id" value="{{ old('user_id') }}" data-user-id>

                    <div class="col-span-2">
                        <x-input-label for="user_name" value="الاسم" />
                        <div class="relative">
                            <x-auth-icon name="user" />
                            <x-text-input id="user_name" name="name" value="{{ old('name') }}" required placeholder="اسم المستخدم" class="ps-11!" />
                        </div>
                        <x-input-error :messages="$errors->get('name')" />
                    </div>

                    <div class="col-span-2">
                        <x-input-label for="user_email" value="البريد الإلكتروني" />
                        <div class="relative">
                            <x-auth-icon name="mail" />
                            <x-text-input id="user_email" type="email" name="email" value="{{ old('email') }}" required placeholder="user@company.com" class="ps-11!" dir="ltr" />
                        </div>
                        <x-input-error :messages="$errors->get('email')" />
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <x-input-label for="user_password" value="كلمة مرور جديدة" />
                        <div class="relative">
                            <x-auth-icon name="lock" />
                            <x-text-input id="user_password" type="password" name="password" placeholder="اختياري" class="px-11!" autocomplete="new-password" />
                            <x-password-toggle target="user_password" />
                        </div>
                        <x-input-error :messages="$errors->get('password')" />
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <x-input-label value="النوع" />
                        <label class="flex h-[42px] cursor-pointer items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 dark:border-white/10 dark:bg-white/5">
                            <span class="text-sm font-medium text-slate-600 dark:text-slate-300" data-owner-label>موظف</span>
                            <span class="relative">
                                <input type="checkbox" name="is_owner" value="1" class="peer sr-only" data-user-owner @checked(old('is_owner'))>
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
    <div data-modal="user-delete" class="fixed inset-0 z-50 hidden">
        <div data-modal-overlay class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div data-modal-dismiss class="absolute inset-0 flex items-center justify-center p-4">
            <div data-modal-panel class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-2xl dark:border-white/10 dark:bg-slate-900">
                <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-rose-50 text-rose-600 dark:bg-rose-500/10">
                    <x-icon name="trash" class="h-7 w-7" />
                </span>
                <h3 class="mt-4 text-lg font-bold text-brand-navy dark:text-white">حذف المستخدم</h3>
                <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">
                    هل أنت متأكد من حذف المستخدم "<span class="font-semibold text-slate-700 dark:text-slate-200" data-delete-name></span>"؟ لا يمكن التراجع عن هذا الإجراء.
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

    @if ($errors->any() && old('user_id'))
        <script>
            window.__openUserModal = { id: '{{ old('user_id') }}' };
        </script>
    @endif
</x-layouts.admin>
