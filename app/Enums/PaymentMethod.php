<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Transfer = 'transfer';
    case Card = 'card';

    /**
     * Human readable label for the payment method.
     */
    public function label(): string
    {
        return match ($this) {
            self::Cash => 'نقداً',
            self::Transfer => 'تحويل بنكي',
            self::Card => 'بطاقة',
        };
    }

    /**
     * All method values.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
