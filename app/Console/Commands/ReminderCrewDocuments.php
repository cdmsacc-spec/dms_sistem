<?php

namespace App\Console\Commands;

use App\Services\CrewDocumentReminderService;
use Illuminate\Console\Command;

class ReminderCrewDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reminder-crew-documents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
 
    public function handle(CrewDocumentReminderService $service)
    {
        $service->updateAll();

        $this->info('✅ documment crew statuses updated successfully.');
        return 0;
    }
}
