<?php

namespace App\Filament\Document\Widgets;

use App\Filament\Document\Resources\Dokumens\DokumenResource;
use App\Models\Dokumen;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;

class DokumenAnalytic extends StatsOverviewWidget
{

    use HasWidgetShield;
    use InteractsWithPageFilters;

    protected ?string $heading = 'Document Analytics';
    protected static bool $isLazy = true;

    public function getColumns(): array|int|null
    {
        return [
            'sm' => 1,
            'md' => 2,
            'lg' => 2,
            'xl' => 4
        ];
    }

    protected function getStats(): array
    {
        $perusahaan    = $this->filters['perusahaan'] ?? null;
        $kapal         = $this->filters['kapal'] ?? null;
        $jenis         = $this->filters['jenis'] ?? null;
        $dari_tanggal  = $this->filters['dari_tanggal'] ?? null;
        $sampai_tanggal = $this->filters['sampai_tanggal'] ?? null;

        $results = Dokumen::query()
            ->when($jenis, fn($q) => $q->where('id_jenis_dokumen', $jenis))
            ->when(
                $perusahaan,
                fn($q) =>
                $q->whereHas('kapal', fn($qq) => $qq->where('id_perusahaan', $perusahaan))
            )
            ->when(
                $kapal,
                fn($q) =>
                $q->whereHas('kapal', fn($qq) => $qq->where('id', $kapal))
            )
            ->when(
                $dari_tanggal && $sampai_tanggal,
                fn($q) =>
                $q->whereBetween('created_at', [$dari_tanggal, $sampai_tanggal])
            )
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $total_document             = $results->sum();
        $total_document_uptodate    = $results['uptodate']    ?? 0;
        $total_document_expired     = $results['expired']     ?? 0;
        $total_document_near_expiry = $results['near expiry'] ?? 0;


        $data = [
            [
                "total_data" => $total_document,
                "color" => 'info',
                "label" => 'Total Dokumen',
                "query" => ''
            ],
            [
                "total_data" => $total_document_uptodate,
                "color" => 'success',
                "label" => 'Uptodate',
                "query" => 'uptodate'
            ],
            [
                "total_data" => $total_document_near_expiry,
                "color" => 'warning',
                "label" => 'Near Expiry',
                "query" => 'near expiry'
            ],
            [
                "total_data" => $total_document_expired,
                "color" => 'danger',
                "label" => 'Expired',
                "query" => 'expired',
            ]
        ];

        $stats =  [];

        foreach ($data as $value) {
            $stats[] = Stat::make('A', $value['total_data'])
                ->icon('heroicon-o-folder-open')
                ->label(new HtmlString('<span class="text-xs font-medium text-' . $value['color'] . '-600">' . $value['label'] . '</span>'))
                ->description('')
                ->chart([10, 10])
                ->extraAttributes(['class' => 'stats-'.$value['label']])
                ->color($value['color'])
                ->url(auth()->user()?->can('view-any:dokumen') == true ? DokumenResource::getUrl() . '?' . http_build_query([
                    'filters' => [
                        'status' => [
                            'value' => $value['query']
                        ],
                    ],
                ]) : null);
        }

        return $stats;
    }
}
