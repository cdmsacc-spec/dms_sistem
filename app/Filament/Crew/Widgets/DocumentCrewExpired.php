<?php

namespace App\Filament\Crew\Widgets;

use App\Enums\StatusDocumentFile;
use App\Filament\Crew\Resources\CrewAllResource;
use App\Filament\Crew\Resources\CrewOverviewResource;
use App\Models\CrewCertificates;
use App\Models\CrewDocuments;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DocumentCrewExpired extends BaseWidget
{
    use HasWidgetShield;
    protected static string $view = 'filament.staff-crew.widgets.document-crew-expired';
    protected static ?string $heading = 'Document Near Expiry';


    protected int | string | array $columnSpan = 4;
    public function table(Table $table): Table
    {
        return $table
            ->query(
                CrewDocuments::query()->where('status', StatusDocumentFile::NearExpiry)
            )
            ->heading('')
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('tidak ada certificates crew near expiry')
            ->columns([
                Tables\Columns\TextColumn::make('nomor_document')->label('Nomor Dokumen')->searchable(),
                Tables\Columns\TextColumn::make('applicant.nama_crew')->label('Crew')->icon('heroicon-o-user')->searchable(),
                Tables\Columns\TextColumn::make('kategory')->label('Kategory')->searchable(),
                Tables\Columns\TextColumn::make('jenis_document')->label('Jenis')->searchable(),
                Tables\Columns\TextColumn::make('tanggal_expired')->label('Expired')->badge()->color('danger'),
            ])->actions([
                Action::make('detail')->label('Detail')->button()->color('success')->icon('heroicon-o-eye')
                    ->hidden(!auth()->user()?->can('view_any_crewapplicants'))
                    ->url(fn($record) => CrewAllResource::getUrl('view', ['record' => $record->applicant_id])),
            ]);
    }
}
