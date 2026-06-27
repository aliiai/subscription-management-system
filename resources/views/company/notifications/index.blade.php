<x-layouts.dashboard title="الإشعارات">
  @php
      $notificationColors = [
          'brand' => 'bg-brand/10 text-brand',
          'blue' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
          'emerald' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
          'amber' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
          'rose' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400',
      ];
  @endphp
  <div class="flex h-full flex-col">
    {{-- header --}}
    <div data-animate="auth-card" class="flex shrink-0 flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('company.dashboard') }}" class="transition hover:text-brand">الرئيسية</a>
                <span>/</span>
                <span class="text-slate-500 dark:text-slate-300">الإشعارات</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-brand-navy sm:text-3xl dark:text-white">الإشعارات</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">آخر الأحداث المهمة في حساب شركتك.</p>
        </div>

        @if ($unreadCount > 0)
            <form method="POST" action="{{ route('company.notifications.read-all') }}" class="shrink-0">
                @csrf
                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-brand-navy shadow-sm transition hover:border-brand/30 hover:bg-brand/5 dark:border-white/10 dark:bg-slate-900 dark:text-white">
                    <x-icon name="check-double" class="h-5 w-5" />
                    تعليم الكل كمقروء
                </button>
            </form>
        @endif
    </div>

    {{-- status toast --}}
    @if (session('status'))
        <div data-toast class="mt-5 flex shrink-0 items-center gap-3 rounded-2xl border border-brand/20 bg-brand/10 px-4 py-3 text-sm font-medium text-brand">
            <x-icon name="check-double" class="h-5 w-5" />
            {{ session('status') }}
        </div>
    @endif

    {{-- list card --}}
    <div data-animate="auth-card" class="mt-6 flex min-h-0 flex-1 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">
        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6 dark:border-white/10">
            <h2 class="text-sm font-bold text-brand-navy dark:text-white">جميع الإشعارات</h2>
            @if ($unreadCount > 0)
                <span class="rounded-full bg-brand/10 px-2.5 py-0.5 text-[11px] font-semibold text-brand">{{ $unreadCount }} غير مقروء</span>
            @endif
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto brand-scroll">
            @if ($notifications->isEmpty())
                <div class="flex h-full flex-col items-center justify-center px-6 py-16 text-center">
                    <span class="grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-white/5">
                        <x-icon name="bell" class="h-7 w-7" />
                    </span>
                    <p class="mt-3 text-sm font-medium text-slate-600 dark:text-slate-300">لا توجد إشعارات بعد</p>
                    <p class="mt-1 text-xs text-slate-400">ستظهر هنا الأحداث المهمة مثل الفواتير والدفعات والاشتراكات.</p>
                </div>
            @else
                <ul class="divide-y divide-slate-100 dark:divide-white/5">
                    @foreach ($notifications as $notification)
                        <li>
                            <a href="{{ route('company.notifications.open', $notification) }}"
                               class="flex items-start gap-3 px-4 py-4 transition hover:bg-slate-50 sm:px-6 dark:hover:bg-white/5 {{ $notification->isUnread() ? 'bg-brand/[0.03]' : '' }}">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl {{ $notificationColors[$notification->color] ?? $notificationColors['brand'] }}">
                                    <x-icon :name="$notification->icon" class="h-5 w-5" />
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="flex items-center gap-2 text-sm font-semibold text-brand-navy dark:text-white">
                                        @if ($notification->isUnread())
                                            <span class="h-2 w-2 shrink-0 rounded-full bg-brand"></span>
                                        @endif
                                        <span class="truncate">{{ $notification->title }}</span>
                                    </p>
                                    @if ($notification->body)
                                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $notification->body }}</p>
                                    @endif
                                </div>
                                <span class="shrink-0 whitespace-nowrap text-[11px] text-slate-400">{{ $notification->created_at->diffForHumans() }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        @if ($notifications->hasPages())
            <div class="shrink-0 border-t border-slate-100 px-4 py-3 sm:px-6 dark:border-white/10">
                {{ $notifications->onEachSide(1)->links() }}
            </div>
        @endif
    </div>
  </div>
</x-layouts.dashboard>
