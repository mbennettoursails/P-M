<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create an author (preferably shokuin or admin)
        $author = User::role(['admin', 'shokuin'])->first() 
            ?? User::first() 
            ?? User::factory()->create(['name' => 'システム管理者']);

        $newsArticles = [
            [
                'title' => '年末年始の配送スケジュールについて',
                'excerpt' => '年末年始期間中の配送スケジュールをお知らせします。計画的なご注文にご協力ください。',
                'content' => '<h2>年末年始の配送休止期間</h2>
                <p>いつも北東京生活クラブをご利用いただき、ありがとうございます。</p>
                <p>年末年始の配送スケジュールについてお知らせいたします。</p>
                
                <h3>配送休止期間</h3>
                <ul>
                    <li>12月29日（日）～ 1月3日（金）</li>
                </ul>
                
                <h3>注文締切日</h3>
                <p>年内最終配送分のご注文は、<strong>12月25日（水）</strong>までにお願いいたします。</p>
                
                <blockquote>
                <p>年始の初回配送は1月6日（月）からとなります。お正月用品のご注文はお早めにお願いします。</p>
                </blockquote>
                
                <p>ご不便をおかけしますが、何卒ご理解のほどよろしくお願いいたします。</p>',
                'category' => 'announcement',
                'is_pinned' => true,
                'is_featured' => true,
                'status' => 'published',
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => '冬の定番！白菜と豚肉のミルフィーユ鍋',
                'excerpt' => '組合員さんから大好評のレシピをご紹介。国産白菜と豚バラ肉で作る、体が温まるお鍋です。',
                'content' => '<h2>材料（4人分）</h2>
                <ul>
                    <li>白菜 1/4個</li>
                    <li>豚バラ薄切り肉 300g</li>
                    <li>だし汁 800ml</li>
                    <li>酒 大さじ2</li>
                    <li>塩 小さじ1</li>
                    <li>ポン酢 適量</li>
                </ul>
                
                <h2>作り方</h2>
                <ol>
                    <li>白菜を1枚ずつはがし、豚バラ肉と交互に重ねます。</li>
                    <li>5cm幅に切り、鍋に隙間なく敷き詰めます。</li>
                    <li>だし汁、酒、塩を加えて火にかけます。</li>
                    <li>蓋をして中火で15分ほど煮込みます。</li>
                    <li>白菜が柔らかくなったら完成です。</li>
                </ol>
                
                <h3>ポイント</h3>
                <p>生活クラブの<strong>平田牧場三元豚</strong>を使うと、脂の甘みが違います！白菜は今が旬。甘みが増して最高においしいですよ。</p>',
                'category' => 'recipe',
                'is_pinned' => false,
                'is_featured' => true,
                'status' => 'published',
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => '新春交流会のお知らせ',
                'excerpt' => '1月の新春交流会を開催します。組合員同士の交流を深める良い機会です。ぜひご参加ください。',
                'content' => '<h2>新春交流会 2025</h2>
                <p>新年を迎え、組合員の皆さまと新春の交流会を開催いたします。</p>
                
                <h3>開催概要</h3>
                <ul>
                    <li><strong>日時：</strong>2025年1月15日（水）10:00～12:00</li>
                    <li><strong>場所：</strong>練馬区立関区民ホール</li>
                    <li><strong>参加費：</strong>無料</li>
                    <li><strong>定員：</strong>50名（先着順）</li>
                </ul>
                
                <h3>内容</h3>
                <ol>
                    <li>理事長新年ご挨拶</li>
                    <li>2024年活動報告</li>
                    <li>生産者さんとの交流タイム</li>
                    <li>試食会（お正月料理）</li>
                </ol>
                
                <p>お子様連れでのご参加も大歓迎です。キッズスペースもご用意しています。</p>
                
                <blockquote>
                <p>参加申込は、本アプリの「イベント」ページから、または事務局までお電話ください。</p>
                </blockquote>',
                'category' => 'event',
                'is_pinned' => false,
                'is_featured' => false,
                'status' => 'published',
                'published_at' => now()->subDays(7),
            ],
            [
                'title' => '【緊急】システムメンテナンスのお知らせ',
                'excerpt' => '1月20日深夜にシステムメンテナンスを実施します。一時的にサービスがご利用いただけなくなります。',
                'content' => '<h2>システムメンテナンスについて</h2>
                <p>システム安定性向上のため、下記日程でメンテナンス作業を実施いたします。</p>
                
                <h3>メンテナンス日時</h3>
                <p><strong>2025年1月20日（月）深夜2:00 ～ 5:00</strong></p>
                
                <h3>影響範囲</h3>
                <ul>
                    <li>本アプリへのアクセス不可</li>
                    <li>注文システムの一時停止</li>
                </ul>
                
                <p>ご不便をおかけしますが、ご理解とご協力をお願いいたします。</p>',
                'category' => 'urgent',
                'is_pinned' => true,
                'is_featured' => false,
                'status' => 'published',
                'published_at' => now()->subDay(),
            ],
            [
                'title' => '電気代節約のコツ - 冬の省エネ生活',
                'excerpt' => '電気代が気になる季節。ちょっとした工夫で省エネ・節約できるコツをご紹介します。',
                'content' => '<h2>冬の省エネ術</h2>
                <p>寒い季節は暖房費がかさみがち。でも、ちょっとした工夫で快適に過ごしながら節約できます。</p>
                
                <h3>1. 窓の断熱を強化</h3>
                <p>窓からの熱損失は全体の約50%。断熱シートや厚手のカーテンで対策しましょう。</p>
                
                <h3>2. 暖房の設定温度を見直す</h3>
                <p>設定温度を1度下げるだけで、約10%の節電になります。20度を目安に。</p>
                
                <h3>3. 湯たんぽの活用</h3>
                <p>寝る前に布団に入れておくと、暖房なしでも快適に眠れます。</p>
                
                <h3>4. 重ね着で体感温度アップ</h3>
                <p>薄手の服を重ねることで空気の層ができ、保温効果が高まります。</p>
                
                <blockquote>
                <p>生活クラブでは、環境に配慮した暮らしを応援しています。</p>
                </blockquote>',
                'category' => 'tips',
                'is_pinned' => false,
                'is_featured' => false,
                'status' => 'published',
                'published_at' => now()->subDays(10),
            ],
            [
                'title' => '2025年度 委員会メンバー募集',
                'excerpt' => '来年度の委員会活動に参加してくださる方を募集しています。一緒に生活クラブを盛り上げましょう！',
                'content' => '<h2>委員会メンバー募集</h2>
                <p>北東京ブロックでは、2025年度の委員会活動を一緒に進めてくださる仲間を募集しています。</p>
                
                <h3>募集する委員会</h3>
                <ul>
                    <li><strong>消費委員会：</strong>消費材の学習会企画、試食会の運営</li>
                    <li><strong>環境委員会：</strong>リサイクル活動、環境学習の企画</li>
                    <li><strong>福祉委員会：</strong>地域福祉活動、助け合いの推進</li>
                    <li><strong>広報委員会：</strong>ニュースレター作成、SNS運営</li>
                </ul>
                
                <h3>活動頻度</h3>
                <p>月1回程度の定例会議 + 随時のイベント参加</p>
                
                <h3>応募方法</h3>
                <p>本アプリの「お問い合わせ」から、または事務局までご連絡ください。</p>
                
                <p>初めての方も大歓迎！先輩委員がサポートします。</p>',
                'category' => 'general',
                'is_pinned' => false,
                'is_featured' => false,
                'status' => 'published',
                'published_at' => now()->subDays(14),
            ],
            [
                'title' => '【下書き】春の新商品のご紹介',
                'excerpt' => '春に向けた新商品の情報です。（公開前の下書きです）',
                'content' => '<h2>春の新商品ラインナップ</h2>
                <p>この記事は下書き状態です。公開前にご確認ください。</p>
                
                <h3>予定商品</h3>
                <ul>
                    <li>春野菜セット</li>
                    <li>桜餅（期間限定）</li>
                    <li>新茶</li>
                </ul>',
                'category' => 'announcement',
                'is_pinned' => false,
                'is_featured' => false,
                'status' => 'draft',
                'published_at' => null,
            ],
        ];

        foreach ($newsArticles as $articleData) {
            News::create([
                'uuid' => Str::uuid(),
                'slug' => Str::slug($articleData['title']) . '-' . Str::random(6),
                'author_id' => $author->id,
                'view_count' => rand(10, 500),
                ...$articleData,
            ]);
        }

        $this->command->info('Created ' . count($newsArticles) . ' news articles.');
    }
}
