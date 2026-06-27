@php
    $currencies = [
        'SAR' => 'ر.س', 'AED' => 'د.إ', 'QAR' => 'ر.ق', 'KWD' => 'د.ك',
        'BHD' => 'د.ب', 'OMR' => 'ر.ع', 'EGP' => 'ج.م', 'USD' => '$', 'EUR' => '€',
    ];
    $hasFilters = ($filters['q'] ?? '') !== '' || ($filters['status'] ?? '') !== '' || ($filters['cycle'] ?? '') !== '';
@endphp

{{-- Flexible scroll area: fills the remaining viewport height so the page itself never scrolls. --}}
<div class="brand-scroll min-h-0 flex-1 overflow-y-auto overflow-x-auto">
    @if ($plans->isEmpty())
        <div class="flex h-full flex-col items-center justify-center gap-4 px-6 text-center">
            <span class="grid h-16 w-16 place-items-center rounded-2xl bg-brand/10 text-brand">
                <x-icon name="plans" class="h-8 w-8" />
            </span>
            <div>
                <h3 class="text-base font-semibold text-brand-navy dark:text-white">
                    {{ $hasFilters ? 'لا توجد نتائج مطابقة' : 'لا توجد خطط بعد' }}
                </h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ $hasFilters ? 'جرّب تعديل كلمات البحث أو الفلتر.' : 'ابدأ بإنشاء أول خطة اشتراك لعملائك.' }}
                </p>
            </div>
            @unless ($hasFilters)
                <button type="button" data-plan-create
                        class="inline-flex items-center gap-2 rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand/90">
                    <x-icon name="plus" class="h-5 w-5" /> إنشاء خطة
                </button>
            @endunless
        </div>
    @else
        <table class="w-full min-w-[680px] border-collapse text-right">
            <thead class="sticky top-0 z-10">
                <tr class="bg-slate-50/95 text-[11px] font-semibold uppercase tracking-wider text-slate-400 backdrop-blur dark:bg-slate-800/95">
                    <th class="px-6 py-3 font-semibold">الخطة</th>
                    <th class="px-6 py-3 font-semibold">السعر</th>
                    <th class="px-6 py-3 font-semibold">المشتركون</th>
                    <th class="hidden px-6 py-3 font-semibold lg:table-cell">المميزات</th>
                    <th class="px-6 py-3 font-semibold">الحالة</th>
                    <th class="px-6 py-3 text-left font-semibold">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                @foreach ($plans as $plan)
                    <tr data-plan-row class="group transition hover:bg-brand/[0.03] dark:hover:bg-white/[0.03]">
                        <td class="px-6 py-2.5">
                            <div class="flex items-center gap-3">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-gradient-to-br from-brand/15 to-brand-blue/10 text-brand ring-1 ring-inset ring-brand/10">
                                    <x-icon name="plans" class="h-4 w-4" />
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-brand-navy dark:text-white">{{ $plan->name }}</p>
                                    @if ($plan->description)
                                        <p class="max-w-[16rem] truncate text-xs text-slate-400">{{ $plan->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-2.5 whitespace-nowrap">
                            <div class="flex items-baseline gap-1">
                                <span class="text-sm font-bold text-brand-navy dark:text-white">{{ number_format((float) $plan->price, 2) }}</span>
                                <span class="text-xs font-medium text-slate-400">{{ $currencies[$plan->currency] ?? $plan->currency }}</span>
                            </div>
                            <span class="text-[11px] text-slate-400">/ {{ $plan->billing_cycle->label() }}</span>
                        </td>
                        <td class="px-6 py-2.5">
                            <a href="{{ route('company.subscriptions') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 transition hover:bg-brand/10 hover:text-brand dark:bg-white/5 dark:text-slate-300">
                                <x-icon name="customers" class="h-3.5 w-3.5" /> {{ $plan->active_subscribers_count }}
                            </a>
                        </td>
                        <td class="hidden px-6 py-2.5 lg:table-cell">
                            @if (count($plan->features ?? []) > 0)
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach (array_slice($plan->features ?? [], 0, 2) as $feature)
                                        <span class="rounded-md bg-brand/10 px-2 py-1 text-[11px] font-medium text-brand">{{ $feature }}</span>
                                    @endforeach
                                    @if (count($plan->features ?? []) > 2)
                                        <span class="rounded-md bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-500 dark:bg-white/5 dark:text-slate-400">+{{ count($plan->features) - 2 }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-slate-300 dark:text-slate-600">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-2.5">
                            @if ($plan->is_active)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> نشطة
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500 dark:bg-white/5 dark:text-slate-400">
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span> متوقفة
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-2.5">
                            <div class="flex items-center justify-end gap-1.5 opacity-70 transition group-hover:opacity-100">
                                <button type="button"
                                        class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-brand/30 hover:bg-brand/10 hover:text-brand dark:border-white/10 dark:text-slate-400"
                                        data-plan-edit
                                        data-id="{{ $plan->id }}"
                                        data-name="{{ $plan->name }}"
                                        data-description="{{ $plan->description }}"
                                        data-price="{{ $plan->price }}"
                                        data-currency="{{ $plan->currency }}"
                                        data-billing="{{ $plan->billing_cycle->value }}"
                                        data-active="{{ $plan->is_active ? '1' : '0' }}"
                                        data-features='@json($plan->features ?? [])'
                                        title="تعديل">
                                    <x-icon name="pencil" class="h-4 w-4" />
                                </button>
                                <button type="button"
                                        class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-rose-300 hover:bg-rose-50 hover:text-rose-600 dark:border-white/10 dark:text-slate-400 dark:hover:bg-rose-500/10"
                                        data-plan-delete
                                        data-id="{{ $plan->id }}"
                                        data-name="{{ $plan->name }}"
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
        <span class="font-semibold text-slate-500 dark:text-slate-300">{{ $plans->firstItem() ?? 0 }}</span>
        -
        <span class="font-semibold text-slate-500 dark:text-slate-300">{{ $plans->lastItem() ?? 0 }}</span>
        من
        <span class="font-semibold text-slate-500 dark:text-slate-300">{{ $plans->total() }}</span>
        خطة
    </p>

    @if ($plans->hasPages())
        <div class="flex items-center gap-1">
            <button type="button" data-plans-page="{{ $plans->currentPage() - 1 }}" @disabled($plans->onFirstPage())
                    class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-brand/30 hover:bg-brand/10 hover:text-brand disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:border-slate-200 disabled:hover:bg-transparent disabled:hover:text-slate-500 dark:border-white/10 dark:text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6"/></svg>
            </button>

            @foreach ($plans->getUrlRange(max(1, $plans->currentPage() - 2), min($plans->lastPage(), $plans->currentPage() + 2)) as $page => $url)
                <button type="button" data-plans-page="{{ $page }}"
                        class="grid h-8 min-w-8 place-items-center rounded-lg border px-2 text-sm font-medium transition {{ $page === $plans->currentPage() ? 'border-brand bg-brand text-white shadow shadow-brand/25' : 'border-slate-200 text-slate-500 hover:border-brand/30 hover:bg-brand/10 hover:text-brand dark:border-white/10 dark:text-slate-400' }}">
                    {{ $page }}
                </button>
            @endforeach

            <button type="button" data-plans-page="{{ $plans->currentPage() + 1 }}" @disabled(! $plans->hasMorePages())
                    class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-brand/30 hover:bg-brand/10 hover:text-brand disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:border-slate-200 disabled:hover:bg-transparent disabled:hover:text-slate-500 dark:border-white/10 dark:text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 6l-6 6 6 6"/></svg>
            </button>
        </div>
    @endif
</div>
