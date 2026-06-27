<x-layouts.admin title="العملاء">
    {{-- header --}}
    <div data-animate="auth-card" class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('admin.dashboard') }}" class="transition hover:text-brand">لوحة التحكم</a>
                <span>/</span>
                <span class="text-slate-500 dark:text-slate-300">العملاء</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-brand-navy sm:text-3xl dark:text-white">العملاء</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">جميع عملاء الشركات المسجّلة في المنصة.</p>
        </div>

        <span class="inline-flex shrink-0 items-center gap-2 self-start rounded-xl bg-brand/10 px-4 py-2.5 text-sm font-semibold text-brand">
            <x-icon name="customers" class="h-5 w-5" />
            {{ $customers->count() }} عميل
        </span>
    </div>

    {{-- table / empty state --}}
    <div data-animate="auth-card" class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">
        @if ($customers->isEmpty())
            <div class="flex flex-col items-center justify-center gap-4 px-6 py-16 text-center">
                <span class="grid h-16 w-16 place-items-center rounded-2xl bg-brand/10 text-brand">
                    <x-icon name="customers" class="h-8 w-8" />
                </span>
                <div>
                    <h3 class="text-lg font-semibold text-brand-navy dark:text-white">لا يوجد عملاء بعد</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">سيظهر هنا عملاء الشركات بمجرد إضافتهم.</p>
                </div>
            </div>
        @else
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-6 py-4 dark:border-white/10">
                <div class="flex items-center gap-2">
                    <h2 class="text-sm font-bold text-brand-navy dark:text-white">قائمة العملاء</h2>
                    <span class="rounded-full bg-brand/10 px-2 py-0.5 text-xs font-semibold text-brand">{{ $customers->count() }}</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] border-collapse text-right">
                    <thead>
                        <tr class="bg-slate-50/70 text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:bg-white/[0.02]">
                            <th class="px-6 py-3.5 font-semibold">العميل</th>
                            <th class="px-6 py-3.5 font-semibold">الشركة</th>
                            <th class="px-6 py-3.5 font-semibold">الخطة</th>
                            <th class="px-6 py-3.5 font-semibold">الهاتف</th>
                            <th class="hidden px-6 py-3.5 font-semibold lg:table-cell">تاريخ الإضافة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @foreach ($customers as $customer)
                            <tr class="transition hover:bg-slate-50/80 dark:hover:bg-white/[0.03]">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-brand to-brand-blue text-sm font-bold text-white">
                                            {{ mb_substr($customer->name, 0, 1) }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-brand-navy dark:text-white">{{ $customer->name }}</p>
                                            <p class="mt-0.5 truncate text-xs text-slate-400" dir="ltr">{{ $customer->email ?? '—' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 text-sm text-slate-600 dark:text-slate-300">
                                        <x-icon name="building" class="h-4 w-4 text-slate-400" />
                                        {{ $customer->tenant?->name ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($customer->plan)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-brand/10 px-2.5 py-1 text-xs font-medium text-brand">
                                            <x-icon name="plans" class="h-3.5 w-3.5" /> {{ $customer->plan->name }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-300 dark:text-slate-600">بدون خطة</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300" dir="ltr">
                                    <span class="block text-right">{{ $customer->phone ?? '—' }}</span>
                                </td>
                                <td class="hidden px-6 py-4 text-sm text-slate-500 lg:table-cell dark:text-slate-400">
                                    {{ $customer->created_at?->format('Y/m/d') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-6 py-3 text-xs text-slate-400 dark:border-white/10">
                إجمالي العملاء: <span class="font-semibold text-slate-500 dark:text-slate-300">{{ $customers->count() }}</span>
            </div>
        @endif
    </div>
</x-layouts.admin>
