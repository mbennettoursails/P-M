<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendDeadlineReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'decisions:send-reminders 
                            {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send deadline reminders for proposals with voting deadlines approaching (24 hours)';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        $this->info('Checking for proposals with approaching deadlines...');

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE - No notifications will be sent');
        }

        try {
            if ($this->option('dry-run')) {
                // In dry-run mode, just show what would happen
                $proposals = \App\Models\Proposal::where('current_stage', 'voting')
                    ->whereNotNull('voting_deadline')
                    ->whereBetween('voting_deadline', [now(), now()->addHours(24)])
                    ->get();

                $totalReminders = 0;
                foreach ($proposals as $proposal) {
                    $nonVoters = $proposal->participants()
                        ->wherePivot('can_vote', true)
                        ->whereDoesntHave('votes', fn($q) => $q->where('proposal_id', $proposal->id))
                        ->count();
                    
                    $this->line("Proposal: {$proposal->title}");
                    $this->line("  - Deadline: {$proposal->voting_deadline}");
                    $this->line("  - Non-voters: {$nonVoters}");
                    $totalReminders += $nonVoters;
                }

                $this->info("Would send {$totalReminders} reminders");
            } else {
                $count = $notificationService->sendDeadlineReminders();
                $this->info("Sent {$count} deadline reminders.");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error sending reminders: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
