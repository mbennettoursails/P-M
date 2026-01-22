<?php

namespace Database\Seeders;

use App\Models\Proposal;
use App\Models\ProposalComment;
use App\Models\ProposalStage;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProposalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create test users with reijikai role
        $users = User::whereHas('roles', function ($q) {
            $q->where('name', 'reijikai');
        })->take(5)->get();

        if ($users->count() < 3) {
            $this->command->warn('Need at least 3 users with reijikai role. Skipping seeder.');
            return;
        }

        $author = $users->first();

        // Create a draft proposal
        $draft = $this->createProposal($author, [
            'title' => 'Community Garden Expansion Project',
            'description' => "Proposal to expand our community garden by adding 10 new plots.\n\nThis will allow more members to participate in our urban farming initiative and provide fresh produce for the food distribution program.",
            'decision_type' => 'democratic',
            'current_stage' => 'draft',
        ]);

        // Create a proposal in feedback stage
        $feedback = $this->createProposal($author, [
            'title' => 'New Member Orientation Program Redesign',
            'description' => "We propose redesigning the new member orientation program to include:\n\n1. Interactive online modules\n2. Small group welcome sessions\n3. Mentor pairing for the first 3 months\n\nThis aims to improve member retention and engagement.",
            'decision_type' => 'consensus',
            'current_stage' => 'feedback',
            'feedback_deadline' => now()->addDays(5),
        ]);
        
        // Add comments to feedback proposal
        $this->addComments($feedback, $users);

        // Create a proposal in voting stage
        $voting = $this->createProposal($author, [
            'title' => 'Annual Budget Allocation 2025',
            'description' => "Proposed budget allocation for fiscal year 2025:\n\n- Operations: 40%\n- Community Programs: 35%\n- Infrastructure: 15%\n- Reserve Fund: 10%\n\nTotal projected budget: ¥50,000,000",
            'decision_type' => 'democratic',
            'current_stage' => 'voting',
            'quorum_percentage' => 60,
            'pass_threshold' => 50,
            'voting_deadline' => now()->addDays(3),
            'show_results_during_voting' => true,
        ]);

        // Add some votes
        $this->addVotes($voting, $users->skip(1));

        // Create a closed/passed proposal
        $passed = $this->createProposal($users->skip(1)->first(), [
            'title' => 'Holiday Schedule Adjustment',
            'description' => "Proposal to adjust the holiday schedule to include Golden Week closure from April 29 - May 5.",
            'decision_type' => 'consent',
            'current_stage' => 'closed',
            'outcome' => 'passed',
            'outcome_summary' => 'Approved with no objections. 2 concerns were noted and addressed.',
            'closed_at' => now()->subDays(2),
        ]);

        // Create a rejected proposal
        $rejected = $this->createProposal($users->skip(2)->first(), [
            'title' => 'Membership Fee Increase',
            'description' => "Proposal to increase monthly membership fees from ¥1,000 to ¥1,500 to cover rising operational costs.",
            'decision_type' => 'democratic',
            'current_stage' => 'closed',
            'outcome' => 'rejected',
            'outcome_summary' => 'Rejected with 35% Yes, 65% No. Alternative cost-saving measures to be explored.',
            'closed_at' => now()->subWeek(),
        ]);

        $this->command->info('Created 5 sample proposals for testing.');
    }

    protected function createProposal(User $author, array $data): Proposal
    {
        $proposal = Proposal::create(array_merge([
            'uuid' => Str::uuid(),
            'quorum_percentage' => 50,
            'pass_threshold' => 50,
            'allow_anonymous_voting' => false,
            'show_results_during_voting' => true,
            'allowed_roles' => ['reijikai'],
            'is_invite_only' => false,
            'author_id' => $author->id,
        ], $data));

        // Create initial stage record
        ProposalStage::create([
            'proposal_id' => $proposal->id,
            'stage_type' => 'draft',
            'started_at' => now()->subDays(rand(5, 10)),
            'ended_at' => $data['current_stage'] !== 'draft' ? now()->subDays(rand(3, 5)) : null,
            'is_active' => $data['current_stage'] === 'draft',
            'transitioned_by' => $author->id,
        ]);

        // Add more stage records for non-draft proposals
        if ($data['current_stage'] !== 'draft') {
            $stages = ['feedback', 'refinement', 'voting', 'closed'];
            $currentIndex = array_search($data['current_stage'], $stages);

            foreach ($stages as $index => $stage) {
                if ($index > $currentIndex) break;

                ProposalStage::create([
                    'proposal_id' => $proposal->id,
                    'stage_type' => $stage,
                    'started_at' => now()->subDays($currentIndex - $index + 1),
                    'ended_at' => $index < $currentIndex ? now()->subDays($currentIndex - $index) : null,
                    'is_active' => $index === $currentIndex,
                    'transitioned_by' => $author->id,
                ]);
            }
        }

        return $proposal;
    }

    protected function addComments(Proposal $proposal, $users): void
    {
        $comments = [
            'I think this is a great initiative. The current orientation is quite brief.',
            'How will the mentor pairing work? Will mentors be volunteers?',
            'Online modules would be very helpful for members who can\'t attend in person.',
        ];

        foreach ($comments as $index => $content) {
            $user = $users->get($index % $users->count());
            
            ProposalComment::create([
                'uuid' => Str::uuid(),
                'proposal_id' => $proposal->id,
                'user_id' => $user->id,
                'content' => $content,
            ]);
        }
    }

    protected function addVotes(Proposal $proposal, $users): void
    {
        $voteValues = ['yes', 'yes', 'yes', 'no', 'abstain'];

        foreach ($users as $index => $user) {
            if ($index >= count($voteValues)) break;

            Vote::create([
                'uuid' => Str::uuid(),
                'proposal_id' => $proposal->id,
                'user_id' => $user->id,
                'vote_value' => $voteValues[$index],
                'reason' => $index === 3 ? 'I have concerns about the infrastructure allocation.' : null,
            ]);
        }
    }
}
