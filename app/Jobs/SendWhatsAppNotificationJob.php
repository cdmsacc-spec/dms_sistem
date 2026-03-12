<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWhatsAppNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $toNumber;
    protected $toName;
    protected $variables;

    public function __construct($toNumber, $toName, array $variables)
    {
        $this->toNumber = $toNumber;
        $this->toName = $toName;
        $this->variables = $variables;
    }

    public function handle()
    {
        Log::info('oke wa send');
        try {
            $response = Http::withToken(config('services.qontaq.token'))
                ->post('https://service-chat.qontak.com/api/open/v1/broadcasts/whatsapp/direct', [
                    'to_number' => $this->toNumber,
                    'to_name' => $this->toName,
                    'message_template_id' => config('services.qontaq.template_id'),
                    'channel_integration_id' => config('services.qontaq.channel_id'),
                    'language' => ['code' => 'id'],
                    'parameters' => [
                        'body' => $this->variables
                    ]
                ]);

            Log::info($response->status());
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
        }

        if ($response->failed()) {
            Log::error("WA Job Failed for {$this->toNumber}: " . $response->body());
        }
    }
}
