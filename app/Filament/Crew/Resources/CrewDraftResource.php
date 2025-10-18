<?php

namespace App\Filament\Crew\Resources;

use App\Enums\StatusCrew;
use App\Filament\Crew\Resources\CrewDraftResource\Pages\ListCrewDrafts;
use App\Models\CrewApplicants;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CrewDraftResource extends Resource
{
    protected static ?string $slug = 'crew-draft';
    protected static ?string $model = CrewApplicants::class;
    protected static ?string $navigationLabel = 'Draft';
    protected static ?string $pluralModelLabel = 'Draft';
    protected static ?string $navigationGroup = 'Crew Management';
    protected static ?int $navigationSort = 3;
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('status_proses', StatusCrew::Draft))
            ->emptyStateHeading('Tidak Ada Data')
            ->defaultSort('created_at', 'desc')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->columns([
                Tables\Columns\ImageColumn::make('foto')
                    ->circular(),
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
            ->actions([
                Tables\Actions\Action::make('update_status')
                    ->button()
                    ->requiresConfirmation()
                    ->modalDescription('Are you sure you want to update the crew’s status to Ready for Interview?')
                    ->action(function ($record) {
                        $record->update(['status_proses' => StatusCrew::ReadyForInterview]);
                        Notification::make()
                            ->title('Success')
                            ->body('Status proses has been updated to Ready For Interview.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\ViewAction::make()->color('success')->button()->label('Detail')
                    ->url(fn($record) => CrewAllResource::getUrl('view', ['record' => $record])),
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
            'index' => ListCrewDrafts::route('/'),
        ];
    }
}
