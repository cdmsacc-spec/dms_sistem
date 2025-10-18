<?php

namespace App\Http\Controllers;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Log;

class LogoutController
{

    public function destroy($request)
    {
         Log::info('User logged out: 1');
        $user = $request->user();
         Log::info('User logged out: ');
        if ($user) {
            Log::info('User logged out: ' . $user->id . ' - ' . $user->name);
        }
        $panel = Filament::getCurrentPanel(); // Ambil panel saat ini
        $loginUrl = $panel ? $panel->getLoginUrl() : route('filament.auth.login');

        return redirect($loginUrl);
    }
}
