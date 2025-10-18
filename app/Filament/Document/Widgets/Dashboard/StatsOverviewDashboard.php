<?php

namespace App\Filament\Document\Widgets\Dashboard;

use App\Filament\Document\Resources\DocumentResource;
use App\Models\Document;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;



class StatsOverviewDashboard extends BaseWidget
{
    use InteractsWithPageFilters;
    use HasWidgetShield;

    protected ?string $heading = 'Document Analytics';
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 4;
    protected function getStats(): array
    {
        $perusahaan    = $this->filters['perusahaan'] ?? null;
        $kapal         = $this->filters['kapal'] ?? null;
        $jenis         = $this->filters['jenis'] ?? null;
        $dari_tanggal  = $this->filters['dari_tanggal'] ?? null;
        $sampai_tanggal = $this->filters['sampai_tanggal'] ?? null;

        // Query sekali saja
        $results = Document::query()
            ->when($jenis, fn($q) => $q->where('jenis_dokumen_id', $jenis))
            ->when(
                $perusahaan,
                fn($q) =>
                $q->whereHas('kapal', fn($qq) => $qq->where('perusahaan_id', $perusahaan))
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

        // Hitung total semua dokumen
        $total_document             = $results->sum();

        // Ambil hasil per status (default 0 kalau tidak ada)
        $total_document_uptodate    = $results['UpToDate']    ?? 0;
        $total_document_expired     = $results['Expired']     ?? 0;
        $total_document_near_expiry = $results['Near Expiry'] ?? 0;

        $data = [
            [
                "total_data" => $total_document,
                "color" => 'info',
                "label" => 'Total Document',
                "query" => ''
            ],
            [
                "total_data" => $total_document_uptodate,
                "color" => 'success',
                "label" => 'Uptodate',
                "query" => 'UpToDate'
            ],
            [
                "total_data" => $total_document_near_expiry,
                "color" => 'warning',
                "label" => 'Near Expiry',
                "query" => 'Near Expiry'
            ],
            [
                "total_data" => $total_document_expired,
                "color" => 'danger',
                "label" => 'Expired',
                "query" => 'Expired',
            ]
        ];

        $stats =  [];

        foreach ($data as $value) {
            $stats[] = Stat::make('A', $value['total_data'])
                ->icon('heroicon-o-folder-open')
                ->label(new HtmlString('<span class="text-xs font-medium text-' . $value['color'] . '-600">' . $value['label'] . '</span>'))
                ->description('')
                ->chart([10, 10])
                ->color($value['color'])
                ->url(auth()->user()?->can('view_any_document') == true ? DocumentResource::getUrl() . '?' . http_build_query([
                    'tableFilters' => [
                        'status' => [
                            'value' => $value['query']
                        ],
                    ],
                ]) : null);
        }

        return $stats;
    }
}
