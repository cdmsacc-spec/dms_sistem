<?php

namespace App\Filament\Crew\Resources;

use App\Filament\Crew\Resources\PklReminderResource\Pages;
use App\Filament\Crew\Resources\PklReminderResource\RelationManagers;
use App\Models\PklReminder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PklReminderResource extends Resource
{
    protected static ?string $model = PklReminder::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Reminder';
    protected static ?string $navigationGroup = 'Settings';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('reminder_hari')
                    ->numeric()
                    ->prefix('H-')
                    ->required(),
                Forms\Components\TimePicker::make('reminder_jam')
                    ->label('Jam Reminder')
                    ->required()
                    ->seconds(false)
                    ->time()
                    ->placeholder('Pilih jam'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reminder_hari')
                    ->prefix('H- ')
                    ->numeric()
                    ->searchable(),
                Tables\Columns\TextColumn::make('reminder_jam'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->button(),
                Tables\Actions\DeleteAction::make()->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePklReminders::route('/'),
        ];
    }
}
