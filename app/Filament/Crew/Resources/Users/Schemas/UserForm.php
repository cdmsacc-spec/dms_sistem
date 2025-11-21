<?php

namespace App\Filament\Crew\Resources\Users\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('avatar')
                            ->image()
                            ->maxSize(10240)
                            ->imageEditor()
                            ->hiddenLabel()
                            ->disk('public')
                            ->panelLayout('integrated')
                            ->removeUploadedFileButtonPosition('right')
                            ->uploadButtonPosition('center')
                            ->uploadProgressIndicatorPosition('center')
                            ->directory('user/profile')
                            ->openable()
                            ->alignCenter()
                            ->avatar()
                            ->columnSpanFull(),
                        TextInput::make('name')
                            ->required()
                            ->columnSpanFull()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->columnSpan(1)
                            ->maxLength(255),
                        TextInput::make('p')
                            ->label('Password')
                            ->password()
                            ->nullable()
                            ->columnSpan(1)
                            ->maxLength(255)
                            ->placeholder('default pw 12345'),
                    ])
            ]);
    }
}
