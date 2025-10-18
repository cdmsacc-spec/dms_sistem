<?php

namespace App\Filament\Crew\Pages;

use App\Filament\Crew\Widgets\AccountWidgets;
use App\Filament\Crew\Widgets\CertificatesCrewExpired;
use App\Filament\Crew\Widgets\CrewAnalyticStats;
use App\Filament\Crew\Widgets\CrewPerjabatan;
use App\Filament\Crew\Widgets\DateWidgets;
use App\Filament\Crew\Widgets\DocumentCrewExpired;
use App\Filament\Crew\Widgets\KontrakCrewNearExpiry;
use App\Filament\Crew\Widgets\MutasiBerjalan;
use App\Filament\Crew\Widgets\PenggolonganUsia;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\Carbon;

class Dashboard extends \Filament\Pages\Dashboard
{
    use HasFiltersForm;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Dashboard';
    public function persistsFiltersInSession(): bool
    {
        return false;
    }

    public function getColumns(): array|int|string
    {
        return 4;
    }
    public function getHeaderWidgets(): array
    {
        return [
            AccountWidgets::class,
            DateWidgets::class,

        ];
    }
    public function getWidgets(): array
    {
        return [
            CrewAnalyticStats::class,
            MutasiBerjalan::class,
            PenggolonganUsia::class,
            CrewPerjabatan::class,
            DocumentCrewExpired::class,
            CertificatesCrewExpired::class,
            KontrakCrewNearExpiry::class
        ];
    }

    public function filtersForm(Form $form)
    {
        return $form->schema([
            Section::make('Filter')
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
                        ->color('danger')
                        ->button()
                        ->size(ActionSize::ExtraLarge)
                        ->action(function () use ($form) {
                            $form->fill([]);
                        }),
                ])
        ]);
    }
}
