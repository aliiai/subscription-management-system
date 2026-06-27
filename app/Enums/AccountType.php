<?php

namespace App\Enums;

enum AccountType: string
{
    case Asset = 'asset';
    case Liability = 'liability';
    case Equity = 'equity';
    case Revenue = 'revenue';
    case Expense = 'expense';

    /**
     * Human readable label for the account type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Asset => 'أصول',
            self::Liability => 'التزامات',
            self::Equity => 'حقوق ملكية',
            self::Revenue => 'إيرادات',
            self::Expense => 'مصروفات',
        };
    }

    /**
     * The side on which this account type normally increases.
     */
    public function normalBalance(): string
    {
        return match ($this) {
            self::Asset, self::Expense => 'debit',
            self::Liability, self::Equity, self::Revenue => 'credit',
        };
    }
}
