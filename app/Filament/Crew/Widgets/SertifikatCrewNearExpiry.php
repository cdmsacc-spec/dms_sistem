<?php

namespace App\Filament\Crew\Widgets;

use App\Filament\Crew\Resources\AllCrews\AllCrewResource;
use App\Models\CrewSertifikat;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\View\TablesRenderHook;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class SertifikatCrewNearExpiry extends TableWidget
{
    use HasWidgetShield;
    protected int | string | array $columnSpan = 4;
    protected static bool $isLazy = true;

    public function mount(): void
    {
        FilamentView::registerRenderHook(
            TablesRenderHook::TOOLBAR_START,
            fn() =>
            view('component.heading.tabel-heading', [
                'title' => 'Sertifikat Crew Mendekati Kadaluarsa'
            ])->render()
        );
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => CrewSertifikat::query()->where('status', 'near expiry'))
            ->heading('')
            ->columns([
                TextColumn::make('index')
                    ->label('No. ')
                    ->width('sm')
                    ->rowIndex(),
                TextColumn::make('nomor_sertifikat')
                    ->label('Nomor Sertifikat')
                    ->searchable(),
                TextColumn::make('crew.nama_crew')
                    ->label('Crew')
                    ->icon('heroicon-o-user')
                    ->searchable(),
                TextColumn::make('kategory')
                    ->label('Kategory')
                    ->searchable(),
                TextColumn::make('nama_sertifikat')
                    ->label('Nama Sertifikat')
                    ->searchable(),
                TextColumn::make('tanggal_expired')
                    ->formatStateUsing(fn($state) => Carbon::parse($state)->format('d-M-Y'))
                    ->label('Expired')
                    ->badge()
                    ->color('danger'),
            ])
            ->recordActions([
                Action::make('detail')->label('Detail')->button()->color('success')->icon('heroicon-o-eye')
                    ->hidden(!auth()->user()?->can('view-any:crew'))
                    ->url(fn($record) => AllCrewResource::getUrl('view', ['record' => $record->id_crew . '?relation=1'])),
            ]);
    }
}
