<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Services\DocumentStatusService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateDocumentStatus extends Command
{
    /**
     * The name and signature of the console command.
     *php artisan make:command UpdateDocumentStatus
     *php artisan schedule:work

     * @var string
     */
    protected $signature = 'app:update-document-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(DocumentStatusService $service): int
    {
        $service->updateAll();

        $this->info('✅ Document statuses updated successfully.');
        return 0;
    }
}
