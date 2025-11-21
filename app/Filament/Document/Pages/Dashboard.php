<?php

namespace App\Filament\Document\Pages;

use App\Filament\Widgets\AccountWidget;
use App\Filament\Document\Widgets\DokumenAnalytic;
use App\Filament\Document\Widgets\DokumentNearExpired;
use App\Filament\Document\Widgets\StatusAllDokumen;
use App\Filament\Document\Widgets\StatusDokumen;
use App\Filament\Widgets\DateWidget;
use App\Models\Dokumen;
use App\Models\JenisDokumen;
use App\Models\Kapal;
use App\Models\Perusahaan;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as PagesDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;

class Dashboard extends PagesDashboard
{
    use HasFiltersForm;

    public static function getNavigationIcon(): string | BackedEnum | Htmlable | null
    {
        return 'heroicon-o-squares-2x2';
    }
    public function persistsFiltersInSession(): bool
    {
        return false;
    }

    public function filtersForm(Schema $form)
    {
        return $form->schema([
            Section::make('Filter')
                ->columnSpan(
                    [
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 4,
                    ]
                )

                ->extraAttributes(['class' => 'section-filter-dashboard'])
                ->schema([
                    Grid::make([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 3,
                        'xl' => 4
                    ])->schema([
                        Select::make('perusahaan')
                            ->label('')
                            ->placeholder('perusahaan')
                            ->native(false)
                            ->options(fn() => Perusahaan::pluck('nama_perusahaan', 'id'))
                            ->searchable()
                            ->reactive()
                            ->preload()
                            ->columnSpan(2)
                            ->afterStateUpdated(function ($set, $state) {
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
                                    return Kapal::where('id_perusahaan', $perusahaanId)
                                        ->pluck('nama_kapal', 'id')
                                        ->toArray();
                                }
                                return [];
                            })
                            ->searchable(),
                        Select::make('jenis')
                            ->label('')
                            ->placeholder('jenis dokumen')
                            ->native(false)
                            ->options(fn() => JenisDokumen::pluck('nama_jenis', 'id'))
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
                        ->color('warning')
                        ->icon('heroicon-o-x-mark')
                        ->button()
                        ->size(Size::Medium)
                        ->action(function () use ($form) {
                            $form->fill([]);
                        }),
                ])
        ]);
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return ["sm" => 1, "md" => 1, "lg" => 1, "xl" => 2];
    }
    public function getHeaderWidgets(): array
    {
        return [
            AccountWidget::class,
            DateWidget::class,
        ];
    }

    public function getColumns(): array|int
    {
        return 4;
    }
    public function getWidgets(): array
    {
        return [
            DokumenAnalytic::class,
            StatusAllDokumen::class,
            StatusDokumen::class,
        ];
    }

    public function getFooterWidgets(): array
    {
        return [
            DokumentNearExpired::class,
        ];
    }

    public function boot()
    {
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

        if (Dokumen::where('status', '!=', 'expired')->exists()) {
            // Log::info('Tidak ada dokumen expired');
            return null;
        }

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
    @livewire(\App\Filament\Document\Widgets\DokumenExpired::class)
    <x-slot name="footer">
        <x-filament::button x-on:click="$dispatch(\'close-modal\', { id: \'expired-docs-modal\' })">
            Tutup
        </x-filament::button>
    </x-slot>
</x-filament::modal>
');
    }
}
