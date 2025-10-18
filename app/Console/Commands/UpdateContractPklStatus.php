<?php

namespace App\Console\Commands;

use App\Services\KontrakPklStatusService;
use Illuminate\Console\Command;

class UpdateContractPklStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-contract-pkl-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(KontrakPklStatusService $service)
    {
        $service->updateAll();

        $this->info('✅ Kontrak PKL statuses updated successfully.');
        return 0;
    }
}
