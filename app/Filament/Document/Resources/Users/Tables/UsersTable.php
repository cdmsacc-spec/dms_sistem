<?php

namespace App\Filament\Document\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('name', 'staff_dokumen');
                });
            })
            ->columns([
                ImageColumn::make('avatar')
                    ->disk('public')
                    ->circular()
                    ->default(url('storage/crew/avatar/default.jpg'))
                    ->defaultImageUrl(url('storage/crew/avatar/default.jpg')),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->searchable()
                    ->badge()
                    ->color('info')
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()->color('success')
                    ->button()
                    ->modalWidth('md')
                    ->modalAlignment('center')
                    ->schema([
                        Section::make('')
                            ->columns(2)
                            ->schema([
                                ImageEntry::make('avatar')
                                    ->hiddenLabel()
                                    ->default(url('storage/crew/avatar/default.jpg'))
                                    ->disk('public')
                                    ->alignCenter()
                                    ->columnSpanFull(),
                                TextEntry::make('name')
                                    ->columnSpanFull(),
                                TextEntry::make('email')
                                    ->columnSpan(1),
                                TextEntry::make('p')
                                    ->label('Password')
                                    ->columnSpan(1)
                                    ->placeholder('default pw 12345'),
                            ])
                    ]),
                EditAction::make()->button(),
                DeleteAction::make()->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
