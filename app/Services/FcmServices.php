<?php

namespace App\Services;

use Google\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmServices
{
    private $client;
    private $projectId;

    public function __construct()
    {
        $path = storage_path('app/firebase/service-account.json');

        if (!file_exists($path)) {
            throw new \Exception("Service account file not found: " . $path);
        }

        $this->client = new Client();
        $this->client->setAuthConfig($path);
        $this->client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $json = json_decode(file_get_contents($path), true);
        $this->projectId = $json['project_id'];
    }

    private function getAccessToken()
    {
        $token = $this->client->fetchAccessTokenWithAssertion();

        Log::info("FCM Access Token", $token);

        return $token['access_token'];
    }

    public function sendToToken(string $token, string $title, string $body, array $data = [])
    {
        $accessToken = $this->getAccessToken();

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        $payload = [
            "message" => [
                "token" => $token,
                "notification" => [
                    "title" => $title,
                    "body"  => $body
                ],
                "data" => array_map('strval', $data)
            ]
        ];

        $response = Http::withToken($accessToken)
            ->post($url, $payload);

        Log::info("FCM Response", [
            "status" => $response->status(),
            "body"   => $response->body()
        ]);

        return $response->json();
    }
}
