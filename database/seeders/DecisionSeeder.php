<?php

namespace Database\Seeders;

use App\Models\Proposal;
use App\Models\User;
use App\Models\Vote;
use App\Models\Comment;
use App\Models\ProposalDocument;
use App\Models\ProposalNotification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DecisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating sample proposals and votes...');

        // Get or create users for each role
        $reijikaiUsers = $this->getOrCreateUsers('reijikai', 5);
        $shokuinUsers = $this->getOrCreateUsers('shokuin', 3);
        $volunteerUsers = $this->getOrCreateUsers('volunteer', 10);
        
        $allUsers = $reijikaiUsers->concat($shokuinUsers)->concat($volunteerUsers);

        // Create proposals in various stages
        $this->createDemocraticProposals($reijikaiUsers, $allUsers);
        $this->createConsensusProposals($shokuinUsers, $reijikaiUsers);
        $this->createConsentProposals($volunteerUsers, $allUsers);

        $this->command->info('Decision seeding completed!');
    }

    private function getOrCreateUsers(string $role, int $count)
    {
        $existingUsers = User::where('role', $role)->limit($count)->get();
        
        if ($existingUsers->count() >= $count) {
            return $existingUsers;
        }

        $needed = $count - $existingUsers->count();
        $newUsers = User::factory()->count($needed)->create([
            'role' => $role,
        ]);

        return $existingUsers->concat($newUsers);
    }

    private function createDemocraticProposals($authors, $participants): void
    {
        $proposals = [
            [
                'title' => '2024年度イベント予算の承認',
                'title_en' => 'Approval of 2024 Event Budget',
                'description' => "今年度のイベント予算について、以下の配分を提案いたします。\n\n1. 春祭り: ¥300,000\n2. 夏のBBQ大会: ¥200,000\n3. 秋の収穫祭: ¥250,000\n4. 年末パーティー: ¥350,000\n\n合計: ¥1,100,000\n\nご意見をお聞かせください。",
                'description_en' => 'Proposal for this year\'s event budget allocation.',
                'stage' => 'voting',
                'quorum' => 50,
                'threshold' => 50,
            ],
            [
                'title' => '新規会員向けオリエンテーションの実施',
                'title_en' => 'New Member Orientation Program',
                'description' => "新規会員の皆様に、組合の活動内容や参加方法をご理解いただくためのオリエンテーションプログラムを提案します。\n\n■ 実施頻度: 月1回（第2土曜日）\n■ 所要時間: 2時間\n■ 内容:\n  - 組合の歴史と理念\n  - 活動内容の紹介\n  - 参加方法の説明\n  - 先輩会員との交流会",
                'description_en' => 'Proposed orientation program for new members.',
                'stage' => 'feedback',
                'quorum' => 50,
                'threshold' => 50,
            ],
            [
                'title' => 'オンライン会議ツールの導入',
                'title_en' => 'Introduction of Online Meeting Tools',
                'description' => "遠方の会員や忙しい方も参加しやすくするため、オンライン会議ツールの導入を提案します。\n\nZoom または Google Meet の利用を検討しています。",
                'description_en' => 'Proposal to introduce online meeting tools for remote participation.',
                'stage' => 'closed',
                'quorum' => 50,
                'threshold' => 50,
                'outcome' => 'passed',
            ],
        ];

        foreach ($proposals as $data) {
            $author = $authors->random();
            
            $proposal = Proposal::create([
                'uuid' => (string) Str::uuid(),
                'title' => $data['title'],
                'title_en' => $data['title_en'],
                'description' => $data['description'],
                'description_en' => $data['description_en'],
                'decision_type' => 'democratic',
                'current_stage' => $data['stage'],
                'quorum_percentage' => $data['quorum'],
                'pass_threshold' => $data['threshold'],
                'allow_anonymous_voting' => false,
                'show_results_during_voting' => $data['stage'] === 'closed',
                'voting_deadline' => $data['stage'] === 'voting' ? now()->addDays(5) : null,
                'feedback_deadline' => $data['stage'] === 'feedback' ? now()->addDays(7) : null,
                'closed_at' => $data['stage'] === 'closed' ? now()->subDays(2) : null,
                'outcome' => $data['outcome'] ?? null,
                'author_id' => $author->id,
            ]);

            // Add participants
            $selectedParticipants = $participants->random(min(12, $participants->count()));
            foreach ($selectedParticipants as $participant) {
                if ($participant->id !== $author->id) {
                    $proposal->participants()->attach($participant->id, [
                        'can_vote' => true,
                        'can_comment' => true,
                        'invited_at' => now()->subDays(rand(1, 7)),
                    ]);
                }
            }

            // Add votes for voting/closed proposals
            if (in_array($data['stage'], ['voting', 'closed'])) {
                $this->addVotes($proposal, ['yes', 'no', 'abstain']);
            }

            // Add comments
            $this->addComments($proposal, $selectedParticipants);

            $this->command->line("  Created: {$data['title']}");
        }
    }

    private function createConsensusProposals($authors, $participants): void
    {
        $proposals = [
            [
                'title' => '会員規約第5条の改定について',
                'title_en' => 'Amendment to Article 5 of Member Regulations',
                'description' => "会員規約第5条（会費）について、以下の改定を提案します。\n\n【現行】\n月会費: ¥1,000\n\n【改定案】\n月会費: ¥1,200（年間 ¥14,400）\n\n※ 改定理由: 活動費用の増加に対応するため\n\n重要な規約変更のため、全員の合意を求めます。",
                'description_en' => 'Proposed amendment to membership fee regulations.',
                'stage' => 'voting',
                'quorum' => 75,
            ],
        ];

        foreach ($proposals as $data) {
            $author = $authors->random();
            
            $proposal = Proposal::create([
                'uuid' => (string) Str::uuid(),
                'title' => $data['title'],
                'title_en' => $data['title_en'],
                'description' => $data['description'],
                'description_en' => $data['description_en'],
                'decision_type' => 'consensus',
                'current_stage' => $data['stage'],
                'quorum_percentage' => $data['quorum'],
                'pass_threshold' => 100,
                'allow_anonymous_voting' => false,
                'show_results_during_voting' => true,
                'voting_deadline' => now()->addDays(10),
                'author_id' => $author->id,
            ]);

            // Add participants
            foreach ($participants as $participant) {
                if ($participant->id !== $author->id) {
                    $proposal->participants()->attach($participant->id, [
                        'can_vote' => true,
                        'can_comment' => true,
                        'invited_at' => now()->subDays(rand(1, 7)),
                    ]);
                }
            }

            // Add votes
            $this->addVotes($proposal, ['agree', 'stand_aside']);

            // Add comments
            $this->addComments($proposal, $participants);

            $this->command->line("  Created: {$data['title']}");
        }
    }

    private function createConsentProposals($authors, $participants): void
    {
        $proposals = [
            [
                'title' => '次回ボランティア活動の日程',
                'title_en' => 'Schedule for Next Volunteer Activity',
                'description' => "次回のボランティア清掃活動を以下の日程で実施することを提案します。\n\n日時: 2024年3月15日（土）9:00～12:00\n場所: 北公園\n内容: 公園内のゴミ拾いと花壇の手入れ\n\n異議がなければ、この日程で進めたいと思います。",
                'description_en' => 'Proposed schedule for the next volunteer cleanup activity.',
                'stage' => 'voting',
                'quorum' => 30,
            ],
            [
                'title' => '交流会の飲み物選定',
                'title_en' => 'Beverage Selection for Social Gathering',
                'description' => "来月の交流会で提供する飲み物について、以下を提案します。\n\n- お茶（緑茶・麦茶）\n- コーヒー\n- オレンジジュース\n- 水\n\n特に問題がなければ、これで進めます。",
                'description_en' => 'Proposed beverage selection for next month\'s social gathering.',
                'stage' => 'draft',
                'quorum' => 30,
            ],
        ];

        foreach ($proposals as $data) {
            $author = $authors->random();
            
            $proposal = Proposal::create([
                'uuid' => (string) Str::uuid(),
                'title' => $data['title'],
                'title_en' => $data['title_en'],
                'description' => $data['description'],
                'description_en' => $data['description_en'],
                'decision_type' => 'consent',
                'current_stage' => $data['stage'],
                'quorum_percentage' => $data['quorum'],
                'pass_threshold' => 100,
                'allow_anonymous_voting' => true,
                'show_results_during_voting' => true,
                'voting_deadline' => $data['stage'] === 'voting' ? now()->addDays(3) : null,
                'author_id' => $author->id,
            ]);

            // Add participants for non-draft proposals
            if ($data['stage'] !== 'draft') {
                $selectedParticipants = $participants->random(min(8, $participants->count()));
                foreach ($selectedParticipants as $participant) {
                    if ($participant->id !== $author->id) {
                        $proposal->participants()->attach($participant->id, [
                            'can_vote' => true,
                            'can_comment' => true,
                            'invited_at' => now()->subDays(rand(1, 3)),
                        ]);
                    }
                }

                // Add votes
                if ($data['stage'] === 'voting') {
                    $this->addVotes($proposal, ['no_objection', 'concern']);
                }
            }

            $this->command->line("  Created: {$data['title']}");
        }
    }

    private function addVotes(Proposal $proposal, array $preferredValues): void
    {
        $participants = $proposal->participants()
            ->wherePivot('can_vote', true)
            ->get();

        $voteCount = (int) ($participants->count() * rand(40, 80) / 100);
        $votersToVote = $participants->random(min($voteCount, $participants->count()));

        foreach ($votersToVote as $voter) {
            Vote::create([
                'proposal_id' => $proposal->id,
                'user_id' => $voter->id,
                'vote_value' => $preferredValues[array_rand($preferredValues)],
                'reason' => rand(1, 10) > 7 ? 'この決定を支持します。' : null,
                'is_anonymous' => $proposal->allow_anonymous_voting && rand(1, 10) > 8,
                'voted_at' => now()->subHours(rand(1, 48)),
            ]);
        }
    }

    private function addComments(Proposal $proposal, $participants): void
    {
        if ($proposal->current_stage === 'draft') {
            return;
        }

        $comments = [
            'この提案に賛成です。',
            'いくつか質問があります。詳細を教えてください。',
            '良いアイデアだと思います！',
            'スケジュールについて相談したいです。',
            '予算面で少し懸念があります。',
            '前向きに検討できると思います。',
        ];

        $commentCount = rand(2, 5);
        $commenters = $participants->random(min($commentCount, $participants->count()));

        foreach ($commenters as $commenter) {
            $comment = Comment::create([
                'proposal_id' => $proposal->id,
                'user_id' => $commenter->id,
                'content' => $comments[array_rand($comments)],
                'stage_context' => $proposal->current_stage === 'voting' ? 'feedback' : $proposal->current_stage,
                'created_at' => now()->subHours(rand(1, 72)),
            ]);

            // Sometimes add a reply
            if (rand(1, 10) > 7) {
                $replier = $participants->except($commenter->id)->random();
                Comment::create([
                    'proposal_id' => $proposal->id,
                    'user_id' => $replier->id,
                    'parent_id' => $comment->id,
                    'content' => 'ご意見ありがとうございます。検討します。',
                    'stage_context' => $comment->stage_context,
                    'created_at' => now()->subHours(rand(1, 24)),
                ]);
            }
        }
    }
}
