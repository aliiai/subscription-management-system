@php
    $activityColors = [
        'brand' => 'bg-brand/10 text-brand ring-brand/20',
        'blue' => 'bg-blue-500/10 text-blue-600 ring-blue-500/20 dark:text-blue-400',
        'emerald' => 'bg-emerald-500/10 text-emerald-600 ring-emerald-500/20 dark:text-emerald-400',
        'amber' => 'bg-amber-500/10 text-amber-600 ring-amber-500/20 dark:text-amber-400',
        'rose' => 'bg-rose-500/10 text-rose-600 ring-rose-500/20 dark:text-rose-400',
    ];
    $grouped = $activities->getCollection()->groupBy(fn ($item) => $item->created_at->format('Y-m-d'));
@endphp

{{-- scrollable timeline area --}}
<div class="min-h-0 flex-1 overflow-y-auto brand-scroll px-4 py-5 sm:px-6">
    @if ($activities->isEmpty())
        <div class="flex h-full min-h-[40vh] flex-col items-center justify-center text-center">
            <span class="grid h-16 w-16 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-white/5">
                <x-icon name="activity" class="h-8 w-8" />
            </span>
            <p class="mt-4 text-sm font-semibold text-slate-600 dark:text-slate-300">لا توجد أنشطة مطابقة</p>
            <p class="mt-1 text-xs text-slate-400">ستظهر هنا كل الأحداث المهمة مثل إضافة العملاء والفواتير والمدفوعات.</p>
        </div>
    @else
        @foreach ($grouped as $date => $items)
            @php $day = \Illuminate\Support\Carbon::parse($date); @endphp
            {{-- date divider --}}
            <div class="sticky top-0 z-10 -mx-4 mb-4 bg-white/85 px-4 py-1.5 backdrop-blur sm:-mx-6 sm:px-6 dark:bg-slate-900/85">
                <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500 dark:bg-white/5 dark:text-slate-300">
                    <x-icon name="calendar" class="h-3.5 w-3.5" />
                    @if ($day->isToday())
                        اليوم
                    @elseif ($day->isYesterday())
                        أمس
                    @else
                        {{ $day->translatedFormat('l، j F Y') }}
                    @endif
                </span>
            </div>

            {{-- timeline --}}
            <ol class="relative mb-2 space-y-4 border-s border-slate-200 ps-6 dark:border-white/10">
                @foreach ($items as $activity)
                    <li class="relative">
                        {{-- node --}}
                        <span class="absolute -start-[34px] grid h-9 w-9 place-items-center rounded-full ring-4 ring-white {{ $activityColors[$activity->color] ?? $activityColors['brand'] }} dark:ring-slate-900">
                            <x-icon :name="$activity->icon" class="h-4 w-4" />
                        </span>

                        <div class="rounded-xl border border-slate-100 bg-slate-50/60 px-4 py-3 transition hover:border-brand/20 hover:bg-white dark:border-white/5 dark:bg-white/[0.03] dark:hover:bg-white/5">
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-sm font-semibold text-brand-navy dark:text-white">{{ $activity->title }}</p>
                                <time class="shrink-0 whitespace-nowrap text-[11px] text-slate-400" title="{{ $activity->created_at->format('Y-m-d H:i') }}">
                                    {{ $activity->created_at->format('h:i A') }}
                                </time>
                            </div>
                            @if ($activity->body)
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $activity->body }}</p>
                            @endif
                            @if ($activity->url)
                                <a href="{{ $activity->url }}" class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-brand transition hover:gap-1.5">
                                    عرض التفاصيل
                                    <x-icon name="chevron-down" class="h-3.5 w-3.5 -rotate-90" />
                                </a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @endforeach
    @endif
</div>

{{-- footer: count + pagination --}}
<div class="flex shrink-0 flex-col items-center justify-between gap-3 border-t border-slate-100 px-6 py-3 sm:flex-row dark:border-white/10">
    <p class="text-xs text-slate-400">
        عرض
        <span class="font-semibold text-slate-500 dark:text-slate-300">{{ $activities->firstItem() ?? 0 }}</span>
        -
        <span class="font-semibold text-slate-500 dark:text-slate-300">{{ $activities->lastItem() ?? 0 }}</span>
        من
        <span class="font-semibold text-slate-500 dark:text-slate-300">{{ $activities->total() }}</span>
        حدث
    </p>

    @if ($activities->hasPages())
        <div class="flex items-center gap-1">
            <button type="button" data-activity-page="{{ $activities->currentPage() - 1 }}" @disabled($activities->onFirstPage())
                    class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-brand/30 hover:bg-brand/10 hover:text-brand disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:border-slate-200 disabled:hover:bg-transparent disabled:hover:text-slate-500 dark:border-white/10 dark:text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6"/></svg>
            </button>

            @foreach ($activities->getUrlRange(max(1, $activities->currentPage() - 2), min($activities->lastPage(), $activities->currentPage() + 2)) as $page => $url)
                <button type="button" data-activity-page="{{ $page }}"
                        class="grid h-8 min-w-8 place-items-center rounded-lg border px-2 text-sm font-medium transition {{ $page === $activities->currentPage() ? 'border-brand bg-brand text-white shadow shadow-brand/25' : 'border-slate-200 text-slate-500 hover:border-brand/30 hover:bg-brand/10 hover:text-brand dark:border-white/10 dark:text-slate-400' }}">
                    {{ $page }}
                </button>
            @endforeach

            <button type="button" data-activity-page="{{ $activities->currentPage() + 1 }}" @disabled(! $activities->hasMorePages())
                    class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-brand/30 hover:bg-brand/10 hover:text-brand disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:border-slate-200 disabled:hover:bg-transparent disabled:hover:text-slate-500 dark:border-white/10 dark:text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 6l-6 6 6 6"/></svg>
            </button>
        </div>
    @endif
</div>
