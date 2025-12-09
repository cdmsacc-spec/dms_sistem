<?php

namespace App\Jobs;

use App\Services\FcmServices;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendFcmNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $token;
    public string $title;
    public string $body;
    public array $data;

    public function __construct($token, $title, $body, array $data = [])
    {
        $this->token = $token;
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
    }

    public function handle(FcmServices $fcm)
    {
        $fcm->sendToToken(
            $this->token,
            $this->title,
            $this->body,
            $this->data
        );
    }
}
