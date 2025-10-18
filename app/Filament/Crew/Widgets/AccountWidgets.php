<?php

namespace App\Filament\Crew\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;

class AccountWidgets extends Widget
{
    protected static string $view = 'filament.widgets.account-widgets';
        use HasWidgetShield;

}
