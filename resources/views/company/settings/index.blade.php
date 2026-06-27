<x-layouts.dashboard title="الإعدادات">
    @php
        $companyHasErrors = $errors->getBag('company')->isNotEmpty();
        $profileHasErrors = $errors->getBag('profile')->isNotEmpty();
        $passwordHasErrors = $errors->getBag('password')->isNotEmpty();
        $activeTab = $profileHasErrors ? 'profile' : ($passwordHasErrors ? 'security' : 'company');

        $tabs = [
            ['id' => 'company', 'label' => 'معلومات الشركة', 'desc' => 'الاسم والشعار والتواصل', 'icon' => 'building'],
            ['id' => 'profile', 'label' => 'الملف الشخصي', 'desc' => 'بياناتك الشخصية', 'icon' => 'user-cog'],
            ['id' => 'security', 'label' => 'الأمان', 'desc' => 'كلمة المرور', 'icon' => 'shield'],
            ['id' => 'danger', 'label' => 'منطقة الخطر', 'desc' => 'تعطيل الحساب', 'icon' => 'logout'],
        ];
    @endphp

    <div class="mx-auto w-full max-w-6xl" data-settings data-initial="{{ $activeTab }}">

        {{-- ===================== HERO ===================== --}}
        <div data-animate="auth-card" class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">
            <div class="relative h-28 bg-gradient-to-l from-brand to-brand-blue sm:h-32">
                <div class="pointer-events-none absolute inset-0 opacity-[0.15]"
                     style="background-image: radial-gradient(white 1px, transparent 1px); background-size: 18px 18px;"></div>
            </div>
            <div class="flex flex-col gap-4 px-5 pb-5 sm:flex-row sm:items-end sm:px-7 sm:pb-6">
                <span class="relative z-10 -mt-12 grid h-24 w-24 shrink-0 place-items-center overflow-hidden rounded-3xl bg-gradient-to-br from-brand to-brand-blue text-3xl font-bold text-white shadow-xl shadow-brand/30 ring-4 ring-white sm:-mt-14 dark:ring-slate-900">
                    <img data-logo-preview src="{{ $tenant->logo_url }}" alt=""
                         class="h-full w-full object-cover {{ $tenant->logo_url ? '' : 'hidden' }}">
                    <span data-logo-fallback class="{{ $tenant->logo_url ? 'hidden' : '' }}">{{ mb_substr($tenant->name, 0, 1) }}</span>
                </span>
                <div class="min-w-0 flex-1 sm:pb-1">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h1 class="truncate text-xl font-bold tracking-tight text-brand-navy sm:text-2xl dark:text-white">{{ $tenant->name }}</h1>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> {{ $tenant->status->label() }}
                        </span>
                    </div>
                    <p class="mt-1 flex items-center gap-1.5 text-sm text-slate-500 dark:text-slate-400">
                        <x-icon name="mail" class="h-4 w-4 shrink-0" />
                        <span dir="ltr" class="truncate">{{ $tenant->email ?? $user->email }}</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- status toast --}}
        @if (session('status'))
            <div data-toast class="mt-5 flex items-center gap-3 rounded-2xl border border-brand/20 bg-brand/10 px-4 py-3 text-sm font-medium text-brand">
                <x-icon name="check-double" class="h-5 w-5 shrink-0" />
                {{ session('status') }}
            </div>
        @endif

        {{-- ===================== BODY: NAV + PANELS ===================== --}}
        <div class="mt-6 grid gap-6 lg:grid-cols-[260px_minmax(0,1fr)]">

            {{-- nav --}}
            <aside data-animate="auth-card" class="lg:sticky lg:top-0 lg:self-start">
                <nav class="flex gap-1.5 overflow-x-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-sm lg:flex-col lg:overflow-visible dark:border-white/10 dark:bg-slate-900">
                    @foreach ($tabs as $tab)
                        @php $isDanger = $tab['id'] === 'danger'; @endphp
                        <button type="button" data-settings-tab="{{ $tab['id'] }}" data-active="{{ $activeTab === $tab['id'] ? 'true' : 'false' }}"
                                @class([
                                    'group flex shrink-0 items-center gap-3 rounded-xl px-3 py-2.5 text-right text-sm font-medium transition lg:w-full',
                                    // active (brand)
                                    'data-[active=true]:bg-gradient-to-l data-[active=true]:from-brand data-[active=true]:to-brand-blue data-[active=true]:text-white data-[active=true]:shadow-lg data-[active=true]:shadow-brand/25' => ! $isDanger,
                                    'data-[active=false]:text-slate-600 data-[active=false]:hover:bg-slate-100 dark:data-[active=false]:text-slate-300 dark:data-[active=false]:hover:bg-white/5' => ! $isDanger,
                                    // active (danger)
                                    'data-[active=true]:bg-rose-600 data-[active=true]:text-white data-[active=true]:shadow-lg data-[active=true]:shadow-rose-500/25' => $isDanger,
                                    'data-[active=false]:text-rose-600 data-[active=false]:hover:bg-rose-50 dark:data-[active=false]:hover:bg-rose-500/10' => $isDanger,
                                ])>
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-current/10 transition group-data-[active=true]:bg-white/20">
                                <x-icon name="{{ $tab['icon'] }}" class="h-5 w-5" />
                            </span>
                            <span class="hidden flex-1 sm:block">
                                <span class="block leading-tight">{{ $tab['label'] }}</span>
                                <span class="block text-[11px] font-normal opacity-70 group-data-[active=true]:opacity-90">{{ $tab['desc'] }}</span>
                            </span>
                            <span class="sm:hidden">{{ $tab['label'] }}</span>
                        </button>
                    @endforeach
                </nav>
            </aside>

            {{-- panels --}}
            <div class="min-w-0">

                {{-- ===================== COMPANY PROFILE ===================== --}}
                <section data-settings-panel="company" @class(['space-y-6', 'hidden' => $activeTab !== 'company'])>
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">
                        <div class="flex items-center gap-3 border-b border-slate-100 px-4 py-4 sm:px-6 dark:border-white/10">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand/10 text-brand">
                                <x-icon name="building" class="h-5 w-5" />
                            </span>
                            <div>
                                <h2 class="text-base font-bold text-brand-navy dark:text-white">معلومات الشركة</h2>
                                <p class="text-xs text-slate-400">الاسم وبيانات التواصل والشعار الذي يظهر في الفواتير.</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('company.settings.company') }}" enctype="multipart/form-data" class="px-4 py-5 sm:px-6">
                            @csrf
                            @method('PUT')

                            {{-- logo --}}
                            <div class="flex flex-col gap-4 rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 p-4 sm:flex-row sm:items-center dark:border-white/10 dark:bg-white/[0.02]">
                                <span class="grid h-20 w-20 shrink-0 place-items-center overflow-hidden rounded-2xl bg-gradient-to-br from-brand to-brand-blue text-2xl font-bold text-white shadow-lg shadow-brand/25">
                                    <img data-logo-preview src="{{ $tenant->logo_url }}" alt=""
                                         class="h-full w-full object-cover {{ $tenant->logo_url ? '' : 'hidden' }}">
                                    <span data-logo-fallback class="{{ $tenant->logo_url ? 'hidden' : '' }}">{{ mb_substr($tenant->name, 0, 1) }}</span>
                                </span>
                                <div class="flex flex-col gap-2">
                                    <p class="text-sm font-semibold text-brand-navy dark:text-white">شعار الشركة</p>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <input type="file" name="logo" id="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml" data-logo-input class="hidden">
                                        <label for="logo" class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-brand-navy shadow-sm transition hover:border-brand/30 hover:bg-brand/5 dark:border-white/10 dark:bg-slate-900 dark:text-white">
                                            <x-icon name="plus" class="h-4 w-4" /> رفع شعار
                                        </label>
                                        @if ($tenant->logo_url)
                                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium text-rose-600 transition hover:bg-rose-50 dark:hover:bg-rose-500/10">
                                                <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500"> إزالة
                                            </label>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-400">PNG أو JPG أو SVG، بحد أقصى 2 ميجابايت.</p>
                                    <x-input-error :messages="$errors->getBag('company')->get('logo')" />
                                </div>
                            </div>

                            <div class="mt-6 grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <x-input-label for="company_name" value="اسم الشركة" />
                                    <div class="relative">
                                        <span class="pointer-events-none absolute inset-y-0 start-0 grid w-11 place-items-center text-slate-400"><x-icon name="building" class="h-5 w-5" /></span>
                                        <x-text-input id="company_name" name="name" type="text" class="ps-11"
                                                      value="{{ $companyHasErrors ? old('name', $tenant->name) : $tenant->name }}" required />
                                    </div>
                                    <x-input-error :messages="$errors->getBag('company')->get('name')" />
                                </div>
                                <div>
                                    <x-input-label for="company_email" value="البريد الإلكتروني" />
                                    <div class="relative">
                                        <span class="pointer-events-none absolute inset-y-0 start-0 grid w-11 place-items-center text-slate-400"><x-icon name="mail" class="h-5 w-5" /></span>
                                        <x-text-input id="company_email" name="email" type="email" dir="ltr" class="ps-11 text-right"
                                                      value="{{ $companyHasErrors ? old('email', $tenant->email) : $tenant->email }}" placeholder="company@example.com" />
                                    </div>
                                    <x-input-error :messages="$errors->getBag('company')->get('email')" />
                                </div>
                                <div>
                                    <x-input-label for="company_phone" value="رقم الهاتف" />
                                    <div class="relative">
                                        <span class="pointer-events-none absolute inset-y-0 start-0 grid w-11 place-items-center text-slate-400"><x-icon name="phone" class="h-5 w-5" /></span>
                                        <x-text-input id="company_phone" name="phone" type="text" dir="ltr" class="ps-11 text-right"
                                                      value="{{ $companyHasErrors ? old('phone', $tenant->phone) : $tenant->phone }}" placeholder="05xxxxxxxx" />
                                    </div>
                                    <x-input-error :messages="$errors->getBag('company')->get('phone')" />
                                </div>
                            </div>

                            <div class="mt-5 flex justify-end border-t border-slate-100 pt-4 dark:border-white/10">
                                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-l from-brand to-brand-blue px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand/25 transition hover:scale-[1.02]">
                                    <x-icon name="save" class="h-4 w-4" /> حفظ التغييرات
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                {{-- ===================== MY PROFILE ===================== --}}
                <section data-settings-panel="profile" @class(['space-y-6', 'hidden' => $activeTab !== 'profile'])>
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">
                        <div class="flex items-center gap-3 border-b border-slate-100 px-4 py-4 sm:px-6 dark:border-white/10">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand/10 text-brand">
                                <x-icon name="user-cog" class="h-5 w-5" />
                            </span>
                            <div>
                                <h2 class="text-base font-bold text-brand-navy dark:text-white">الملف الشخصي</h2>
                                <p class="text-xs text-slate-400">اسمك وبريد تسجيل الدخول.</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('company.settings.profile') }}" class="px-4 py-5 sm:px-6">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-2">
                                <div>
                                    <x-input-label for="profile_name" value="الاسم" />
                                    <div class="relative">
                                        <span class="pointer-events-none absolute inset-y-0 start-0 grid w-11 place-items-center text-slate-400"><x-icon name="account" class="h-5 w-5" /></span>
                                        <x-text-input id="profile_name" name="name" type="text" class="ps-11"
                                                      value="{{ $profileHasErrors ? old('name', $user->name) : $user->name }}" required />
                                    </div>
                                    <x-input-error :messages="$errors->getBag('profile')->get('name')" />
                                </div>
                                <div>
                                    <x-input-label for="profile_email" value="البريد الإلكتروني" />
                                    <div class="relative">
                                        <span class="pointer-events-none absolute inset-y-0 start-0 grid w-11 place-items-center text-slate-400"><x-icon name="mail" class="h-5 w-5" /></span>
                                        <x-text-input id="profile_email" name="email" type="email" dir="ltr" class="ps-11 text-right"
                                                      value="{{ $profileHasErrors ? old('email', $user->email) : $user->email }}" required />
                                    </div>
                                    <x-input-error :messages="$errors->getBag('profile')->get('email')" />
                                </div>
                            </div>

                            <div class="mt-5 flex justify-end border-t border-slate-100 pt-4 dark:border-white/10">
                                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-l from-brand to-brand-blue px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand/25 transition hover:scale-[1.02]">
                                    <x-icon name="save" class="h-4 w-4" /> حفظ
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                {{-- ===================== SECURITY ===================== --}}
                <section data-settings-panel="security" @class(['space-y-6', 'hidden' => $activeTab !== 'security'])>
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">
                        <div class="flex items-center gap-3 border-b border-slate-100 px-4 py-4 sm:px-6 dark:border-white/10">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand/10 text-brand">
                                <x-icon name="shield" class="h-5 w-5" />
                            </span>
                            <div>
                                <h2 class="text-base font-bold text-brand-navy dark:text-white">الأمان</h2>
                                <p class="text-xs text-slate-400">غيّر كلمة المرور الخاصة بك بانتظام للحفاظ على أمان حسابك.</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('company.settings.password') }}" class="px-4 py-5 sm:px-6">
                            @csrf
                            @method('PUT')

                            <div class="space-y-4">
                                <div class="max-w-md">
                                    <x-input-label for="current_password" value="كلمة المرور الحالية" />
                                    <div class="relative">
                                        <span class="pointer-events-none absolute inset-y-0 start-0 grid w-11 place-items-center text-slate-400"><x-icon name="key" class="h-5 w-5" /></span>
                                        <x-text-input id="current_password" name="current_password" type="password" class="px-11" autocomplete="current-password" />
                                        <x-password-toggle target="current_password" />
                                    </div>
                                    <x-input-error :messages="$errors->getBag('password')->get('current_password')" />
                                </div>

                                <div class="grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-2">
                                    <div>
                                        <x-input-label for="password" value="كلمة المرور الجديدة" />
                                        <div class="relative">
                                            <span class="pointer-events-none absolute inset-y-0 start-0 grid w-11 place-items-center text-slate-400"><x-icon name="key" class="h-5 w-5" /></span>
                                            <x-text-input id="password" name="password" type="password" class="px-11" autocomplete="new-password" />
                                            <x-password-toggle target="password" />
                                        </div>
                                        <x-input-error :messages="$errors->getBag('password')->get('password')" />
                                    </div>
                                    <div>
                                        <x-input-label for="password_confirmation" value="تأكيد كلمة المرور" />
                                        <div class="relative">
                                            <span class="pointer-events-none absolute inset-y-0 start-0 grid w-11 place-items-center text-slate-400"><x-icon name="key" class="h-5 w-5" /></span>
                                            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="px-11" autocomplete="new-password" />
                                            <x-password-toggle target="password_confirmation" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 flex justify-end border-t border-slate-100 pt-4 dark:border-white/10">
                                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-l from-brand to-brand-blue px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand/25 transition hover:scale-[1.02]">
                                    <x-icon name="save" class="h-4 w-4" /> تحديث كلمة المرور
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                {{-- ===================== DANGER ZONE ===================== --}}
                <section data-settings-panel="danger" @class(['space-y-6', 'hidden' => $activeTab !== 'danger'])>
                    <div class="overflow-hidden rounded-2xl border border-rose-200 bg-white shadow-sm dark:border-rose-500/20 dark:bg-slate-900">
                        <div class="flex items-center gap-3 border-b border-rose-100 px-4 py-4 sm:px-6 dark:border-rose-500/10">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-500/10">
                                <x-icon name="bell" class="h-5 w-5" />
                            </span>
                            <div>
                                <h2 class="text-base font-bold text-rose-600">منطقة الخطر</h2>
                                <p class="text-xs text-slate-400">إجراءات حساسة لا يمكن التراجع عنها بسهولة.</p>
                            </div>
                        </div>

                        <div class="flex flex-col gap-4 rounded-2xl border border-rose-100 bg-rose-50/50 px-5 py-4 m-5 sm:flex-row sm:items-center sm:justify-between dark:border-rose-500/10 dark:bg-rose-500/5">
                            <div>
                                <p class="text-sm font-semibold text-brand-navy dark:text-white">تعطيل حساب الشركة</p>
                                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">سيتم تسجيل خروجك ولن يتمكن أحد من الدخول حتى يعيد الدعم التفعيل.</p>
                            </div>
                            <button type="button" data-account-deactivate
                                    class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rose-500/25 transition hover:bg-rose-700">
                                <x-icon name="logout" class="h-4 w-4" /> تعطيل الحساب
                            </button>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </div>

    {{-- ===================== DEACTIVATE CONFIRM MODAL ===================== --}}
    <div data-modal="account-deactivate" class="fixed inset-0 z-50 hidden">
        <div data-modal-overlay class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div data-modal-dismiss class="absolute inset-0 flex items-center justify-center p-4">
            <div data-modal-panel class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-2xl dark:border-white/10 dark:bg-slate-900">
                <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-rose-50 text-rose-600 dark:bg-rose-500/10"><x-icon name="bell" class="h-7 w-7" /></span>
                <h3 class="mt-4 text-lg font-bold text-brand-navy dark:text-white">تأكيد تعطيل الحساب</h3>
                <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">
                    سيتم إيقاف حساب "<span class="font-semibold text-slate-700 dark:text-slate-200">{{ $tenant->name }}</span>" وتسجيل خروجك فوراً. لإعادة التفعيل تواصل مع الدعم.
                </p>
                <form method="POST" action="{{ route('company.settings.deactivate') }}" class="mt-6 flex items-center justify-center gap-3">
                    @csrf
                    @method('DELETE')
                    <button type="button" data-modal-close class="rounded-xl px-4 py-2.5 text-sm font-medium text-slate-500 transition hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-white/5">تراجع</button>
                    <button type="submit" class="rounded-xl bg-rose-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rose-500/25 transition hover:bg-rose-700">نعم، عطّل الحساب</button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
