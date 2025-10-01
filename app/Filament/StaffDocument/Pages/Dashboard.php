<?php

namespace App\Filament\StaffDocument\Pages;

use App\Models\JenisDocument;
use App\Models\NamaKapal;
use App\Models\Perusahaan;
use App\Models\WilayahOperasional;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\HtmlString;

class Dashboard extends \Filament\Pages\Dashboard
{
    use HasFiltersForm;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Dashboard';
    public function persistsFiltersInSession(): bool
    {
        return false;
    }


    public function filtersForm(Form $form)
    {
        return $form->schema([
            Section::make('Filter')
                ->extraAttributes(['class' => 'section-filter-dashboard'])
                ->schema([
                    Grid::make(4)->schema([
                        Select::make('perusahaan')
                            ->label('')
                            ->placeholder('perusahaan')
                            ->native(false)
                            ->options(fn() => Perusahaan::pluck('nama_perusahaan', 'id'))
                            ->searchable()
                            ->columnSpan(2),
                        Select::make('kapal')
                            ->label('')
                            ->placeholder('kapal')
                            ->native(false)
                            ->options(fn() => NamaKapal::pluck('nama_kapal', 'id'))
                            ->searchable(),
                        Select::make('jenis')
                            ->label('')
                            ->placeholder('jenis document')
                            ->native(false)
                            ->options(fn() => JenisDocument::pluck('nama_dokumen', 'id'))
                            ->searchable(),
                        DatePicker::make('dari_tanggal')
                            ->label('')
                            ->native(false)
                            ->placeholder('dari tanggal'),
                        DatePicker::make('sampai_tanggal')
                            ->label('')
                            ->native(false)
                            ->placeholder('sampai tanggal'),
                    ]),
                ])->headerActions([
                    Action::make('resetFilter')
                        ->label('Reset Filter')
                        ->color('danger')
                        ->button()
                        ->size(ActionSize::ExtraLarge)
                        ->action(function () use ($form) {
                            $form->fill([]);
                        }),
                ])
        ]);
    }
}
