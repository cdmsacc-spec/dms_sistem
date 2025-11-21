@php
$user = filament()->auth()->user();
use Carbon\Carbon;
use App\Filament\Pages\EditProfiles;

$hour = Carbon::now()->format('H');
if ($hour < 12) { $greeting='Selamat Pagi' ; } elseif ($hour < 15) { $greeting='Selamat Siang' ; } elseif ($hour < 18) {
    $greeting='Selamat Sore' ; } else { $greeting='Selamat Malam' ; } @endphp <x-filament-widgets::widget
    class="fi-account-widget" style="border-radius: 12px;">
    <x-filament::section>
        <div style="
        display: flex;
        align-items: center;
        width: 100%;
    ">

            {{-- Avatar --}}
            <div style="width: 64px; height: 64px; border-radius: 100%; overflow: hidden;">
                @if ($user->getFilamentAvatarUrl())
                <img src="{{ $user->getFilamentAvatarUrl() }}" alt="{{ $user->name }}"
                    style="width: 100%; height: 100%; object-fit: cover; border-radius: 100%;">
                @else
                <x-filament-panels::avatar.user :user="$user"
                    style="width: 100%; height: 100%; border-radius: 100%; background: #e5e7eb; color: #374151;" />
                @endif
            </div>

            {{-- Tanggal & Jam --}}
            <div style="flex: 1; margin-left: 12px;">
                <h2 style="
                font-size: 20px;
                font-weight: 600;
                margin: 0;
                color: var(--fi-color-gray-900);
            ">
                    {{ $greeting }},
                </h2>

                <p style="font-size: 12px; margin-top: 4px; color: #6b7280;">
                    {{ filament()->getUserName($user) }} {{ $user->getRoleNames()->map(fn($r) =>
                    Str::title(str_replace('_', ' ', $r)))->implode(', ') }}
                </p>
            </div>

            {{-- Tombol --}}
             @can('view:edit-profiles')
            <form action="" method="post" style="margin:0; margin-left:auto;">
                @csrf

                <a href="{{EditProfiles::getUrl() }}" style="
                background: rgb(0, 110, 255);
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
                        stroke="currentColor" class="size-6" style="width:20px;height:20px;">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>

                    Edit Profile
                </a>
            </form>
            @endcan

        </div>
    </x-filament::section>

    </x-filament-widgets::widget>