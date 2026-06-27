@php
    $currencySymbols = [
        'SAR' => 'ر.س', 'AED' => 'د.إ', 'QAR' => 'ر.ق', 'KWD' => 'د.ك',
        'BHD' => 'د.ب', 'OMR' => 'ر.ع', 'EGP' => 'ج.م', 'USD' => '$', 'EUR' => '€',
    ];
    $statusStyles = [
        'active' => ['bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400', 'bg-emerald-500'],
        'canceled' => ['bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400', 'bg-rose-500'],
        'expired' => ['bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400', 'bg-amber-500'],
    ];
    $hasFilters = ($filters['q'] ?? '') !== '' || ($filters['status'] ?? '') !== '' || ($filters['plan'] ?? '') !== '';
@endphp

{{-- Flexible scroll area: fills the remaining viewport height so the page itself never scrolls. --}}
<div class="brand-scroll min-h-0 flex-1 overflow-y-auto overflow-x-auto">
    @if ($subscriptions->isEmpty())
        <div class="flex h-full flex-col items-center justify-center gap-4 px-6 text-center">
            <span class="grid h-16 w-16 place-items-center rounded-2xl bg-brand/10 text-brand">
                <x-icon name="subscriptions" class="h-8 w-8" />
            </span>
            <div>
                <h3 class="text-base font-semibold text-brand-navy dark:text-white">
                    {{ $hasFilters ? 'لا توجد نتائج مطابقة' : 'لا توجد اشتراكات بعد' }}
                </h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ $hasFilters ? 'جرّب تعديل كلمات البحث أو الفلتر.' : 'ابدأ بربط عملائك بخطط الاشتراك.' }}
                </p>
            </div>
        </div>
    @else
        <table class="w-full min-w-[720px] border-collapse text-right">
            <thead class="sticky top-0 z-10">
                <tr class="bg-slate-50/95 text-[11px] font-semibold uppercase tracking-wider text-slate-400 backdrop-blur dark:bg-slate-800/95">
                    <th class="px-6 py-3 font-semibold">العميل</th>
                    <th class="px-6 py-3 font-semibold">الخطة</th>
                    <th class="hidden px-6 py-3 font-semibold md:table-cell">تاريخ البدء</th>
                    <th class="px-6 py-3 font-semibold">السعر</th>
                    <th class="px-6 py-3 font-semibold">الحالة</th>
                    <th class="px-6 py-3 text-left font-semibold">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                @foreach ($subscriptions as $subscription)
                    @php $style = $statusStyles[$subscription->status->value] ?? $statusStyles['expired']; @endphp
                    <tr class="group transition hover:bg-brand/[0.03] dark:hover:bg-white/[0.03]">
                        <td class="px-6 py-2.5">
                            <div class="flex items-center gap-3">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-gradient-to-br from-brand to-brand-blue text-xs font-bold text-white">
                                    {{ mb_substr($subscription->customer?->name ?? '?', 0, 1) }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-brand-navy dark:text-white">{{ $subscription->customer?->name ?? '—' }}</p>
                                    <p class="truncate text-xs text-slate-400" dir="ltr">{{ $subscription->customer?->email ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-2.5">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-brand/10 px-2.5 py-1 text-xs font-medium text-brand">
                                <x-icon name="plans" class="h-3.5 w-3.5" /> {{ $subscription->plan?->name ?? '—' }}
                            </span>
                        </td>
                        <td class="hidden px-6 py-2.5 text-sm text-slate-600 md:table-cell dark:text-slate-300">
                            {{ $subscription->start_date?->format('Y/m/d') }}
                        </td>
                        <td class="px-6 py-2.5 whitespace-nowrap">
                            <span class="text-sm font-bold text-brand-navy dark:text-white">{{ number_format((float) $subscription->price, 2) }}</span>
                            <span class="text-xs text-slate-400">{{ $currencySymbols[$subscription->plan?->currency] ?? $subscription->plan?->currency }}</span>
                        </td>
                        <td class="px-6 py-2.5">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium {{ $style[0] }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $style[1] }}"></span> {{ $subscription->status->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-2.5">
                            <div class="flex items-center justify-end gap-1.5 opacity-70 transition group-hover:opacity-100">
                                <button type="button"
                                        class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-brand/30 hover:bg-brand/10 hover:text-brand dark:border-white/10 dark:text-slate-400"
                                        data-subscription-edit
                                        data-id="{{ $subscription->id }}"
                                        data-customer="{{ $subscription->customer_id }}"
                                        data-plan="{{ $subscription->plan_id }}"
                                        data-start="{{ $subscription->start_date?->format('Y-m-d') }}"
                                        data-status="{{ $subscription->status->value }}"
                                        title="تعديل">
                                    <x-icon name="pencil" class="h-4 w-4" />
                                </button>
                                <button type="button"
                                        class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-rose-300 hover:bg-rose-50 hover:text-rose-600 dark:border-white/10 dark:text-slate-400 dark:hover:bg-rose-500/10"
                                        data-subscription-delete
                                        data-id="{{ $subscription->id }}"
                                        data-name="{{ $subscription->customer?->name }} · {{ $subscription->plan?->name }}"
                                        title="حذف">
                                    <x-icon name="trash" class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- footer: count + pagination --}}
<div class="flex shrink-0 flex-col items-center justify-between gap-3 border-t border-slate-100 px-6 py-3 sm:flex-row dark:border-white/10">
    <p class="text-xs text-slate-400">
        عرض
        <span class="font-semibold text-slate-500 dark:text-slate-300">{{ $subscriptions->firstItem() ?? 0 }}</span>
        -
        <span class="font-semibold text-slate-500 dark:text-slate-300">{{ $subscriptions->lastItem() ?? 0 }}</span>
        من
        <span class="font-semibold text-slate-500 dark:text-slate-300">{{ $subscriptions->total() }}</span>
        اشتراك
    </p>

    @if ($subscriptions->hasPages())
        <div class="flex items-center gap-1">
            <button type="button" data-subscriptions-page="{{ $subscriptions->currentPage() - 1 }}" @disabled($subscriptions->onFirstPage())
                    class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-brand/30 hover:bg-brand/10 hover:text-brand disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:border-slate-200 disabled:hover:bg-transparent disabled:hover:text-slate-500 dark:border-white/10 dark:text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6"/></svg>
            </button>

            @foreach ($subscriptions->getUrlRange(max(1, $subscriptions->currentPage() - 2), min($subscriptions->lastPage(), $subscriptions->currentPage() + 2)) as $page => $url)
                <button type="button" data-subscriptions-page="{{ $page }}"
                        class="grid h-8 min-w-8 place-items-center rounded-lg border px-2 text-sm font-medium transition {{ $page === $subscriptions->currentPage() ? 'border-brand bg-brand text-white shadow shadow-brand/25' : 'border-slate-200 text-slate-500 hover:border-brand/30 hover:bg-brand/10 hover:text-brand dark:border-white/10 dark:text-slate-400' }}">
                    {{ $page }}
                </button>
            @endforeach

            <button type="button" data-subscriptions-page="{{ $subscriptions->currentPage() + 1 }}" @disabled(! $subscriptions->hasMorePages())
                    class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-brand/30 hover:bg-brand/10 hover:text-brand disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:border-slate-200 disabled:hover:bg-transparent disabled:hover:text-slate-500 dark:border-white/10 dark:text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 6l-6 6 6 6"/></svg>
            </button>
        </div>
    @endif
</div>
