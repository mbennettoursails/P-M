<?php

return [
    // Page titles
    'title' => 'イベント',
    'subtitle' => 'CO-OPのイベントをチェックして参加しよう',

    // Filters
    'filter' => [
        'upcoming' => '今後のイベント',
        'past' => '過去のイベント',
        'all' => 'すべて',
    ],

    // Calendar
    'calendar' => [
        'today' => '今日',
    ],

    // Status labels
    'status' => [
        'open' => '受付中',
        'full' => '満員',
        'waitlist' => 'キャンセル待ち',
        'cancelled' => '中止',
        'draft' => '下書き',
        'published' => '公開中',
        'completed' => '終了',
    ],

    // Badges
    'badges' => [
        'pinned' => '固定',
        'featured' => '注目',
    ],

    // General labels
    'participants' => '人参加',
    'remaining' => '残り:count席',
    'online' => 'オンライン',
    'join_online' => 'オンラインで参加',

    // Empty state
    'empty' => [
        'title' => 'イベントがありません',
        'description' => '現在表示できるイベントはありません。',
    ],

    // Create/Edit
    'create' => [
        'title' => 'イベントを作成',
        'button' => 'イベント作成',
    ],
    'edit' => [
        'title' => 'イベントを編集',
    ],

    // Details page
    'details' => [
        'datetime' => '日時',
        'location' => '場所',
        'capacity' => '定員',
        'cost' => '参加費',
        'description' => '詳細',
        'organizer' => '主催者',
    ],

    // Registration
    'registration' => [
        'register_button' => '参加申し込み',
        'join_waitlist' => 'キャンセル待ちに登録',
        'cancel_button' => '参加をキャンセル',
        'leave_waitlist' => 'キャンセル待ちを解除',
        'registered' => '参加登録済み',
        'registered_message' => 'このイベントへの参加が確定しています',
        'waitlisted' => 'キャンセル待ち中',
        'waitlist_message' => 'キャンセルが出た場合、自動的に登録されます',
        'closed' => '参加受付は終了しました',
        'success' => '参加登録が完了しました',
        'cancelled' => '参加をキャンセルしました',
    ],

    // Modal dialogs
    'modal' => [
        'register' => '参加申し込み',
        'join_waitlist' => 'キャンセル待ちに登録',
        'notes' => 'メモ・連絡事項',
        'notes_placeholder' => 'アレルギーや特別な配慮が必要な場合はご記入ください',
        'guests' => '同伴者',
        'no_guests' => 'なし',
        'guests_count' => '人',
        'cancel' => 'キャンセル',
        'confirm_register' => '申し込む',
        'confirm_waitlist' => 'キャンセル待ちに登録',
        'cancel_title' => '参加をキャンセル',
        'cancel_message' => '本当にこのイベントの参加をキャンセルしますか？',
        'keep_registration' => '参加を続ける',
        'confirm_cancel' => 'キャンセルする',
    ],

    // Form fields
    'form' => [
        'basic_info' => '基本情報',
        'title' => 'タイトル',
        'title_placeholder' => 'イベントのタイトルを入力',
        'title_en' => 'タイトル（英語）',
        'title_en_placeholder' => 'English title (optional)',
        'category' => 'カテゴリー',
        'color' => 'テーマカラー',
        'description' => '概要',
        'description_placeholder' => 'イベントの概要を入力',
        'description_help' => '検索結果やリストに表示される短い説明文',

        'datetime' => '日時',
        'all_day' => '終日イベント',
        'start_date' => '開始日',
        'start_time' => '開始時刻',
        'end_date' => '終了日',
        'end_time' => '終了時刻',

        'location_section' => '場所',
        'is_online' => 'オンラインイベント',
        'online_url' => '参加URL',
        'online_url_help' => '参加者のみに表示されます',
        'venue' => '会場名',
        'venue_placeholder' => '例: CO-OPキッチンスタジオ',
        'address' => '住所',
        'address_placeholder' => '例: 東京都練馬区...',

        'registration_section' => '参加登録',
        'registration_required' => '参加登録を必要とする',
        'capacity' => '定員',
        'capacity_placeholder' => '定員数を入力（空欄は無制限）',
        'capacity_help' => '空欄にすると定員なし（無制限）になります',
        'waitlist_enabled' => 'キャンセル待ちを有効にする',
        'registration_opens' => '受付開始日時',
        'registration_closes' => '受付終了日時',

        'cost_section' => '参加費',
        'cost' => '金額',
        'cost_help' => '0円の場合は「無料」と表示されます',
        'cost_notes' => '備考',
        'cost_notes_placeholder' => '例: 材料費含む',

        'featured_image' => 'アイキャッチ画像',
        'image_alt' => '画像の説明',
        'image_alt_placeholder' => 'この画像の説明（アクセシビリティ用）',

        'status_section' => '公開設定',
        'status' => 'ステータス',
        'is_featured' => '注目イベントとして表示',
        'is_pinned' => '固定表示',
        'visible_to_roles' => '表示対象',
        'visible_to_roles_help' => '選択しない場合、全員に表示されます',

        'cancel' => 'キャンセル',
        'create' => '作成する',
        'update' => '更新する',
    ],

    // Validation messages
    'validation' => [
        'title_required' => 'タイトルは必須です',
        'date_required' => '開始日は必須です',
        'time_required' => '終日イベントでない場合、開始時刻は必須です',
    ],

    // Flash messages
    'messages' => [
        'created' => 'イベントを作成しました',
        'updated' => 'イベントを更新しました',
        'deleted' => 'イベントを削除しました',
    ],
];
