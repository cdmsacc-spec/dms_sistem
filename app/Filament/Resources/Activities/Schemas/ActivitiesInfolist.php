<?php

namespace App\Filament\Resources\Activities\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ActivitiesInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('')
                    ->columnSpanFull()
                    ->columns([
                        'sm' => 1,
                        'md' => 3,
                        'lg' => 3,
                        'xl' => 4
                    ])
                    ->schema([
                        TextEntry::make('causer.name')
                            ->icon('heroicon-o-user')
                            ->formatStateUsing(fn($record) =>  $record->causer_id === null ? 'null' : $record->causer->name)
                            ->label('Author'),
                        TextEntry::make('event')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'created' => 'success',
                                'updated' => 'info',
                                'deleted' => 'danger',
                                default => 'gray',
                            })
                            ->label('Event'),

                        TextEntry::make('event_time')
                            ->getStateUsing(fn($record) => $record->created_at),

                        TextEntry::make('description')
                            ->label('Description')
                            ->formatStateUsing(fn($record) => $record->causer == null ? 'sistem created' : $record->causer['name'] . ' ' . $record->event . ' ' . $record->description),

                    ]),

                KeyValueEntry::make('properties.old')
                    ->label('Old')
                    ->formatStateUsing(fn($state) => $state ?? null)
                    ->visible(fn($record) => isset($record->properties['old'])),

                KeyValueEntry::make('properties.attributes')
                    ->label('New')
                    ->formatStateUsing(fn($state) => $state ?? null)
                    ->visible(fn($record) => isset($record->properties['attributes'])),
            ]);
    }
}
