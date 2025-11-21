<?php

namespace App\Filament\Document\Resources\Dokumens\Pages;

use App\Filament\Document\Resources\Activities\ActivityResource;
use App\Filament\Document\Resources\Dokumens\DokumenResource;
use App\Models\Dokumen;
use App\Models\HistoryDokumen;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class DokumenAudit extends Page implements HasTable
{

    use InteractsWithTable;

    protected static string $resource = DokumenResource::class;
    protected string $view = 'filament.document.resources.dokumens.pages.dokumen-audit';
    public function mount($record)
    {
        $this->record = Dokumen::findOrFail($record);
    }

    protected function getTableQuery()
    {

        $data = Activity::query()
            ->where('log_name', 'Dokumen')
            ->where('subject_id', $this->record->id)
            ->latest();
        return $data;
    }
    public function table(Table $table)
    {
        return $table->columns([
            TextColumn::make('index')
                ->label('No. ')
                ->width('sm')
                ->rowIndex(),
            TextColumn::make('causer.name')
                ->label('Author')
                ->icon('heroicon-o-user')
                ->color('info'),
            TextColumn::make('event')
                ->label('Event')
                ->searchable()
                ->sortable()
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'created' => 'success',
                    'updated' => 'warning',
                    'deleted' => 'danger',
                    default => 'gray',
                }),
            TextColumn::make('description')
                ->label('Deskripsi')
                ->sortable(),

            TextColumn::make('created_at')
                ->label('Tanggal')
                ->dateTime('d M Y H:i')
                ->sortable(),
        ])
            ->recordActions([
                Action::make('view')
                    ->button()
                    ->color('info')
                    ->url(fn($record) => ActivityResource::getUrl('view', ['record' => $record]))
            ]);
    }
}
