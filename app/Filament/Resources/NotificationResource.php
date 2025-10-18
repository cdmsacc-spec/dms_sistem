<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Filament\Resources\NotificationResource\RelationManagers;
use App\Models\Notification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('type')
                    ->disabled(),

                Forms\Components\TextInput::make('notifiable_type')
                    ->disabled(),

                Forms\Components\TextInput::make('notifiable_id')
                    ->disabled(),

                Forms\Components\KeyValue::make('data')
                    ->label('Data JSON')
                    ->disabled(),

                Forms\Components\DateTimePicker::make('read_at')
                    ->label('Read At')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->where('notifiable_id', Auth::id()))
            ->columns([
                Tables\Columns\TextColumn::make('data.title')
                    ->label('Title')
                    ->wrap(),
                Tables\Columns\TextColumn::make('data.body')
                    ->label('Body')
                    ->wrap(),
                Tables\Columns\IconColumn::make('read_at')
                    ->boolean()
                    ->label('Read'),

                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()->button()->color('danger')
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
            'index' => Pages\ManageNotifications::route('/'),
        ];
    }
}
