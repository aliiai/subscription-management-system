{{-- as-of label --}}
<p class="shrink-0 text-sm text-slate-500 dark:text-slate-400">
    كما في: <span class="font-semibold text-brand-navy dark:text-white" dir="ltr">{{ $report['as_of']->format('Y/m/d') }}</span>
</p>

{{-- KPIs --}}
<div class="grid shrink-0 grid-cols-1 gap-3 sm:grid-cols-3">
    @php
        $kpiCards = [
            ['إجمالي الأصول', number_format($report['total_assets'], 2).' ر.س', 'balance', 'text-emerald-600 bg-emerald-50 dark:bg-emerald-500/10'],
            ['إجمالي الالتزامات', number_format($report['total_liabilities'], 2).' ر.س', 'invoices', 'text-amber-600 bg-amber-50 dark:bg-amber-500/10'],
            ['حقوق الملكية', number_format($report['total_equity'], 2).' ر.س', 'revenue', 'text-brand bg-brand/10'],
        ];
    @endphp
    @foreach ($kpiCards as $card)
        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm dark:border-white/10 dark:bg-slate-900">
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

{{-- two columns --}}
<div class="grid min-h-0 flex-1 grid-cols-1 gap-3 lg:grid-cols-2">
    {{-- assets --}}
    <div class="flex min-h-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">
        <div class="shrink-0 border-b border-slate-100 bg-slate-50/70 px-6 py-3.5 dark:border-white/10 dark:bg-white/[0.02]">
            <h2 class="text-base font-bold text-brand-navy dark:text-white">الأصول</h2>
        </div>
        <div class="brand-scroll min-h-0 flex-1 divide-y divide-slate-100 overflow-y-auto px-6 dark:divide-white/5">
            @forelse ($report['asset_lines'] as $line)
                <div class="flex items-center justify-between py-3 text-sm">
                    <span class="text-slate-600 dark:text-slate-300">{{ $line['name'] }}</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-200">{{ number_format($line['balance'], 2) }} <span class="text-xs text-slate-400">ر.س</span></span>
                </div>
            @empty
                <div class="py-3.5 text-sm text-slate-400">لا توجد حسابات أصول.</div>
            @endforelse
        </div>
        <div class="flex shrink-0 items-center justify-between border-t border-slate-100 bg-emerald-50/40 px-6 py-3.5 dark:border-white/10 dark:bg-emerald-500/5">
            <span class="font-bold text-brand-navy dark:text-white">إجمالي الأصول</span>
            <span class="font-bold text-emerald-600">{{ number_format($report['total_assets'], 2) }} <span class="text-xs text-slate-400">ر.س</span></span>
        </div>
    </div>

    {{-- liabilities + equity --}}
    <div class="flex min-h-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">
        <div class="shrink-0 border-b border-slate-100 bg-slate-50/70 px-6 py-3.5 dark:border-white/10 dark:bg-white/[0.02]">
            <h2 class="text-base font-bold text-brand-navy dark:text-white">الالتزامات وحقوق الملكية</h2>
        </div>
        <div class="brand-scroll min-h-0 flex-1 overflow-y-auto px-6">
            <p class="pt-4 text-xs font-semibold uppercase tracking-wider text-slate-400">الالتزامات</p>
            <div class="divide-y divide-slate-100 dark:divide-white/5">
                @forelse ($report['liability_lines'] as $line)
                    <div class="flex items-center justify-between py-3 text-sm">
                        <span class="text-slate-600 dark:text-slate-300">{{ $line['name'] }}</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-200">{{ number_format($line['balance'], 2) }} <span class="text-xs text-slate-400">ر.س</span></span>
                    </div>
                @empty
                    <div class="py-3.5 text-sm text-slate-400">لا توجد التزامات.</div>
                @endforelse
            </div>

            <p class="pt-4 text-xs font-semibold uppercase tracking-wider text-slate-400">حقوق الملكية</p>
            <div class="divide-y divide-slate-100 pb-2 dark:divide-white/5">
                @foreach ($report['equity_lines'] as $line)
                    <div class="flex items-center justify-between py-3 text-sm">
                        <span class="text-slate-600 dark:text-slate-300">{{ $line['name'] }}</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-200">{{ number_format($line['balance'], 2) }} <span class="text-xs text-slate-400">ر.س</span></span>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="flex shrink-0 items-center justify-between border-t border-slate-100 bg-amber-50/40 px-6 py-3.5 dark:border-white/10 dark:bg-amber-500/5">
            <span class="font-bold text-brand-navy dark:text-white">إجمالي الالتزامات وحقوق الملكية</span>
            <span class="font-bold text-amber-600">{{ number_format($report['total_liabilities_equity'], 2) }} <span class="text-xs text-slate-400">ر.س</span></span>
        </div>
    </div>
</div>

{{-- balance check --}}
<div class="shrink-0">
    @if ($report['balanced'])
        <div class="flex items-center justify-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-600 dark:border-emerald-500/20 dark:bg-emerald-500/10">
            <x-icon name="check-double" class="h-5 w-5 shrink-0" />
            <span class="text-center">الميزانية متوازنة: الأصول ({{ number_format($report['total_assets'], 2) }}) = الالتزامات + حقوق الملكية ({{ number_format($report['total_liabilities_equity'], 2) }})</span>
        </div>
    @else
        <div class="flex items-center justify-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-600 dark:border-rose-500/20 dark:bg-rose-500/10">
            <x-icon name="bell" class="h-5 w-5 shrink-0" />
            <span class="text-center">تحذير: الميزانية غير متوازنة (فرق {{ number_format($report['total_assets'] - $report['total_liabilities_equity'], 2) }} ر.س)</span>
        </div>
    @endif
</div>
