<?php

namespace App\Filament\Crew\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;

class DateWidgets extends Widget
{
    protected static string $view = 'filament.widgets.date-widgets';
    use HasWidgetShield;

    protected static bool $isLazy = false;
}
