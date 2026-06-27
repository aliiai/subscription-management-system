@props(['title' => null])

@php
    $user = auth()->user();

    $groups = [
        [
            'label' => 'عام',
            'items' => [
                ['label' => 'الرئيسية', 'route' => 'company.dashboard', 'icon' => 'home'],
            ],
        ],
        [
            'label' => 'إدارة الاشتراكات',
            'items' => [
                ['label' => 'العملاء', 'route' => 'company.customers', 'icon' => 'customers'],
                ['label' => 'خطط الاشتراك', 'route' => 'company.plans', 'icon' => 'plans'],
                ['label' => 'الاشتراكات', 'route' => 'company.subscriptions', 'icon' => 'subscriptions'],
            ],
        ],
        [
            'label' => 'الفوترة والتحصيل',
            'items' => [
                ['label' => 'الفواتير', 'route' => 'company.invoices', 'icon' => 'invoices'],
                ['label' => 'المدفوعات', 'route' => 'company.payments', 'icon' => 'payments'],
            ],
        ],
        [
            'label' => 'المحاسبة والتقارير',
            'items' => [
                ['label' => 'الاعتراف بالإيرادات', 'route' => 'company.revenue-recognition', 'icon' => 'revenue'],
                ['label' => 'قائمة الدخل', 'route' => 'company.income-statement', 'icon' => 'income'],
                ['label' => 'الميزانية العمومية', 'route' => 'company.balance-sheet', 'icon' => 'balance'],
            ],
        ],
        [
            'label' => 'النظام',
            'items' => [
                ['label' => 'الإعدادات', 'route' => 'company.settings', 'icon' => 'settings'],
                ['label' => 'سجل النشاط', 'route' => 'company.activity-log', 'icon' => 'activity'],
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
    <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name', 'Accrual Hub') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logoicon.png') }}">

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
    <div class="flex h-screen overflow-hidden print:block print:h-auto print:overflow-visible">

        {{-- ===================== SIDEBAR (right in RTL) ===================== --}}
        <aside id="sidebar"
               class="fixed inset-y-0 right-0 z-50 flex w-72 shrink-0 translate-x-full flex-col border-l border-slate-200 bg-white transition-transform duration-300 ease-in-out group-data-[mobile-open=true]/shell:translate-x-0 lg:relative lg:z-30 lg:translate-x-0 lg:transition-[width] lg:group-data-[collapsed=true]/shell:w-[78px] print:hidden dark:border-white/10 dark:bg-slate-900">

            {{-- brand --}}
            <div class="relative flex h-16 items-center gap-3 overflow-hidden border-b border-slate-200 px-5 dark:border-white/10">
                <div class="pointer-events-none absolute inset-0 opacity-[0.07]"
                     style="background-image: radial-gradient(currentColor 1px, transparent 1px); background-size: 16px 16px;"></div>
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-brand to-brand-blue text-base font-bold text-white shadow-lg shadow-brand/25">
                    <img src="{{ asset('assets/images/logoicon.png') }}" alt="" class="h-6 w-auto" onerror="this.replaceWith(Object.assign(document.createElement('span'),{textContent:'A'}))">
                </span>
                <span class="truncate text-lg font-bold tracking-tight text-brand-navy lg:group-data-[collapsed=true]/shell:hidden dark:text-white">
                    {{ config('app.name', 'Accrual Hub') }}
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
                                @if (isset($item['children']))
                                    @php
                                        $childActive = collect($item['children'])->contains(fn ($c) => request()->routeIs($c['route']));
                                    @endphp
                                    <li>
                                        <button type="button" data-submenu-toggle aria-expanded="{{ $childActive ? 'true' : 'false' }}"
                                                title="{{ $item['label'] }}"
                                                class="group/link flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-brand-navy dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white {{ $childActive ? 'bg-slate-100 text-brand-navy dark:bg-white/5 dark:text-white' : '' }}">
                                            <span class="grid h-6 w-6 shrink-0 place-items-center text-slate-400 group-hover/link:text-brand {{ $childActive ? 'text-brand' : '' }}">
                                                <x-icon :name="$item['icon']" />
                                            </span>
                                            <span class="flex-1 text-right lg:group-data-[collapsed=true]/shell:hidden">{{ $item['label'] }}</span>
                                            <x-icon name="chevron-down" data-chevron class="h-4 w-4 transition-transform lg:group-data-[collapsed=true]/shell:hidden {{ $childActive ? 'rotate-180' : '' }}" />
                                        </button>
                                        <div data-submenu class="overflow-hidden lg:group-data-[collapsed=true]/shell:hidden" @style(['display: none' => ! $childActive])>
                                            <ul class="mt-1 space-y-1 border-r-2 border-slate-100 pr-3 mr-5 dark:border-white/10">
                                                @foreach ($item['children'] as $child)
                                                    <li>
                                                        <a href="{{ route($child['route']) }}"
                                                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition {{ request()->routeIs($child['route']) ? 'bg-brand/10 font-medium text-brand' : 'text-slate-500 hover:bg-slate-100 hover:text-brand-navy dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white' }}">
                                                            <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ request()->routeIs($child['route']) ? 'bg-brand' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                                                            {{ $child['label'] }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </li>
                                @else
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
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </nav>

        </aside>

        {{-- mobile sidebar overlay --}}
        <div data-sidebar-overlay
             class="fixed inset-0 z-40 bg-slate-900/50 opacity-0 backdrop-blur-sm transition-opacity duration-300 pointer-events-none group-data-[mobile-open=true]/shell:pointer-events-auto group-data-[mobile-open=true]/shell:opacity-100 lg:hidden print:hidden"></div>

        {{-- ===================== MAIN ===================== --}}
        <div class="flex flex-1 flex-col overflow-hidden print:overflow-visible">

            {{-- header --}}
            <header class="relative z-40 flex h-16 shrink-0 items-center justify-between gap-4 border-b border-slate-200 bg-white/80 px-4 backdrop-blur-md sm:px-6 print:hidden dark:border-white/10 dark:bg-slate-900/80">
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
                    @php
                        $notificationColors = [
                            'brand' => 'bg-brand/10 text-brand',
                            'blue' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
                            'emerald' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
                            'amber' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
                            'rose' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400',
                        ];
                        $unreadCount = $unreadNotificationsCount ?? 0;
                    @endphp
                    <div class="relative" data-dropdown>
                        <button type="button" data-dropdown-trigger
                                class="relative grid h-10 w-10 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-brand-navy dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white">
                            <x-icon name="bell" />
                            @if ($unreadCount > 0)
                                <span class="absolute -right-0.5 -top-0.5 grid h-4 min-w-4 place-items-center rounded-full bg-brand px-1 text-[10px] font-bold leading-none text-white ring-2 ring-white dark:ring-slate-900">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                            @endif
                        </button>
                        <div data-dropdown-menu class="absolute left-0 mt-2 hidden w-80 origin-top overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-300/40 dark:border-white/10 dark:bg-slate-800 dark:shadow-black/40">
                            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-white/10">
                                <span class="text-sm font-semibold text-brand-navy dark:text-white">الإشعارات</span>
                                @if ($unreadCount > 0)
                                    <form method="POST" action="{{ route('company.notifications.read-all') }}">
                                        @csrf
                                        <button type="submit" class="rounded-full bg-brand/10 px-2 py-0.5 text-[11px] font-semibold text-brand transition hover:bg-brand/20">تعليم الكل كمقروء</button>
                                    </form>
                                @endif
                            </div>
                            <ul class="max-h-72 divide-y divide-slate-100 overflow-y-auto brand-scroll dark:divide-white/5">
                                @forelse ($headerNotifications ?? [] as $notification)
                                    <li>
                                        <a href="{{ route('company.notifications.open', $notification) }}"
                                           class="flex gap-3 px-4 py-2.5 transition hover:bg-slate-50 dark:hover:bg-white/5 {{ $notification->isUnread() ? 'bg-brand/[0.03]' : '' }}">
                                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg {{ $notificationColors[$notification->color] ?? $notificationColors['brand'] }}">
                                                <x-icon :name="$notification->icon" class="h-5 w-5" />
                                            </span>
                                            <div class="min-w-0 flex-1">
                                                <p class="flex items-center gap-1.5 text-sm font-medium text-slate-700 dark:text-slate-200">
                                                    @if ($notification->isUnread())
                                                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-brand"></span>
                                                    @endif
                                                    <span class="truncate">{{ $notification->title }}</span>
                                                </p>
                                                @if ($notification->body)
                                                    <p class="mt-0.5 truncate text-xs text-slate-500 dark:text-slate-400">{{ $notification->body }}</p>
                                                @endif
                                                <p class="mt-0.5 text-[11px] text-slate-400">{{ $notification->created_at->diffForHumans() }}</p>
                                            </div>
                                        </a>
                                    </li>
                                @empty
                                    <li class="px-4 py-10 text-center">
                                        <span class="mx-auto grid h-11 w-11 place-items-center rounded-full bg-slate-100 text-slate-400 dark:bg-white/5">
                                            <x-icon name="bell" class="h-5 w-5" />
                                        </span>
                                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">لا توجد إشعارات بعد</p>
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    {{-- account --}}
                    <div class="relative" data-dropdown>
                        <button type="button" data-dropdown-trigger
                                class="flex items-center gap-2 rounded-xl p-1 pe-2 transition hover:bg-slate-100 dark:hover:bg-white/5">
                            <span class="grid h-9 w-9 place-items-center rounded-lg bg-gradient-to-br from-brand to-brand-blue text-sm font-bold text-white">
                                {{ mb_substr($user->name, 0, 1) }}
                            </span>
                            <span class="hidden text-right sm:block">
                                <span class="block text-sm font-medium leading-tight text-brand-navy dark:text-white">{{ $user->name }}</span>
                                <span class="block text-[11px] leading-tight text-slate-400">{{ $user->tenant?->name ?? $user->role->label() }}</span>
                            </span>
                            <x-icon name="chevron-down" class="hidden h-4 w-4 text-slate-400 sm:block" />
                        </button>
                        <div data-dropdown-menu class="absolute left-0 mt-2 hidden w-56 origin-top overflow-hidden rounded-2xl border border-slate-200 bg-white p-1.5 shadow-xl shadow-slate-300/40 dark:border-white/10 dark:bg-slate-800 dark:shadow-black/40">
                            <div class="border-b border-slate-100 px-3 py-2.5 dark:border-white/10">
                                <p class="truncate text-sm font-semibold text-brand-navy dark:text-white">{{ $user->name }}</p>
                                <p class="truncate text-xs text-slate-400">{{ $user->email }}</p>
                                @if ($user->tenant)
                                    <p class="mt-1.5 inline-flex items-center gap-1 rounded-md bg-brand/10 px-2 py-0.5 text-[11px] font-medium text-brand">
                                        <x-icon name="building" class="h-3 w-3" /> {{ $user->tenant->name }}
                                    </p>
                                @endif
                            </div>
                            <a href="{{ route('company.settings') }}" class="mt-1 flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5">
                                <x-icon name="settings" class="h-4 w-4" /> الإعدادات
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
            <main class="flex-1 overflow-y-auto p-5 sm:p-8 print:overflow-visible print:p-0">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
