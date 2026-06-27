{{-- Flexible scroll area: fills the remaining viewport height so the page itself never scrolls. --}}
<div class="brand-scroll min-h-0 flex-1 overflow-y-auto overflow-x-auto">
    @if ($customers->isEmpty())
        <div class="flex h-full flex-col items-center justify-center gap-4 px-6 text-center">
            <span class="grid h-16 w-16 place-items-center rounded-2xl bg-brand/10 text-brand">
                <x-icon name="customers" class="h-8 w-8" />
            </span>
            <div>
                <h3 class="text-base font-semibold text-brand-navy dark:text-white">
                    @if (($filters['q'] ?? '') !== '' || ($filters['plan'] ?? '') !== '')
                        لا توجد نتائج مطابقة
                    @else
                        لا يوجد عملاء بعد
                    @endif
                </h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    @if (($filters['q'] ?? '') !== '' || ($filters['plan'] ?? '') !== '')
                        جرّب تعديل كلمات البحث أو الفلتر.
                    @else
                        ابدأ بإضافة أول عميل لشركتك.
                    @endif
                </p>
            </div>
            @if (($filters['q'] ?? '') === '' && ($filters['plan'] ?? '') === '')
                <button type="button" data-customer-create
                        class="inline-flex items-center gap-2 rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand/90">
                    <x-icon name="plus" class="h-5 w-5" /> إضافة عميل
                </button>
            @endif
        </div>
    @else
        <table class="w-full min-w-[680px] border-collapse text-right">
            <thead class="sticky top-0 z-10">
                <tr class="bg-slate-50/95 text-[11px] font-semibold uppercase tracking-wider text-slate-400 backdrop-blur dark:bg-slate-800/95">
                    <th class="px-6 py-3 font-semibold">العميل</th>
                    <th class="px-6 py-3 font-semibold">الهاتف</th>
                    <th class="px-6 py-3 font-semibold">الخطة</th>
                    <th class="hidden px-6 py-3 font-semibold lg:table-cell">تاريخ الإضافة</th>
                    <th class="px-6 py-3 text-left font-semibold">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                @foreach ($customers as $customer)
                    <tr class="group transition hover:bg-brand/[0.03] dark:hover:bg-white/[0.03]">
                        <td class="px-6 py-2.5">
                            <div class="flex items-center gap-3">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-gradient-to-br from-brand to-brand-blue text-xs font-bold text-white">
                                    {{ mb_substr($customer->name, 0, 1) }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-brand-navy dark:text-white">{{ $customer->name }}</p>
                                    <p class="truncate text-xs text-slate-400" dir="ltr">{{ $customer->email ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-2.5 text-sm text-slate-600 dark:text-slate-300" dir="ltr">
                            <span class="block text-right">{{ $customer->phone ?? '—' }}</span>
                        </td>
                        <td class="px-6 py-2.5">
                            @if ($customer->plan)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-brand/10 px-2.5 py-1 text-xs font-medium text-brand">
                                    <x-icon name="plans" class="h-3.5 w-3.5" /> {{ $customer->plan->name }}
                                </span>
                            @else
                                <span class="text-xs text-slate-300 dark:text-slate-600">بدون خطة</span>
                            @endif
                        </td>
                        <td class="hidden px-6 py-2.5 text-sm text-slate-500 lg:table-cell dark:text-slate-400">
                            {{ $customer->created_at?->format('Y/m/d') }}
                        </td>
                        <td class="px-6 py-2.5">
                            <div class="flex items-center justify-end gap-1.5 opacity-70 transition group-hover:opacity-100">
                                <button type="button"
                                        class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-brand/30 hover:bg-brand/10 hover:text-brand dark:border-white/10 dark:text-slate-400"
                                        data-customer-edit
                                        data-id="{{ $customer->id }}"
                                        data-name="{{ $customer->name }}"
                                        data-email="{{ $customer->email }}"
                                        data-phone="{{ $customer->phone }}"
                                        data-plan="{{ $customer->plan_id }}"
                                        data-start="{{ $customer->current_start_date ? \Illuminate\Support\Carbon::parse($customer->current_start_date)->format('Y-m-d') : '' }}"
                                        title="تعديل">
                                    <x-icon name="pencil" class="h-4 w-4" />
                                </button>
                                <button type="button"
                                        class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-rose-300 hover:bg-rose-50 hover:text-rose-600 dark:border-white/10 dark:text-slate-400 dark:hover:bg-rose-500/10"
                                        data-customer-delete
                                        data-id="{{ $customer->id }}"
                                        data-name="{{ $customer->name }}"
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
        <span class="font-semibold text-slate-500 dark:text-slate-300">{{ $customers->firstItem() ?? 0 }}</span>
        -
        <span class="font-semibold text-slate-500 dark:text-slate-300">{{ $customers->lastItem() ?? 0 }}</span>
        من
        <span class="font-semibold text-slate-500 dark:text-slate-300">{{ $customers->total() }}</span>
        عميل
    </p>

    @if ($customers->hasPages())
        <div class="flex items-center gap-1">
            {{-- previous --}}
            <button type="button" data-customers-page="{{ $customers->currentPage() - 1 }}" @disabled($customers->onFirstPage())
                    class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-brand/30 hover:bg-brand/10 hover:text-brand disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:border-slate-200 disabled:hover:bg-transparent disabled:hover:text-slate-500 dark:border-white/10 dark:text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6"/></svg>
            </button>

            @foreach ($customers->getUrlRange(max(1, $customers->currentPage() - 2), min($customers->lastPage(), $customers->currentPage() + 2)) as $page => $url)
                <button type="button" data-customers-page="{{ $page }}"
                        class="grid h-8 min-w-8 place-items-center rounded-lg border px-2 text-sm font-medium transition {{ $page === $customers->currentPage() ? 'border-brand bg-brand text-white shadow shadow-brand/25' : 'border-slate-200 text-slate-500 hover:border-brand/30 hover:bg-brand/10 hover:text-brand dark:border-white/10 dark:text-slate-400' }}">
                    {{ $page }}
                </button>
            @endforeach

            {{-- next --}}
            <button type="button" data-customers-page="{{ $customers->currentPage() + 1 }}" @disabled(! $customers->hasMorePages())
                    class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-brand/30 hover:bg-brand/10 hover:text-brand disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:border-slate-200 disabled:hover:bg-transparent disabled:hover:text-slate-500 dark:border-white/10 dark:text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 6l-6 6 6 6"/></svg>
            </button>
        </div>
    @endif
</div>
