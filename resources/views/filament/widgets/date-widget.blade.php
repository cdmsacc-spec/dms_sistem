@php
use Carbon\Carbon;

$user = filament()->auth()->user();
$now = Carbon::now();
$tanggal = $now->translatedFormat('l, d F Y');
$jam = $now->format('H:i:s');
@endphp


<x-filament-widgets::widget class="fi-account-widget" style="border-radius: 12px;">
    <x-filament::section>
        <div style="
        display: flex;
        align-items: center;
        width: 100%;
    ">

            {{-- Avatar --}}
            <div style="width: 64px; height: 64px; border-radius: 100%; overflow: hidden;">
                <img src="{{ asset('img/logo_callender.png') }}" style="width:100%; height:100%; object-fit:cover;">
            </div>

            {{-- Tanggal & Jam --}}
            <div style="flex: 1; margin-left: 12px;">
                <h2 style="
                font-size: 20px;
                font-weight: 600;
                margin: 0;
                color: var(--fi-color-gray-900);
            " id="tanggal">
                    {{ $tanggal }}
                </h2>

                <p id="jam" style="font-size: 12px; margin-top: 4px; color: var(--fi-color-gray-500);">
                    {{ $jam }}
                </p>
            </div>

            {{-- Tombol --}}
            <form action="{{ filament()->getLogoutUrl() }}" method="post" style="margin:0; margin-left:auto;">
                @csrf

                <button type="submit" style="  background: rgb(252, 44, 44);
                color: white;
                display: flex;
                align-items: center;
                gap: 6px;
                padding: 8px 16px;
                border-radius: 8px;
                font-size: 12px;
                text-decoration: none;
            ">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" style="width:20px;height:20px;">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" />
                    </svg>

                    Logout
                </button>
            </form>

        </div>
    </x-filament::section>

</x-filament-widgets::widget>





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