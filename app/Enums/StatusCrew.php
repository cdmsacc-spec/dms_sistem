<?php

namespace App\Enums;

enum StatusCrew: string
{
    case Active = 'Active';
    case Inactive = 'Inactive';
    case Standby = 'Standby';
    case ReadyForInterview = 'Ready For Interview';
    case Draft = 'Draft';
    case Rejected = 'Rejected';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Standby => 'Standby',
            self::ReadyForInterview => 'Ready For Interview',
            self::Draft => 'Draft',
            self::Rejected => 'Rejected',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
