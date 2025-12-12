<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables;


class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption('5')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(function ($state) {
                        if ($state == 'admin') {
                            return 'success';
                        }
                        if ($state == 'staff_crew' || $state == 'manager_crew') {
                            return 'info';
                        }
                        if ($state == 'staff_document' || $state == 'operation' || $state == 'manager_document') {
                            return 'danger';
                        }
                    })
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->button(),
                DeleteAction::make()->button()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
