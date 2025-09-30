<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->unique(ignorable: fn($record) => $record),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignorable: fn($record) => $record),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn($record) => $record)
                    ->maxLength(255)
                    ->helperText('Default isi dengan 12345'),
                Forms\Components\Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->native(false)
                    ->preload()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->searchable()
                    ->badge()
                    ->color(function ($state) {
                        if ($state == 'admin') {
                            return 'success';
                        }
                        if ($state == 'staff_crew') {
                            return 'info';
                        }
                        if ($state == 'staff_document') {
                            return 'danger';
                        }
                    })

            ])
            ->actions([
                Tables\Actions\ViewAction::make()->color('success')->button(),
                Tables\Actions\EditAction::make()->color('info')->button(),
                Tables\Actions\DeleteAction::make()->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'view' => Pages\ViewUserExpiration::route('/{record}/view'),

        ];
    }
    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin');
    }
}
