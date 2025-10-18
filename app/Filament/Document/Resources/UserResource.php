<?php

namespace App\Filament\Document\Resources;

use App\Filament\Document\Resources\UserResource\Pages;
use App\Filament\Document\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

      protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'User Document';
    protected static ?string $modelLabel = 'User';
    protected static ?string $pluralModelLabel = 'UserDocument';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('name', 'staff_document');
                });
            })->columns([
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
        ];
    }
}
