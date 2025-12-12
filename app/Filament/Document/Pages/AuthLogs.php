<?php

namespace App\Filament\Document\Pages;

use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelAuthenticationLog\Models\AuthenticationLog;
use UnitEnum;

class AuthLogs extends Page implements HasTable
{

    use InteractsWithTable;
    use HasPageShield;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';
    protected string $view = 'filament.document.pages.auth-logs';
    protected static ?string $navigationLabel = "Authentication Log";
    protected static ?string $pluralModelLabel = "Authentication Log";
    protected static string | UnitEnum | null $navigationGroup = 'Logs';
    protected static ?string $permissionGroup = 'User';
    public function table(Table $table): Table
    {
        return $table
            ->query(AuthenticationLog::query()
                ->with(['authenticatable.roles'])
                ->whereHas('authenticatable.roles', function ($query) {
                    $query->whereIn('name', [
                        'staff_dokumen',
                        'manager_dokumen',
                        'operation_dokumen',
                    ]);
                }))
            ->defaultSort(
                config('filament-authentication-log.sort.column'),
                config('filament-authentication-log.sort.direction'),
            )
            ->defaultPaginationPageOption('5')
            ->columns([
                TextColumn::make('index')
                    ->label('No. ')
                    ->width('sm')
                    ->rowIndex(),
                TextColumn::make('authenticatable.name')
                    ->label('Nama User')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                TextColumn::make('ip_address')
                    ->label('Alamat IP'),

                TextColumn::make('user_agent')->searchable()
                    ->sortable()
                    ->limit(50)
                    ->label('User Agent')
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        return $state;
                    }),
                TextColumn::make('login_at')
                    ->label('Login At')
                    ->dateTime(),

                IconColumn::make('login_successful')
                    ->label(trans('filament-authentication-log::filament-authentication-log.column.login_successful'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('logout_at')
                    ->label('Logout At')
                    ->dateTime(),

            ])
            ->filtersFormColumns(1)
            ->filtersApplyAction(fn(Action $action) => $action
                ->button()
                ->badgeColor('danger')
                ->color('info')
                ->label('Terapkan Filter'),)
            ->filtersTriggerAction(
                fn(Action $action) => $action
                    ->button()
                    ->badgeColor('danger')
                    ->color('info')
                    ->label('Filter'),
            )
            ->filters([
                Filter::make('login_successful')
                    ->toggle()
                    ->query(fn(Builder $query): Builder => $query->where('login_successful', true)),
                Filter::make('login_at')
                    ->schema([
                        DatePicker::make('login_from'),
                        DatePicker::make('login_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['login_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('login_at', '>=', $date),
                            )
                            ->when(
                                $data['login_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('login_at', '<=', $date),
                            );
                    }),
            ]);
    }
}
