<?php

return [
    'title' => '意思決定',
    'proposals' => '提案',
    'create_proposal' => '新規提案',
    'my_proposals' => '自分の提案',
    'back_to_list' => '一覧に戻る',

    'tabs' => [
        'active' => '進行中',
        'voting' => '投票中',
        'needs_vote' => '要投票',
        'drafts' => '下書き',
        'closed' => '終了',
        'mine' => '自分の提案',
    ],

    'stages' => [
        'draft' => '下書き',
        'feedback' => 'フィードバック',
        'refinement' => '修正',
        'voting' => '投票中',
        'closed' => '終了',
        'archived' => 'アーカイブ',
    ],

    'decision_types' => [
        'democratic' => '民主的（多数決）',
        'consensus' => 'コンセンサス（全員合意）',
        'consent' => '同意（異議なし）',
    ],

    'votes' => [
        'yes' => '賛成',
        'no' => '反対',
        'abstain' => '棄権',
        'agree' => '賛成',
        'disagree' => '反対',
        'stand_aside' => '傍観',
        'block' => 'ブロック',
        'no_objection' => '異議なし',
        'concern' => '懸念あり',
        'object' => '異議あり',
    ],

    'outcomes' => [
        'passed' => '可決',
        'rejected' => '否決',
        'no_quorum' => '定足数未達',
        'blocked' => 'ブロック',
        'withdrawn' => '撤回',
    ],

    'actions' => [
        'submit_vote' => '投票する',
        'change_vote' => '変更する',
        'add_comment' => 'コメントする',
        'reply' => '返信',
        'edit' => '編集',
        'delete' => '削除',
        'advance_stage' => '次の段階へ',
        'close_voting' => '投票を締め切る',
        'withdraw' => '撤回する',
        'invite' => '招待する',
        'send_reminders' => 'リマインダーを送信',
    ],

    'labels' => [
        'quorum' => '定足数',
        'deadline' => '期限',
        'participants' => '参加者',
        'votes_cast' => '投票数',
        'anonymous' => '匿名',
        'reason' => '理由（任意）',
        'author' => '提案者',
        'created_at' => '作成日',
        'updated_at' => '更新日',
        'decision_type' => '決定方式',
        'pass_threshold' => '可決閾値',
    ],

    'create' => [
        'title' => '新規提案を作成',
        'step1_title' => '基本情報',
        'step1_subtitle' => '提案のタイトルと説明を入力してください',
        'step2_title' => '決定方式',
        'step2_subtitle' => '投票方法と設定を選択してください',
        'step3_title' => '参加者',
        'step3_subtitle' => '誰が参加できるかを設定してください',
        'step4_title' => '資料と期限',
        'step4_subtitle' => '関連資料をアップロードし、期限を設定してください',
    ],

    'show' => [
        'tabs' => [
            'overview' => '概要',
            'discussion' => '議論',
            'vote' => '投票',
            'documents' => '資料',
            'participants' => '参加者',
            'history' => '履歴',
        ],
    ],

    'voting' => [
        'select_vote' => '投票を選択してください',
        'cast_your_vote' => '投票する',
        'you_voted' => 'あなたの投票',
        'results_hidden' => '結果は投票終了後に表示されます',
        'quorum_status' => '定足数状況',
        'anonymous_vote' => '匿名で投票',
    ],

    'comments' => [
        'add_comment' => 'コメントを追加',
        'reply_to' => '返信',
        'edited' => '編集済み',
        'deleted' => '削除されました',
        'cannot_edit' => 'このコメントは編集できません',
        'content_required' => 'コメント内容は必須です',
        'content_too_long' => 'コメントは5000文字以内で入力してください',
    ],

    'documents' => [
        'upload' => 'ファイルをアップロード',
        'add_link' => 'リンクを追加',
        'file_required' => 'ファイルを選択してください',
        'file_too_large' => 'ファイルサイズは10MB以下にしてください',
        'url_required' => 'URLは必須です',
        'url_invalid' => '有効なURLを入力してください',
        'title_required' => 'タイトルは必須です',
    ],

    'messages' => [
        'created' => '提案が作成されました',
        'updated' => '提案が更新されました',
        'draft_saved' => '下書きが保存されました',
        'stage_changed' => 'ステージが変更されました',
        'vote_cast' => '投票しました',
        'voting_closed' => '投票が締め切られました',
        'withdrawn' => '提案が撤回されました',
        'comment_added' => 'コメントが追加されました',
        'reply_added' => '返信が追加されました',
        'comment_updated' => 'コメントが更新されました',
        'comment_deleted' => 'コメントが削除されました',
        'document_uploaded' => '資料がアップロードされました',
        'document_deleted' => '資料が削除されました',
        'link_added' => 'リンクが追加されました',
        'users_invited' => ':count人のユーザーを招待しました',
        'reminders_sent' => ':count人にリマインダーを送信しました',
        'all_read' => 'すべて既読にしました',
    ],

    'empty' => [
        'no_proposals' => '提案がありません',
        'no_comments' => 'コメントはまだありません',
        'no_documents' => '資料はまだありません',
        'no_participants' => '参加者はまだいません',
    ],

    'expired' => '期限切れ',
];
