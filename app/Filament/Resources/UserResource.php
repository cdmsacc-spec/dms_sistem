<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\Permission\Models\Role;

class UserResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = User::class;
    protected static ?string $slug = 'user-management';
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationGroup = 'Settings';
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',

        ];
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                  ->unique(ignoreRecord: true),
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
            ->actions([
                Tables\Actions\ViewAction::make()->color('success')->button(),
                Tables\Actions\EditAction::make()->color('info')->button(),
                Tables\Actions\DeleteAction::make()->button(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Filter Berdasarkan Role')
                    ->native(false)
                    ->options(Role::pluck('name', 'name')->toArray())
                    ->query(function ($query, array $data): void {
                        if ($data['value']) {
                            $query->whereHas('roles', fn($q) => $q->where('name', $data['value']));
                        }
                    }),
            ],)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
