<?php

namespace App\Filament\StaffDocument\Resources;

use App\Filament\StaffDocument\Resources\NamaKapalResource\Pages;
use App\Filament\StaffDocument\Resources\NamaKapalResource\RelationManagers;
use App\Models\NamaKapal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NamaKapalResource extends Resource
{
    protected static ?string $model = NamaKapal::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $navigationLabel = 'Kapal';
    protected static ?string $modelLabel = 'Kapal';
    protected static ?string $pluralModelLabel = 'Kapal';
    protected static ?string $navigationGroup = 'Master Data';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('perusahaan_id')
                    ->relationship('perusahaan', 'nama_perusahaan')
                    ->native(false)
                    ->required(),
                Forms\Components\Select::make('jenis_kapal_id')
                    ->relationship('jenisKapal', 'nama_jenis')
                    ->native(false)
                    ->required(),
                Forms\Components\TextInput::make('nama_kapal')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('status_certified')
                    ->options([
                        'Certified BKI' => 'Certified BKI',
                        'Certified Perhubungan' => 'Certified Perhubungan',
                        'Non-Certified' => 'Non-Certified'
                    ])
                    ->native(false)
                    ->required(),
                Forms\Components\TextInput::make('tahun_kapal')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Tidak Ada Data')
            ->defaultSort('created_at', 'desc')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->columns([
                Tables\Columns\TextColumn::make('nama_kapal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenisKapal.nama_jenis')
                    ->icon('heroicon-o-queue-list')
                    ->searchable(),
                Tables\Columns\TextColumn::make('perusahaan.nama_perusahaan')
                    ->icon('heroicon-o-building-office')
                    ->color('success')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status_certified')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tahun_kapal')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ViewAction::make()
                        ->color('success'),
                ])
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
            'index' => Pages\ManageNamaKapals::route('/'),
        ];
    }
}
