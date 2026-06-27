<!DOCTYPE html>
<html lang="ar" dir="rtl" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Accrual Hub') }} · منصة إدارة الاشتراكات والمحاسبة</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logoicon.png') }}">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [data-landing-nav] { transition: background-color .3s ease, box-shadow .3s ease, border-color .3s ease; }
        [data-landing-nav].nav-scrolled {
            background-color: rgba(255, 255, 255, .82);
            -webkit-backdrop-filter: blur(14px);
            backdrop-filter: blur(14px);
            border-bottom-color: rgb(226 232 240);
            box-shadow: 0 8px 30px -12px rgba(7, 48, 96, .12);
        }
        .tilt-card { transform: perspective(1600px) rotateY(7deg) rotateX(4deg) rotate(-1deg); }
        @media (max-width: 1023px) { .tilt-card { transform: none; } }
        .bg-grid {
            background-image:
                linear-gradient(rgba(7,48,96,.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(7,48,96,.05) 1px, transparent 1px);
            background-size: 36px 36px;
        }
    </style>
</head>
<body data-page="landing" class="overflow-x-hidden bg-white text-slate-800 antialiased">

    {{-- ===================== NAVBAR ===================== --}}
    <header data-landing-nav class="fixed inset-x-0 top-0 z-50 border-b border-transparent">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 sm:px-8">
            {{-- logo --}}
            <a href="#top" class="flex items-center gap-2.5">
                <span class="grid h-12 w-12 place-items-center">
                    <img src="{{ asset('assets/images/logoicon.png') }}" alt="{{ config('app.name') }}" class="h-10 w-auto" onerror="this.replaceWith(Object.assign(document.createElement('span'),{textContent:'A',className:'text-xl font-bold text-brand-navy'}))">
                </span>
                <span class="text-lg font-bold tracking-tight text-brand-navy">{{ config('app.name', 'Accrual Hub') }}</span>
            </a>

            {{-- desktop links --}}
            <div class="hidden items-center gap-8 text-sm font-medium text-slate-600 md:flex">
                <a href="#top" class="transition hover:text-brand-navy">الرئيسية</a>
                <a href="#about" class="transition hover:text-brand-navy">من نحن</a>
                <a href="#features" class="transition hover:text-brand-navy">المميزات</a>
                <a href="#how" class="transition hover:text-brand-navy">كيف تعمل المنصة</a>
                <a href="#accounting" class="transition hover:text-brand-navy">القوة المحاسبية</a>
            </div>

            {{-- desktop actions --}}
            <div class="hidden items-center gap-3 md:flex">
                <a href="{{ route('login') }}" class="rounded-xl px-4 py-2 text-sm font-semibold text-brand-navy transition hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
                    دخول
                </a>
                <a href="{{ route('login', ['form' => 'register']) }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-l from-brand to-brand-blue px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-blue/20 transition hover:scale-[1.03] hover:shadow-brand/30 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
                    ابدأ الآن
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m0 0l6 6m-6-6l6-6" /></svg>
                </a>
            </div>

            {{-- mobile toggle --}}
            <button type="button" data-menu-toggle aria-expanded="false" aria-label="القائمة"
                    class="grid h-10 w-10 place-items-center rounded-xl text-brand-navy transition hover:bg-slate-100 md:hidden">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" /></svg>
            </button>
        </nav>

        {{-- mobile menu --}}
        <div data-mobile-menu class="hidden overflow-hidden border-t border-slate-200 bg-white/95 backdrop-blur md:hidden">
            <div class="space-y-1 px-5 py-4">
                <a href="#top" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100">الرئيسية</a>
                <a href="#about" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100">من نحن</a>
                <a href="#features" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100">المميزات</a>
                <a href="#how" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100">كيف تعمل المنصة</a>
                <a href="#accounting" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100">القوة المحاسبية</a>
                <div class="grid grid-cols-2 gap-3 pt-3">
                    <a href="{{ route('login') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-center text-sm font-semibold text-brand-navy transition hover:bg-slate-50">دخول</a>
                    <a href="{{ route('login', ['form' => 'register']) }}" class="rounded-xl bg-gradient-to-l from-brand to-brand-blue px-4 py-2.5 text-center text-sm font-semibold text-white">ابدأ الآن</a>
                </div>
            </div>
        </div>
    </header>

    <main id="top">
        {{-- ===================== HERO ===================== --}}
        <section class="relative overflow-hidden px-5 pt-28 pb-20 sm:px-8 lg:pt-36 lg:pb-28">
            {{-- decorative background --}}
            <div class="bg-grid pointer-events-none absolute inset-0 [mask-image:radial-gradient(70%_60%_at_70%_0%,#000,transparent)]"></div>
            <div data-animate="orb" class="pointer-events-none absolute -top-24 right-[-6rem] h-96 w-96 rounded-full bg-brand/10 blur-3xl"></div>
            <div class="pointer-events-none absolute bottom-[-8rem] left-[-6rem] h-80 w-80 rounded-full bg-brand-blue/10 blur-3xl"></div>

            <div class="relative mx-auto grid max-w-7xl items-center gap-10 lg:grid-cols-2 lg:gap-14">
                {{-- copy --}}
                <div>
                    <span data-animate="hero-badge" class="inline-flex items-center gap-2 rounded-full border border-brand/20 bg-brand/5 px-4 py-1.5 text-xs font-semibold text-brand-blue">
                        <span class="h-1.5 w-1.5 rounded-full bg-brand"></span>
                        إدارة الاشتراكات... بطريقة ذكية.
                    </span>

                    <h1 data-animate="hero-title" class="mt-6 text-3xl font-bold leading-[1.45] tracking-tight text-brand-navy sm:text-4xl sm:leading-[1.5] md:text-5xl lg:text-[3.4rem]">
                        إدارة اشتراكاتك وفواتيرك
                        <span class="bg-gradient-to-l from-brand to-brand-blue bg-clip-text text-transparent">صارت أبسط</span>
                        مما تتخيّل
                    </h1>

                    <p data-animate="hero-subtitle" class="mt-6 max-w-xl text-lg leading-relaxed text-slate-600">
                        نظام محاسبي دقيق يفوتر ويحاسب لك تلقائيًا، ويعطيك تقارير لحظية واضحة تخليك تاخذ
                        قرارك وانت مطمّن.
                    </p>

                    <div data-animate="reveal" class="mt-9 flex flex-wrap items-center gap-4">
                        <a href="{{ route('login', ['form' => 'register']) }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-l from-brand to-brand-blue px-7 py-3.5 text-sm font-semibold text-white shadow-lg shadow-brand-blue/25 transition hover:scale-[1.03] hover:shadow-brand/30 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
                            ابدأ الآن مجانًا
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m0 0l6 6m-6-6l6-6" /></svg>
                        </a>
                        <a href="#how" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-7 py-3.5 text-sm font-semibold text-brand-navy transition hover:border-brand/40 hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
                            شوف كيف تعمل المنصة
                        </a>
                    </div>

                    <p data-animate="reveal" class="mt-6 flex items-center gap-2 text-sm text-slate-500">
                        <x-icon name="check-double" class="h-4 w-4 text-brand" />
                        بدون بطاقة ائتمان · جاهز خلال دقائق
                    </p>
                </div>

                {{-- dashboard preview mockup --}}
                <div class="relative" data-animate="reveal">
                    <div class="tilt-card relative rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl shadow-brand-navy/15">
                        {{-- window bar --}}
                        <div class="flex items-center gap-1.5 px-1 pb-3">
                            <span class="h-2.5 w-2.5 rounded-full bg-rose-300"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-amber-300"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-300"></span>
                            <span class="ms-3 text-[11px] font-medium text-slate-400">لوحة التحكم</span>
                        </div>
                        {{-- kpi row --}}
                        <div class="grid grid-cols-3 gap-3">
                            @foreach ([['الإيرادات','٨٤٬٢٠٠','brand'],['التحصيل','٧١٬٥٠٠','brand-blue'],['فواتير','١٬٣٢٠','brand-navy']] as [$lbl,$val,$c])
                                <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                                    <div class="text-[10px] text-slate-400">{{ $lbl }}</div>
                                    <div class="mt-1 text-base font-bold text-{{ $c }}">{{ $val }}</div>
                                </div>
                            @endforeach
                        </div>
                        {{-- chart --}}
                        <div class="mt-3 rounded-xl border border-slate-100 bg-white p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-500">اتجاه الإيرادات</span>
                                <span class="rounded-md bg-brand/10 px-2 py-0.5 text-[10px] font-semibold text-brand">+12.4%</span>
                            </div>
                            <div class="mt-4 flex h-24 items-end gap-2">
                                @foreach ([40,58,46,70,55,82,64,90] as $h)
                                    <span class="flex-1 rounded-t bg-gradient-to-t from-brand/25 to-brand" style="height: {{ $h }}%"></span>
                                @endforeach
                            </div>
                        </div>
                        {{-- mini rows --}}
                        <div class="mt-3 space-y-2">
                            @foreach (['فاتورة #١٠٢٤','فاتورة #١٠٢٣','فاتورة #١٠٢٢'] as $i => $row)
                                <div class="flex items-center justify-between rounded-lg border border-slate-100 px-3 py-2">
                                    <span class="text-xs font-medium text-slate-600">{{ $row }}</span>
                                    <span class="rounded-full {{ $i === 1 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }} px-2 py-0.5 text-[10px] font-semibold">{{ $i === 1 ? 'بانتظار' : 'مدفوعة' }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- floating stat card --}}
                    <div class="absolute -bottom-6 -right-4 hidden rounded-2xl border border-slate-100 bg-white p-4 shadow-xl shadow-brand-navy/10 sm:block">
                        <div class="flex items-center gap-3">
                            <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand/10 text-brand">
                                <x-icon name="revenue" class="h-5 w-5" />
                            </span>
                            <div>
                                <div class="text-[10px] text-slate-400">إيراد معترف به</div>
                                <div class="text-sm font-bold text-brand-navy">٦٢٬٣٠٠ ر.س</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===================== ABOUT ===================== --}}
        <section id="about" class="px-5 py-20 sm:px-8 lg:py-28">
            <div class="mx-auto grid max-w-7xl items-center gap-10 lg:grid-cols-2 lg:gap-14">
                {{-- visual --}}
                <div data-animate="reveal" class="relative order-last lg:order-first">
                    <div class="relative mx-auto max-w-md">
                        <div class="absolute -inset-4 rounded-3xl bg-gradient-to-br from-brand/10 to-brand-blue/10 blur-2xl"></div>
                        <div class="relative rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-brand-navy/10">
                            <div class="rounded-2xl bg-brand-navy p-6 text-white">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold">قائمة المركز المالي</span>
                                    <x-icon name="balance" class="h-5 w-5 text-brand" />
                                </div>
                                <div class="mt-5 space-y-3 text-sm">
                                    <div class="flex items-center justify-between border-b border-white/10 pb-2"><span class="text-white/70">النقدية</span><span class="font-semibold">٤٨٬٠٠٠</span></div>
                                    <div class="flex items-center justify-between border-b border-white/10 pb-2"><span class="text-white/70">الذمم المدينة</span><span class="font-semibold">٢٣٬٢٠٠</span></div>
                                    <div class="flex items-center justify-between"><span class="text-white/70">الإيراد المؤجل</span><span class="font-semibold text-brand">١٢٬٨٠٠</span></div>
                                </div>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-3">
                                <div class="rounded-xl bg-slate-50 p-3 text-center">
                                    <div class="text-lg font-bold text-brand-navy">٩٩.٩٪</div>
                                    <div class="text-[11px] text-slate-500">دقة القيود</div>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-3 text-center">
                                    <div class="text-lg font-bold text-brand-navy">لحظي</div>
                                    <div class="text-[11px] text-slate-500">تحديث التقارير</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- copy --}}
                <div data-animate="reveal">
                    <span class="text-sm font-semibold text-brand">من نحن</span>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-brand-navy sm:text-4xl">
                        شريكك الذكي لإدارة الاشتراكات والفوترة
                    </h2>
                    <p class="mt-5 text-lg leading-relaxed text-slate-600">
                        إحنا وفرنا لك منصة متكاملة تساعدك تدير اشتراكات عملائك، الفواتير، والمدفوعات بكل سهولة
                        واحترافية. هدفنا نبسط العمليات اليومية، نوفر عليك الوقت، ونمنحك تقارير مالية دقيقة تساعدك
                        تتخذ قراراتك بثقة. كل هذا داخل نظام آمن، سريع، وقابل للتوسع مهما كبر حجم أعمالك.
                    </p>

                    <div data-animate="stagger" class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        @foreach ([
                            ['shield','أمان وعزل','بيانات كل شركة محفوظة ومعزولة'],
                            ['check-double','دقة محاسبية','قيود صحيحة ومتوازنة تلقائيًا'],
                            ['activity','بساطة','واجهة سهلة بدون تعقيد'],
                        ] as [$icon,$title,$desc])
                            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                                <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand/10 text-brand">
                                    <x-icon name="{{ $icon }}" class="h-5 w-5" />
                                </span>
                                <h3 class="mt-3 text-sm font-bold text-brand-navy">{{ $title }}</h3>
                                <p class="mt-1 text-xs leading-relaxed text-slate-500">{{ $desc }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- ===================== FEATURES (Bento) ===================== --}}
        <section id="features" class="bg-slate-50 px-5 py-20 sm:px-8 lg:py-28">
            <div class="mx-auto max-w-7xl">
                <div data-animate="reveal" class="mx-auto max-w-2xl text-center">
                    <span class="text-sm font-semibold text-brand">المميزات</span>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-brand-navy sm:text-4xl">كل اللي تحتاجه لإدارة أعمالك</h2>
                    <p class="mt-4 text-lg text-slate-600">أدوات قوية بشكل بسيط، تشتغل لك من ورا الكواليس.</p>
                </div>

                <div data-animate="stagger" class="mt-14 grid grid-cols-1 gap-5 md:grid-cols-3">
                    {{-- big card --}}
                    <article class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition hover:shadow-xl hover:shadow-brand-navy/10 md:col-span-2 md:row-span-2">
                        <div class="pointer-events-none absolute -left-16 -top-16 h-56 w-56 rounded-full bg-brand/5 blur-2xl"></div>
                        <span class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-brand to-brand-blue text-white shadow-lg shadow-brand-blue/20">
                            <x-icon name="invoices" class="h-6 w-6" />
                        </span>
                        <h3 class="mt-5 text-xl font-bold text-brand-navy">فوترة تلقائية بدون عناء</h3>
                        <p class="mt-2 max-w-md text-sm leading-relaxed text-slate-600">
                            النظام يصدّر فواتير اشتراكات عملائك شهريًا بشكل تلقائي، ويتابع التحصيل عنك — انت بس تابع.
                        </p>
                        {{-- mini invoice preview --}}
                        <div class="mt-6 rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-slate-600">فاتورة #١٠٢٤</span>
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 font-semibold text-emerald-700">مدفوعة</span>
                            </div>
                            <div class="mt-3 flex items-end justify-between">
                                <span class="text-xs text-slate-400">الخطة الذهبية · شهري</span>
                                <span class="text-lg font-bold text-brand-navy">٥٠٠ ر.س</span>
                            </div>
                            <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-slate-200">
                                <div class="h-full w-full rounded-full bg-gradient-to-l from-brand to-brand-blue"></div>
                            </div>
                        </div>
                    </article>

                    {{-- subscriptions --}}
                    <article class="group rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition hover:shadow-xl hover:shadow-brand-navy/10">
                        <span class="grid h-11 w-11 place-items-center rounded-2xl bg-brand/10 text-brand">
                            <x-icon name="subscriptions" class="h-5 w-5" />
                        </span>
                        <h3 class="mt-4 text-base font-bold text-brand-navy">إدارة الاشتراكات والعملاء</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-slate-600">اربط عملاءك بالخطط وتابع اشتراكاتهم من لوحة وحدة.</p>
                    </article>

                    {{-- reports --}}
                    <article class="group rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition hover:shadow-xl hover:shadow-brand-navy/10">
                        <span class="grid h-11 w-11 place-items-center rounded-2xl bg-brand-blue/10 text-brand-blue">
                            <x-icon name="chart-pie" class="h-5 w-5" />
                        </span>
                        <h3 class="mt-4 text-base font-bold text-brand-navy">تقارير مالية لحظية</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-slate-600">قائمة دخل وميزانية جاهزة بأي وقت تبيها.</p>
                        <div class="mt-4 flex h-10 items-end gap-1.5">
                            @foreach ([50,75,40,90,65] as $h)
                                <span class="flex-1 rounded-t bg-brand-blue/30" style="height: {{ $h }}%"></span>
                            @endforeach
                        </div>
                    </article>

                    {{-- security (spans 2 on md) --}}
                    <article class="group rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition hover:shadow-xl hover:shadow-brand-navy/10 md:col-span-2">
                        <div class="flex items-start gap-4">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-brand-navy/10 text-brand-navy">
                                <x-icon name="shield" class="h-5 w-5" />
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-brand-navy">عزل وأمان كامل للبيانات</h3>
                                <p class="mt-1.5 text-sm leading-relaxed text-slate-600">بيانات كل شركة معزولة تمامًا عن غيرها، محفوظة في بيئة آمنة لا يوصل لها إلا أنت وفريقك.</p>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        {{-- ===================== HOW IT WORKS ===================== --}}
        <section id="how" class="bg-white px-5 py-16 sm:px-8 lg:flex lg:h-[90vh] lg:min-h-[600px] lg:max-h-[920px] lg:items-center lg:py-0">
            <div class="mx-auto grid w-full max-w-7xl items-center gap-12 lg:grid-cols-2 lg:gap-16">
                {{-- RIGHT: heading + description --}}
                <div data-animate="reveal">
                    <span class="text-sm font-semibold text-brand">كيف تعمل المنصة</span>
                    <h2 class="mt-3 text-3xl font-bold leading-tight tracking-tight text-brand-navy sm:text-4xl lg:text-[2.7rem]">
                        من التسجيل إلى التقارير... خطوة بخطوة
                    </h2>
                    <p class="mt-5 text-lg leading-relaxed text-slate-600">
                        رحلة واضحة وسلسة، كل خطوة فيها يشتغل النظام عنك أكثر — من إنشاء حسابك
                        إلى قيودك المحاسبية وتقاريرك المالية.
                    </p>

                    <div class="mt-7 rounded-2xl border border-slate-100 bg-slate-50/70 p-5">
                        <div class="flex items-center gap-3">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-brand/10 text-brand">
                                <x-icon name="check-double" class="h-5 w-5" />
                            </span>
                            <p class="text-sm leading-relaxed text-slate-600">
                                ما تحتاج خبرة محاسبية — النظام يسوّي القيود والاعتراف بالإيراد عنك تلقائيًا.
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('login', ['form' => 'register']) }}" class="mt-7 inline-flex items-center gap-2 rounded-xl bg-gradient-to-l from-brand to-brand-blue px-7 py-3.5 text-sm font-semibold text-white shadow-lg shadow-brand-blue/25 transition hover:scale-[1.03] hover:shadow-brand/30 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
                        ابدأ رحلتك الآن
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m0 0l6 6m-6-6l6-6" /></svg>
                    </a>
                </div>

                {{-- LEFT: compact steps timeline --}}
                <div class="relative">
                    <div class="absolute right-5 top-3 bottom-3 w-0.5 -translate-x-1/2 rounded-full bg-slate-200"></div>
                    <div data-steps-line class="absolute right-5 top-3 bottom-3 w-0.5 -translate-x-1/2 origin-top rounded-full bg-gradient-to-b from-brand to-brand-blue"></div>

                    <div data-animate="stagger" class="space-y-3">
                        @foreach ([
                            ['١','building','سجّل شركتك','أنشئ حسابك خلال دقيقة بمساحة معزولة بالكامل.'],
                            ['٢','plans','جهّز خطط الاشتراك','حدد خططك وأسعارها — شهري أو سنوي.'],
                            ['٣','customers','أضف عملاءك','سجّل عملاءك بدون حد على العدد.'],
                            ['٤','subscriptions','فعّل الاشتراكات','اربط كل عميل بخطته وحدد تاريخ البداية.'],
                            ['٥','invoices','الفوترة والتحصيل','فواتير شهرية تلقائية وتسجيل مدفوعات.'],
                            ['٦','balance','المحاسبة والتقارير','قيود مزدوجة وتقارير مالية لحظية.'],
                        ] as [$num,$icon,$title,$desc])
                            <div class="relative flex items-start gap-3.5">
                                {{-- node --}}
                                <div class="relative z-10 flex w-10 shrink-0 justify-center">
                                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-brand to-brand-blue text-sm font-bold text-white shadow-md shadow-brand-blue/25 ring-4 ring-white">
                                        {{ $num }}
                                    </span>
                                </div>

                                {{-- card --}}
                                <div class="group flex-1 rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm transition hover:border-brand/30 hover:shadow-md hover:shadow-brand-navy/10">
                                    <div class="flex items-center gap-2 text-brand-navy">
                                        <x-icon name="{{ $icon }}" class="h-4 w-4 shrink-0 text-brand" />
                                        <h3 class="text-sm font-bold leading-tight">{{ $title }}</h3>
                                    </div>
                                    <p class="mt-1 text-xs leading-relaxed text-slate-500">{{ $desc }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- ===================== ACCOUNTING POWER ===================== --}}
        <section id="accounting" class="relative overflow-hidden bg-brand-navy px-5 py-20 text-white sm:px-8 lg:py-28">
            <div class="pointer-events-none absolute -top-24 left-1/4 h-96 w-96 rounded-full bg-brand/20 blur-3xl"></div>
            <div class="pointer-events-none absolute bottom-[-8rem] right-[-4rem] h-80 w-80 rounded-full bg-brand-blue/30 blur-3xl"></div>

            <div class="relative mx-auto grid max-w-7xl items-center gap-10 lg:grid-cols-2 lg:gap-14">
                {{-- copy --}}
                <div data-animate="reveal">
                    <span class="text-sm font-semibold text-brand">القوة المحاسبية</span>
                    <h2 class="mt-3 text-3xl font-bold leading-tight tracking-tight sm:text-4xl">
                        محاسبة صحيحة... من غير ما تتعب
                    </h2>
                    <p class="mt-5 text-lg leading-relaxed text-white/70">
                        كل حركة مالية تتسجّل بقيد مزدوج متوازن، والإيرادات المؤجلة تتحوّل لإيراد فعلي
                        في وقتها الصح — كل هذا تلقائيًا بدون أي تدخّل منك.
                    </p>

                    <ul data-animate="stagger" class="mt-8 space-y-4">
                        @foreach ([
                            ['journal','قيود تلقائية متوازنة','مدين ودائن مضبوط لكل عملية'],
                            ['revenue','إيرادات مؤجلة ذكية','اعتراف بالإيراد في وقته الصحيح'],
                            ['balance','تقارير دقيقة','قائمة دخل وميزانية موثوقة'],
                        ] as [$icon,$title,$desc])
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white/10 text-brand ring-1 ring-white/15">
                                    <x-icon name="{{ $icon }}" class="h-5 w-5" />
                                </span>
                                <div>
                                    <div class="text-sm font-bold">{{ $title }}</div>
                                    <div class="text-sm text-white/60">{{ $desc }}</div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- live journal entry card --}}
                <div data-animate="reveal" class="relative">
                    <div class="rounded-3xl border border-white/10 bg-white/[0.06] p-6 backdrop-blur-md">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-white/80">قيد آلي · فاتورة جديدة</span>
                            <span class="rounded-md bg-brand/20 px-2 py-0.5 text-[11px] font-semibold text-brand">متوازن</span>
                        </div>

                        <div class="mt-5 overflow-hidden rounded-2xl border border-white/10">
                            <div class="grid grid-cols-[1fr_auto_auto] gap-2 bg-white/5 px-4 py-2.5 text-[11px] font-semibold text-white/50">
                                <span>الحساب</span><span class="w-16 text-center">مدين</span><span class="w-16 text-center">دائن</span>
                            </div>
                            <div data-ledger-row class="grid grid-cols-[1fr_auto_auto] items-center gap-2 px-4 py-3 text-sm">
                                <span class="text-white/85">الذمم المدينة</span>
                                <span class="w-16 text-center font-semibold text-brand">٥٠٠</span>
                                <span class="w-16 text-center text-white/30">—</span>
                            </div>
                            <div data-ledger-row class="grid grid-cols-[1fr_auto_auto] items-center gap-2 border-t border-white/10 px-4 py-3 text-sm">
                                <span class="text-white/85">الإيراد المؤجل</span>
                                <span class="w-16 text-center text-white/30">—</span>
                                <span class="w-16 text-center font-semibold text-brand">٥٠٠</span>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between rounded-xl bg-white/5 px-4 py-3 text-sm">
                            <span class="text-white/60">الإجمالي</span>
                            <div class="flex items-center gap-6">
                                <span class="font-bold">٥٠٠</span>
                                <span class="font-bold">٥٠٠</span>
                            </div>
                        </div>

                        <p class="mt-4 flex items-center gap-2 text-xs text-white/50">
                            <x-icon name="check-double" class="h-4 w-4 text-brand" />
                            تم تسجيل القيد تلقائيًا لحظة إصدار الفاتورة
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===================== FINAL CTA ===================== --}}
        <section class="px-5 py-20 sm:px-8 lg:py-28">
            <div data-animate="reveal" class="relative mx-auto max-w-5xl overflow-hidden rounded-[2rem] bg-gradient-to-l from-brand-navy via-brand-blue to-brand-navy px-6 py-16 text-center text-white shadow-2xl shadow-brand-navy/30 sm:px-12">
                <div class="pointer-events-none absolute -top-16 right-1/4 h-64 w-64 rounded-full bg-brand/25 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-20 left-1/4 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>

                <div class="relative">
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">جاهز تبدأ تدير أعمالك صح؟</h2>
                    <p class="mx-auto mt-4 max-w-xl text-lg text-white/75">سجّل شركتك اليوم وخلّ المنصة تشيل عنك هم الفواتير والمحاسبة.</p>
                    <a href="{{ route('login', ['form' => 'register']) }}" class="mt-9 inline-flex items-center gap-2 rounded-xl bg-white px-8 py-4 text-sm font-bold text-brand-navy shadow-lg transition hover:scale-[1.03] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                        ابدأ الآن مجانًا
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m0 0l6 6m-6-6l6-6" /></svg>
                    </a>
                </div>
            </div>
        </section>
    </main>

    {{-- ===================== FOOTER ===================== --}}
    <footer class="border-t border-slate-200 bg-white px-5 py-14 sm:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="grid grid-cols-2 gap-10 md:grid-cols-4">
                {{-- brand --}}
                <div class="col-span-2 md:col-span-1">
                    <a href="#top" class="flex items-center gap-2.5">
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-brand-blue to-brand-navy">
                            <img src="{{ asset('assets/images/logoicon.png') }}" alt="{{ config('app.name') }}" class="h-6 w-auto" onerror="this.replaceWith(Object.assign(document.createElement('span'),{textContent:'A',className:'text-lg font-bold text-white'}))">
                        </span>
                        <span class="text-lg font-bold text-brand-navy">{{ config('app.name', 'Accrual Hub') }}</span>
                    </a>
                    <p class="mt-4 max-w-xs text-sm leading-relaxed text-slate-500">
                        منصتك السعودية لإدارة الاشتراكات والفوترة والمحاسبة في مكان واحد.
                    </p>
                </div>

                {{-- links --}}
                <div>
                    <h4 class="text-sm font-bold text-brand-navy">المنتج</h4>
                    <ul class="mt-4 space-y-2.5 text-sm text-slate-500">
                        <li><a href="#features" class="transition hover:text-brand">المميزات</a></li>
                        <li><a href="#how" class="transition hover:text-brand">كيف تعمل المنصة</a></li>
                        <li><a href="#accounting" class="transition hover:text-brand">القوة المحاسبية</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-brand-navy">الشركة</h4>
                    <ul class="mt-4 space-y-2.5 text-sm text-slate-500">
                        <li><a href="#about" class="transition hover:text-brand">من نحن</a></li>
                        <li><a href="{{ route('login') }}" class="transition hover:text-brand">دخول</a></li>
                        <li><a href="{{ route('login', ['form' => 'register']) }}" class="transition hover:text-brand">إنشاء حساب</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-brand-navy">قانوني</h4>
                    <ul class="mt-4 space-y-2.5 text-sm text-slate-500">
                        <li><a href="#" class="transition hover:text-brand">الشروط والأحكام</a></li>
                        <li><a href="#" class="transition hover:text-brand">سياسة الخصوصية</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 flex flex-col items-center justify-between gap-4 border-t border-slate-100 pt-6 sm:flex-row">
                <p class="text-xs text-slate-400">© {{ date('Y') }} {{ config('app.name', 'Accrual Hub') }} — كل الحقوق محفوظة.</p>
                <div class="flex items-center gap-3">
                    @foreach (['M22 12a10 10 0 10-11.5 9.9v-7H8v-2.9h2.5V9.5c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6v1.9H17l-.4 2.9h-2.1v7A10 10 0 0022 12z','M18.9 2H21l-6.6 7.5L22 22h-6.2l-4.8-6.3L5.5 22H3.4l7-8L2 2h6.3l4.3 5.8L18.9 2z','M16 8a6 6 0 016 6v6h-4v-6a2 2 0 00-4 0v6h-4v-6a6 6 0 016-6zM6 9H2v11h4V9zM4 2a2 2 0 110 4 2 2 0 010-4z'] as $path)
                        <a href="#" class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-brand/40 hover:text-brand">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="{{ $path }}" /></svg>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
