<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

#[Fillable([
    'tenant_id', 'customer_id', 'subscription_id', 'invoice_number',
    'issue_date', 'due_date', 'period_start', 'period_end',
    'amount', 'amount_paid', 'currency', 'status', 'revenue_recognized_at',
])]
class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'period_start' => 'date',
            'period_end' => 'date',
            'amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'status' => InvoiceStatus::class,
            'revenue_recognized_at' => 'datetime',
        ];
    }

    /**
     * Generate the next sequential invoice number for a tenant (e.g. INV-0001).
     */
    public static function nextNumberFor(Tenant $tenant): string
    {
        return 'INV-'.str_pad((string) ($tenant->invoices()->count() + 1), 4, '0', STR_PAD_LEFT);
    }

    /**
     * The outstanding balance still owed on the invoice.
     */
    public function balance(): float
    {
        return round((float) $this->amount - (float) $this->amount_paid, 2);
    }

    /**
     * Whether the invoice is overdue (past due date and not fully settled).
     */
    public function isOverdue(): bool
    {
        return $this->status !== InvoiceStatus::Paid
            && $this->status !== InvoiceStatus::Void
            && $this->due_date !== null
            && $this->due_date->isPast();
    }

    /**
     * Whether the invoice's revenue has already been recognized.
     */
    public function isRecognized(): bool
    {
        return $this->revenue_recognized_at !== null;
    }

    /**
     * Scope invoices that are eligible for revenue recognition in the given period.
     *
     * Eligible invoices are not voided, have an unrecognized positive amount, and
     * their service period ends within the target month (accrual basis).
     *
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    public function scopeEligibleForRecognition(Builder $query, Carbon $period): Builder
    {
        return $query
            ->whereNull('revenue_recognized_at')
            ->where('status', '!=', InvoiceStatus::Void)
            ->where('amount', '>', 0)
            ->whereBetween('period_end', [
                $period->copy()->startOfMonth(),
                $period->copy()->endOfMonth(),
            ]);
    }

    /**
     * The tenant (company) that owns the invoice.
     *
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * The customer the invoice is billed to.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * The subscription that generated the invoice, if any.
     *
     * @return BelongsTo<Subscription, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * The payments recorded against the invoice.
     *
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * The journal entries posted for this invoice.
     *
     * @return MorphMany<JournalEntry, $this>
     */
    public function journalEntries(): MorphMany
    {
        return $this->morphMany(JournalEntry::class, 'source');
    }
}
