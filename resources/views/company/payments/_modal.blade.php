@php
    $currencySymbolsModal = [
        'SAR' => 'ر.س', 'AED' => 'د.إ', 'QAR' => 'ر.ق', 'KWD' => 'د.ك',
        'BHD' => 'د.ب', 'OMR' => 'ر.ع', 'EGP' => 'ج.م', 'USD' => '$', 'EUR' => '€',
    ];
@endphp

<div data-modal="payment" class="fixed inset-0 z-50 hidden">
    <div data-modal-overlay class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
    <div data-modal-dismiss class="absolute inset-0 flex items-center justify-center p-4">
        <div data-modal-panel class="w-full max-w-lg overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-white/10 dark:bg-slate-900">
            <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4 dark:border-white/10">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/25">
                    <x-icon name="payments" class="h-5 w-5" />
                </span>
                <div class="flex-1">
                    <h3 class="text-base font-bold text-brand-navy dark:text-white">تسجيل دفعة</h3>
                    <p class="text-xs text-slate-400">سجّل دفعة مقابل فاتورة مفتوحة.</p>
                </div>
                <button type="button" data-modal-close class="grid h-9 w-9 shrink-0 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-white/5">
                    <x-icon name="close" class="h-5 w-5" />
                </button>
            </div>

            <form method="POST" action="{{ route('company.payments.store') }}" data-payment-form class="grid grid-cols-2 gap-x-4 gap-y-3.5 px-6 py-5">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ url()->current() }}">

                <div class="col-span-2">
                    <x-input-label for="pay_invoice" value="الفاتورة" />
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 start-0 grid w-11 place-items-center text-slate-400"><x-icon name="invoices" class="h-5 w-5" /></span>
                        <select id="pay_invoice" name="invoice_id" required data-payment-invoice
                                class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 py-2.5 pe-4 ps-11 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                            <option value="">اختر الفاتورة</option>
                            @foreach (($openInvoices ?? collect()) as $openInvoice)
                                <option value="{{ $openInvoice->id }}" data-balance="{{ $openInvoice->balance() }}" @selected(old('invoice_id') == $openInvoice->id)>
                                    {{ $openInvoice->invoice_number }} · {{ $openInvoice->customer?->name }} (متبقي {{ number_format($openInvoice->balance(), 2) }} {{ $currencySymbolsModal[$openInvoice->currency] ?? $openInvoice->currency }})
                                </option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-slate-400"><x-icon name="chevron-down" class="h-4 w-4" /></span>
                    </div>
                    <x-input-error :messages="$errors->get('invoice_id')" />
                </div>

                <div class="col-span-2 sm:col-span-1">
                    <x-input-label for="pay_amount" value="المبلغ" />
                    <input id="pay_amount" type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" required placeholder="0.00" data-payment-amount
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                    <x-input-error :messages="$errors->get('amount')" />
                </div>

                <div class="col-span-2 sm:col-span-1">
                    <x-input-label for="pay_date" value="تاريخ الدفع" />
                    <input id="pay_date" type="date" name="paid_at" value="{{ old('paid_at', now()->format('Y-m-d')) }}" required
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                    <x-input-error :messages="$errors->get('paid_at')" />
                </div>

                <div class="col-span-2 sm:col-span-1">
                    <x-input-label for="pay_method" value="طريقة الدفع" />
                    <div class="relative">
                        <select id="pay_method" name="method" required
                                class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                            @foreach (\App\Enums\PaymentMethod::cases() as $method)
                                <option value="{{ $method->value }}" @selected(old('method', 'cash') === $method->value)>{{ $method->label() }}</option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-slate-400"><x-icon name="chevron-down" class="h-4 w-4" /></span>
                    </div>
                    <x-input-error :messages="$errors->get('method')" />
                </div>

                <div class="col-span-2 sm:col-span-1">
                    <x-input-label for="pay_reference" value="المرجع (اختياري)" />
                    <input id="pay_reference" name="reference" value="{{ old('reference') }}" placeholder="رقم العملية أو ملاحظة" dir="ltr"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/25 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                    <x-input-error :messages="$errors->get('reference')" />
                </div>

                <div class="col-span-2 mt-1 flex items-center justify-end gap-3 border-t border-slate-100 pt-4 dark:border-white/10">
                    <button type="button" data-modal-close class="rounded-xl px-4 py-2.5 text-sm font-medium text-slate-500 transition hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-white/5">إلغاء</button>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-l from-emerald-500 to-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/25 transition hover:scale-[1.02]">
                        <x-icon name="save" class="h-4 w-4" /> حفظ الدفعة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
