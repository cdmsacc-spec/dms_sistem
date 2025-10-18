@php
use Carbon\Carbon;

$user = filament()->auth()->user();
$now = Carbon::now();
$tanggal = $now->translatedFormat('l, d F Y');
$jam = $now->format('H:i:s');
@endphp

<x-filament-widgets::widget class="fi-account-widget">
    <x-filament::section>
        <div class="flex items-center justify-between">
             
            {{-- Bagian Tanggal & Jam --}}
            <div class="flex flex-col flex-1">
                {{-- Tanggal Besar --}}
                <p id="tanggal" class="text-2xl font-semibold text-primary-600 dark:text-white leading-tight">
                {{ $tanggal }}
                </p>

                {{-- Jam di bawah tanggal --}}
                <p id="jam" class="text-xs text-gray-500 dark:text-gray-400 mt-4">
                    {{ $jam }}
                </p>
            </div>

            {{-- Tombol Logout --}}
            <form action="{{ filament()->getLogoutUrl() }}" method="post" class="ml-4 my-auto">
                @csrf
                <x-filament::button color="gray" icon="heroicon-m-arrow-left-on-rectangle" labeled-from="sm"
                    tag="button" type="submit">
                    {{ __('Logout') }}
                </x-filament::button>
            </form>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

{{-- Real-time update --}}
<script>
    setInterval(() => {
        const now = new Date();

        // Format tanggal dan jam
        const tanggal = now.toLocaleDateString('id-ID', {
            weekday: 'long', day: '2-digit', month: 'long', year: 'numeric'
        });
        const jam = now.toLocaleTimeString('id-ID', { hour12: false });

        // Update elemen
        document.getElementById('tanggal').textContent = `📅 ${tanggal}`;
        document.getElementById('jam').textContent = `${jam}`;
    }, 1000);
</script>