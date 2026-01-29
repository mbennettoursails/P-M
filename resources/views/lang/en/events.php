<?php

return [
    // Page titles
    'title' => 'Events',
    'subtitle' => 'Discover and join CO-OP events',

    // Filters
    'filter' => [
        'upcoming' => 'Upcoming',
        'past' => 'Past',
        'all' => 'All',
    ],

    // Calendar
    'calendar' => [
        'today' => 'Today',
    ],

    // Status labels
    'status' => [
        'open' => 'Open',
        'full' => 'Full',
        'waitlist' => 'Waitlist',
        'cancelled' => 'Cancelled',
        'draft' => 'Draft',
        'published' => 'Published',
        'completed' => 'Completed',
    ],

    // Badges
    'badges' => [
        'pinned' => 'Pinned',
        'featured' => 'Featured',
    ],

    // General labels
    'participants' => ' attending',
    'remaining' => ':count spots left',
    'online' => 'Online',
    'join_online' => 'Join Online',

    // Empty state
    'empty' => [
        'title' => 'No Events Found',
        'description' => 'There are no events to display at this time.',
    ],

    // Create/Edit
    'create' => [
        'title' => 'Create Event',
        'button' => 'Create Event',
    ],
    'edit' => [
        'title' => 'Edit Event',
    ],

    // Details page
    'details' => [
        'datetime' => 'Date & Time',
        'location' => 'Location',
        'capacity' => 'Capacity',
        'cost' => 'Cost',
        'description' => 'Description',
        'organizer' => 'Organizer',
    ],

    // Registration
    'registration' => [
        'register_button' => 'Register',
        'join_waitlist' => 'Join Waitlist',
        'cancel_button' => 'Cancel Registration',
        'leave_waitlist' => 'Leave Waitlist',
        'registered' => 'Registered',
        'registered_message' => 'You are registered for this event',
        'waitlisted' => 'On Waitlist',
        'waitlist_message' => 'You will be automatically registered if a spot opens',
        'closed' => 'Registration is closed',
        'success' => 'Successfully registered',
        'cancelled' => 'Registration cancelled',
    ],

    // Modal dialogs
    'modal' => [
        'register' => 'Register for Event',
        'join_waitlist' => 'Join Waitlist',
        'notes' => 'Notes',
        'notes_placeholder' => 'Please note any allergies or special requirements',
        'guests' => 'Additional Guests',
        'no_guests' => 'None',
        'guests_count' => '',
        'cancel' => 'Cancel',
        'confirm_register' => 'Register',
        'confirm_waitlist' => 'Join Waitlist',
        'cancel_title' => 'Cancel Registration',
        'cancel_message' => 'Are you sure you want to cancel your registration for this event?',
        'keep_registration' => 'Keep Registration',
        'confirm_cancel' => 'Cancel Registration',
    ],

    // Form fields
    'form' => [
        'basic_info' => 'Basic Information',
        'title' => 'Title',
        'title_placeholder' => 'Enter event title',
        'title_en' => 'Title (English)',
        'title_en_placeholder' => 'English title (optional)',
        'category' => 'Category',
        'color' => 'Theme Color',
        'description' => 'Summary',
        'description_placeholder' => 'Enter event summary',
        'description_help' => 'Brief description shown in search results and lists',

        'datetime' => 'Date & Time',
        'all_day' => 'All-day event',
        'start_date' => 'Start Date',
        'start_time' => 'Start Time',
        'end_date' => 'End Date',
        'end_time' => 'End Time',

        'location_section' => 'Location',
        'is_online' => 'Online event',
        'online_url' => 'Meeting URL',
        'online_url_help' => 'Only visible to registered attendees',
        'venue' => 'Venue Name',
        'venue_placeholder' => 'e.g., CO-OP Kitchen Studio',
        'address' => 'Address',
        'address_placeholder' => 'e.g., Nerima, Tokyo...',

        'registration_section' => 'Registration',
        'registration_required' => 'Require registration',
        'capacity' => 'Capacity',
        'capacity_placeholder' => 'Enter capacity (leave blank for unlimited)',
        'capacity_help' => 'Leave blank for unlimited capacity',
        'waitlist_enabled' => 'Enable waitlist',
        'registration_opens' => 'Registration Opens',
        'registration_closes' => 'Registration Closes',

        'cost_section' => 'Cost',
        'cost' => 'Amount',
        'cost_help' => 'Enter 0 for free events',
        'cost_notes' => 'Notes',
        'cost_notes_placeholder' => 'e.g., Includes materials',

        'featured_image' => 'Featured Image',
        'image_alt' => 'Image Description',
        'image_alt_placeholder' => 'Describe this image (for accessibility)',

        'status_section' => 'Publishing',
        'status' => 'Status',
        'is_featured' => 'Feature this event',
        'is_pinned' => 'Pin to top',
        'visible_to_roles' => 'Visible to',
        'visible_to_roles_help' => 'Leave empty to show to everyone',

        'cancel' => 'Cancel',
        'create' => 'Create',
        'update' => 'Update',
    ],

    // Validation messages
    'validation' => [
        'title_required' => 'Title is required',
        'date_required' => 'Start date is required',
        'time_required' => 'Start time is required for non all-day events',
    ],

    // Flash messages
    'messages' => [
        'created' => 'Event created successfully',
        'updated' => 'Event updated successfully',
        'deleted' => 'Event deleted successfully',
    ],
];
