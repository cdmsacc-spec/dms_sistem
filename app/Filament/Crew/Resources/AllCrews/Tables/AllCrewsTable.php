<?php

namespace App\Filament\Crew\Resources\AllCrews\Tables;

use App\Models\Jabatan;
use App\Models\Perusahaan;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AllCrewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('avatar')
                    ->disk('public')
                    ->circular(),
                TextColumn::make('nama_crew')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('jenis_kelamin')
                    ->searchable(),
                TextColumn::make('no_hp')
                    ->label('Telepon')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('status_identitas')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->searchable()
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'draft' => 'info',
                        'ready for interview' => 'warning',
                        'inactive' => 'danger',
                        'standby' => 'primary',
                        'active' => 'success',
                        'rejected' => 'danger',
                        default => 'secondary',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->options(
                        [
                            'draft' => 'draft',
                            'ready for interview' => 'ready for interview',
                            'inactive' => 'inactive',
                            'standby' => 'standby',
                            'active' => 'active',
                            'rejected' => 'rejected',
                        ]
                    ),

                SelectFilter::make('jabatan')
                    ->label('Jabatan')
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->options(function () {
                        return Jabatan::pluck('golongan', 'id')->toArray();
                    })
                    ->query(function ($query, $data) {
                        if (empty($data['value'])) return;

                        $query->whereHas('kontrak', function ($q) use ($data) {
                            $q->where('status_kontrak', 'active')
                                ->whereHas('jabatan', function ($q2) use ($data) {
                                    $q2->where('id', $data['value']);
                                });
                        });
                    }),

                SelectFilter::make('perusahaaan')
                    ->label('Perusahaan')
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->options(function () {
                        return Perusahaan::get()->pluck('nama_perusahaan', 'nama_perusahaan')->toArray();
                    })
                    ->query(function ($query, $data) {
                        if (empty($data['value'])) return;
                        $query->whereHas('kontrak', function ($q) use ($data) {
                            $q->where('status_kontrak', 'active')
                                ->whereHas('perusahaan', function ($q2) use ($data) {
                                    $q2->where('nama_perusahaan', $data['value']);
                                });
                        });
                    }),

                Filter::make('usia')
                    ->label('Usia')
                    ->schema([
                        TextInput::make('age')
                            ->label('Usia')
                            ->numeric()
                            ->placeholder('Enter age'),
                    ])
                    ->query(function ($query, array $data) {
                        if (empty($data['age'])) {
                            return;
                        }
                        $query->whereRaw(
                            'EXTRACT(YEAR FROM AGE(current_date, tanggal_lahir)) = ?',
                            [$data['age']]
                        );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!empty($data['age'])) {
                            return "usia: " . $data['age'] . " Tahun";
                        }
                        return  null;
                    }),

            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->filtersApplyAction(fn(Action $action) => $action
                ->button()
                ->badgeColor('danger')
                ->color('info')
                ->label('Terapkan Filter'),)
            ->recordActions([
                Action::make('change_status')
                    ->label('Change To Standby')
                    ->button()
                    ->icon('heroicon-o-link')
                    ->color('warning')
                    ->hidden(fn($record) => $record->status != 'inactive')
                    ->action(function ($record) {
                        $record->update(['status' => 'standby']);
                        Notification::make()
                            ->title('Success')
                            ->body('Status proses has been updated to standby.')
                            ->success()
                            ->send();
                    }),
                Action::make('change_status')
                    ->label('Change To Draft')
                    ->button()
                    ->icon('heroicon-o-link')
                    ->color('warning')
                    ->hidden(fn($record) => $record->status != 'rejected')
                    ->action(function ($record) {
                        $record->update(['status' => 'draft']);
                        Notification::make()
                            ->title('Success')
                            ->body('Status proses has been updated to draft.')
                            ->success()
                            ->send();
                    }),
                ViewAction::make()->button(),
                EditAction::make()->button(),
                DeleteAction::make()->button()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
