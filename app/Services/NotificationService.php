<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantNotification;
use Illuminate\Support\Carbon;

class NotificationService
{
    /**
     * Currency symbols keyed by ISO code, falling back to the raw code.
     *
     * @var array<string, string>
     */
    protected array $currencySymbols = [
        'SAR' => 'ر.س', 'AED' => 'د.إ', 'QAR' => 'ر.ق', 'KWD' => 'د.ك',
        'BHD' => 'د.ب', 'OMR' => 'ر.ع', 'EGP' => 'ج.م', 'USD' => '$', 'EUR' => '€',
    ];

    /**
     * Persist a notification for the given tenant.
     *
     * @param  array{body?: string|null, icon?: string, color?: string, url?: string|null}  $attributes
     */
    public function record(Tenant $tenant, string $type, string $title, array $attributes = []): TenantNotification
    {
        return $tenant->notifications()->create([
            'type' => $type,
            'title' => $title,
            'body' => $attributes['body'] ?? null,
            'icon' => $attributes['icon'] ?? 'bell',
            'color' => $attributes['color'] ?? 'brand',
            'url' => $attributes['url'] ?? null,
        ]);
    }

    /**
     * A new customer was added.
     */
    public function customerCreated(Customer $customer): void
    {
        $this->record($customer->tenant, 'customer', 'عميل جديد', [
            'body' => "تمت إضافة العميل {$customer->name}.",
            'icon' => 'customers',
            'color' => 'brand',
            'url' => route('company.customers'),
        ]);
    }

    /**
     * A new subscription was created.
     */
    public function subscriptionCreated(Subscription $subscription): void
    {
        $customer = $subscription->customer?->name ?? 'عميل';
        $plan = $subscription->plan?->name ?? 'خطة';

        $this->record($subscription->tenant, 'subscription', 'اشتراك جديد', [
            'body' => "{$customer} اشترك في خطة {$plan}.",
            'icon' => 'subscriptions',
            'color' => 'blue',
            'url' => route('company.subscriptions'),
        ]);
    }

    /**
     * A new invoice was created.
     */
    public function invoiceCreated(Invoice $invoice): void
    {
        $customer = $invoice->customer?->name ?? 'عميل';

        $this->record($invoice->tenant, 'invoice', 'فاتورة جديدة', [
            'body' => "فاتورة {$invoice->invoice_number} بمبلغ {$this->money($invoice->amount, $invoice->currency)} للعميل {$customer}.",
            'icon' => 'invoices',
            'color' => 'amber',
            'url' => route('company.invoices.show', $invoice),
        ]);
    }

    /**
     * A batch of invoices was generated for a billing period.
     */
    public function invoicesGenerated(Tenant $tenant, int $count, Carbon $period): void
    {
        $this->record($tenant, 'invoice', 'توليد فواتير الفترة', [
            'body' => "تم توليد {$count} فاتورة لفترة {$period->format('Y/m')}.",
            'icon' => 'invoices',
            'color' => 'amber',
            'url' => route('company.invoices'),
        ]);
    }

    /**
     * A payment was received against an invoice.
     */
    public function paymentReceived(Payment $payment): void
    {
        $customer = $payment->customer?->name ?? 'عميل';
        $number = $payment->invoice?->invoice_number ?? '';

        $this->record($payment->tenant, 'payment', 'دفعة مستلمة', [
            'body' => "تم استلام {$this->money($payment->amount, $payment->invoice?->currency)} من {$customer} للفاتورة {$number}.",
            'icon' => 'payments',
            'color' => 'emerald',
            'url' => route('company.payments'),
        ]);
    }

    /**
     * An invoice was voided.
     */
    public function invoiceVoided(Invoice $invoice): void
    {
        $this->record($invoice->tenant, 'invoice', 'إلغاء فاتورة', [
            'body' => "تم إلغاء الفاتورة {$invoice->invoice_number}.",
            'icon' => 'close',
            'color' => 'rose',
            'url' => route('company.invoices'),
        ]);
    }

    /**
     * Revenue was recognized for a period.
     */
    public function revenueRecognized(Tenant $tenant, int $count, float $amount, Carbon $period): void
    {
        $this->record($tenant, 'revenue', 'الاعتراف بالإيراد', [
            'body' => "تم الاعتراف بإيراد {$count} فاتورة بإجمالي {$this->money($amount, 'SAR')} لفترة {$period->format('Y/m')}.",
            'icon' => 'revenue',
            'color' => 'brand',
            'url' => route('company.income-statement'),
        ]);
    }

    /**
     * Format a monetary value with its currency symbol.
     */
    protected function money(float|string|null $amount, ?string $currency): string
    {
        $symbol = $this->currencySymbols[$currency ?? 'SAR'] ?? ($currency ?? '');

        return trim(number_format((float) $amount, 2).' '.$symbol);
    }
}
