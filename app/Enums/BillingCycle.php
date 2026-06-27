<?php

namespace App\Enums;

enum BillingCycle: string
{
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case SemiAnnual = 'semi_annual';
    case Yearly = 'yearly';

    /**
     * Human readable label for the billing cycle.
     */
    public function label(): string
    {
        return match ($this) {
            self::Monthly => 'شهري',
            self::Quarterly => 'ربع سنوي',
            self::SemiAnnual => 'نصف سنوي',
            self::Yearly => 'سنوي',
        };
    }

    /**
     * All billing cycle values.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
