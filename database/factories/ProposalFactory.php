<?php

namespace Database\Factories;

use App\Models\Proposal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Proposal>
 */
class ProposalFactory extends Factory
{
    protected $model = Proposal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $decisionType = $this->faker->randomElement(['democratic', 'consensus', 'consent']);
        
        return [
            'uuid' => (string) Str::uuid(),
            'title' => $this->faker->sentence(6),
            'title_en' => $this->faker->sentence(6),
            'description' => $this->faker->paragraphs(3, true),
            'description_en' => $this->faker->paragraphs(2, true),
            'decision_type' => $decisionType,
            'current_stage' => 'draft',
            'quorum_percentage' => $this->faker->randomElement([50, 60, 66, 75]),
            'pass_threshold' => $decisionType === 'democratic' ? $this->faker->randomElement([50, 60, 66]) : 100,
            'allow_anonymous_voting' => $this->faker->boolean(20),
            'show_results_during_voting' => $this->faker->boolean(40),
            'allowed_roles' => null,
            'is_invite_only' => false,
            'feedback_deadline' => null,
            'voting_deadline' => null,
            'outcome' => null,
            'author_id' => User::factory(),
        ];
    }

    /**
     * Indicate that the proposal is in feedback stage.
     */
    public function feedback(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_stage' => 'feedback',
            'feedback_deadline' => now()->addDays(7),
        ]);
    }

    /**
     * Indicate that the proposal is in voting stage.
     */
    public function voting(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_stage' => 'voting',
            'voting_deadline' => now()->addDays(3),
        ]);
    }

    /**
     * Indicate that the proposal is closed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_stage' => 'closed',
            'closed_at' => now(),
            'outcome' => $this->faker->randomElement(['passed', 'rejected', 'no_quorum']),
        ]);
    }

    /**
     * Indicate that the proposal is democratic type.
     */
    public function democratic(): static
    {
        return $this->state(fn (array $attributes) => [
            'decision_type' => 'democratic',
            'pass_threshold' => 50,
        ]);
    }

    /**
     * Indicate that the proposal is consensus type.
     */
    public function consensus(): static
    {
        return $this->state(fn (array $attributes) => [
            'decision_type' => 'consensus',
            'pass_threshold' => 100,
        ]);
    }

    /**
     * Indicate that the proposal is consent type.
     */
    public function consent(): static
    {
        return $this->state(fn (array $attributes) => [
            'decision_type' => 'consent',
            'pass_threshold' => 100,
        ]);
    }

    /**
     * Indicate that the proposal is invite-only.
     */
    public function inviteOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_invite_only' => true,
        ]);
    }

    /**
     * Indicate that the proposal is restricted to specific roles.
     */
    public function forRoles(array $roles): static
    {
        return $this->state(fn (array $attributes) => [
            'allowed_roles' => $roles,
        ]);
    }

    /**
     * Indicate that the proposal allows anonymous voting.
     */
    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'allow_anonymous_voting' => true,
        ]);
    }

    /**
     * Indicate that the proposal shows results during voting.
     */
    public function showResults(): static
    {
        return $this->state(fn (array $attributes) => [
            'show_results_during_voting' => true,
        ]);
    }

    /**
     * Create a Japanese-language proposal.
     */
    public function japanese(): static
    {
        $titles = [
            '2024年度予算案について',
            '新規イベント企画の提案',
            '会員規約の改定について',
            'ボランティア活動の拡充提案',
            '環境保護活動の実施について',
        ];

        $descriptions = [
            '今年度の予算配分について、皆様のご意見をお聞かせください。',
            '地域コミュニティの活性化を目指した新しいイベントの企画です。',
            '会員規約の一部を時代に合わせて改定することを提案します。',
            'より多くの方にボランティア活動に参加していただくための提案です。',
            '地球環境保護のため、具体的な活動を提案いたします。',
        ];

        return $this->state(fn (array $attributes) => [
            'title' => $this->faker->randomElement($titles),
            'description' => $this->faker->randomElement($descriptions) . "\n\n" . $this->faker->paragraphs(2, true),
        ]);
    }
}
