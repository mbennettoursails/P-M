<?php

namespace Database\Seeders;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KnowledgeBaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create categories
        $categories = [
            [
                'name' => 'よくある質問',
                'name_en' => 'FAQ',
                'slug' => 'faq',
                'description' => '組合員の皆さまからよくいただくご質問とその回答',
                'icon' => 'question-mark-circle',
                'color' => 'blue',
                'sort_order' => 1,
            ],
            [
                'name' => 'ガイド',
                'name_en' => 'Guides',
                'slug' => 'guides',
                'description' => '生活クラブの利用方法やサービスのガイド',
                'icon' => 'book-open',
                'color' => 'green',
                'sort_order' => 2,
            ],
            [
                'name' => 'レシピ',
                'name_en' => 'Recipes',
                'slug' => 'recipes',
                'description' => '生活クラブの食材を使ったレシピ集',
                'icon' => 'fire',
                'color' => 'yellow',
                'sort_order' => 3,
            ],
            [
                'name' => '暮らしのヒント',
                'name_en' => 'Living Tips',
                'slug' => 'tips',
                'description' => '日々の暮らしに役立つヒント',
                'icon' => 'light-bulb',
                'color' => 'purple',
                'sort_order' => 4,
            ],
            [
                'name' => 'マニュアル',
                'name_en' => 'Manuals',
                'slug' => 'manuals',
                'description' => 'システムや各種手続きのマニュアル',
                'icon' => 'document-text',
                'color' => 'gray',
                'sort_order' => 5,
            ],
        ];

        $categoryModels = [];
        foreach ($categories as $categoryData) {
            $categoryModels[$categoryData['slug']] = KnowledgeCategory::create([
                'uuid' => Str::uuid(),
                ...$categoryData,
            ]);
        }

        // Get author
        $author = User::role(['admin', 'shokuin'])->first() ?? User::first();

        // Create articles
        $articles = [
            // FAQ
            [
                'title' => '注文のキャンセル・変更はできますか？',
                'excerpt' => '注文締切日までであれば、マイページからキャンセル・変更が可能です。',
                'content' => '<h2>注文の変更・キャンセルについて</h2>
                <p>ご注文いただいた商品は、<strong>注文締切日まで</strong>であれば変更・キャンセルが可能です。</p>
                
                <h3>変更・キャンセルの方法</h3>
                <ol>
                    <li>マイページにログイン</li>
                    <li>「注文履歴」を選択</li>
                    <li>変更したい注文を選択</li>
                    <li>「変更」または「キャンセル」ボタンをクリック</li>
                </ol>
                
                <h3>注意事項</h3>
                <ul>
                    <li>注文締切後のキャンセルはできません</li>
                    <li>一部の商品は変更・キャンセル対象外の場合があります</li>
                    <li>ご不明な点は事務局までお問い合わせください</li>
                </ul>',
                'category' => 'faq',
                'type' => 'faq',
                'tags' => ['注文', 'キャンセル', 'マイページ'],
                'is_featured' => true,
            ],
            [
                'title' => '配送日を変更することはできますか？',
                'excerpt' => '配送日は地域ごとに決まっていますが、長期不在の場合は事前にご連絡ください。',
                'content' => '<h2>配送日について</h2>
                <p>生活クラブの配送日は、お住まいの地域によって曜日が決まっています。</p>
                
                <h3>長期不在の場合</h3>
                <p>旅行や入院などで長期間ご不在の場合は、事前に事務局までご連絡ください。配送の一時停止が可能です。</p>
                
                <h3>受け取りができない場合</h3>
                <p>不在の場合は、指定の場所に保冷ボックスでお届けします。夏場は保冷剤を増量して対応いたします。</p>',
                'category' => 'faq',
                'type' => 'faq',
                'tags' => ['配送', '不在', 'スケジュール'],
            ],
            [
                'title' => '支払い方法を教えてください',
                'excerpt' => '口座振替またはクレジットカード払いがご利用いただけます。',
                'content' => '<h2>お支払い方法</h2>
                <p>生活クラブでは以下のお支払い方法をご利用いただけます。</p>
                
                <h3>1. 口座振替</h3>
                <p>毎月26日（金融機関休業日の場合は翌営業日）に、ご登録の口座から自動引き落としとなります。</p>
                
                <h3>2. クレジットカード</h3>
                <p>VISA、Mastercard、JCBがご利用いただけます。毎月の利用金額が翌月に請求されます。</p>
                
                <blockquote>
                <p>お支払い方法の変更は、マイページまたは事務局へのお電話で承ります。</p>
                </blockquote>',
                'category' => 'faq',
                'type' => 'faq',
                'tags' => ['支払い', '口座振替', 'クレジットカード'],
            ],

            // Guides
            [
                'title' => '生活クラブはじめてガイド',
                'excerpt' => '生活クラブの基本的な使い方をご紹介します。初めての方はこちらをご覧ください。',
                'content' => '<h2>生活クラブへようこそ</h2>
                <p>このガイドでは、生活クラブの基本的な使い方をご紹介します。</p>
                
                <h3>1. カタログを見る</h3>
                <p>毎週届くカタログで商品をチェックしましょう。Webカタログもご利用いただけます。</p>
                
                <h3>2. 注文する</h3>
                <p>注文用紙、電話、またはWebから注文できます。締切日にご注意ください。</p>
                
                <h3>3. 受け取る</h3>
                <p>決まった曜日にご自宅まで配送します。不在時は指定場所に置き配します。</p>
                
                <h3>4. 空き容器を返却</h3>
                <p>リターナブルびんや通い箱は、次回配送時にご返却ください。</p>',
                'category' => 'guides',
                'type' => 'guide',
                'tags' => ['初心者', '使い方', '基本'],
                'is_featured' => true,
                'is_pinned' => true,
            ],
            [
                'title' => 'このアプリの使い方',
                'excerpt' => '北東京生活クラブHUBアプリの機能と使い方をご説明します。',
                'content' => '<h2>アプリでできること</h2>
                <p>このアプリでは、以下の機能をご利用いただけます。</p>
                
                <h3>📰 ニュース</h3>
                <p>生活クラブからのお知らせ、イベント情報、レシピなどをチェックできます。</p>
                
                <h3>🤝 助け合い</h3>
                <p>組合員同士で助け合うためのサービスです。お手伝いの依頼や提供ができます。</p>
                
                <h3>📚 知識倉庫</h3>
                <p>よくある質問、ガイド、レシピなどを検索できます。</p>
                
                <h3>📋 意思決定（委員会向け）</h3>
                <p>委員会メンバーは、提案の作成や投票ができます。</p>',
                'category' => 'guides',
                'type' => 'guide',
                'tags' => ['アプリ', '使い方', '機能'],
            ],

            // Recipes
            [
                'title' => '平田牧場三元豚のしょうが焼き',
                'excerpt' => '生活クラブの人気食材を使った、定番のしょうが焼きレシピです。',
                'content' => '<h2>材料（2人分）</h2>
                <ul>
                    <li>平田牧場三元豚ロース薄切り 200g</li>
                    <li>玉ねぎ 1/2個</li>
                    <li>しょうが 1かけ</li>
                    <li>醤油 大さじ2</li>
                    <li>みりん 大さじ1</li>
                    <li>酒 大さじ1</li>
                    <li>サラダ油 適量</li>
                </ul>
                
                <h2>作り方</h2>
                <ol>
                    <li>しょうがをすりおろし、調味料と合わせてタレを作ります。</li>
                    <li>玉ねぎを薄切りにします。</li>
                    <li>フライパンに油を熱し、豚肉を広げて焼きます。</li>
                    <li>肉の色が変わったら玉ねぎを加えます。</li>
                    <li>タレを回しかけ、全体に絡めたら完成！</li>
                </ol>
                
                <h3>ポイント</h3>
                <p>三元豚の脂は甘みがあり、しょうが焼きにぴったり。肉を焼きすぎないのがコツです。</p>',
                'category' => 'recipes',
                'type' => 'recipe',
                'tags' => ['豚肉', '定番', '簡単'],
                'is_featured' => true,
            ],
            [
                'title' => '簡単！野菜たっぷり味噌汁',
                'excerpt' => '冷蔵庫の野菜を使って、栄養満点の味噌汁を作りましょう。',
                'content' => '<h2>材料（4人分）</h2>
                <ul>
                    <li>大根 5cm</li>
                    <li>にんじん 1/3本</li>
                    <li>小松菜 2株</li>
                    <li>豆腐 1/2丁</li>
                    <li>だし汁 800ml</li>
                    <li>味噌 大さじ3〜4</li>
                </ul>
                
                <h2>作り方</h2>
                <ol>
                    <li>大根、にんじんは薄いいちょう切り、小松菜は3cm幅に切ります。</li>
                    <li>鍋にだし汁と根菜を入れ、火にかけます。</li>
                    <li>野菜が柔らかくなったら豆腐を加えます。</li>
                    <li>火を弱め、味噌を溶き入れます。</li>
                    <li>小松菜を加え、さっと火を通して完成！</li>
                </ol>',
                'category' => 'recipes',
                'type' => 'recipe',
                'tags' => ['野菜', '味噌汁', '簡単', '定番'],
            ],

            // Tips
            [
                'title' => '野菜の鮮度を保つ保存方法',
                'excerpt' => '届いた野菜を長持ちさせるコツをご紹介します。',
                'content' => '<h2>野菜別保存のコツ</h2>
                
                <h3>葉物野菜（ほうれん草、小松菜など）</h3>
                <ul>
                    <li>湿らせた新聞紙で包む</li>
                    <li>ポリ袋に入れて野菜室へ</li>
                    <li>立てて保存すると長持ち</li>
                </ul>
                
                <h3>根菜（大根、にんじんなど）</h3>
                <ul>
                    <li>葉がついている場合は切り落とす</li>
                    <li>新聞紙で包んで冷暗所へ</li>
                    <li>冬場は常温保存もOK</li>
                </ul>
                
                <h3>じゃがいも</h3>
                <ul>
                    <li>りんごと一緒に保存すると芽が出にくい</li>
                    <li>光を避けて冷暗所で保存</li>
                    <li>冷蔵庫には入れない</li>
                </ul>',
                'category' => 'tips',
                'type' => 'article',
                'tags' => ['保存', '野菜', '長持ち'],
            ],

            // Manuals
            [
                'title' => 'パスワードの変更方法',
                'excerpt' => 'マイページのパスワードを変更する手順をご説明します。',
                'content' => '<h2>パスワード変更手順</h2>
                
                <ol>
                    <li>マイページにログインします</li>
                    <li>画面右上の「設定」をクリック</li>
                    <li>「パスワード変更」を選択</li>
                    <li>現在のパスワードを入力</li>
                    <li>新しいパスワードを2回入力</li>
                    <li>「変更する」ボタンをクリック</li>
                </ol>
                
                <h3>パスワードの要件</h3>
                <ul>
                    <li>8文字以上</li>
                    <li>英字と数字を含む</li>
                    <li>以前使用したパスワードは使用不可</li>
                </ul>
                
                <blockquote>
                <p>パスワードを忘れた場合は、ログイン画面の「パスワードを忘れた方」からリセットできます。</p>
                </blockquote>',
                'category' => 'manuals',
                'type' => 'manual',
                'tags' => ['パスワード', 'セキュリティ', 'マイページ'],
            ],
        ];

        foreach ($articles as $articleData) {
            $category = $categoryModels[$articleData['category']];
            unset($articleData['category']);

            KnowledgeArticle::create([
                'uuid' => Str::uuid(),
                'slug' => Str::slug($articleData['title']) . '-' . Str::random(6),
                'category_id' => $category->id,
                'author_id' => $author->id,
                'status' => 'published',
                'published_at' => now()->subDays(rand(1, 30)),
                'view_count' => rand(10, 500),
                'helpful_count' => rand(5, 50),
                'not_helpful_count' => rand(0, 10),
                ...$articleData,
            ]);
        }

        $this->command->info('Created ' . count($categories) . ' categories and ' . count($articles) . ' articles.');
    }
}
