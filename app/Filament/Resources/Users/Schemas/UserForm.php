<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 2,
                        'xl' => 2,
                    ])
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->columnSpanFull()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->default(null)
                            ->columnSpanFull()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('p')
                            ->label('Password')
                            ->password()
                            ->nullable()
                            ->columnSpan(1)
                            ->maxLength(255)
                            ->placeholder('default pw 12345'),
                        Forms\Components\Select::make('roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->native(false)
                            ->preload()
                    ])
            ]);
    }
}
