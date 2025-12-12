<?php

namespace App\Filament\Crew\Resources\CrewMutasis\Tables;

use App\Filament\Crew\Resources\AllCrews\AllCrewResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CrewMutasisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->where('status', 'active'))
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->defaultPaginationPageOption('5')
            ->columns([
                TextColumn::make('index')
                    ->label('No. ')
                    ->width('sm')
                    ->rowIndex(),
                TextColumn::make('nama_crew')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('jenis_kelamin')
                    ->searchable(),
                TextColumn::make('no_hp')
                    ->label('Telepon')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color('success')
                    ->searchable(),
            ])
            ->recordActions([

                EditAction::make()
                    ->button()
                    ->color('info')
                    ->label('Mutasi / promosi'),
                Action::make('detail')
                    ->button()
                    ->color('success')
                    ->icon('heroicon-o-eye')
                    ->label('Detail')
                    ->url(fn($record) => AllCrewResource::getUrl('view', ['record' => $record])),
            ])
            ->toolbarActions([]);
    }
}
