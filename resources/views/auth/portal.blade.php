@php
    $activeForm = old('form', request('form') === 'register' ? 'register' : 'login');
@endphp

<x-layouts.guest>
    <div class="flex h-screen w-full overflow-hidden">
        {{-- ================= BRAND PANEL (right in RTL) ================= --}}
        <aside class="relative hidden w-1/2 flex-col justify-between overflow-hidden p-10 text-white lg:flex xl:p-14"
               style="background-color: #073060;">

            {{-- logo lockup (enlarged) --}}
            <div data-animate="brand" class="relative flex items-center gap-4">
                <span class="grid h-16 w-16 place-items-center rounded-2xl bg-white/12 ring-1 ring-white/20 backdrop-blur">
                    <img src="{{ asset('assets/images/whitelogo.png') }}" alt="{{ config('app.name') }}" class="h-11 w-auto" onerror="this.replaceWith(Object.assign(document.createElement('span'),{textContent:'A',className:'text-2xl font-bold'}))">
                </span>
                <span class="text-2xl font-bold tracking-tight">{{ config('app.name', 'Accrual Hub') }}</span>
            </div>

            {{-- headline --}}
            <div class="relative max-w-xl">
                <span data-animate="brand" class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/8 px-4 py-1.5 text-xs font-medium text-white/85 backdrop-blur">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#3dd7c7]"></span>
                    كل اشتراكاتك، فواتيرك، ومدفوعاتك في منصة وحدة
                </span>

                <h2 data-animate="brand" class="mt-6 text-[2.5rem] font-bold leading-[1.18] tracking-tight">
                    خل إدارة اشتراكاتك
                    <span class="bg-gradient-to-l from-[#3dd7c7] to-white bg-clip-text text-transparent">أسهل وأكثر احترافية</span>
                </h2>

                <p data-animate="brand" class="mt-5 text-base leading-relaxed text-white/75">
                    نظام متكامل يساعد الشركات على إدارة العملاء، الاشتراكات، الفواتير، والمدفوعات بكل سهولة ودقة.
                    صممنا المنصة عشان توفر عليك الوقت، تقلل الأخطاء، وتعطيك تقارير مالية واضحة تساعدك تتخذ قراراتك بثقة.
                </p>

                {{-- feature list (replaces the old fake chart card) --}}
                <ul data-animate="brand" class="mt-8 space-y-3.5">
                    @foreach ([
                        'إدارة ذكية للاشتراكات والعملاء',
                        'فوترة تلقائية',
                        'تقارير مالية دقيقة',
                        'أمان وعزل كامل للبيانات',
                    ] as $feature)
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 grid h-6 w-6 shrink-0 place-items-center rounded-lg bg-[#1aa399]/20 text-[#7ff0e3] ring-1 ring-[#1aa399]/40">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </span>
                            <span class="text-sm leading-relaxed text-white/80">{{ $feature }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <p data-animate="brand" class="relative text-xs text-white/40">© {{ date('Y') }} {{ config('app.name', 'Accrual Hub') }} — كل الحقوق محفوظة.</p>
        </aside>

        {{-- ================= FORM PANEL (left in RTL) ================= --}}
        <main class="relative flex w-full items-center justify-center overflow-y-auto bg-white px-6 py-8 sm:px-10 lg:w-1/2 lg:px-16 xl:px-24">
            <div data-animate="auth-card" class="relative w-full max-w-lg">
                {{-- mobile brand header (enlarged logo) --}}
                <div class="mb-6 flex flex-col items-center gap-2.5 text-center lg:hidden">
                    <span class="grid h-16 w-16 place-items-center rounded-2xl bg-gradient-to-br from-brand-blue to-brand-navy shadow-lg shadow-brand-navy/25">
                        <img src="{{ asset('assets/images/logoicon.png') }}" alt="{{ config('app.name') }}" class="h-10 w-auto" onerror="this.replaceWith(Object.assign(document.createElement('span'),{textContent:'A',className:'text-2xl font-bold text-white'}))">
                    </span>
                    <span class="text-lg font-bold text-brand-navy">{{ config('app.name', 'Accrual Hub') }}</span>
                </div>

                <h1 class="text-2xl font-bold tracking-tight text-brand-navy sm:text-3xl" data-auth-title>
                    {{ $activeForm === 'register' ? 'سجّل شركتك بثواني' : 'حيّاك الله من جديد' }}
                </h1>
                <p class="mt-2 text-sm text-slate-500 sm:text-base" data-auth-subtitle>
                    {{ $activeForm === 'register' ? 'أنشئ حساب شركتك وابدأ تدير اشتراكاتك على طول.' : 'سجّل دخولك وكمّل من وين وقفت.' }}
                </p>

                {{-- tab switcher --}}
                <div class="relative mt-6 grid grid-cols-2 gap-1 rounded-xl bg-slate-100 p-1 text-sm font-semibold">
                    <span data-auth-indicator class="absolute inset-y-1 right-1 w-[calc(50%-0.25rem)] rounded-lg bg-white shadow-sm ring-1 ring-black/5 transition-transform duration-300 ease-out {{ $activeForm === 'register' ? '-translate-x-full' : '' }}"></span>
                    <button type="button" data-auth-tab="login" class="relative z-10 rounded-lg py-2 text-center transition-colors {{ $activeForm === 'login' ? 'text-brand-blue' : 'text-slate-500 hover:text-slate-700' }}">
                        دخول
                    </button>
                    <button type="button" data-auth-tab="register" class="relative z-10 rounded-lg py-2 text-center transition-colors {{ $activeForm === 'register' ? 'text-brand-blue' : 'text-slate-500 hover:text-slate-700' }}">
                        حساب جديد
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

                    <label class="flex w-fit cursor-pointer items-center gap-2 text-sm text-slate-500">
                        <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand/40">
                        خلّيني مسجّل
                    </label>

                    <x-primary-button>
                        دخول
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
                                <x-input-label for="reg_company_phone" value="رقم الجوال (اختياري)" />
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

                        <div class="grid grid-cols-2 gap-3">
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
                        أنشئ حساب الشركة
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m0 0l6 6m-6-6l6-6" /></svg>
                    </x-primary-button>

                </form>
            </div>
        </main>
    </div>
</x-layouts.guest>
