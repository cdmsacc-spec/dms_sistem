<?php

namespace App\Filament\Document\Resources\Users;

use App\Filament\Document\Resources\Users\Pages\CreateUser;
use App\Filament\Document\Resources\Users\Pages\EditUser;
use App\Filament\Document\Resources\Users\Pages\ListUsers;
use App\Filament\Document\Resources\Users\Schemas\UserForm;
use App\Filament\Document\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::User;

    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationLabel = "User Staff Dokumen";
    protected static ?string $pluralModelLabel = "User Staff Dokumen";
    protected static ?string $permissionGroup = 'User';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
