<?php

namespace App\Filament\Crew\Resources\ReminderCrews;

use App\Filament\Crew\Resources\ReminderCrews\Pages\ManageReminderCrews;
use App\Models\ReminderCrew;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ReminderCrewResource extends Resource
{
    protected static ?string $model = ReminderCrew::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Clock;

    protected static ?string $recordTitleAttribute = 'reminder_hari';
    protected static ?string $navigationLabel = "Reminder";
    protected static ?string $pluralModelLabel = "Reminder";
    protected static string | UnitEnum | null $navigationGroup = 'Reminder Setting';
    protected static ?string $permissionGroup = 'User';
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reminder_hari')
                    ->label('Hari')
                    ->numeric()
                    ->prefix('H-')
                    ->required(),
                TimePicker::make('reminder_jam')
                    ->label('Jam')
                    ->required()
                    ->seconds(false)
                    ->time()
                    ->columnSpan(1)
                    ->placeholder('Pilih jam'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reminder_hari')
            ->columns([
                TextColumn::make('index')
                    ->label('No. ')
                    ->width('sm')
                    ->rowIndex(),
                TextColumn::make('reminder_jam')
                    ->time()
                    ->sortable(),
                TextColumn::make('reminder_hari'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->button(),
                DeleteAction::make()->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageReminderCrews::route('/'),
        ];
    }
}
