<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'proposal_id' => Proposal::factory(),
            'user_id' => User::factory(),
            'parent_id' => null,
            'content' => $this->faker->paragraph(),
            'stage_context' => 'feedback',
            'is_edited' => false,
            'edited_at' => null,
        ];
    }

    /**
     * Configure the factory for a specific proposal.
     */
    public function forProposal(Proposal $proposal): static
    {
        return $this->state(fn (array $attributes) => [
            'proposal_id' => $proposal->id,
            'stage_context' => $proposal->current_stage,
        ]);
    }

    /**
     * Configure as a reply to another comment.
     */
    public function replyTo(Comment $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'proposal_id' => $parent->proposal_id,
            'parent_id' => $parent->id,
            'stage_context' => $parent->stage_context,
        ]);
    }

    /**
     * Configure as edited comment.
     */
    public function edited(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_edited' => true,
            'edited_at' => now(),
        ]);
    }

    /**
     * Configure with Japanese content.
     */
    public function japanese(): static
    {
        $comments = [
            'この提案に賛成です。ぜひ実現させましょう。',
            '一つ質問があります。予算はどのくらいを想定していますか？',
            'とても良いアイデアだと思います。他のメンバーの意見も聞きたいです。',
            '実現可能性について、もう少し詳しく教えてください。',
            '以前にも同様の提案がありましたが、今回は条件が異なりますね。',
            '環境への影響も考慮する必要があるかもしれません。',
            'スケジュール的に少し厳しいかもしれません。',
        ];

        return $this->state(fn (array $attributes) => [
            'content' => $this->faker->randomElement($comments),
        ]);
    }

    /**
     * Configure for feedback stage.
     */
    public function inFeedback(): static
    {
        return $this->state(fn (array $attributes) => [
            'stage_context' => 'feedback',
        ]);
    }

    /**
     * Configure for refinement stage.
     */
    public function inRefinement(): static
    {
        return $this->state(fn (array $attributes) => [
            'stage_context' => 'refinement',
        ]);
    }

    /**
     * Configure for voting stage.
     */
    public function inVoting(): static
    {
        return $this->state(fn (array $attributes) => [
            'stage_context' => 'voting',
        ]);
    }
}
