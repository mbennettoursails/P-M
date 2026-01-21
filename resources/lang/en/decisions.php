<?php

return [
    // Page Titles
    'title' => 'Decisions',
    'proposals' => 'Proposals',
    'create_proposal' => 'New Proposal',
    'my_proposals' => 'My Proposals',

    // Tabs
    'tabs' => [
        'active' => 'Active',
        'voting' => 'Voting',
        'needs_vote' => 'Needs Vote',
        'drafts' => 'Drafts',
        'closed' => 'Closed',
        'mine' => 'My Proposals',
    ],

    // Stages
    'stages' => [
        'draft' => 'Draft',
        'feedback' => 'Feedback',
        'refinement' => 'Refinement',
        'voting' => 'Voting',
        'closed' => 'Closed',
        'archived' => 'Archived',
    ],

    // Decision Types
    'decision_types' => [
        'democratic' => 'Democratic (Majority)',
        'democratic_desc' => 'Simple majority wins. For quick operational decisions.',
        'consensus' => 'Consensus',
        'consensus_desc' => 'All must agree or stand aside. For major policy changes.',
        'consent' => 'Consent',
        'consent_desc' => 'No meaningful objections. For "safe to try" decisions.',
    ],

    // Vote Options
    'votes' => [
        'yes' => 'Yes',
        'no' => 'No',
        'abstain' => 'Abstain',
        'agree' => 'Agree',
        'disagree' => 'Disagree',
        'stand_aside' => 'Stand Aside',
        'block' => 'Block',
        'no_objection' => 'No Objection',
        'concern' => 'Concern',
        'object' => 'Object',
    ],

    // Outcomes
    'outcomes' => [
        'passed' => 'Passed',
        'rejected' => 'Rejected',
        'no_quorum' => 'No Quorum',
        'blocked' => 'Blocked',
        'withdrawn' => 'Withdrawn',
    ],

    // Actions
    'actions' => [
        'submit_vote' => 'Submit Vote',
        'change_vote' => 'Change Vote',
        'cancel' => 'Cancel',
        'add_comment' => 'Add Comment',
        'reply' => 'Reply',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'advance_stage' => 'Advance Stage',
        'close_voting' => 'Close Voting',
        'withdraw' => 'Withdraw',
        'save_draft' => 'Save Draft',
        'publish' => 'Publish',
        'invite' => 'Invite',
        'send_reminders' => 'Send Reminders',
        'upload' => 'Upload',
        'add_link' => 'Add Link',
    ],

    // Labels
    'labels' => [
        'quorum' => 'Quorum',
        'deadline' => 'Deadline',
        'participants' => 'Participants',
        'votes_cast' => 'Votes Cast',
        'anonymous' => 'Anonymous',
        'reason' => 'Reason (optional)',
        'author' => 'Author',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
        'stage' => 'Stage',
        'decision_type' => 'Decision Type',
        'pass_threshold' => 'Pass Threshold',
        'voting_deadline' => 'Voting Deadline',
        'feedback_deadline' => 'Feedback Deadline',
        'all_roles' => 'Everyone',
        'invite_only' => 'Invite Only',
        'documents' => 'Documents',
        'comments' => 'Comments',
        'history' => 'History',
    ],

    // Create Form
    'create' => [
        'title' => 'Create New Proposal',
        'step1_title' => 'Basic Information',
        'step1_subtitle' => 'Enter the title and description of your proposal',
        'step2_title' => 'Decision Settings',
        'step2_subtitle' => 'Configure voting method and conditions',
        'step3_title' => 'Participants',
        'step3_subtitle' => 'Set who can participate',
        'step4_title' => 'Documents & Timeline',
        'step4_subtitle' => 'Attach documents and set deadlines',
        'title_label' => 'Title',
        'title_en_label' => 'Title (English)',
        'description_label' => 'Description',
        'description_en_label' => 'Description (English)',
        'decision_type_label' => 'Decision Type',
        'quorum_label' => 'Quorum (%)',
        'pass_threshold_label' => 'Pass Threshold (%)',
        'anonymous_voting_label' => 'Allow anonymous voting',
        'show_results_label' => 'Show results during voting',
        'allowed_roles_label' => 'Allowed roles',
        'invite_only_label' => 'Invite only',
        'select_users_label' => 'Select users',
        'feedback_deadline_label' => 'Feedback deadline',
        'voting_deadline_label' => 'Voting deadline',
        'upload_files_label' => 'Upload files',
        'external_links_label' => 'External links',
        'next' => 'Next',
        'previous' => 'Previous',
        'preview' => 'Preview',
    ],

    // Show Page
    'show' => [
        'tabs' => [
            'overview' => 'Overview',
            'discussion' => 'Discussion',
            'vote' => 'Vote',
            'documents' => 'Documents',
            'participants' => 'Participants',
            'history' => 'History',
        ],
        'stage_actions' => 'Stage Actions',
        'advance_to' => 'Advance to :stage',
        'confirm_advance' => 'Are you sure you want to advance to this stage?',
        'confirm_withdraw' => 'Are you sure you want to withdraw this proposal?',
        'confirm_close' => 'Are you sure you want to close voting?',
        'notes_placeholder' => 'Notes (optional)',
        'withdraw_reason_placeholder' => 'Reason for withdrawal (optional)',
    ],

    // Voting
    'voting' => [
        'title' => 'Vote',
        'select_vote' => 'Please select your vote',
        'your_vote' => 'Your Vote',
        'vote_anonymous' => 'Vote anonymously',
        'reason_placeholder' => 'Enter your reason (optional)',
        'voting_closed' => 'Voting is closed',
        'not_started' => 'Voting has not started yet',
        'already_voted' => 'Already voted',
        'change_vote' => 'Change vote',
        'deadline_passed' => 'Voting deadline has passed',
    ],

    // Results
    'results' => [
        'title' => 'Results',
        'hidden' => 'Results will be shown after voting ends',
        'quorum_status' => 'Quorum: :current/:required (:percentage%)',
        'quorum_reached' => 'Quorum reached',
        'quorum_not_reached' => 'Quorum not reached',
        'total_votes' => 'Total votes',
        'outcome' => 'Outcome',
    ],

    // Comments
    'comments' => [
        'title' => 'Comments',
        'add_comment' => 'Add a comment',
        'reply_to' => 'Reply to',
        'edited' => '(edited)',
        'deleted' => 'This comment has been deleted',
        'content_required' => 'Please enter a comment',
        'content_too_long' => 'Comment must be less than 5000 characters',
        'cannot_edit' => 'You cannot edit this comment',
        'edit_window' => 'Comments can be edited within 15 minutes of posting',
        'filter_by_stage' => 'Filter by stage',
        'no_comments' => 'No comments yet',
        'placeholder' => 'Write a comment...',
    ],

    // Documents
    'documents' => [
        'title' => 'Documents',
        'upload' => 'Upload file',
        'add_link' => 'Add external link',
        'no_documents' => 'No documents attached',
        'file_required' => 'Please select a file',
        'file_too_large' => 'File size must be less than 10MB',
        'url_required' => 'Please enter a URL',
        'url_invalid' => 'Please enter a valid URL',
        'title_required' => 'Please enter a title',
        'title_placeholder' => 'Title (optional, defaults to filename)',
        'url_placeholder' => 'https://example.com/document',
        'link_title_placeholder' => 'Link title',
        'external_link' => 'External link',
        'uploaded_by' => 'Uploaded by :name',
    ],

    // Participants
    'participants' => [
        'title' => 'Participants',
        'invite' => 'Invite participants',
        'search_placeholder' => 'Search by name or email',
        'can_vote' => 'Can vote',
        'can_comment' => 'Can comment',
        'invited' => 'Invited',
        'voted' => 'Voted',
        'not_voted' => 'Not voted',
        'remove' => 'Remove',
        'no_participants' => 'No participants',
    ],

    // History
    'history' => [
        'title' => 'History',
        'stage_changed' => ':user changed to :stage',
        'created' => ':user created',
        'duration' => 'Duration: :duration',
    ],

    // Notifications
    'notifications' => [
        'title' => 'Notifications',
        'mark_all_read' => 'Mark all as read',
        'no_notifications' => 'No notifications',
        'view_all' => 'View all',
    ],

    // Messages
    'messages' => [
        'created' => 'Proposal created successfully',
        'draft_saved' => 'Draft saved',
        'updated' => 'Proposal updated',
        'stage_changed' => 'Stage changed successfully',
        'vote_cast' => 'Vote submitted',
        'vote_changed' => 'Vote changed',
        'voting_closed' => 'Voting closed',
        'withdrawn' => 'Proposal withdrawn',
        'comment_added' => 'Comment added',
        'reply_added' => 'Reply added',
        'comment_updated' => 'Comment updated',
        'comment_deleted' => 'Comment deleted',
        'document_uploaded' => 'Document uploaded',
        'link_added' => 'Link added',
        'document_deleted' => 'Document deleted',
        'users_invited' => ':count user(s) invited',
        'reminders_sent' => 'Reminders sent to :count user(s)',
        'all_read' => 'All notifications marked as read',
    ],

    // Errors
    'errors' => [
        'not_found' => 'Proposal not found',
        'unauthorized' => 'You are not authorized',
        'cannot_vote' => 'You cannot vote on this proposal',
        'cannot_comment' => 'You cannot comment on this proposal',
        'cannot_edit' => 'You cannot edit this proposal',
        'invalid_stage' => 'Invalid stage transition',
    ],

    // Time
    'expired' => 'Expired',
    'time_remaining' => ':time remaining',

    // Empty States
    'empty' => [
        'no_proposals' => 'No proposals yet',
        'no_active' => 'No active proposals',
        'no_drafts' => 'No drafts',
        'create_first' => 'Create your first proposal',
    ],
];
