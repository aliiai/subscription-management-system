@php $reportTenant = auth()->user()?->tenant; @endphp

{{-- print-only report header --}}
<div class="hidden print:block">
    <div class="flex items-start justify-between gap-4 border-b-2 border-brand pb-4">
        <div class="flex items-center gap-3">
            <span class="grid h-14 w-14 shrink-0 place-items-center overflow-hidden rounded-2xl bg-brand text-2xl font-bold text-white">
                @if ($reportTenant?->logo_url)
                    <img src="{{ $reportTenant->logo_url }}" alt="" class="h-full w-full object-cover">
                @else
                    {{ mb_substr($reportTenant?->name ?? 'A', 0, 1) }}
                @endif
            </span>
            <div>
                <p class="text-lg font-bold text-brand-navy">{{ $reportTenant?->name }}</p>
                @if ($reportTenant?->email)
                    <p class="text-xs text-slate-500" dir="ltr">{{ $reportTenant->email }}</p>
                @endif
            </div>
        </div>
        <div class="text-left">
            <p class="text-lg font-bold text-brand-navy">قائمة الدخل</p>
            <p class="mt-0.5 text-xs text-slate-600">الفترة: <span dir="ltr">{{ $report['from']->format('Y/m/d') }} - {{ $report['to']->format('Y/m/d') }}</span></p>
            <p class="text-[11px] text-slate-400">تاريخ الإصدار: <span dir="ltr">{{ now()->format('Y/m/d H:i') }}</span></p>
        </div>
    </div>
</div>

{{-- period label (screen only) --}}
<p class="shrink-0 text-sm text-slate-500 print:hidden dark:text-slate-400">
    الفترة: <span class="font-semibold text-brand-navy dark:text-white" dir="ltr">{{ $report['from']->format('Y/m/d') }} - {{ $report['to']->format('Y/m/d') }}</span>
</p>

{{-- KPIs --}}
<div class="grid shrink-0 grid-cols-1 gap-3 sm:grid-cols-3">
    @php
        $cards = [
            ['إجمالي الإيرادات', number_format($report['total_revenue'], 2).' ر.س', 'revenue', 'text-emerald-600 bg-emerald-50 dark:bg-emerald-500/10'],
            ['إجمالي المصروفات', number_format($report['total_expenses'], 2).' ر.س', 'payments', 'text-rose-600 bg-rose-50 dark:bg-rose-500/10'],
            ['صافي الدخل', number_format($report['net_income'], 2).' ر.س', 'income', 'text-brand bg-brand/10'],
        ];
    @endphp
    @foreach ($cards as $card)
        <div class="print-avoid-break rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm dark:border-white/10 dark:bg-slate-900">
            <div class="flex items-center gap-2.5">
                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg {{ $card[3] }}">
                    <x-icon name="{{ $card[2] }}" class="h-4 w-4" />
                </span>
                <div class="min-w-0">
                    <p class="truncate text-[11px] font-medium text-slate-500 dark:text-slate-400">{{ $card[0] }}</p>
                    <p class="truncate text-base font-bold text-brand-navy dark:text-white">{{ $card[1] }}</p>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- statement --}}
<div class="print-avoid-break flex min-h-0 flex-1 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">
    <div class="shrink-0 border-b border-slate-100 px-6 py-3.5 dark:border-white/10">
        <h2 class="text-base font-bold text-brand-navy dark:text-white">بيان الدخل</h2>
    </div>

    @if ($report['total_revenue'] == 0 && empty($report['expense_lines']))
        <div class="flex flex-1 flex-col items-center justify-center gap-4 px-6 text-center">
            <span class="grid h-16 w-16 place-items-center rounded-2xl bg-brand/10 text-brand">
                <x-icon name="income" class="h-8 w-8" />
            </span>
            <div>
                <h3 class="text-base font-semibold text-brand-navy dark:text-white">لا توجد إيرادات معترف بها في هذه الفترة</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    تظهر الإيرادات هنا بعد تشغيل
                    <a href="{{ route('company.revenue-recognition') }}" class="font-semibold text-brand hover:underline">الاعتراف بالإيرادات</a>
                    لفواتير الفترة.
                </p>
            </div>
        </div>
    @else
        <div class="brand-scroll min-h-0 flex-1 divide-y divide-slate-100 overflow-y-auto dark:divide-white/5">
            {{-- revenue section --}}
            <div class="px-6 py-4">
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">الإيرادات</h3>
                <div class="space-y-2">
                    @forelse ($report['revenue_lines'] as $line)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-600 dark:text-slate-300">{{ $line['name'] }}</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ number_format($line['balance'], 2) }} <span class="text-xs text-slate-400">ر.س</span></span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">لا توجد إيرادات.</p>
                    @endforelse
                </div>
                <div class="mt-3 flex items-center justify-between border-t border-dashed border-slate-200 pt-3 text-sm dark:border-white/10">
                    <span class="font-semibold text-brand-navy dark:text-white">إجمالي الإيرادات</span>
                    <span class="font-bold text-emerald-600">{{ number_format($report['total_revenue'], 2) }} <span class="text-xs text-slate-400">ر.س</span></span>
                </div>
            </div>

            {{-- expenses section --}}
            <div class="px-6 py-4">
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">المصروفات</h3>
                <div class="space-y-2">
                    @forelse ($report['expense_lines'] as $line)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-600 dark:text-slate-300">{{ $line['name'] }}</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ number_format($line['balance'], 2) }} <span class="text-xs text-slate-400">ر.س</span></span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">لا توجد مصروفات مسجّلة.</p>
                    @endforelse
                </div>
                <div class="mt-3 flex items-center justify-between border-t border-dashed border-slate-200 pt-3 text-sm dark:border-white/10">
                    <span class="font-semibold text-brand-navy dark:text-white">إجمالي المصروفات</span>
                    <span class="font-bold text-rose-600">{{ number_format($report['total_expenses'], 2) }} <span class="text-xs text-slate-400">ر.س</span></span>
                </div>
            </div>
        </div>

        {{-- net income (pinned footer) --}}
        <div class="flex shrink-0 items-center justify-between border-t border-slate-100 bg-brand/5 px-6 py-3.5 dark:border-white/10 dark:bg-brand/10">
            <span class="text-base font-bold text-brand-navy dark:text-white">صافي الدخل</span>
            <span class="text-lg font-bold {{ $report['net_income'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ number_format($report['net_income'], 2) }} <span class="text-xs text-slate-400">ر.س</span></span>
        </div>
    @endif
</div>
