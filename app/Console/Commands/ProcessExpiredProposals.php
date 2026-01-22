<?php

namespace App\Console\Commands;

use App\Services\ProposalService;
use Illuminate\Console\Command;

class ProcessExpiredProposals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proposals:process-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process proposals with expired voting deadlines and close them automatically';

    /**
     * Execute the console command.
     */
    public function handle(ProposalService $service): int
    {
        $this->info('Processing expired proposal deadlines...');

        try {
            $count = $service->processExpiredDeadlines();
            
            if ($count > 0) {
                $this->info("Successfully closed {$count} proposal(s) with expired deadlines.");
            } else {
                $this->info('No expired proposals found.');
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error processing expired proposals: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
