@php
$user = filament()->auth()->user();
use Carbon\Carbon;
use Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage;

$hour = Carbon::now()->format('H');
if ($hour < 12) { $greeting='Selamat Pagi' ; } elseif ($hour < 15) { $greeting='Selamat Siang' ; } elseif ($hour < 18) {
    $greeting='Selamat Sore' ; } else { $greeting='Selamat Malam' ; } @endphp <x-filament-widgets::widget
    class="fi-account-widget">
    <x-filament::section>
        <div class="flex items-center gap-x-3">
            <div class="w-16 h-16 rounded-full overflow-hidden">
                @if ($user->getFilamentAvatarUrl())
                <img src="{{ $user->getFilamentAvatarUrl() }}" alt="{{ $user->name }}"
                    class="w-full h-full object-cover rounded-full">
                @else
                <x-filament-panels::avatar.user :user="$user"
                    class="w-full h-full rounded-full bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-300" />
                @endif

            </div>
            <div class="flex-1">
                {{-- Greeting --}}
                <h2 class="text-xl font-semibold leading-6 text-gray-950 dark:text-white">
                    {{ $greeting }},
                    <span class="text-primary-600 dark:text-primary-400 text-xl">
                        {{ filament()->getUserName($user) }}
                    </span>
                </h2>

                {{-- Role --}}
                @if ($user && method_exists($user, 'getRoleNames'))
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ $user->getRoleNames()->map(fn($r) => Str::title(str_replace('_', ' ', $r)))->implode(', ') }}
                </p>
                @endif
            </div>

            <form action="{{ filament()->getLogoutUrl() }}" method="post" class="my-auto">
                @csrf

                @can('page_EditProfilePage')
                <x-filament::button color="info" icon="heroicon-m-user-circle" labeled-from="sm" tag="a"
                    :href="EditProfilePage::getUrl()">
                    {{ __('Edit Profil') }}
                </x-filament::button>
                @endcan

            </form>
        </div>
    </x-filament::section>
    </x-filament-widgets::widget>