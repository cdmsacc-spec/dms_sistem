<?php

namespace App\Filament\Crew\Resources\AllCrews\Pages;

use App\Filament\Crew\Resources\AllCrews\AllCrewResource;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Hugomyb\FilamentMediaAction\Actions\MediaAction;
use Illuminate\Support\Facades\Storage;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Carbon;

class HistoryInterview extends ManageRelatedRecords
{
    protected static string $resource = AllCrewResource::class;

    protected static string $relationship = 'interview';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('tanggal')
                    ->displayFormat('d-M-Y')
                    ->label('Tanggal Interview')
                    ->columnSpan(2)
                    ->prefixIcon('heroicon-m-calendar')
                    ->native(false)
                    ->required(),
                Textarea::make('crewing')
                    ->label('Crewing')
                    ->required()
                    ->columnSpan(1),
                Textarea::make('user_operation')
                    ->label('User Operation')
                    ->required()
                    ->columnSpan(1),
                Textarea::make('summary')
                    ->label('Summary')
                    ->required()
                    ->columnSpan(1),
                Textarea::make('keterangan')
                    ->label('Keterangan Tambahan')
                    ->required()
                    ->columnSpan(1),
                FileUpload::make('file')
                    ->label('Upload File Interview')
                    ->columnSpanFull()
                    ->required()
                    ->preserveFilenames()
                    ->maxSize(10240)
                    ->disk('public')
                    ->directory('crew/interview')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('')
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->defaultPaginationPageOption('5')
            ->columns([
                TextColumn::make('index')
                    ->label('No. ')
                    ->width('sm')
                    ->rowIndex(),
                TextColumn::make('tanggal')
                    ->badge()
                    ->formatStateUsing(fn($state) => Carbon::parse($state)->format('d-M-Y'))
                    ->color('success')
                    ->icon('heroicon-m-calendar'),
                TextColumn::make('keterangan'),
                TextColumn::make('crewing')
                    ->label('Crewing'),
                TextColumn::make('user_operation')
                    ->label('User operation'),
                TextColumn::make('summary')
                    ->label('Summary'),
            ])
            ->headerActions([])
            ->recordActions([
                Action::make('download')
                    ->size('sm')
                    ->color('info')
                    ->button()
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn($record) => asset('storage/' . $record->file), shouldOpenInNewTab: true)
                    ->visible(function ($record) {
                        $path = $record->file ?? null;
                        if (! $path) return false;
                        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                        return $extension != 'pdf';
                    }),

                MediaAction::make('Preview')
                    ->label('Preview')
                    ->size('sm')
                    ->color('info')
                    ->button()
                    ->modalWidth('full')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn($record) => 'Interview ' . $record->tanggal)
                    ->media(fn($record) => str_replace(' ', '%20', Storage::url($record->file)))
                    ->visible(function ($record) {
                        $path = $record->file ?? null;
                        if (! $path) return false;
                        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                        return $extension === 'pdf';
                    }),

                EditAction::make()->button()
                    ->modalIcon('heroicon-o-pencil-square')
                    ->modalHeading('Edit Data interview')
                    ->modalAlignment(Alignment::Center),
                ViewAction::make()->button()
                    ->modalIcon('heroicon-o-eye')
                    ->modalHeading('View Data interview')
                    ->modalAlignment(Alignment::Center),
                DeleteAction::make()->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
