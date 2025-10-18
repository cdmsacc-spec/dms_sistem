<?php

namespace App\Filament\Crew\Resources;

use App\Filament\Crew\Resources\AppraisalResource\Pages;
use App\Filament\Crew\Resources\AppraisalResource\RelationManagers;
use App\Models\Appraisal;
use App\Models\CrewPkl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppraisalResource extends Resource
{
    protected static ?string $model = CrewPkl::class;
    protected static ?string $navigationIcon = '';
    protected static ?string $navigationLabel = 'Crew Appraisal';
    protected static ?string $pluralModelLabel = 'Appraisal';

    protected static ?string $navigationGroup = 'Crew Management';
    protected static ?int $navigationSort = 6;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Crew Info')
                    ->columnSpan(1)
                    ->columns(2)
                    ->description('Detail informasi crew')
                    ->icon('heroicon-m-user')
                    ->headerActions([
                        Forms\Components\Actions\Action::make('Selengkapnya')
                            ->url(fn($record) => CrewAllResource::getUrl('view', ['record' => $record->crew->id]))
                            ->openUrlInNewTab(false)
                    ])
                    ->schema([
                        Forms\Components\Placeholder::make('nama')
                            ->content(fn($record): string => $record->crew->nama_crew),
                        Forms\Components\Placeholder::make('no_telepon')
                            ->content(fn($record): string => $record->crew->no_hp),
                        Forms\Components\Placeholder::make('alamat')
                            ->extraAttributes([
                                'class' => 'truncate max-w-xs', // max-w-xs bisa diganti sesuai kebutuhan
                                'title' => $record->crew->alamat_sekarang ?? '', // biar kalau hover muncul tooltip full
                            ])
                            ->content(fn($record): string => $record->crew->alamat_sekarang),
                        Forms\Components\Placeholder::make('jenis_kelamin')
                            ->content(fn($record): string => $record->crew->jenis_kelamin),
                    ]),

                Forms\Components\Section::make('Kontrak Active')
                    ->columnSpan(1)
                    ->columns(2)
                    ->description('Detail informasi kontrak')
                    ->icon('heroicon-m-clipboard-document-check')
                    ->headerActions([
                        Forms\Components\Actions\Action::make('Selengkapnya')
                            ->url(fn($record) => CrewAllResource::getUrl('detail_pkl', ['record' => $record->id]))
                            ->openUrlInNewTab(false)
                    ])
                    ->schema([
                        Forms\Components\Placeholder::make('nama_perusahaan')
                            ->content(fn($record): string => $record->perusahaan->nama_perusahaan),
                        Forms\Components\Placeholder::make('appraisal_summary')
                            ->content(function ($record) { {
                                    $appraisals = $record->appraisal()->pluck('nilai');

                                    if ($appraisals->isEmpty()) {
                                        return 'Belum Ada Penilaian';
                                    }
                                    $average = round($appraisals->avg());
                                    return match (true) {
                                        $average >= 100 => "Sangat Memuaskan ($average)",
                                        $average >= 75  => "Memuaskan ($average)",
                                        $average >= 50  => "Cukup Memuaskan ($average)",
                                        $average >= 25  => "Tidak Memuaskan ($average)",
                                        default => "Belum Dinilai",
                                    };
                                }
                            }),
                        Forms\Components\Placeholder::make('start_date')
                            ->content(fn($record): string => $record->start_date),
                        Forms\Components\Placeholder::make('end_date')
                            ->content(fn($record): string => $record->end_date),
                    ]),

                Forms\Components\Section::make('')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('appraiser')
                            ->dehydrated(false)
                            ->required(),
                        Forms\Components\Select::make('nilai')
                            ->native(false)
                            ->options([
                                100 => "Sangat Memuaskan",
                                70 => 'Memuaskan',
                                50 => 'Cukup Memuaskan',
                                25 => 'Tidak Memuaskan',
                            ])
                            ->dehydrated(false)
                            ->required(),
                        Forms\Components\Textarea::make('keterangan')
                            ->dehydrated(false)
                            ->columnSpanFull()
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn(Builder $query) => $query->where('status_kontrak', 'Active')->withAvg('appraisal', 'nilai'))
            ->columns([
                Tables\Columns\TextColumn::make('crew.nama_crew')
                    ->searchable(),
                Tables\Columns\TextColumn::make('perusahaan.kode_perusahaan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jabatan.kode_jabatan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kapal.nama_kapal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status_kontrak')
                    ->badge()
                    ->color('success')
                    ->searchable(),
                Tables\Columns\TextColumn::make('appraisal_avg_nilai')
                    ->badge()
                    ->description(fn($state) => match (true) {
                        $state >= 100 => 'Sangat memuaskan ',
                        $state >= 75  => 'Memuaskan',
                        $state >= 50  => 'Cukup memuaskan',
                        $state >= 25  => 'Tidak memuaskan',
                        default => 'gray',
                    })
                    ->formatStateUsing(
                        fn($state) => $state ? number_format($state, 0) : '-'
                    )
                    ->color(fn($state) => match (true) {
                        $state >= 100 => 'success',
                        $state >= 75  => 'primary',
                        $state >= 50  => 'warning',
                        $state >= 25  => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('file_path')
                    ->label('File')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn($state) => $state != null ? 'Download' : '')
                    ->url(fn($record) => $record->file_path ? asset('storage/' . $record->file_path) : null, shouldOpenInNewTab: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->button()
                    ->label('Appraisal'),
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
            RelationManagers\AppraisalRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppraisals::route('/'),
            'create' => Pages\CreateAppraisal::route('/create'),
            'edit' => Pages\EditAppraisal::route('/{record}/edit'),
        ];
    }
}
