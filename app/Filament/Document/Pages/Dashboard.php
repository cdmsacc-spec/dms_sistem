<?php

namespace App\Filament\Document\Pages;

use App\Filament\Document\Widgets\Dashboard\AccountWidgets;
use App\Filament\Document\Widgets\Dashboard\BarChartDashboard;
use App\Filament\Document\Widgets\Dashboard\DateWidgets;
use App\Filament\Document\Widgets\Dashboard\PieChartDashboard;
use App\Filament\Document\Widgets\Dashboard\StatsOverviewDashboard;
use App\Filament\Document\Widgets\Dashboard\TabelExpiredDashboard;
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
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
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

    public function getColumns(): array|int|string
    {
        return 4;
    }

    public function getHeaderWidgets(): array
    {
        return [
            AccountWidgets::class,
            DateWidgets::class,

        ];
    }

    public function getWidgets(): array
    {
        return [
            StatsOverviewDashboard::class,
            BarChartDashboard::class,
            PieChartDashboard::class,
            TabelExpiredDashboard::class
        ];
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
                            ->reactive()
                            ->preload()
                            ->columnSpan(2)
                            ->afterStateUpdated(function (\Filament\Forms\Get $get, \Filament\Forms\Set $set, $state) {
                                if (empty($state)) {
                                    $set('kapal', null);
                                }
                            }),
                        Select::make('kapal')
                            ->label('')
                            ->placeholder('kapal')
                            ->native(false)
                            ->options(function (callable $get) {
                                $perusahaanId = $get('perusahaan');
                                if ($perusahaanId) {
                                    return Namakapal::where('perusahaan_id', $perusahaanId)
                                        ->pluck('nama_kapal', 'id')
                                        ->toArray();
                                }
                                return [];
                            })
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


    public function boot()
    {
        // Render hook setelah konten utama dashboard selesai dimuat
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn(): string => Blade::render($this->getExpiredDocumentsModal())
        );
    }

    protected function getExpiredDocumentsModal(): ?string
    {
        if (! session('show_expired_modal')) {
            return null;
        }

        // Hapus flag agar tidak muncul lagi setelah ditampilkan sekali
        session()->forget('show_expired_modal');



        return Blade::render('
<script>
    document.addEventListener("livewire:navigated", () => {
        window.dispatchEvent(new CustomEvent("open-modal", { detail: { id: "expired-docs-modal" } }));
    });
</script>


<x-filament::modal id="expired-docs-modal" width="5xl">
    <x-slot name="heading">
        📄 Dokumen Sudah Expired
    </x-slot>
    @livewire(\App\Filament\Document\Widgets\DocumentExpiredDashboard::class)
    <x-slot name="footer">
        <x-filament::button x-on:click="$dispatch(\'close-modal\', { id: \'expired-docs-modal\' })">
            Tutup
        </x-filament::button>
    </x-slot>
</x-filament::modal>
');
    }
}
