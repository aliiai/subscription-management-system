@php
    $activeForm = old('form', 'login');
@endphp

<x-layouts.guest>
    <div class="flex h-screen w-full">
        {{-- ================= BRAND PANEL (right in RTL) ================= --}}
        <aside class="relative hidden w-1/2 flex-col justify-between overflow-hidden p-12 text-white lg:flex xl:p-16"
               style="background: radial-gradient(120% 120% at 100% 0%, #0a4589 0%, #073060 55%, #04203f 100%);">

            {{-- subtle dotted grid --}}
            <div class="pointer-events-none absolute inset-0 opacity-[0.18]"
                 style="background-image: radial-gradient(rgba(255,255,255,.35) 1px, transparent 1px); background-size: 22px 22px;"></div>
            {{-- glows --}}
            <div data-animate="orb" class="pointer-events-none absolute -top-24 -left-24 h-96 w-96 rounded-full bg-brand/30 blur-3xl"></div>
            <div class="pointer-events-none absolute bottom-[-12rem] right-[-8rem] h-[26rem] w-[26rem] rounded-full bg-brand-blue/40 blur-3xl"></div>

            {{-- logo --}}
            <div data-animate="brand" class="relative flex items-center gap-3">
                <span class="grid h-11 w-11 place-items-center rounded-2xl bg-white/10 ring-1 ring-white/15 backdrop-blur">
                    <img src="{{ asset('assets/images/logoicon.png') }}" alt="{{ config('app.name') }}" class="h-7 w-auto" onerror="this.replaceWith(Object.assign(document.createElement('span'),{textContent:'A',className:'text-lg font-bold'}))">
                </span>
                <span class="text-lg font-semibold tracking-tight">{{ config('app.name', 'Accrual Hub') }}</span>
            </div>

            {{-- headline --}}
            <div class="relative max-w-lg">
                <span data-animate="brand" class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3.5 py-1.5 text-xs font-medium text-white/80 backdrop-blur">
                    <span class="h-1.5 w-1.5 rounded-full bg-brand"></span>
                    منصة SaaS متعددة الشركات
                </span>

                <h2 data-animate="brand" class="mt-6 text-[2.6rem] font-bold leading-[1.15] tracking-tight">
                    إدارة الاشتراكات والفوترة
                    <span class="bg-gradient-to-l from-brand to-white bg-clip-text text-transparent">المحاسبية</span>
                    بذكاء
                </h2>

                <p data-animate="brand" class="mt-5 text-base leading-relaxed text-white/65">
                    نظام محاسبي دقيق يدعم القيد المزدوج والإيرادات المؤجلة، مع تقارير مالية لحظية
                    لكل شركة على حدة ضمن بيئة آمنة ومعزولة.
                </p>

                {{-- floating mini report card --}}
                <div data-animate="brand" class="mt-9 max-w-sm rounded-2xl border border-white/10 bg-white/[0.07] p-5 backdrop-blur-md">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-white/60">ملخص الأداء المالي</span>
                        <span class="rounded-md bg-brand/20 px-2 py-0.5 text-[11px] font-semibold text-brand">+12.4%</span>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-2xl font-bold tracking-tight">٨٤٬٢٠٠</p>
                            <p class="mt-0.5 text-[11px] text-white/50">إجمالي الإيرادات</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold tracking-tight">١٬٣٢٠</p>
                            <p class="mt-0.5 text-[11px] text-white/50">فاتورة نشطة</p>
                        </div>
                    </div>
                    {{-- mini bar chart --}}
                    <div class="mt-5 flex h-12 items-end gap-1.5">
                        @foreach ([45, 60, 38, 72, 55, 88, 67] as $h)
                            <span class="flex-1 rounded-t bg-gradient-to-t from-brand/30 to-brand" style="height: {{ $h }}%"></span>
                        @endforeach
                    </div>
                </div>
            </div>

            <p data-animate="brand" class="relative text-xs text-white/35">© {{ date('Y') }} {{ config('app.name', 'Accrual Hub') }} — جميع الحقوق محفوظة.</p>
        </aside>

        {{-- ================= FORM PANEL (left in RTL) ================= --}}
        <main class="relative flex w-full items-center justify-center overflow-y-auto bg-slate-50 px-6 py-10 lg:w-1/2">
            <div class="pointer-events-none absolute -top-32 left-1/2 h-72 w-72 -translate-x-1/2 rounded-full bg-brand/5 blur-3xl"></div>

            <div data-animate="auth-card" class="relative my-auto w-full max-w-md rounded-3xl border border-slate-200/70 bg-white p-8 shadow-xl shadow-slate-300/30">
                {{-- mobile brand header --}}
                <div class="mb-7 flex flex-col items-center gap-2 text-center lg:hidden">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-brand-navy">
                        <img src="{{ asset('assets/images/logoicon.png') }}" alt="{{ config('app.name') }}" class="h-7 w-auto" onerror="this.replaceWith(Object.assign(document.createElement('span'),{textContent:'A',className:'text-lg font-bold text-white'}))">
                    </span>
                    <span class="text-lg font-semibold text-brand-navy">{{ config('app.name', 'Accrual Hub') }}</span>
                </div>

                <h1 class="text-2xl font-bold tracking-tight text-brand-navy" data-auth-title>
                    {{ $activeForm === 'register' ? 'أنشئ حساب شركتك' : 'تسجيل الدخول' }}
                </h1>
                <p class="mt-1.5 text-sm text-slate-500" data-auth-subtitle>
                    {{ $activeForm === 'register' ? 'ابدأ بإدارة اشتراكاتك وفواتيرك خلال دقائق' : 'مرحباً بك من جديد، سجّل دخولك للمتابعة' }}
                </p>

                {{-- tab switcher --}}
                <div class="relative mt-7 grid grid-cols-2 gap-1 rounded-xl bg-slate-100 p-1 text-sm font-medium">
                    <span data-auth-indicator class="absolute inset-y-1 right-1 w-[calc(50%-0.25rem)] rounded-lg bg-white shadow-sm ring-1 ring-black/5 transition-transform duration-300 ease-out {{ $activeForm === 'register' ? '-translate-x-full' : '' }}"></span>
                    <button type="button" data-auth-tab="login" class="relative z-10 rounded-lg py-2 text-center transition-colors {{ $activeForm === 'login' ? 'text-brand-blue' : 'text-slate-500 hover:text-slate-700' }}">
                        تسجيل الدخول
                    </button>
                    <button type="button" data-auth-tab="register" class="relative z-10 rounded-lg py-2 text-center transition-colors {{ $activeForm === 'register' ? 'text-brand-blue' : 'text-slate-500 hover:text-slate-700' }}">
                        حساب شركة جديد
                    </button>
                </div>

                {{-- LOGIN --}}
                <form method="POST" action="{{ route('login.store') }}" data-auth-panel="login" class="mt-6 space-y-4 {{ $activeForm === 'login' ? '' : 'hidden' }}">
                    @csrf
                    <input type="hidden" name="form" value="login">

                    <div>
                        <x-input-label for="login_email" value="البريد الإلكتروني" />
                        <div class="relative">
                            <x-auth-icon name="mail" />
                            <x-text-input id="login_email" type="email" name="email" value="{{ old('form') === 'login' ? old('email') : '' }}" required autocomplete="email" placeholder="you@company.com" class="ps-11!" />
                        </div>
                        @if ($activeForm === 'login')
                            <x-input-error :messages="$errors->get('email')" />
                        @endif
                    </div>

                    <div>
                        <x-input-label for="login_password" value="كلمة المرور" />
                        <div class="relative">
                            <x-auth-icon name="lock" />
                            <x-text-input id="login_password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" class="px-11!" />
                            <x-password-toggle target="login_password" />
                        </div>
                        @if ($activeForm === 'login')
                            <x-input-error :messages="$errors->get('password')" />
                        @endif
                    </div>

                    <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-500">
                        <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand/40">
                        تذكّرني
                    </label>

                    <x-primary-button>
                        تسجيل الدخول
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m0 0l6 6m-6-6l6-6" /></svg>
                    </x-primary-button>
                </form>

                {{-- REGISTER (company only) --}}
                <form method="POST" action="{{ route('register') }}" data-auth-panel="register" class="mt-6 space-y-5 {{ $activeForm === 'register' ? '' : 'hidden' }}">
                    @csrf
                    <input type="hidden" name="form" value="register">

                    {{-- company section --}}
                    <div class="space-y-3.5">
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-400">
                            <span class="grid h-5 w-5 place-items-center rounded-md bg-brand/10 text-brand">
                                <x-icon name="building" class="h-3 w-3" />
                            </span>
                            بيانات الشركة
                        </div>

                        <div>
                            <x-input-label for="reg_company" value="اسم الشركة" />
                            <div class="relative">
                                <x-auth-icon name="building" />
                                <x-text-input id="reg_company" type="text" name="company_name" value="{{ old('company_name') }}" required placeholder="شركتك" class="ps-11!" />
                            </div>
                            @if ($activeForm === 'register')
                                <x-input-error :messages="$errors->get('company_name')" />
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <x-input-label for="reg_company_email" value="بريد الشركة (اختياري)" />
                                <div class="relative">
                                    <x-auth-icon name="mail" />
                                    <x-text-input id="reg_company_email" type="email" name="company_email" value="{{ old('company_email') }}" placeholder="info@company.com" class="ps-11!" />
                                </div>
                                @if ($activeForm === 'register')
                                    <x-input-error :messages="$errors->get('company_email')" />
                                @endif
                            </div>
                            <div>
                                <x-input-label for="reg_company_phone" value="رقم الهاتف (اختياري)" />
                                <div class="relative">
                                    <x-auth-icon name="phone" />
                                    <x-text-input id="reg_company_phone" type="text" name="company_phone" value="{{ old('company_phone') }}" placeholder="05xxxxxxxx" class="ps-11!" />
                                </div>
                                @if ($activeForm === 'register')
                                    <x-input-error :messages="$errors->get('company_phone')" />
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- manager section --}}
                    <div class="space-y-3.5 border-t border-slate-100 pt-4">
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-400">
                            <span class="grid h-5 w-5 place-items-center rounded-md bg-brand-blue/10 text-brand-blue">
                                <x-icon name="user-cog" class="h-3 w-3" />
                            </span>
                            بيانات المدير
                        </div>

                        <div>
                            <x-input-label for="reg_name" value="الاسم" />
                            <div class="relative">
                                <x-auth-icon name="user" />
                                <x-text-input id="reg_name" type="text" name="name" value="{{ old('name') }}" required placeholder="اسمك الكامل" class="ps-11!" />
                            </div>
                            @if ($activeForm === 'register')
                                <x-input-error :messages="$errors->get('name')" />
                            @endif
                        </div>

                        <div>
                            <x-input-label for="reg_email" value="البريد الإلكتروني" />
                            <div class="relative">
                                <x-auth-icon name="mail" />
                                <x-text-input id="reg_email" type="email" name="email" value="{{ old('form') === 'register' ? old('email') : '' }}" required autocomplete="email" placeholder="you@company.com" class="ps-11!" />
                            </div>
                            @if ($activeForm === 'register')
                                <x-input-error :messages="$errors->get('email')" />
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <x-input-label for="reg_password" value="كلمة المرور" />
                                <div class="relative">
                                    <x-auth-icon name="lock" />
                                    <x-text-input id="reg_password" type="password" name="password" required autocomplete="new-password" placeholder="••••••••" class="px-11!" />
                                    <x-password-toggle target="reg_password" />
                                </div>
                                @if ($activeForm === 'register')
                                    <x-input-error :messages="$errors->get('password')" />
                                @endif
                            </div>
                            <div>
                                <x-input-label for="reg_password_confirmation" value="تأكيد كلمة المرور" />
                                <div class="relative">
                                    <x-auth-icon name="lock" />
                                    <x-text-input id="reg_password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" class="px-11!" />
                                    <x-password-toggle target="reg_password_confirmation" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <x-primary-button>
                        إنشاء حساب الشركة
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m0 0l6 6m-6-6l6-6" /></svg>
                    </x-primary-button>

                    <p class="flex items-center justify-center gap-1.5 text-center text-xs text-slate-400">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        أول مستخدم يصبح مدير الشركة تلقائياً
                    </p>
                </form>
            </div>
        </main>
    </div>
</x-layouts.guest>
