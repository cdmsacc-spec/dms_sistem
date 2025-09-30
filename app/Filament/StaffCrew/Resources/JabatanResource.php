<?php

namespace App\Filament\StaffCrew\Resources;

use App\Filament\StaffCrew\Resources\JabatanResource\Pages;
use App\Filament\StaffCrew\Resources\JabatanResource\RelationManagers;
use App\Models\Jabatan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JabatanResource extends Resource
{
    protected static ?string $model = Jabatan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Jabatan';
    protected static ?int $navigationSort = 9;

    protected static ?string $navigationGroup = 'Master Data';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_jabatan')
                    ->required(),
                Forms\Components\TextInput::make('kode_jabatan')
                    ->required(),
                Forms\Components\Select::make('golongan')
                    ->native(false)
                    ->required()
                    ->options([
                        'perwira' => 'Perwira',
                        'non-perwira' => 'Non-Perwira'
                    ]),
                Forms\Components\Select::make('devisi')
                    ->native(false)
                    ->required()
                    ->options([
                        'Deck' => 'Deck',
                        'Mesin' => 'Mesin'
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->columns([
                Tables\Columns\TextColumn::make('nama_jabatan')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                Tables\Columns\TextColumn::make('golongan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('devisi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kode_jabatan')
                    ->badge()
                    ->color('info')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('golongan')
                    ->native(false)
                    ->options([
                        "perwira" => "Perwira",
                        "non-perwira" => "Non Perwira"
                    ])
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->button()->color('success'),
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
            'index' => Pages\ManageJabatans::route('/'),
        ];
    }
}
