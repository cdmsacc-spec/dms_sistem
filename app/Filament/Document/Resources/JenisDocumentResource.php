<?php

namespace App\Filament\Document\Resources;

use App\Filament\Document\Resources\JenisDocumentResource\Pages;
use App\Filament\Document\Resources\JenisDocumentResource\RelationManagers;
use App\Models\JenisDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JenisDocumentResource extends Resource
{
    protected static ?string $model = JenisDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Jenis Document';
    protected static ?string $modelLabel = 'Jenis Document';
    protected static ?string $pluralModelLabel = 'Jenis Document';
    protected static ?string $navigationGroup = 'Document Management';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_dokumen')
                    ->unique(ignorable: fn($record) => $record)
                    ->required(),
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
                Tables\Columns\TextColumn::make('nama_dokumen')
                    ->badge()
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->searchable(),
                Tables\Columns\TextColumn::make('deskripsi'),
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
            'index' => Pages\ManageJenisDocuments::route('/'),
        ];
    }
}
