<?php

namespace App\Filament\StaffCrew\Widgets;

use App\Filament\StaffCrew\Resources\CrewOverviewResource;
use App\Models\CrewCertificates;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

class CertificatesCrewExpired extends BaseWidget
{
    protected int | string | array $columnSpan = 4;
    public function table(Table $table): Table
    {
        $now = Carbon::now();
        $jumlahExpired = CrewCertificates::whereDate('tanggal_expired', '<', $now)->count();
        return $table
            ->query(
                CrewCertificates::query()->whereDate('tanggal_expired', '<', $now)

            )
            ->heading('Total Certificates Crew Expired ' . $jumlahExpired)
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum document crew yang expired')
            ->columns([
                Tables\Columns\TextColumn::make('applicant.nama_crew')->label('Crew')->icon('heroicon-o-user')->searchable(),
                Tables\Columns\TextColumn::make('kategori')->label('Kategory')->searchable(),
                Tables\Columns\TextColumn::make('nama_sertifikat')->label('Nama Sertifikat')->searchable(),
                Tables\Columns\TextColumn::make('tanggal_expired')->label('Expired')->badge()->color('danger'),
            ])->actions([
                Action::make('detail')->label('Detail')->button()->color('success')->icon('heroicon-o-eye')
                    ->url(fn($record) => CrewOverviewResource::getUrl('view', ['record' => $record->applicant_id])),
            ]);
    }
}
