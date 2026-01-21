<?php

namespace Database\Factories;

use App\Models\Vote;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vote>
 */
class VoteFactory extends Factory
{
    protected $model = Vote::class;

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
            'vote_value' => 'yes', // Will be overridden based on proposal type
            'reason' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
            'is_anonymous' => false,
            'voted_at' => now(),
            'changed_at' => null,
        ];
    }

    /**
     * Configure the factory for a specific proposal.
     */
    public function forProposal(Proposal $proposal): static
    {
        $voteOptions = $proposal->vote_options;
        
        return $this->state(fn (array $attributes) => [
            'proposal_id' => $proposal->id,
            'vote_value' => $this->faker->randomElement($voteOptions),
        ]);
    }

    /**
     * Configure as a democratic vote.
     */
    public function democratic(): static
    {
        return $this->state(fn (array $attributes) => [
            'vote_value' => $this->faker->randomElement(['yes', 'no', 'abstain']),
        ]);
    }

    /**
     * Configure as a consensus vote.
     */
    public function consensus(): static
    {
        return $this->state(fn (array $attributes) => [
            'vote_value' => $this->faker->randomElement(['agree', 'disagree', 'stand_aside', 'block']),
        ]);
    }

    /**
     * Configure as a consent vote.
     */
    public function consent(): static
    {
        return $this->state(fn (array $attributes) => [
            'vote_value' => $this->faker->randomElement(['no_objection', 'concern', 'object']),
        ]);
    }

    /**
     * Configure as anonymous vote.
     */
    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_anonymous' => true,
        ]);
    }

    /**
     * Configure with a reason.
     */
    public function withReason(): static
    {
        return $this->state(fn (array $attributes) => [
            'reason' => $this->faker->sentence(),
        ]);
    }

    /**
     * Configure as a positive vote (yes/agree/no_objection).
     */
    public function positive(): static
    {
        return $this->state(fn (array $attributes) => [
            'vote_value' => $this->faker->randomElement(['yes', 'agree', 'no_objection']),
        ]);
    }

    /**
     * Configure as a negative vote (no/disagree/object).
     */
    public function negative(): static
    {
        return $this->state(fn (array $attributes) => [
            'vote_value' => $this->faker->randomElement(['no', 'disagree', 'object']),
        ]);
    }
}
