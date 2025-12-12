<?php

namespace App\Filament\Crew\Resources\CrewSignoffs\RelationManagers;

use App\Filament\Crew\Resources\CrewSignoffs\CrewSignoffResource;
use Filament\Actions\Action;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;

class SignoffRelationManager extends RelationManager
{
    protected static string $relationship = 'signoff';
    protected $listeners = ['refresh' => '$refresh'];
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('tanggal'),
                Hidden::make('keterangan'),
                Hidden::make('id_alasan'),
                FileUpload::make('file')
                    ->label('File')
                    ->columnSpan(2)
                    ->disk('public')
                    ->downloadable()
                    ->preserveFilenames()
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ])
                    ->directory('crew/signoff'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->defaultPaginationPageOption('5')
            ->defaultSort('created_at', 'desc')
            ->heading('')
            ->recordTitleAttribute('nomor_dokumen')
            ->columns([
                TextColumn::make('index')
                    ->label('No. ')
                    ->width('sm')
                    ->rowIndex(),
                TextColumn::make('nomor_dokumen')
                    ->searchable(),
                TextColumn::make('tanggal')
                    ->date('d-M-Y')
                    ->searchable(),
                TextColumn::make('alasanBerhenti.nama_alasan')
                    ->searchable(),
                TextColumn::make('keterangan')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->recordActions([
                Action::make('generate_document_sign_off')
                    ->button()
                    ->label('Generate dokumen')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-printer')
                    ->modalDescription('generate dokumen sign off untuk kontrak ini')
                    ->modalWidth(Width::Small)
                    ->hidden(fn($record) => $record->file != null)
                    ->before(fn($record,  $action) => redirect()->route('generate.signoff', [
                        'id' => $this->ownerRecord->id,
                        'id_sign_off' => $record->id
                    ]))
                    ->after(fn($record, $action) => $action->cancel()),
                EditAction::make()
                    ->button()
                    ->label('Upload File')
                    ->modalWidth('md')
                    ->modalHeading('Upload File Sign Off')
                    ->modalIcon('heroicon-o-pencil-square')
                    ->modalAlignment('center')
                    ->modalDescription('dengan mengupload file maka status kontrak crew akan berubah menjadi expired dan status crew adalah inactive')
                    // ->hidden(fn($record) => $record->file != null)
                    ->after(function ($record) {
                        if (!empty($record->file)) {
                            $record->crew()->update(['status' => 'inactive']);
                            $record->crew->kontrak()->update(['status_kontrak' => 'expired', 'end_date' => $record->tanggal]);
                            redirect(CrewSignoffResource::getUrl('index'));
                        }
                    }),
                DeleteAction::make()->button(),
            ])
            ->toolbarActions([]);
    }
}
