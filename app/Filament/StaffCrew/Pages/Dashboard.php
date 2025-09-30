<?php

namespace App\Filament\StaffCrew\Pages;

use App\Filament\StaffCrew\Widgets\CertificatesCrewExpired;
use App\Filament\StaffCrew\Widgets\CrewAnalytic;
use App\Filament\StaffCrew\Widgets\CrewAnalyticStats;
use App\Filament\StaffCrew\Widgets\CrewPerjabatan;
use App\Filament\StaffCrew\Widgets\DocumentCrewExpired;
use App\Filament\StaffCrew\Widgets\MutasiBerjalan;
use App\Filament\StaffCrew\Widgets\MutasiBulanBerjalan;
use App\Filament\StaffCrew\Widgets\PenggolonganUsia;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Pages\Page;
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

    public function getWidgets(): array
    {
        return [
            CrewAnalyticStats::class,
            MutasiBerjalan::class,
            PenggolonganUsia::class,
            CrewPerjabatan::class,
            DocumentCrewExpired::class,
            CertificatesCrewExpired::class,
        ];
    }

    public function filtersForm(Form $form)
    {
        return $form->schema([
            Section::make('Filter')
                ->schema([
                    TextInput::make('periode')
                        ->label('Periode')
                        ->type('month')
                        ->default(Carbon::now()->format('Y-m'))
                        ->placeholder('Tanggal')
                        ->columnSpan(1),
                ])
                ->headerActions([
                    Action::make('resetFilter')
                        ->label('Reset Filter')
                        ->color('danger')
                        ->button()
                        ->action(function () use ($form) {
                            $form->fill([]);
                        }),
                ])
        ]);
    }
}
