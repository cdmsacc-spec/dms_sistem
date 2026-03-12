<?php

namespace App\Console\Commands;

use App\Services\CrewNotidWaService;
use Illuminate\Console\Command;

class ReminderWaCrew extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reminder-wa-crew';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(CrewNotidWaService $service)
    {
        $service->updateAll();

        $this->info('✅ Document Crew statuses updated successfully.');
        return 0;
    }
}
