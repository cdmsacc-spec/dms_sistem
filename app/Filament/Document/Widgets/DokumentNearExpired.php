<?php

namespace App\Filament\Document\Widgets;

use App\Filament\Document\Resources\Dokumens\DokumenResource;
use App\Models\Dokumen;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Filament\Tables\View\TablesRenderHook;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class DokumentNearExpired extends TableWidget
{
    use HasWidgetShield;
    use InteractsWithPageFilters;
    protected static ?string $heading = 'Dokumen Near Expiry';
    protected static ?int $sort = 7;
    protected static bool $isLazy = true;

    protected int | string | array $columnSpan = 4;

    public function mount(): void
    {
        FilamentView::registerRenderHook(
            TablesRenderHook::TOOLBAR_START,
            fn() =>
            view('component.heading.tabel-heading', [
                'title' => 'Dokumen Mendekati Kadaluarsa'
            ])->render()

        );
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => Dokumen::query()->where('status', 'near expiry'))
            ->heading('')
            ->defaultGroup('kapal.perusahaan.kode_perusahaan')
            ->groups([
                Group::make('kapal.perusahaan.kode_perusahaan')
                    ->label('Perusahaan')
                    ->collapsible(),
            ])
            ->groupingSettingsHidden()
            ->columns([
                TextColumn::make('index')
                    ->label('No. ')
                    ->width('sm')
                    ->rowIndex(),
                TextColumn::make('kapal.perusahaan.nama_perusahaan')
                    ->searchable(),
                TextColumn::make('kapal.nama_kapal')
                    ->searchable(),
                TextColumn::make('jenisDokumen.nama_jenis')
                    ->searchable(),
                TextColumn::make('latestHistory.nomor_dokumen')
                    ->label('Nomor dokumen')
                    ->searchable(),
                TextColumn::make('latestHistory.tanggal_expired')
                    ->label('Expired')
                    ->badge()
                    ->color('danger'),
            ])
            ->filters([
                SelectFilter::make('perusahaan')
                    ->label('Perusahaan')
                    ->native(false)
                    ->relationship('kapal.perusahaan', 'nama_perusahaan')
                    ->getOptionLabelFromRecordUsing(fn($record): string =>  $record->nama_perusahaan)
                    ->preload()
            ])
            ->filtersFormWidth('md')
            ->filtersFormColumns(2)
            ->filtersTriggerAction(
                fn(Action $action) => $action
                    ->button()
                    ->badgeColor('danger')
                    ->color('info')
                    ->label('Filter'),
            )
            ->headerActions([])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->visible(auth()->user()?->can('view-any:dokumen'))
                    ->button()
                    ->url(fn($record) => DokumenResource::getUrl('view', ['record' => $record])),
            ])
            ->toolbarActions([]);
    }
}
