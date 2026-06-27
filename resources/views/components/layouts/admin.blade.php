@props(['title' => null])

@php
    $user = auth()->user();

    $groups = [
        [
            'label' => 'عام',
            'items' => [
                ['label' => 'لوحة التحكم', 'route' => 'admin.dashboard', 'icon' => 'home'],
            ],
        ],
        [
            'label' => 'إدارة الشركات',
            'items' => [
                ['label' => 'الشركات', 'route' => 'admin.companies', 'icon' => 'buildings'],
                ['label' => 'مستخدمو الشركات', 'route' => 'admin.company-users', 'icon' => 'users'],
                ['label' => 'العملاء', 'route' => 'admin.customers', 'icon' => 'customers'],
            ],
        ],
        [
            'label' => 'الاشتراكات والفوترة',
            'items' => [
                ['label' => 'الخطط الاشتراكية', 'route' => 'admin.plans', 'icon' => 'plans'],
                ['label' => 'الاشتراكات', 'route' => 'admin.subscriptions', 'icon' => 'subscriptions'],
                ['label' => 'الفواتير', 'route' => 'admin.invoices', 'icon' => 'invoices'],
                ['label' => 'ملخص مالي عام', 'route' => 'admin.financial-summary', 'icon' => 'chart-pie'],
            ],
        ],
        [
            'label' => 'النظام',
            'items' => [
                ['label' => 'المستخدمون والصلاحيات', 'route' => 'admin.users', 'icon' => 'shield'],
                ['label' => 'إعدادات النظام', 'route' => 'admin.settings', 'icon' => 'settings'],
                ['label' => 'سجل النظام', 'route' => 'admin.system-log', 'icon' => 'activity'],
                ['label' => 'التنبيهات', 'route' => 'admin.alerts', 'icon' => 'bell'],
            ],
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="ar" dir="rtl" class="group/shell h-full" data-collapsed="false" data-mobile-open="false">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name', 'Accrual Hub') }} — الإدارة</title>

    <script>
        (function () {
            try {
                var t = localStorage.getItem('theme');
                if (t === 'dark' || (!t && matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
                if (localStorage.getItem('sidebar-collapsed') === 'true') {
                    document.documentElement.dataset.collapsed = 'true';
                }
            } catch (e) {}
        })();
    </script>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-100 text-slate-800 antialiased dark:bg-slate-950 dark:text-slate-200">
    <div class="flex h-screen overflow-hidden">

        {{-- ===================== SIDEBAR (right in RTL) ===================== --}}
        <aside id="sidebar"
               class="fixed inset-y-0 right-0 z-50 flex w-72 shrink-0 translate-x-full flex-col border-l border-slate-200 bg-white transition-transform duration-300 ease-in-out group-data-[mobile-open=true]/shell:translate-x-0 lg:relative lg:z-30 lg:translate-x-0 lg:transition-[width] lg:group-data-[collapsed=true]/shell:w-[78px] dark:border-white/10 dark:bg-slate-900">

            {{-- brand --}}
            <div class="relative flex h-16 items-center gap-3 overflow-hidden border-b border-slate-200 px-5 dark:border-white/10">
                <div class="pointer-events-none absolute inset-0 opacity-[0.07]"
                     style="background-image: radial-gradient(currentColor 1px, transparent 1px); background-size: 16px 16px;"></div>
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-brand-navy to-brand-blue text-base font-bold text-white shadow-lg shadow-brand-blue/25">
                    <img src="{{ asset('assets/images/logoicon.png') }}" alt="" class="h-6 w-auto" onerror="this.replaceWith(Object.assign(document.createElement('span'),{textContent:'A'}))">
                </span>
                <span class="flex min-w-0 flex-col lg:group-data-[collapsed=true]/shell:hidden">
                    <span class="truncate text-base font-bold leading-tight tracking-tight text-brand-navy dark:text-white">
                        {{ config('app.name', 'Accrual Hub') }}
                    </span>
                    <span class="text-[11px] font-medium leading-tight text-brand">لوحة الإدارة</span>
                </span>
            </div>

            {{-- nav --}}
            <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5 [scrollbar-width:thin]">
                @foreach ($groups as $group)
                    <div data-nav-item>
                        <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400 lg:group-data-[collapsed=true]/shell:hidden dark:text-slate-500">
                            {{ $group['label'] }}
                        </p>
                        <ul class="space-y-1">
                            @foreach ($group['items'] as $item)
                                @php $active = request()->routeIs($item['route']); @endphp
                                <li>
                                    <a href="{{ route($item['route']) }}" title="{{ $item['label'] }}"
                                       class="group/link relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ $active ? 'bg-gradient-to-l from-brand/15 to-brand/5 text-brand-navy dark:text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-brand-navy dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white' }}">
                                        @if ($active)
                                            <span class="absolute inset-y-1.5 right-0 w-1 rounded-l-full bg-brand"></span>
                                        @endif
                                        <span class="grid h-6 w-6 shrink-0 place-items-center {{ $active ? 'text-brand' : 'text-slate-400 group-hover/link:text-brand' }}">
                                            <x-icon :name="$item['icon']" />
                                        </span>
                                        <span class="lg:group-data-[collapsed=true]/shell:hidden">{{ $item['label'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </nav>

            {{-- footer status card --}}
            <div class="border-t border-slate-200 p-3 dark:border-white/10">
                <div class="flex items-center gap-3 rounded-xl bg-gradient-to-l from-brand-navy to-brand-blue p-3 text-white">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-white/15">
                        <x-icon name="shield" class="h-5 w-5" />
                    </span>
                    <div class="lg:group-data-[collapsed=true]/shell:hidden">
                        <p class="text-xs font-semibold">صلاحيات كاملة</p>
                        <p class="text-[11px] text-white/70">حساب مدير النظام</p>
                    </div>
                </div>
            </div>
        </aside>

        {{-- mobile sidebar overlay --}}
        <div data-sidebar-overlay
             class="fixed inset-0 z-40 bg-slate-900/50 opacity-0 backdrop-blur-sm transition-opacity duration-300 pointer-events-none group-data-[mobile-open=true]/shell:pointer-events-auto group-data-[mobile-open=true]/shell:opacity-100 lg:hidden"></div>

        {{-- ===================== MAIN ===================== --}}
        <div class="flex flex-1 flex-col overflow-hidden">

            {{-- header --}}
            <header class="flex h-16 shrink-0 items-center justify-between gap-4 border-b border-slate-200 bg-white/80 px-4 backdrop-blur-md sm:px-6 dark:border-white/10 dark:bg-slate-900/80">
                <div class="flex items-center gap-3">
                    <button type="button" data-collapse-toggle
                            class="grid h-10 w-10 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-brand-navy dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white">
                        <x-icon name="panel" />
                    </button>
                </div>

                <div class="flex items-center gap-1 sm:gap-2">
                    {{-- dark mode --}}
                    <button type="button" data-theme-toggle
                            class="grid h-10 w-10 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-brand-navy dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white">
                        <x-icon name="moon" class="h-5 w-5 dark:hidden" />
                        <x-icon name="sun" class="hidden h-5 w-5 dark:block" />
                    </button>

                    {{-- notifications --}}
                    <div class="relative" data-dropdown>
                        <button type="button" data-dropdown-trigger
                                class="relative grid h-10 w-10 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-brand-navy dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white">
                            <x-icon name="bell" />
                            <span class="absolute right-2.5 top-2.5 h-2 w-2 rounded-full bg-brand ring-2 ring-white dark:ring-slate-900"></span>
                        </button>
                        <div data-dropdown-menu class="absolute left-0 mt-2 hidden w-80 origin-top overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-300/40 dark:border-white/10 dark:bg-slate-800 dark:shadow-black/40">
                            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-white/10">
                                <span class="text-sm font-semibold text-brand-navy dark:text-white">إشعارات النظام</span>
                                <span class="rounded-full bg-brand/10 px-2 py-0.5 text-[11px] font-semibold text-brand">3 جديدة</span>
                            </div>
                            <ul class="max-h-72 divide-y divide-slate-100 overflow-y-auto dark:divide-white/5">
                                @foreach ([['buildings','تسجيل شركة جديدة في المنصة','منذ 10 دقائق'],['subscriptions','تجديد اشتراك شركة','منذ ساعتين'],['shield','محاولة دخول غير معتادة','أمس']] as $n)
                                    <li class="flex gap-3 px-4 py-3 transition hover:bg-slate-50 dark:hover:bg-white/5">
                                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-brand/10 text-brand">
                                            <x-icon :name="$n[0]" class="h-5 w-5" />
                                        </span>
                                        <div>
                                            <p class="text-sm text-slate-700 dark:text-slate-200">{{ $n[1] }}</p>
                                            <p class="mt-0.5 text-[11px] text-slate-400">{{ $n[2] }}</p>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            <a href="{{ route('admin.alerts') }}" class="block border-t border-slate-100 px-4 py-2.5 text-center text-sm font-medium text-brand hover:bg-slate-50 dark:border-white/10 dark:hover:bg-white/5">عرض كل التنبيهات</a>
                        </div>
                    </div>

                    {{-- account --}}
                    <div class="relative" data-dropdown>
                        <button type="button" data-dropdown-trigger
                                class="flex items-center gap-2 rounded-xl p-1 pe-2 transition hover:bg-slate-100 dark:hover:bg-white/5">
                            <span class="grid h-9 w-9 place-items-center rounded-lg bg-gradient-to-br from-brand-navy to-brand-blue text-sm font-bold text-white">
                                {{ mb_substr($user->name, 0, 1) }}
                            </span>
                            <span class="hidden text-right sm:block">
                                <span class="block text-sm font-medium leading-tight text-brand-navy dark:text-white">{{ $user->name }}</span>
                                <span class="block text-[11px] leading-tight text-slate-400">{{ $user->role->label() }}</span>
                            </span>
                            <x-icon name="chevron-down" class="hidden h-4 w-4 text-slate-400 sm:block" />
                        </button>
                        <div data-dropdown-menu class="absolute left-0 mt-2 hidden w-56 origin-top overflow-hidden rounded-2xl border border-slate-200 bg-white p-1.5 shadow-xl shadow-slate-300/40 dark:border-white/10 dark:bg-slate-800 dark:shadow-black/40">
                            <div class="border-b border-slate-100 px-3 py-2.5 dark:border-white/10">
                                <p class="truncate text-sm font-semibold text-brand-navy dark:text-white">{{ $user->name }}</p>
                                <p class="truncate text-xs text-slate-400">{{ $user->email }}</p>
                                <p class="mt-1.5 inline-flex items-center gap-1 rounded-md bg-brand/10 px-2 py-0.5 text-[11px] font-medium text-brand">
                                    <x-icon name="shield" class="h-3 w-3" /> {{ $user->role->label() }}
                                </p>
                            </div>
                            <a href="{{ route('admin.settings') }}" class="mt-1 flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5">
                                <x-icon name="user-cog" class="h-4 w-4" /> الملف الشخصي
                            </a>
                            <a href="{{ route('admin.settings') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5">
                                <x-icon name="settings" class="h-4 w-4" /> إعدادات النظام
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-slate-100 pt-1 dark:border-white/10">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-rose-600 transition hover:bg-rose-50 dark:hover:bg-rose-500/10">
                                    <x-icon name="logout" class="h-4 w-4" /> تسجيل الخروج
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- content --}}
            <main class="flex-1 overflow-y-auto p-5 sm:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
