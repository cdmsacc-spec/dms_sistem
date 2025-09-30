<?php

namespace App\Filament\StaffCrew\Resources;

use App\Filament\StaffCrew\Resources\CrewSignOffResource\Pages;
use App\Filament\StaffCrew\Resources\CrewSignOffResource\RelationManagers;
use App\Filament\StaffCrew\Resources\CrewSignOffResource\RelationManagers\CrewPklRelationManager;
use App\Filament\StaffCrew\Resources\CrewSignOffResource\RelationManagers\CrewSignOffRelationManager;
use App\Models\CrewApplicants;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CrewSignOffResource extends Resource
{
    protected static ?string $model = CrewApplicants::class;

    protected static ?string $navigationLabel = 'Sign Off';
    protected static ?string $navigationGroup = 'Crew Management';
    protected static ?int $navigationSort = 7;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Upload Document Sign Off')
                    ->columnSpan(1)
                    ->columns(1)
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal')
                            ->prefixIcon('heroicon-m-calendar')
                            ->native(false)
                            ->dehydrated(false),
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

                Forms\Components\Section::make('Status Crew')
                    ->schema([
                        Forms\Components\Radio::make('status_proses')
                            ->label('')
                            ->options([
                                'Draft' => 'Draft',
                                'Ready For Interview' => 'Ready For Interview',
                                'Standby' => 'Standby',
                                'Inactive' => 'Inactive',
                                'Active' => 'Active'
                            ])
                            ->required()
                            ->columns(5)
                            ->columnSpan(1),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('status_proses', 'Active'))
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
                    ->color('success')
                    ->icon('heroicon-o-eye')
                    ->label('Detail')
                    ->url(fn($record) => CrewOverviewResource::getUrl('view', ['record' => $record])),
             
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
            CrewPklRelationManager::class,
            CrewSignOffRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCrewSignOff::route('/'),
            'create' => Pages\CreateCrewSignOff::route('/create'),
            'edit' => Pages\EditCrewSignOff::route('/{record}/edit'),
            'view' => Pages\ViewCrewSignOff::route('/{record}'),

        ];
    }
}
