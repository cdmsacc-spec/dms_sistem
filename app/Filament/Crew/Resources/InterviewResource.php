<?php

namespace App\Filament\Crew\Resources;

use App\Enums\StatusCrew;
use App\Filament\Crew\Resources\InterviewResource\Pages;
use App\Filament\Crew\Resources\InterviewResource\RelationManagers\CrewInterviewRelationManager;
use App\Models\CrewApplicants;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InterviewResource extends Resource
{
    protected static ?string $model = CrewApplicants::class;
    protected static ?string $slug = 'crew-interview';
    protected static ?string $navigationLabel = 'Interview';
    protected static ?string $pluralModelLabel = 'Interviews';
    protected static ?string $navigationGroup = 'Crew Management';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // ==============================
                // Upload Berkas Interview
                // ==============================
                Section::make('')
                    ->columns(3)
                    ->schema([
                        // Grid hasil interview\
                        DatePicker::make('tanggal')
                            ->label('Tanggal Interview')
                            ->prefixIcon('heroicon-m-calendar')
                            ->native(false)
                            ->required(),
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('hasil_interviewe1')
                                ->label('Hasil Interviewer 1')
                                ->required()
                                ->dehydrated(false),

                            Forms\Components\TextInput::make('hasil_interviewe2')
                                ->label('Hasil Interviewer 2')
                                ->required()
                                ->dehydrated(false),

                            Forms\Components\TextInput::make('hasil_interviewe3')
                                ->label('Hasil Interviewer 3')
                                ->required()
                                ->dehydrateStateUsing(fn($state) => $state),

                            Forms\Components\TextInput::make('sumary')
                                ->label('Summary')
                                ->required()
                                ->dehydrateStateUsing(fn($state) => $state),
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->where('status_proses', StatusCrew::ReadyForInterview))
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
                Tables\Columns\TextColumn::make('status_proses')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        StatusCrew::Draft->value => 'info',
                        StatusCrew::ReadyForInterview->value => 'warning',
                        StatusCrew::Inactive->value => 'danger',
                        StatusCrew::Standby->value => 'primary',
                        StatusCrew::Active->value => 'success',
                        default => 'secondary',
                    })->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->button()
                    ->label('Interview'),
                Tables\Actions\Action::make('detail')
                    ->button()
                    ->color('success')
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
            CrewInterviewRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInterviews::route('/'),
            'create' => Pages\CreateInterview::route('/create'),
            'edit' => Pages\EditInterview::route('/{record}/edit'),
        ];
    }
}
