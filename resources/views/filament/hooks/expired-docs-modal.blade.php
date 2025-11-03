@php
    $showModal = false;
    $expiredDocuments = [];

    // Simulasikan kondisi user sedang di dashboard
    if (auth()->check() && request()->routeIs('filament.document.pages.dashboard')) {
        // 💡 Data dummy (misal hasil query expired document)
        $expiredDocuments = [
            (object)[
                'name' => 'Sertifikat Kapal A',
                'expired_date' => now()->subDays(5)->format('d M Y'),
                'url' => '#'
            ],
            (object)[
                'name' => 'Sertifikat Keselamatan B',
                'expired_date' => now()->subDays(10)->format('d M Y'),
                'url' => '#'
            ],
            (object)[
                'name' => 'Sertifikat Peralatan C',
                'expired_date' => now()->subDays(2)->format('d M Y'),
                'url' => '#'
            ],
        ];

        $showModal = count($expiredDocuments) > 0;
    }
@endphp

@if ($showModal)
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.dispatchEvent(new CustomEvent('open-modal', {
                detail: { id: 'expired-docs-modal' }
            }));
        });
    </script>

    <x-filament::modal id="expired-docs-modal" width="xl">
        <x-slot name="heading">
            📄 Dokumen Sudah Expired
        </x-slot>

        <div class="space-y-2">
            @foreach ($expiredDocuments as $doc)
                <div class="flex justify-between items-center border-b py-2">
                    <div>
                        <strong>{{ $doc->name }}</strong><br>
                        <span class="text-sm text-gray-500">
                            Expired: {{ $doc->expired_date }}
                        </span>
                    </div>
                    <a href="{{ $doc->url }}"
                       class="text-primary-600 text-sm font-medium">
                        Lihat
                    </a>
                </div>
            @endforeach
        </div>

        <x-slot name="footer">
            <x-filament::button x-on:click="$dispatch('close-modal', { id: 'expired-docs-modal' })">
                Tutup
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
@endif
