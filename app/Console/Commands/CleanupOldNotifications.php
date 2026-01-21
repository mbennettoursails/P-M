<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class CleanupOldNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'decisions:cleanup-notifications 
                            {--days=90 : Delete read notifications older than this many days}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete old read notifications to keep the database clean';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        $days = (int) $this->option('days');
        
        $this->info("Cleaning up read notifications older than {$days} days...");

        if ($this->option('dry-run')) {
            $count = \App\Models\ProposalNotification::where('created_at', '<', now()->subDays($days))
                ->whereNotNull('read_at')
                ->count();
            
            $this->warn("DRY RUN: Would delete {$count} notifications");
            return Command::SUCCESS;
        }

        try {
            $count = $notificationService->deleteOldNotifications($days);
            $this->info("Deleted {$count} old notifications.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error cleaning up notifications: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
