<?php

namespace App\Enums;

enum UserRole: string
{
    case Company = 'company';

    /**
     * Human readable label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::Company => 'شركة',
        };
    }

    /**
     * The dashboard route name for the role.
     */
    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Company => 'company.dashboard',
        };
    }

    /**
     * All role values.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
