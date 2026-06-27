@php
    $methodStyles = [
        'cash' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
        'transfer' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
        'card' => 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400',
    ];
    $hasFilters = ($filters['q'] ?? '') !== '' || ($filters['method'] ?? '') !== '' || ($filters['from'] ?? '') !== '' || ($filters['to'] ?? '') !== '';
@endphp

{{-- Flexible scroll area: fills the remaining viewport height so the page itself never scrolls. --}}
<div class="brand-scroll min-h-0 flex-1 overflow-y-auto overflow-x-auto">
    @if ($payments->isEmpty())
        <div class="flex h-full flex-col items-center justify-center gap-4 px-6 text-center">
            <span class="grid h-16 w-16 place-items-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10">
                <x-icon name="payments" class="h-8 w-8" />
            </span>
            <div>
                <h3 class="text-base font-semibold text-brand-navy dark:text-white">
                    {{ $hasFilters ? 'لا توجد نتائج مطابقة' : 'لا توجد دفعات' }}
                </h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ $hasFilters ? 'جرّب تعديل البحث أو الفلاتر.' : 'ستظهر هنا الدفعات بمجرد تسجيلها.' }}
                </p>
            </div>
        </div>
    @else
        <table class="w-full min-w-[760px] border-collapse text-right">
            <thead class="sticky top-0 z-10">
                <tr class="bg-slate-50/95 text-[11px] font-semibold uppercase tracking-wider text-slate-400 backdrop-blur dark:bg-slate-800/95">
                    <th class="px-6 py-3 font-semibold">التاريخ</th>
                    <th class="px-6 py-3 font-semibold">العميل</th>
                    <th class="px-6 py-3 font-semibold">الفاتورة</th>
                    <th class="px-6 py-3 font-semibold">المبلغ</th>
                    <th class="px-6 py-3 font-semibold">الطريقة</th>
                    <th class="hidden px-6 py-3 font-semibold lg:table-cell">المرجع</th>
                    <th class="px-6 py-3 text-left font-semibold">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                @foreach ($payments as $payment)
                    <tr class="group transition hover:bg-brand/[0.03] dark:hover:bg-white/[0.03]">
                        <td class="px-6 py-2.5 text-sm text-slate-600 dark:text-slate-300">{{ $payment->paid_at?->format('Y/m/d') }}</td>
                        <td class="px-6 py-2.5">
                            <div class="flex items-center gap-3">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-gradient-to-br from-brand to-brand-blue text-xs font-bold text-white">{{ mb_substr($payment->customer?->name ?? '?', 0, 1) }}</span>
                                <span class="truncate text-sm font-semibold text-brand-navy dark:text-white">{{ $payment->customer?->name ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-2.5">
                            <a href="{{ route('company.invoices.show', $payment->invoice_id) }}" class="font-mono text-sm font-semibold text-brand hover:underline" dir="ltr">{{ $payment->invoice?->invoice_number ?? '—' }}</a>
                        </td>
                        <td class="px-6 py-2.5 whitespace-nowrap text-sm font-bold text-emerald-600">{{ number_format((float) $payment->amount, 2) }} <span class="text-xs text-slate-400">ر.س</span></td>
                        <td class="px-6 py-2.5">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $methodStyles[$payment->method->value] ?? '' }}">{{ $payment->method->label() }}</span>
                        </td>
                        <td class="hidden px-6 py-2.5 text-sm text-slate-400 lg:table-cell" dir="ltr">{{ $payment->reference ?? '—' }}</td>
                        <td class="px-6 py-2.5">
                            <div class="flex items-center justify-end gap-1.5 opacity-70 transition group-hover:opacity-100">
                                <form method="POST" action="{{ route('company.payments.destroy', $payment) }}" data-confirm onsubmit="return confirm('حذف الدفعة وعكس قيدها؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-rose-300 hover:bg-rose-50 hover:text-rose-600 dark:border-white/10 dark:text-slate-400 dark:hover:bg-rose-500/10"
                                            title="حذف">
                                        <x-icon name="trash" class="h-4 w-4" />
                                    </button>
                                </form>
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
        <span class="font-semibold text-slate-500 dark:text-slate-300">{{ $payments->firstItem() ?? 0 }}</span>
        -
        <span class="font-semibold text-slate-500 dark:text-slate-300">{{ $payments->lastItem() ?? 0 }}</span>
        من
        <span class="font-semibold text-slate-500 dark:text-slate-300">{{ $payments->total() }}</span>
        دفعة
    </p>

    @if ($payments->hasPages())
        <div class="flex items-center gap-1">
            <button type="button" data-payments-page="{{ $payments->currentPage() - 1 }}" @disabled($payments->onFirstPage())
                    class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-brand/30 hover:bg-brand/10 hover:text-brand disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:border-slate-200 disabled:hover:bg-transparent disabled:hover:text-slate-500 dark:border-white/10 dark:text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6"/></svg>
            </button>

            @foreach ($payments->getUrlRange(max(1, $payments->currentPage() - 2), min($payments->lastPage(), $payments->currentPage() + 2)) as $page => $url)
                <button type="button" data-payments-page="{{ $page }}"
                        class="grid h-8 min-w-8 place-items-center rounded-lg border px-2 text-sm font-medium transition {{ $page === $payments->currentPage() ? 'border-brand bg-brand text-white shadow shadow-brand/25' : 'border-slate-200 text-slate-500 hover:border-brand/30 hover:bg-brand/10 hover:text-brand dark:border-white/10 dark:text-slate-400' }}">
                    {{ $page }}
                </button>
            @endforeach

            <button type="button" data-payments-page="{{ $payments->currentPage() + 1 }}" @disabled(! $payments->hasMorePages())
                    class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-brand/30 hover:bg-brand/10 hover:text-brand disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:border-slate-200 disabled:hover:bg-transparent disabled:hover:text-slate-500 dark:border-white/10 dark:text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 6l-6 6 6 6"/></svg>
            </button>
        </div>
    @endif
</div>
