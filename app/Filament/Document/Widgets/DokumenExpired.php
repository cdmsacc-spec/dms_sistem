<?php

namespace App\Filament\Document\Widgets;

use App\Filament\Document\Resources\Dokumens\DokumenResource;
use App\Models\Dokumen;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class DokumenExpired extends TableWidget
{
    protected static bool $isLazy = true;

    public function table(Table $table): Table
    {
        return $table
            ->heading('')
            ->query(fn(): Builder =>  Dokumen::query()->where('status', 'expired'))
            ->columns([
                TextColumn::make('kapal.perusahaan.nama_perusahaan'),
                TextColumn::make('kapal.nama_kapal'),
                TextColumn::make('JenisDokumen.nama_dokumen'),
                TextColumn::make('latestHistory.nomor_dokumen'),
                TextColumn::make('latestHistory.tanggal_expired')
                    ->formatStateUsing(fn($state) => Carbon::parse($state)->format('d-M-Y'))
                    ->label('Expired')
                    ->badge()
                    ->color('danger'),
            ])
            ->recordActions([
                Action::make('view')
                    ->color('success')
                    ->visible(auth()->user()?->can('view-any:dokumen'))
                    ->button()
                    ->url(fn($record) => DokumenResource::getUrl('view', ['record' => $record])),

            ]);
    }
}
