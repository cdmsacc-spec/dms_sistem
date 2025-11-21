<?php

namespace App\Filament\Document\Resources\Notifications\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class NotificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('type')
                    ->required(),
                TextInput::make('notifiable_type')
                    ->required(),
                TextInput::make('notifiable_id')
                    ->required()
                    ->numeric(),
                TextInput::make('data')
                    ->required(),
                DateTimePicker::make('read_at'),
            ]);
    }
}
