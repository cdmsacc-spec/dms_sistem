<?php

namespace App\Filament\Widgets\Dashboard;

use App\Enums\StatusKontrakCrew;
use App\Filament\Crew\Resources\CrewAllResource;
use App\Models\CrewCertificates;
use App\Models\CrewDocuments;
use App\Models\CrewPkl;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class KontrakCrewNearExpiry extends BaseWidget
{
    use HasWidgetShield;

    protected static string $view = 'filament.staff-crew.widgets.kontrak-crew-expired';

    protected int | string | array $columnSpan = 4;
    public function table(Table $table): Table
    {
        return $table
            ->query(
                CrewPkl::query()->where('status_kontrak', StatusKontrakCrew::Active->value)->where('isNearExpiry', true)

            )
            ->heading('')
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('tidak ada kontrak crew yang near expiry')
            ->columns([
                Tables\Columns\TextColumn::make('nomor_document')->label('Nomor')->searchable(),
                Tables\Columns\TextColumn::make('crew.nama_crew')->label('Crew')->icon('heroicon-o-user')->searchable(),
                Tables\Columns\TextColumn::make('perusahaan.nama_perusahaan')->label('Perusahaan')->searchable(),
                Tables\Columns\TextColumn::make('kapal.nama_kapal')->label('Kapal')->searchable(),
                Tables\Columns\TextColumn::make('end_date')->label('Expired'),
            ])->actions([
                 Action::make('detail')->label('Detail')->button()->color('success')->icon('heroicon-o-eye')
                    ->hidden(!auth()->user()?->can('view_any_crewapplicants'))
                    ->url(fn($record) => CrewAllResource::getUrl('detail_pkl', ['record' => $record->id])),
            ]);
    }
}
