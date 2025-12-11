<?php

namespace App\Filament\Crew\Resources\CrewSignOns\RelationManagers;

use App\Models\Jabatan;
use App\Models\Kapal;
use App\Models\Perusahaan;
use App\Models\WilayahOperasional;
use Carbon\Carbon;
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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Facades\FilamentView;
use Filament\Support\RawJs;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\View\TablesRenderHook;
use Illuminate\Support\Facades\Blade;

class SigonRelationManager extends RelationManager
{
    protected static string $relationship = 'kontrak';
    protected $listeners = ['refresh' => '$refresh'];

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Penempatan Crew')
                    ->description('Informasi perusahaan, wilayah, kapal, dan jabatan crew')
                    ->columns([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 2,
                        'xl' => 3
                    ])
                    ->columnSpanFull()
                    ->schema([
                        Select::make('id_perusahaan')
                            ->label('Pilih Perusahaan')
                            ->placeholder('')
                            ->searchable()
                            ->preload()
                            ->options(Perusahaan::pluck('nama_perusahaan', 'id'))
                            ->native(false)
                            ->reactive()
                            ->columnSpan(2),

                        Select::make('id_wilayah')
                            ->label('Wilayah Operasional')
                            ->placeholder('')
                            ->searchable()
                            ->preload()
                            ->options(WilayahOperasional::pluck('nama_wilayah', 'id'))
                            ->native(false),
                        Select::make('id_kapal')
                            ->label('Nama Kapal')
                            ->placeholder('')
                            ->preload()
                            ->options(function (callable $get) {
                                $perusahaanId = $get('id_perusahaan');
                                if ($perusahaanId) {
                                    return Kapal::where('id_perusahaan', $perusahaanId)
                                        ->pluck('nama_kapal', 'id');
                                }
                                return [];
                            })
                            ->getSearchResultsUsing(
                                fn(string $search) =>
                                Kapal::where('nama_kapal', 'like', "%{$search}%")
                                    ->pluck('nama_kapal', 'id')
                            )
                            ->getOptionLabelUsing(
                                fn($value): ?string =>
                                Kapal::find($value)?->nama_kapal
                            )
                            ->searchable()
                            ->native(false),

                        Select::make('id_jabatan')
                            ->label('Jabatan Crew')
                            ->options(Jabatan::all()
                                ->mapWithKeys(fn($jabatan) => [
                                    $jabatan->id => "{$jabatan->nama_jabatan} ({$jabatan->devisi} - {$jabatan->golongan})",
                                ]))
                            ->placeholder('')
                            ->searchable()
                            ->preload()
                            ->native(false),

                        Select::make('berangkat_dari')
                            ->label('Berangkat Dari')
                            ->placeholder('')
                            ->searchable()
                            ->preload()
                            ->options([
                                'Jakarta' => 'Jakarta',
                                'Lokal' => 'Lokal'
                            ])
                            ->native(false),

                    ]),

                Section::make('Kontrak Crew')
                    ->description('Detail kontrak, gaji, tanggal mulai dan selesai, serta status kontrak')
                    ->columns([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 2,
                        'xl' => 3
                    ])
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('gaji')
                            ->label('Gaji (Rp.)')
                            ->prefix('Rp.')
                            ->mask(RawJs::make('$money($input)')),

                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai Kontrak')
                            ->displayFormat('d-M-Y')
                            ->prefixIcon('heroicon-m-calendar')
                            ->native(false)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $set('end_date', Carbon::parse($state)->addMonths(9)->format('Y-m-d H:i:s'));
                                } else {
                                    $set('end_date', null);
                                }
                            }),

                        DatePicker::make('end_date')
                            ->displayFormat('d-M-Y')
                            ->label('Tanggal Selesai Kontrak')
                            ->prefixIcon('heroicon-m-calendar')
                            ->native(false)
                            ->disabled(),

                        Select::make('kontrak_lanjutan')
                            ->label('Jenis Kontrak')
                            ->native(false)
                            ->options([
                                false => 'Baru',
                                true => 'Lanjutan'
                            ])
                            ->hidden()
                            ->default(false)
                            ->required(),

                        FileUpload::make('file')
                            ->label('Upload File Signed on')
                            ->columnSpan(1)
                            ->disk('public')
                            ->downloadable()
                            ->preserveFilenames()
                            ->directory('crew/signon')
                            ->columnSpanFull()
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            ])
                            ->required(),
                    ]),
            ]);
    }


    public function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->defaultSort('created_at', 'desc')
            ->heading('')
            ->recordTitleAttribute('nomor_dokumen')
            ->columns([
                TextColumn::make('nomor_dokumen')
                    ->label('Nomor'),
                TextColumn::make('perusahaan.kode_perusahaan')
                    ->searchable(),
                TextColumn::make('jabatan.kode_jabatan')
                    ->searchable(),
                TextColumn::make('wilayah.kode_wilayah')
                    ->searchable(),
                TextColumn::make('kapal.nama_kapal')
                    ->searchable(),
                TextColumn::make('kontrak_lanjutan')
                    ->label('Jenis Kontrak')
                    ->formatStateUsing(fn($state) => $state == true ? 'Lanjutan' : 'Baru'),
                TextColumn::make('start_date')
                    ->date('d-M-Y'),
                TextColumn::make('end_date')
                    ->date('d-M-Y'),
                TextColumn::make('status_kontrak')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'active' => 'success',
                        'expired' => 'danger',
                        'waiting approval' => 'warning',
                    }),
                TextColumn::make('end_date'),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->recordActions([
                Action::make('generate_document_signon')
                    ->button()
                    ->label('Generate dokumen')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-printer')
                    ->modalDescription('generate dokumen untuk kontrak ini')
                    ->schema([
                        Select::make('template_type')
                            ->native(false)
                            ->required()
                            ->placeholder('')
                            ->options([
                                1 => "template 1",
                                2 => "template 2",
                            ])
                    ])
                    ->modalWidth(Width::Small)
                    ->hidden(fn($record) => $record->kategory === 'promosi' || $record->file != null)
                    ->before(fn(array $data, $record,  $action) => redirect()->route('generate.signon', [
                        'id' => $record->id,
                        'template_type' => $data['template_type']
                    ]))
                    ->after(fn($record, $action) => $action->cancel()),
                EditAction::make()
                    ->button()
                    ->slideOver()

                    ->hidden(fn($record) => $record->kategory === 'promosi' || $record->status_kontrak != 'active' && $record->status_kontrak != 'waiting approval')
                    ->after(function ($record) {
                        if (!empty($record->file)) {
                            $record->crew()->update(['status' => 'active']);
                            $record->update(['status_kontrak' => 'active']);
                        }
                    }),
                DeleteAction::make()->button(),

            ])
            ->toolbarActions([]);
    }
}
