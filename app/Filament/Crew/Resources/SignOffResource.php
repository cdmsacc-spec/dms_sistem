<?php

namespace App\Filament\Crew\Resources;

use App\Enums\StatusKontrakCrew;
use App\Filament\Crew\Resources\SignOffResource\Pages;
use App\Filament\Crew\Resources\SignOffResource\RelationManagers;
use App\Filament\Crew\Resources\SignOffResource\RelationManagers\CrewSignOffRelationManager;
use App\Models\CrewApplicants;
use App\Models\SignOff;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class SignOffResource extends Resource
{
    protected static ?string $model = CrewApplicants::class;
    protected static ?string $slug = 'crew-signoff';
    protected static ?string $navigationLabel = 'Sign Off';
    protected static ?string $pluralModelLabel = 'Sign Off';
    protected static ?string $navigationGroup = 'Crew Management';
    protected static ?int $navigationSort = 8;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('')
                    ->columnSpan(1)
                    ->columns(1)
                    ->schema([
                        Forms\Components\Textarea::make('keterangan')
                            ->dehydrated(false),
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Upload File Sign On')
                            ->columnSpan(1)
                            ->disk('public')
                            ->preserveFilenames()
                            ->directory('crew/signoff')
                            ->dehydrated(false),
                    ]),

                Section::make('Kontrak Info')
                    ->columnSpan(1)
                    ->columns(2)
                    ->description('Detail kontrak crew')
                    ->icon('heroicon-m-user')
                    ->headerActions([
                        Forms\Components\Actions\Action::make('Selengkapnya')
                            ->url(fn($record) => CrewAllResource::getUrl('detail_pkl', ['record' => $record->lastCrewPkl->id]))
                            ->openUrlInNewTab(false)
                    ])
                    ->schema([
                        Forms\Components\Placeholder::make('nomor_document')
                            ->content(fn($record): string => $record->lastCrewPkl?->nomor_document ?? '-'),

                        Forms\Components\Placeholder::make('nama')
                            ->content(fn($record): string => $record->lastCrewPkl?->crew?->nama_crew ?? '-'),

                        Forms\Components\Placeholder::make('perusahaan')
                            ->content(fn($record): string => $record->lastCrewPkl?->perusahaan?->nama_perusahaan ?? '-'),

                        Forms\Components\Placeholder::make('jabatan')
                            ->content(
                                fn($record): string =>
                                $record->lastCrewPkl?->jabatan
                                    ? ($record->lastCrewPkl->jabatan->golongan . '-' . $record->lastCrewPkl->jabatan->nama_jabatan)
                                    : '-'
                            ),

                        Forms\Components\Placeholder::make('tanggal_mulai')
                            ->content(fn($record): string => $record->lastCrewPkl?->start_date ?? '-'),

                        Forms\Components\Placeholder::make('tanggal_selesai')
                            ->content(fn($record): string => $record->lastCrewPkl?->end_date ?? '-'),


                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('status_proses', StatusKontrakCrew::Active))
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('nama_crew')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenis_kelamin')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_hp')
                    ->label('Telepon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status_identitas')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status_proses')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'Draft' => 'info',
                        'Ready For Interview' => 'warning',
                        'Inactive' => 'danger',
                        'Standby' => 'info',
                        'Active' => 'success'
                    })->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([

                Tables\Actions\EditAction::make()
                    ->button()
                    ->label('Sign Off'),
                Tables\Actions\Action::make('detail')
                    ->button()
                    ->color(color: 'success')
                    ->icon('heroicon-o-eye')
                    ->label('Detail')
                    ->url(fn($record) => CrewAllResource::getUrl('view', ['record' => $record])),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CrewSignOffRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSignOffs::route('/'),
            'create' => Pages\CreateSignOff::route('/create'),
            'edit' => Pages\EditSignOff::route('/{record}/edit'),
        ];
    }
}
