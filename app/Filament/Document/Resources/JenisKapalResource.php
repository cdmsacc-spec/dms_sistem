<?php

namespace App\Filament\Document\Resources;

use App\Filament\Document\Resources\JenisKapalResource\Pages;
use App\Filament\Document\Resources\JenisKapalResource\RelationManagers;
use App\Models\JenisKapal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JenisKapalResource extends Resource
{
    protected static ?string $model = JenisKapal::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';
    protected static ?string $navigationLabel = 'Jenis Kapal';
    protected static ?string $modelLabel = 'Jenis Kapal';
    protected static ?string $pluralModelLabel = 'Jenis Kapal';
    protected static ?string $navigationGroup = 'Master Data';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_jenis')
                    ->required()
                    ->unique(ignorable: fn($record) => $record)
                    ->maxLength(255),
                Forms\Components\Textarea::make('deskripsi')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Tidak Ada Data')
            ->defaultSort('created_at', 'desc')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->columns([
                Tables\Columns\TextColumn::make('nama_jenis')
                    ->color('info')
                    ->icon('heroicon-o-queue-list')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('deskripsi')

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('success')->button(),
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
            'index' => Pages\ManageJenisKapals::route('/'),
        ];
    }
}
