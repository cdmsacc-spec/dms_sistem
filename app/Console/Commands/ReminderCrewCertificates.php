<?php

namespace App\Console\Commands;

use App\Services\CrewCertificatesReminderService;
use Illuminate\Console\Command;

class ReminderCrewCertificates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reminder-crew-certificates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(CrewCertificatesReminderService $service)
    {
        $service->updateAll();
        $this->info('✅ sertifikat crew statuses updated successfully.');
        return 0;
    }
}
