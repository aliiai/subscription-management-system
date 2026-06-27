@php
    $statusStyles = [
        'unpaid' => ['bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400', 'bg-amber-500'],
        'partially_paid' => ['bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400', 'bg-blue-500'],
        'paid' => ['bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400', 'bg-emerald-500'],
        'void' => ['bg-slate-100 text-slate-500 dark:bg-white/5 dark:text-slate-400', 'bg-slate-400'],
    ];
    $isRecognized = ($view ?? 'pending') === 'recognized';
    $hasSearch = ($filters['q'] ?? '') !== '';
@endphp

{{-- Flexible scroll area: fills the remaining viewport height so the page itself never scrolls. --}}
<div class="brand-scroll min-h-0 flex-1 overflow-y-auto overflow-x-auto">
    @if ($invoices->isEmpty())
        <div class="flex h-full flex-col items-center justify-center gap-4 px-6 text-center">
            <span class="grid h-16 w-16 place-items-center rounded-2xl {{ $isRecognized ? 'bg-brand/10 text-brand' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10' }}">
                <x-icon name="{{ $isRecognized ? 'revenue' : 'check-double' }}" class="h-8 w-8" />
            </span>
            <div>
                <h3 class="text-base font-semibold text-brand-navy dark:text-white">
                    @if ($hasSearch)
                        لا توجد نتائج مطابقة
                    @elseif ($isRecognized)
                        لم يتم الاعتراف بأي إيراد بعد
                    @else
                        لا توجد فواتير بانتظار الاعتراف
                    @endif
                </h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    @if ($hasSearch)
                        جرّب تعديل كلمات البحث.
                    @elseif ($isRecognized)
                        شغّل عملية الاعتراف لتحويل الإيرادات المؤجلة إلى إيرادات فعلية.
                    @else
                        جميع فواتير فترة {{ $period->format('Y/m') }} تم الاعتراف بإيراداتها أو لا توجد فواتير مستحقة.
                    @endif
                </p>
            </div>
        </div>
    @else
        <table class="w-full min-w-[720px] border-collapse text-right">
            <thead class="sticky top-0 z-10">
                <tr class="bg-slate-50/95 text-[11px] font-semibold uppercase tracking-wider text-slate-400 backdrop-blur dark:bg-slate-800/95">
                    <th class="px-6 py-3 font-semibold">رقم الفاتورة</th>
                    <th class="px-6 py-3 font-semibold">العميل</th>
                    <th class="hidden px-6 py-3 font-semibold lg:table-cell">الفترة</th>
                    @if ($isRecognized)
                        <th class="px-6 py-3 font-semibold">المبلغ</th>
                        <th class="hidden px-6 py-3 font-semibold sm:table-cell">تاريخ الاعتراف</th>
                        <th class="px-6 py-3 text-left font-semibold">القيد</th>
                    @else
                        <th class="hidden px-6 py-3 font-semibold sm:table-cell">الاستحقاق</th>
                        <th class="px-6 py-3 font-semibold">المبلغ</th>
                        <th class="px-6 py-3 text-left font-semibold">حالة الدفع</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                @foreach ($invoices as $invoice)
                    @php $style = $statusStyles[$invoice->status->value] ?? $statusStyles['void']; @endphp
                    <tr class="group transition hover:bg-brand/[0.03] dark:hover:bg-white/[0.03]">
                        <td class="px-6 py-2.5">
                            <a href="{{ route('company.invoices.show', $invoice) }}" class="font-mono text-sm font-semibold text-brand hover:underline" dir="ltr">{{ $invoice->invoice_number }}</a>
                        </td>
                        <td class="px-6 py-2.5">
                            <div class="flex items-center gap-3">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-gradient-to-br from-brand to-brand-blue text-xs font-bold text-white">
                                    {{ mb_substr($invoice->customer?->name ?? '?', 0, 1) }}
                                </span>
                                <span class="truncate text-sm font-semibold text-brand-navy dark:text-white">{{ $invoice->customer?->name ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="hidden px-6 py-2.5 text-sm text-slate-600 lg:table-cell dark:text-slate-300" dir="ltr">
                            <span class="block text-right">{{ $invoice->period_start?->format('Y/m/d') }} - {{ $invoice->period_end?->format('Y/m/d') }}</span>
                        </td>
                        @if ($isRecognized)
                            <td class="px-6 py-2.5 whitespace-nowrap">
                                <span class="text-sm font-bold text-emerald-600">{{ number_format((float) $invoice->amount, 2) }}</span>
                                <span class="text-xs text-slate-400">ر.س</span>
                            </td>
                            <td class="hidden px-6 py-2.5 text-sm text-slate-600 sm:table-cell dark:text-slate-300">{{ $invoice->revenue_recognized_at?->format('Y/m/d') }}</td>
                            <td class="px-6 py-2.5">
                                <div class="flex items-center justify-end">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> معترف به
                                    </span>
                                </div>
                            </td>
                        @else
                            <td class="hidden px-6 py-2.5 text-sm text-slate-600 sm:table-cell dark:text-slate-300">{{ $invoice->due_date?->format('Y/m/d') }}</td>
                            <td class="px-6 py-2.5 whitespace-nowrap">
                                <span class="text-sm font-bold text-brand-navy dark:text-white">{{ number_format((float) $invoice->amount, 2) }}</span>
                                <span class="text-xs text-slate-400">ر.س</span>
                            </td>
                            <td class="px-6 py-2.5">
                                <div class="flex items-center justify-end">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium {{ $style[0] }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $style[1] }}"></span> {{ $invoice->status->label() }}
                                    </span>
                                </div>
                            </td>
                        @endif
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
        <span class="font-semibold text-slate-500 dark:text-slate-300">{{ $invoices->firstItem() ?? 0 }}</span>
        -
        <span class="font-semibold text-slate-500 dark:text-slate-300">{{ $invoices->lastItem() ?? 0 }}</span>
        من
        <span class="font-semibold text-slate-500 dark:text-slate-300">{{ $invoices->total() }}</span>
        فاتورة
    </p>

    @if ($invoices->hasPages())
        <div class="flex items-center gap-1">
            <button type="button" data-rev-page="{{ $invoices->currentPage() - 1 }}" @disabled($invoices->onFirstPage())
                    class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-brand/30 hover:bg-brand/10 hover:text-brand disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:border-slate-200 disabled:hover:bg-transparent disabled:hover:text-slate-500 dark:border-white/10 dark:text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6"/></svg>
            </button>

            @foreach ($invoices->getUrlRange(max(1, $invoices->currentPage() - 2), min($invoices->lastPage(), $invoices->currentPage() + 2)) as $page => $url)
                <button type="button" data-rev-page="{{ $page }}"
                        class="grid h-8 min-w-8 place-items-center rounded-lg border px-2 text-sm font-medium transition {{ $page === $invoices->currentPage() ? 'border-brand bg-brand text-white shadow shadow-brand/25' : 'border-slate-200 text-slate-500 hover:border-brand/30 hover:bg-brand/10 hover:text-brand dark:border-white/10 dark:text-slate-400' }}">
                    {{ $page }}
                </button>
            @endforeach

            <button type="button" data-rev-page="{{ $invoices->currentPage() + 1 }}" @disabled(! $invoices->hasMorePages())
                    class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-brand/30 hover:bg-brand/10 hover:text-brand disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:border-slate-200 disabled:hover:bg-transparent disabled:hover:text-slate-500 dark:border-white/10 dark:text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 6l-6 6 6 6"/></svg>
            </button>
        </div>
    @endif
</div>
