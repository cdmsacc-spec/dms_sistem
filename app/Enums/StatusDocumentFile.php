<?php

namespace App\Enums;

enum StatusDocumentFile: string
{
    case UpToDate = 'UpToDate';
    case NearExpiry = 'Near Expiry';
    case Expired = 'Expired';

    public function label(): string
    {
        return match ($this) {
            self::UpToDate => 'UpToDate',
            self::NearExpiry => 'Near Expiry',
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
