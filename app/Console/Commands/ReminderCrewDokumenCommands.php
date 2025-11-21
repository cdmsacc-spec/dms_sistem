<?php

namespace App\Console\Commands;

use App\Services\CrewDokumenServices;
use Illuminate\Console\Command;

class ReminderCrewDokumenCommands extends Command
{
    /**
     * The name and signature of the console command.
     *php artisan make:command UpdateDocumentStatus
     *php artisan schedule:work

     * @var string
     */
    protected $signature = 'app:reminder-crew-dokumen-commands';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(CrewDokumenServices $service): int
    {
        $service->updateAll();

        $this->info('✅ Document statuses updated successfully.');
        return 0;
    }
}
