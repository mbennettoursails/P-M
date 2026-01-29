<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get an admin or staff user for organizing events
        $organizer = User::role(['admin', 'shokuin', 'reijikai'])->first() 
            ?? User::first();

        if (!$organizer) {
            $this->command->warn('No user found. Please create users first.');
            return;
        }

        $events = [
            // Upcoming Events
            [
                'title' => '冬の料理教室',
                'title_en' => 'Winter Cooking Class',
                'description' => '季節の食材を使った温かい料理を学びましょう。初心者大歓迎！',
                'description_en' => 'Learn to cook warm dishes using seasonal ingredients. Beginners welcome!',
                'starts_at' => Carbon::now()->addDays(7)->setTime(14, 0),
                'ends_at' => Carbon::now()->addDays(7)->setTime(16, 0),
                'location' => 'CO-OPキッチンスタジオ',
                'location_en' => 'CO-OP Kitchen Studio',
                'address' => '東京都練馬区春日町3-2-1',
                'capacity' => 20,
                'category' => 'cooking',
                'color' => 'orange',
                'cost' => 500,
                'cost_notes' => '材料費含む',
                'status' => 'published',
                'is_featured' => true,
            ],
            [
                'title' => '月例委員会ミーティング',
                'title_en' => 'Monthly Committee Meeting',
                'description' => '今月の活動報告と来月の計画について話し合います。',
                'description_en' => 'Discussion of this month\'s activities and planning for next month.',
                'starts_at' => Carbon::now()->addDays(3)->setTime(10, 0),
                'ends_at' => Carbon::now()->addDays(3)->setTime(12, 0),
                'location' => '第一会議室',
                'location_en' => 'Meeting Room 1',
                'address' => '東京都練馬区北町2-1-5',
                'capacity' => 30,
                'category' => 'meeting',
                'color' => 'blue',
                'cost' => 0,
                'status' => 'published',
                'visible_to_roles' => ['reijikai'],
            ],
            [
                'title' => '子育てサポート交流会',
                'title_en' => 'Parenting Support Meetup',
                'description' => '子育て中の会員同士で情報交換やお悩み相談ができる交流会です。お子様連れOK！',
                'description_en' => 'A meetup for parents to exchange information and discuss concerns. Children welcome!',
                'starts_at' => Carbon::now()->addDays(10)->setTime(10, 30),
                'ends_at' => Carbon::now()->addDays(10)->setTime(12, 0),
                'location' => 'コミュニティルーム',
                'location_en' => 'Community Room',
                'address' => '東京都板橋区常盤台1-2-3',
                'capacity' => 15,
                'category' => 'social',
                'color' => 'pink',
                'cost' => 0,
                'status' => 'published',
                'is_pinned' => true,
            ],
            [
                'title' => '年末大掃除ボランティア',
                'title_en' => 'Year-End Cleaning Volunteer',
                'description' => '地域の高齢者宅の大掃除をお手伝いするボランティア活動です。',
                'description_en' => 'Volunteer activity to help elderly residents with year-end cleaning.',
                'starts_at' => Carbon::now()->addDays(14)->setTime(9, 0),
                'ends_at' => Carbon::now()->addDays(14)->setTime(12, 0),
                'location' => '練馬区センター集合',
                'location_en' => 'Meet at Nerima Center',
                'address' => '東京都練馬区練馬1-17-1',
                'capacity' => 20,
                'category' => 'volunteer',
                'color' => 'primary',
                'cost' => 0,
                'status' => 'published',
            ],
            [
                'title' => '食品安全セミナー',
                'title_en' => 'Food Safety Seminar',
                'description' => '家庭での食品保存や衛生管理について学ぶオンラインセミナーです。',
                'description_en' => 'Online seminar about food storage and hygiene management at home.',
                'starts_at' => Carbon::now()->addDays(5)->setTime(19, 0),
                'ends_at' => Carbon::now()->addDays(5)->setTime(20, 30),
                'is_online' => true,
                'online_url' => 'https://zoom.us/j/example123',
                'capacity' => 100,
                'category' => 'lecture',
                'color' => 'cyan',
                'cost' => 0,
                'status' => 'published',
            ],
            [
                'title' => '味噌づくりワークショップ',
                'title_en' => 'Miso Making Workshop',
                'description' => '手作り味噌を一緒に仕込みましょう！仕込んだ味噌はお持ち帰りいただけます。',
                'description_en' => 'Let\'s make homemade miso together! You can take your miso home.',
                'starts_at' => Carbon::now()->addDays(21)->setTime(13, 0),
                'ends_at' => Carbon::now()->addDays(21)->setTime(16, 0),
                'location' => 'CO-OPキッチンスタジオ',
                'location_en' => 'CO-OP Kitchen Studio',
                'address' => '東京都練馬区春日町3-2-1',
                'capacity' => 12,
                'category' => 'workshop',
                'color' => 'orange',
                'cost' => 2000,
                'cost_notes' => '材料費・容器代含む',
                'status' => 'published',
                'is_featured' => true,
            ],

            // Past Events (for testing filters)
            [
                'title' => '秋の収穫祭',
                'title_en' => 'Autumn Harvest Festival',
                'description' => '地元農家さんの新鮮野菜を使った料理や販売を行いました。',
                'starts_at' => Carbon::now()->subDays(30)->setTime(10, 0),
                'ends_at' => Carbon::now()->subDays(30)->setTime(16, 0),
                'location' => '練馬区民広場',
                'address' => '東京都練馬区練馬1-17-1',
                'capacity' => 200,
                'category' => 'social',
                'color' => 'orange',
                'cost' => 0,
                'status' => 'completed',
            ],
            [
                'title' => 'エコバッグ作り教室',
                'title_en' => 'Eco Bag Making Class',
                'description' => '古着をリメイクしてオリジナルエコバッグを作りました。',
                'starts_at' => Carbon::now()->subDays(14)->setTime(14, 0),
                'ends_at' => Carbon::now()->subDays(14)->setTime(16, 0),
                'location' => 'クラフトルーム',
                'address' => '東京都板橋区常盤台2-3-4',
                'capacity' => 15,
                'category' => 'workshop',
                'color' => 'primary',
                'cost' => 300,
                'cost_notes' => '材料費',
                'status' => 'completed',
            ],

            // Cancelled event (for testing)
            [
                'title' => '屋外バーベキュー大会',
                'title_en' => 'Outdoor BBQ Party',
                'description' => '荒天のため中止となりました。',
                'starts_at' => Carbon::now()->addDays(2)->setTime(11, 0),
                'ends_at' => Carbon::now()->addDays(2)->setTime(15, 0),
                'location' => '光が丘公園',
                'address' => '東京都練馬区光が丘4-1-1',
                'capacity' => 50,
                'category' => 'social',
                'color' => 'red',
                'cost' => 1000,
                'status' => 'cancelled',
            ],

            // Draft event (for testing admin view)
            [
                'title' => '春の花見イベント（計画中）',
                'title_en' => 'Spring Flower Viewing (Planning)',
                'description' => '桜の名所でのお花見を計画中です。詳細は決まり次第お知らせします。',
                'starts_at' => Carbon::now()->addMonths(3)->setTime(11, 0),
                'ends_at' => Carbon::now()->addMonths(3)->setTime(14, 0),
                'location' => '石神井公園',
                'address' => '東京都練馬区石神井町5-20',
                'capacity' => 40,
                'category' => 'social',
                'color' => 'pink',
                'cost' => 500,
                'status' => 'draft',
            ],
        ];

        foreach ($events as $eventData) {
            $eventData['organizer_id'] = $organizer->id;
            $eventData['created_by'] = $organizer->id;
            $eventData['registration_required'] = true;
            $eventData['waitlist_enabled'] = ($eventData['capacity'] ?? null) !== null;

            Event::create($eventData);
        }

        $this->command->info('Created ' . count($events) . ' sample events.');

        // Add some sample registrations
        $this->seedRegistrations();
    }

    /**
     * Seed sample registrations for events.
     */
    protected function seedRegistrations(): void
    {
        $users = User::take(10)->get();
        $events = Event::published()->upcoming()->take(5)->get();

        if ($users->isEmpty() || $events->isEmpty()) {
            return;
        }

        foreach ($events as $event) {
            // Register 3-8 random users for each event
            $registrations = $users->random(min(rand(3, 8), $users->count()));

            foreach ($registrations as $user) {
                if (!$event->isUserRegistered($user)) {
                    $event->attendees()->attach($user->id, [
                        'status' => 'registered',
                        'registered_at' => now()->subDays(rand(1, 14)),
                        'notes' => rand(0, 3) === 0 ? 'Looking forward to it!' : null,
                        'guests' => rand(0, 2),
                    ]);
                }
            }
        }

        $this->command->info('Added sample registrations to events.');
    }
}
