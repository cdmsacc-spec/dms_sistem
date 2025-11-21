<?php

namespace App\Filament\Crew\Pages;

use App\Filament\Crew\Widgets\CrewActivityBerjalan;
use App\Filament\Crew\Widgets\CrewAnalytic;
use App\Filament\Crew\Widgets\CrewJabatanGroup;
use App\Filament\Crew\Widgets\CrewUsiaGroup;
use App\Filament\Crew\Widgets\DokumenCrewNearExpiry;
use App\Filament\Crew\Widgets\KontrakCrewNearExpiry;
use App\Filament\Crew\Widgets\SertifikatCrewNearExpiry;
use App\Filament\Widgets\AccountWidget;
use App\Filament\Widgets\DateWidget;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Dashboard as PagesDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Size;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends PagesDashboard
{
    use HasFiltersForm;

    public static function getNavigationIcon(): string | BackedEnum | Htmlable | null
    {
        return 'heroicon-o-squares-2x2';
    }

    public function persistsFiltersInSession(): bool
    {
        return false;
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return ["sm"=>1,"md"=>1,"lg"=>1,"xl"=>2];
    }
    public function getHeaderWidgets(): array
    {
        return [
            AccountWidget::class,
            DateWidget::class,
        ];
    }

    public function getColumns(): array|int
    {
        return 4;
    }
    public function getWidgets(): array
    {
        return [
            CrewAnalytic::class,
            CrewActivityBerjalan::class,
            CrewUsiaGroup::class,
            CrewJabatanGroup::class,
        ];
    }

    public function getFooterWidgets(): array
    {
        return [
            DokumenCrewNearExpiry::class,
            SertifikatCrewNearExpiry::class,
            KontrakCrewNearExpiry::class,
        ];
    }

    public function filtersForm(Schema $form)
    {
        return $form->schema([
            Section::make('Filter')
                ->columnSpanFull()
                ->extraAttributes(['class' => 'section-filter-dashboard'])
                ->schema([
                    TextInput::make('periode')
                        ->label('Periode')
                        ->type('month')
                        ->placeholder('Tanggal')
                        ->columnSpan(1),
                ])
                ->headerActions([
                    Action::make('resetFilter')
                        ->label('Reset Filter')
                        ->color('warning')
                        ->button()
                        ->size(Size::Medium)
                        ->action(function () use ($form) {
                            $form->fill([]);
                        }),
                ])
        ]);
    }
}
