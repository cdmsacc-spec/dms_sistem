<?php

namespace App\Enums;

enum StatusKontrakCrew: string
{
    case WaitingApproval = 'Waiting Approval';
    case Expired = 'Expired';
    case Active = 'Active';


    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::WaitingApproval => 'Waiting Approval',
            self::Expired => 'Expired',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
