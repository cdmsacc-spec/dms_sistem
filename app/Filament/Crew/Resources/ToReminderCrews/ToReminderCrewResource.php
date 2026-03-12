<?php

namespace App\Filament\Crew\Resources\ToReminderCrews;

use App\Filament\Crew\Resources\ToReminderCrews\Pages\ManageToReminderCrews;
use App\Models\ToReminderCrew;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ToReminderCrewResource extends Resource
{
    protected static ?string $model = ToReminderCrew::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::PaperAirplane;

    protected static ?string $recordTitleAttribute = 'nama';
    protected static ?string $navigationLabel = "Reminder To";
    protected static ?string $pluralModelLabel = "Reminder To";
    protected static string | UnitEnum | null $navigationGroup = 'Reminder Setting';
    protected static ?string $permissionGroup = 'User';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextInput::make('nama')
                    ->label('Nama')
                    ->required(),
                Select::make('type')
                    ->native(false)
                    ->placeholder('')
                    ->required()
                    ->live()
                    ->options([
                        'wa' => "Wa",
                        'email' => "Email",
                    ]),
                TextInput::make('send_to')
                    ->label('Send To')
                    ->dehydrateStateUsing(function ($state, $get) {
                        if ($get('type') === 'wa' && filled($state)) {
                            $cleanState = preg_replace('/[^0-9]/', '', $state);

                            return str_starts_with($cleanState, '0')
                                ? '62' . substr($cleanState, 1)
                                : $cleanState;
                        }

                        return $state;
                    })
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama')
            ->defaultGroup('type')
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->defaultPaginationPageOption('5')
            ->columns([
                TextColumn::make('index')
                    ->label('No. ')
                    ->width('sm')
                    ->rowIndex(),
                TextColumn::make('nama')
                    ->searchable(),
                TextColumn::make('send_to'),
                TextColumn::make('type'),
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
            'index' => ManageToReminderCrews::route('/'),
        ];
    }
}
