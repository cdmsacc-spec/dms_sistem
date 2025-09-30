<?php

namespace App\Filament\StaffCrew\Resources;

use App\Filament\StaffCrew\Resources\CrewInterviewResource\Pages;
use App\Filament\StaffCrew\Resources\CrewInterviewResource\RelationManagers\CrewInterviewRelationManager;
use App\Models\CrewApplicants;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CrewInterviewResource extends Resource
{
    protected static ?string $model = CrewApplicants::class;
    protected static ?string $navigationLabel = 'Interview';
    protected static ?string $navigationGroup = 'Crew Management';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
    ->schema([
        // ==============================
        // Upload Berkas Interview
        // ==============================
        Section::make('Upload Berkas Interview')
            ->columns(3)
            ->schema([
                // Tanggal Interview
                Forms\Components\DatePicker::make('tanggal')
                    ->label('Tanggal Interview')
                    ->prefixIcon('heroicon-m-calendar')
                    ->native(false)
                    ->dehydrated(false),

                // Grid hasil interview
                Grid::make(2)->schema([
                    Forms\Components\TextInput::make('hasil_interviewe1')
                        ->label('Hasil Interviewer 1')
                        ->dehydrated(false),

                    Forms\Components\TextInput::make('hasil_interviewe2')
                        ->label('Hasil Interviewer 2')
                        ->dehydrated(false),

                    Forms\Components\TextInput::make('hasil_interviewe3')
                        ->label('Hasil Interviewer 3')
                        ->dehydrated(false),

                    Forms\Components\TextInput::make('sumary')
                        ->label('Summary')
                        ->dehydrated(false),
                ]),

                // Keterangan
                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan Tambahan')
                    ->columnSpanFull()
                    ->dehydrated(false),

                // File Upload
                Forms\Components\FileUpload::make('file_path')
                    ->label('Upload File Interview')
                    ->columnSpanFull()
                    ->disk('public')
                    ->directory('crew/interview')
                    ->preserveFilenames()
                    ->dehydrated(false),
            ]),

        // ==============================
        // Status Proses
        // ==============================
        Section::make('Status')
            ->schema([
                Forms\Components\Radio::make('status_proses')
                    ->label('Status Proses')
                    ->options([
                        'Draft' => 'Draft',
                        'Ready For Interview' => 'Ready For Interview',
                        'Standby' => 'Standby',
                        'Inactive' => 'Inactive',
                        'Active' => 'Active',
                    ])
                    ->required()
                    ->columns(5)
                    ->columnSpan(1),
            ]),
    ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('status_proses', 'Ready For Interview'))
            ->emptyStateHeading('Tidak Ada Data')
            ->defaultSort('created_at', 'desc')
            ->emptyStateDescription('belum ada data ditambahkan')
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
                    ->label('Interview'),
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
        //  if (request()->routeIs('filament.staff_crew.resources.crew-interviews.edit')) {
        //     return []; 
        // }

        return [
            CrewInterviewRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCrewInterviews::route('/'),
            'create' => Pages\CreateCrewInterview::route('/create'),
            'edit' => Pages\EditCrewInterview::route('/{record}/edit'),
        ];
    }
}
