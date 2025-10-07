<?php

namespace App\Filament\StaffDocument\Resources;

use App\Filament\StaffDocument\Resources\LookupResource\Pages;
use App\Models\Lookup;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LookupResource extends Resource
{
    protected static ?string $model = Lookup::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Lookup';
    protected static ?int $navigationSort = 13;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('kategori')
                    ->label('Kategori')
                    ->native(false)
                    ->options([
                        'Document' => 'Document',
                    ])
                    ->reactive(), // penting supaya trigger perubahan

                Select::make('code')
                    ->label('Code')
                    ->native(false)
                    ->options(function (callable $get) {
                        $kategori = $get('kategori');
                        return match ($kategori) {
                            'Document' => [
                                'Document Near Expiry' => 'Document Near Expiry',
                            ],

                            default => [],
                        };
                    })
                    ->unique(ignorable: fn ($record) => $record)
                    ->reactive(),

                TextInput::make('value')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultGroup('kategori')
            ->groupingSettingsHidden()
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->modifyQueryUsing(fn(Builder $query) => $query->where('kategori', 'Document')
                ->orderBy('kategori')
                ->orderBy('code'))
            ->columns([
                TextColumn::make('code')
                    ->badge()
                    ->searchable()
                    ->color('info'),
                TextColumn::make('value')
                    ->badge()
                    ->color('success')
                    ->url(
                        fn($record) =>
                        $record->kategori === 'Template Form'
                            ? asset('storage/templates/' . $record->value)
                            : null
                    )
                    ->searchable()
                    ->openUrlInNewTab()
                    ->formatStateUsing(
                        fn($state, $record) =>
                        $record->kategori === 'Template Form'
                            ? 'Download'
                            : $record->value
                    ),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('kategori')
                    ->label('Kategori')
                    ->native(false)
                    ->options(
                        fn() => Lookup::query()
                            ->pluck('kategori', 'kategori')
                            ->toArray()
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->button(),
                Tables\Actions\DeleteAction::make()->button(),
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
            'index' => Pages\ManageLookups::route('/'),
        ];
    }
}
