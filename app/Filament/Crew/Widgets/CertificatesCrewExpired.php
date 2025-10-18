<?php

namespace App\Filament\Crew\Widgets;

use App\Enums\StatusDocumentFile;
use App\Filament\Crew\Resources\CrewAllResource;
use App\Models\CrewCertificates;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class CertificatesCrewExpired extends BaseWidget
{
    use HasWidgetShield;

    protected int | string | array $columnSpan = 4;
    protected static ?string $heading = 'Certificates Near Expiry';
    protected static string $view = 'filament.staff-crew.widgets.certificates-crew-expired';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CrewCertificates::query()->where('status', StatusDocumentFile::NearExpiry)

            )
            ->heading('')
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('tidak ada certificates crew near expiry')
            ->columns([
                Tables\Columns\TextColumn::make('nomor_sertifikat')->label('Nomor Sertifikat')->searchable(),
                Tables\Columns\TextColumn::make('applicant.nama_crew')->label('Crew')->icon('heroicon-o-user')->searchable(),
                Tables\Columns\TextColumn::make('kategori')->label('Kategory')->searchable(),
                Tables\Columns\TextColumn::make('nama_sertifikat')->label('Nama Sertifikat')->searchable(),
                Tables\Columns\TextColumn::make('tanggal_expired')->label('Expired')->badge()->color('danger'),
            ])->actions([
                Action::make('detail')->label('Detail')->button()->color('success')->icon('heroicon-o-eye')
                    ->hidden(!auth()->user()?->can('view_any_crewapplicants'))
                    ->url(fn($record) => CrewAllResource::getUrl('view', ['record' => $record->applicant_id])),
            ]);
    }
}
