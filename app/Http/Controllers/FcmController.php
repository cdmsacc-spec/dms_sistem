<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\FcmTokenService;

class FcmController extends Controller
{
    public function send(Request $request)
    {
        $token = FcmTokenService::getAccessToken();
        $projectId = config('services.fcm.project_id');

        $response = Http::withToken($token)
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                "message" => [
                    "token" => $request->device_token,
                    "notification" => [
                        "title" => $request->title ?? "New Notification",
                        "body" => $request->body ?? "You have a new message"
                    ],
                    "data" => [
                        "route" => $request->route ?? "/doc/dashboard/dokumen/detail",
                        "id" => $request->id ?? "0",
                    ]
                ]
            ]);

        return response()->json($response->json());
    }
}
