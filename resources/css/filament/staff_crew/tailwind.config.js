import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/StaffCrew/**/*.php',
        './resources/views/filament/staff-crew/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
}
