<?php

namespace Tests\Feature;

use App\Models\Proposal;
use App\Models\User;
use App\Models\Vote;
use App\Models\Comment;
use App\Services\ProposalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProposalTest extends TestCase
{
    use RefreshDatabase;

    protected ProposalService $proposalService;
    protected User $author;
    protected User $participant;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->proposalService = app(ProposalService::class);
        $this->author = User::factory()->create(['role' => 'reijikai']);
        $this->participant = User::factory()->create(['role' => 'volunteer']);
    }

    // ─────────────────────────────────────────────────────────────
    // PROPOSAL CREATION TESTS
    // ─────────────────────────────────────────────────────────────

    public function test_can_create_proposal(): void
    {
        $proposal = $this->proposalService->create([
            'title' => 'Test Proposal',
            'description' => 'This is a test proposal description.',
            'decision_type' => 'democratic',
        ], $this->author);

        $this->assertDatabaseHas('proposals', [
            'title' => 'Test Proposal',
            'author_id' => $this->author->id,
            'current_stage' => 'draft',
        ]);

        // Author should be added as participant
        $this->assertTrue($proposal->isUserParticipant($this->author));
        
        // Initial stage should be created
        $this->assertCount(1, $proposal->stages);
        $this->assertEquals('draft', $proposal->stages->first()->stage_type);
    }

    public function test_can_create_proposal_with_all_options(): void
    {
        $proposal = $this->proposalService->create([
            'title' => 'Full Options Proposal',
            'title_en' => 'Full Options Proposal EN',
            'description' => 'Test description with many options.',
            'description_en' => 'Test description EN.',
            'decision_type' => 'consensus',
            'quorum_percentage' => 75,
            'pass_threshold' => 100,
            'allow_anonymous_voting' => true,
            'show_results_during_voting' => true,
            'is_invite_only' => true,
            'feedback_deadline' => now()->addDays(7),
            'voting_deadline' => now()->addDays(14),
        ], $this->author);

        $this->assertEquals('consensus', $proposal->decision_type);
        $this->assertEquals(75, $proposal->quorum_percentage);
        $this->assertTrue($proposal->allow_anonymous_voting);
        $this->assertTrue($proposal->show_results_during_voting);
        $this->assertTrue($proposal->is_invite_only);
    }

    // ─────────────────────────────────────────────────────────────
    // STAGE TRANSITION TESTS
    // ─────────────────────────────────────────────────────────────

    public function test_can_advance_from_draft_to_feedback(): void
    {
        $proposal = Proposal::factory()->create([
            'author_id' => $this->author->id,
            'current_stage' => 'draft',
        ]);

        $success = $this->proposalService->advanceStage($proposal, 'feedback', $this->author);

        $this->assertTrue($success);
        $this->assertEquals('feedback', $proposal->fresh()->current_stage);
    }

    public function test_cannot_skip_stages(): void
    {
        $proposal = Proposal::factory()->create([
            'author_id' => $this->author->id,
            'current_stage' => 'draft',
        ]);

        $this->expectException(\Exception::class);
        $this->proposalService->advanceStage($proposal, 'voting', $this->author);
    }

    public function test_only_author_can_advance_stage(): void
    {
        $proposal = Proposal::factory()->create([
            'author_id' => $this->author->id,
            'current_stage' => 'draft',
        ]);

        $this->expectException(\Exception::class);
        $this->proposalService->advanceStage($proposal, 'feedback', $this->participant);
    }

    public function test_can_move_between_feedback_and_refinement(): void
    {
        $proposal = Proposal::factory()->feedback()->create([
            'author_id' => $this->author->id,
        ]);

        // Move to refinement
        $this->proposalService->advanceStage($proposal, 'refinement', $this->author);
        $this->assertEquals('refinement', $proposal->fresh()->current_stage);

        // Move back to feedback
        $this->proposalService->advanceStage($proposal->fresh(), 'feedback', $this->author);
        $this->assertEquals('feedback', $proposal->fresh()->current_stage);
    }

    // ─────────────────────────────────────────────────────────────
    // VOTING TESTS
    // ─────────────────────────────────────────────────────────────

    public function test_can_cast_vote_on_voting_proposal(): void
    {
        $proposal = Proposal::factory()->voting()->democratic()->create([
            'author_id' => $this->author->id,
        ]);

        // Add participant
        $proposal->participants()->attach($this->participant->id, [
            'can_vote' => true,
            'can_comment' => true,
        ]);

        $vote = $this->proposalService->castVote($proposal, $this->participant, 'yes', 'I support this.');

        $this->assertDatabaseHas('votes', [
            'proposal_id' => $proposal->id,
            'user_id' => $this->participant->id,
            'vote_value' => 'yes',
        ]);

        $this->assertEquals('I support this.', $vote->reason);
    }

    public function test_cannot_vote_on_non_voting_proposal(): void
    {
        $proposal = Proposal::factory()->feedback()->create([
            'author_id' => $this->author->id,
        ]);

        $proposal->participants()->attach($this->participant->id, [
            'can_vote' => true,
        ]);

        $this->expectException(\Exception::class);
        $this->proposalService->castVote($proposal, $this->participant, 'yes');
    }

    public function test_can_change_vote(): void
    {
        $proposal = Proposal::factory()->voting()->democratic()->create([
            'author_id' => $this->author->id,
        ]);

        $proposal->participants()->attach($this->participant->id, [
            'can_vote' => true,
        ]);

        // Cast initial vote
        $this->proposalService->castVote($proposal, $this->participant, 'yes');

        // Change vote
        $vote = $this->proposalService->castVote($proposal, $this->participant, 'no', 'Changed my mind.');

        $this->assertEquals('no', $vote->vote_value);
        $this->assertNotNull($vote->changed_at);
        
        // Should still only have one vote
        $this->assertEquals(1, $proposal->votes()->count());
    }

    public function test_anonymous_voting_works(): void
    {
        $proposal = Proposal::factory()->voting()->democratic()->create([
            'author_id' => $this->author->id,
            'allow_anonymous_voting' => true,
        ]);

        $proposal->participants()->attach($this->participant->id, [
            'can_vote' => true,
        ]);

        $vote = $this->proposalService->castVote($proposal, $this->participant, 'yes', null, true);

        $this->assertTrue($vote->is_anonymous);
        $this->assertEquals('匿名', $vote->voter_name); // Should show "Anonymous" in Japanese
    }

    // ─────────────────────────────────────────────────────────────
    // OUTCOME CALCULATION TESTS
    // ─────────────────────────────────────────────────────────────

    public function test_democratic_proposal_passes_with_majority(): void
    {
        $proposal = Proposal::factory()->voting()->democratic()->create([
            'author_id' => $this->author->id,
            'quorum_percentage' => 50,
            'pass_threshold' => 50,
        ]);

        // Add 4 voters
        $voters = User::factory()->count(4)->create();
        foreach ($voters as $voter) {
            $proposal->participants()->attach($voter->id, ['can_vote' => true]);
        }

        // 3 yes, 1 no (75% yes)
        Vote::factory()->create(['proposal_id' => $proposal->id, 'user_id' => $voters[0]->id, 'vote_value' => 'yes']);
        Vote::factory()->create(['proposal_id' => $proposal->id, 'user_id' => $voters[1]->id, 'vote_value' => 'yes']);
        Vote::factory()->create(['proposal_id' => $proposal->id, 'user_id' => $voters[2]->id, 'vote_value' => 'yes']);
        Vote::factory()->create(['proposal_id' => $proposal->id, 'user_id' => $voters[3]->id, 'vote_value' => 'no']);

        $outcome = $proposal->calculateOutcome();

        $this->assertEquals('passed', $outcome);
    }

    public function test_consensus_proposal_blocked_by_single_block(): void
    {
        $proposal = Proposal::factory()->voting()->consensus()->create([
            'author_id' => $this->author->id,
        ]);

        $voters = User::factory()->count(3)->create();
        foreach ($voters as $voter) {
            $proposal->participants()->attach($voter->id, ['can_vote' => true]);
        }

        // 2 agree, 1 block
        Vote::factory()->create(['proposal_id' => $proposal->id, 'user_id' => $voters[0]->id, 'vote_value' => 'agree']);
        Vote::factory()->create(['proposal_id' => $proposal->id, 'user_id' => $voters[1]->id, 'vote_value' => 'agree']);
        Vote::factory()->create(['proposal_id' => $proposal->id, 'user_id' => $voters[2]->id, 'vote_value' => 'block']);

        $outcome = $proposal->calculateOutcome();

        $this->assertEquals('blocked', $outcome);
    }

    public function test_consent_proposal_passes_with_no_objections(): void
    {
        $proposal = Proposal::factory()->voting()->consent()->create([
            'author_id' => $this->author->id,
        ]);

        $voters = User::factory()->count(3)->create();
        foreach ($voters as $voter) {
            $proposal->participants()->attach($voter->id, ['can_vote' => true]);
        }

        // All no_objection or concern (no object)
        Vote::factory()->create(['proposal_id' => $proposal->id, 'user_id' => $voters[0]->id, 'vote_value' => 'no_objection']);
        Vote::factory()->create(['proposal_id' => $proposal->id, 'user_id' => $voters[1]->id, 'vote_value' => 'no_objection']);
        Vote::factory()->create(['proposal_id' => $proposal->id, 'user_id' => $voters[2]->id, 'vote_value' => 'concern']);

        $outcome = $proposal->calculateOutcome();

        $this->assertEquals('passed', $outcome);
    }

    public function test_no_quorum_when_insufficient_votes(): void
    {
        $proposal = Proposal::factory()->voting()->democratic()->create([
            'author_id' => $this->author->id,
            'quorum_percentage' => 50,
        ]);

        // Add 10 voters
        $voters = User::factory()->count(10)->create();
        foreach ($voters as $voter) {
            $proposal->participants()->attach($voter->id, ['can_vote' => true]);
        }

        // Only 4 votes (40% participation, below 50% quorum)
        for ($i = 0; $i < 4; $i++) {
            Vote::factory()->create(['proposal_id' => $proposal->id, 'user_id' => $voters[$i]->id, 'vote_value' => 'yes']);
        }

        $outcome = $proposal->calculateOutcome();

        $this->assertEquals('no_quorum', $outcome);
    }

    // ─────────────────────────────────────────────────────────────
    // COMMENT TESTS
    // ─────────────────────────────────────────────────────────────

    public function test_can_add_comment(): void
    {
        $proposal = Proposal::factory()->feedback()->create([
            'author_id' => $this->author->id,
        ]);

        $proposal->participants()->attach($this->participant->id, [
            'can_comment' => true,
        ]);

        $comment = $this->proposalService->addComment($proposal, $this->participant, 'Great proposal!');

        $this->assertDatabaseHas('comments', [
            'proposal_id' => $proposal->id,
            'user_id' => $this->participant->id,
            'content' => 'Great proposal!',
        ]);
    }

    public function test_can_reply_to_comment(): void
    {
        $proposal = Proposal::factory()->feedback()->create([
            'author_id' => $this->author->id,
        ]);

        $proposal->participants()->attach($this->participant->id, [
            'can_comment' => true,
        ]);

        $parentComment = $this->proposalService->addComment($proposal, $this->author, 'Original comment');
        $reply = $this->proposalService->addComment($proposal, $this->participant, 'This is a reply', $parentComment->id);

        $this->assertEquals($parentComment->id, $reply->parent_id);
        $this->assertCount(1, $parentComment->replies);
    }

    public function test_cannot_comment_on_closed_proposal(): void
    {
        $proposal = Proposal::factory()->closed()->create([
            'author_id' => $this->author->id,
        ]);

        $proposal->participants()->attach($this->participant->id, [
            'can_comment' => true,
        ]);

        $this->expectException(\Exception::class);
        $this->proposalService->addComment($proposal, $this->participant, 'Late comment');
    }

    // ─────────────────────────────────────────────────────────────
    // WITHDRAWAL TESTS
    // ─────────────────────────────────────────────────────────────

    public function test_author_can_withdraw_proposal(): void
    {
        $proposal = Proposal::factory()->feedback()->create([
            'author_id' => $this->author->id,
        ]);

        $success = $this->proposalService->withdraw($proposal, $this->author, 'Changed priorities');

        $this->assertTrue($success);
        $this->assertEquals('closed', $proposal->fresh()->current_stage);
        $this->assertEquals('withdrawn', $proposal->fresh()->outcome);
    }

    public function test_non_author_cannot_withdraw(): void
    {
        $proposal = Proposal::factory()->feedback()->create([
            'author_id' => $this->author->id,
        ]);

        $this->expectException(\Exception::class);
        $this->proposalService->withdraw($proposal, $this->participant);
    }
}
