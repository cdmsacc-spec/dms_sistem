<?php

namespace App\Filament\Crew\Resources;

use App\Filament\Crew\Resources\LookupResource\Pages;
use App\Filament\Crew\Resources\LookupResource\RelationManagers;
use App\Models\Lookup;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
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
                        'Interview' => 'Interview',
                        'Sign Off'  => 'Sign Off',
                        'Sign On'   => 'Sign On',
                        'Mutasi Promosi' => 'Mutasi / Promosi',
                        'Nomor Document' => 'Nomor Document',
                        'Template Form' => 'Template Form',
                    ])
                    ->required()
                    ->reactive(),

                Select::make('code')
                    ->label('Code')
                    ->native(false)
                    ->options(function (callable $get) {
                        $kategori = $get('kategori');
                        return match ($kategori) {
                            'Interview' => [
                                'Disetujui 1' => 'Disetujui 1',
                                'Disetujui 2' => 'Disetujui 2',
                                'Disetujui 3' => 'Disetujui 3',
                            ],
                            'Sign Off' => [
                                'Crewing Manager' => 'Crewing Manager',
                                'Direktur' => 'Direktur',
                            ],
                            'Sign On' => [
                                'Dibuat Oleh' => 'Dibuat Oleh',
                                'Diperiksa Oleh' => 'Diperiksa Oleh',
                                'Diketahui Oleh' => 'Diketahui Oleh',
                                'Disetujui Oleh' => 'Disetujui Oleh',
                            ],
                            'Nomor Document' => [
                                'SignOn' => 'SignOn',
                            ],
                            'Template Form' => [
                                'Template Interview' => 'Template Interview',
                                'Template Sign On' => 'Template Sign On',
                                'Template Sign Off' => 'Template Sign Off',
                                'Template Mutasi Promosi' => 'Template Mutasi Promosi',
                            ],
                            'Mutasi Promosi' => [
                                'Dibuat Oleh' => 'Dibuat Oleh',
                                'Diketahui Oleh' => 'Diketahui Oleh',
                                'Disetujui Oleh' => 'Disetujui Oleh'
                            ],
                            default => [],
                        };
                    })
                    ->required()
                    ->reactive(),

                TextInput::make('value')
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultGroup('kategori')
            ->groupingSettingsHidden()
            ->emptyStateHeading('Tidak Ada Data')
            ->modifyQueryUsing(fn(Builder $query) => $query->where('kategori', '!=', 'Document')
                ->orderBy('kategori')
                ->orderBy('code'))
            ->emptyStateDescription('belum ada data ditambahkan')
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
