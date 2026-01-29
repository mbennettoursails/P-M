<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EventsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $staffUser;
    protected Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'volunteer']);
        Role::create(['name' => 'shokuin']);
        Role::create(['name' => 'reijikai']);

        // Create users
        $this->user = User::factory()->create();
        $this->user->assignRole('volunteer');

        $this->staffUser = User::factory()->create();
        $this->staffUser->assignRole('shokuin');

        // Create a test event
        $this->event = Event::create([
            'title' => 'Test Event',
            'description' => 'A test event',
            'starts_at' => now()->addDays(7),
            'ends_at' => now()->addDays(7)->addHours(2),
            'location' => 'Test Location',
            'capacity' => 20,
            'category' => 'general',
            'status' => 'published',
            'registration_required' => true,
            'organizer_id' => $this->staffUser->id,
            'created_by' => $this->staffUser->id,
        ]);
    }

    public function test_events_index_is_displayed(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('events.index'));

        $response->assertStatus(200);
        $response->assertSee('Test Event');
    }

    public function test_event_show_is_displayed(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('events.show', $this->event->uuid));

        $response->assertStatus(200);
        $response->assertSee('Test Event');
        $response->assertSee('Test Location');
    }

    public function test_volunteer_cannot_create_events(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('events.create'));

        $response->assertStatus(403);
    }

    public function test_staff_can_create_events(): void
    {
        $response = $this->actingAs($this->staffUser)
            ->get(route('events.create'));

        $response->assertStatus(200);
    }

    public function test_staff_can_edit_events(): void
    {
        $response = $this->actingAs($this->staffUser)
            ->get(route('events.edit', $this->event->uuid));

        $response->assertStatus(200);
    }

    public function test_user_can_register_for_event(): void
    {
        $this->actingAs($this->user);

        // Ensure user is not registered
        $this->assertFalse($this->event->isUserRegistered($this->user));

        // Register the user
        $success = $this->event->register($this->user);

        $this->assertTrue($success);
        $this->assertTrue($this->event->fresh()->isUserRegistered($this->user));
    }

    public function test_user_can_unregister_from_event(): void
    {
        $this->actingAs($this->user);

        // First register
        $this->event->register($this->user);
        $this->assertTrue($this->event->fresh()->isUserRegistered($this->user));

        // Then unregister
        $success = $this->event->unregister($this->user);

        $this->assertTrue($success);
        $this->assertFalse($this->event->fresh()->isUserRegistered($this->user));
    }

    public function test_registration_respects_capacity(): void
    {
        // Create event with capacity of 2
        $smallEvent = Event::create([
            'title' => 'Small Event',
            'starts_at' => now()->addDays(7),
            'location' => 'Test',
            'capacity' => 2,
            'category' => 'general',
            'status' => 'published',
            'registration_required' => true,
            'waitlist_enabled' => false,
            'organizer_id' => $this->staffUser->id,
            'created_by' => $this->staffUser->id,
        ]);

        // Register 2 users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $this->assertTrue($smallEvent->register($user1));
        $this->assertTrue($smallEvent->register($user2));
        
        // Third user should fail (no waitlist)
        $this->assertFalse($smallEvent->register($user3));
    }

    public function test_waitlist_functionality(): void
    {
        // Create event with capacity of 1 and waitlist enabled
        $smallEvent = Event::create([
            'title' => 'Waitlist Event',
            'starts_at' => now()->addDays(7),
            'location' => 'Test',
            'capacity' => 1,
            'category' => 'general',
            'status' => 'published',
            'registration_required' => true,
            'waitlist_enabled' => true,
            'organizer_id' => $this->staffUser->id,
            'created_by' => $this->staffUser->id,
        ]);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // First user registers
        $this->assertTrue($smallEvent->register($user1));
        $this->assertEquals('registered', $smallEvent->getUserRegistrationStatus($user1));

        // Second user goes to waitlist
        $this->assertTrue($smallEvent->register($user2));
        $this->assertEquals('waitlisted', $smallEvent->getUserRegistrationStatus($user2));
    }

    public function test_past_events_show_as_past(): void
    {
        $pastEvent = Event::create([
            'title' => 'Past Event',
            'starts_at' => now()->subDays(7),
            'location' => 'Test',
            'category' => 'general',
            'status' => 'published',
            'registration_required' => true,
            'organizer_id' => $this->staffUser->id,
            'created_by' => $this->staffUser->id,
        ]);

        $this->assertTrue($pastEvent->is_past);
        $this->assertFalse($pastEvent->is_upcoming);
    }

    public function test_event_formatted_date(): void
    {
        $this->assertNotEmpty($this->event->formatted_date);
        $this->assertStringContainsString('年', $this->event->formatted_date);
        $this->assertStringContainsString('月', $this->event->formatted_date);
    }

    public function test_event_cost_display(): void
    {
        // Free event
        $this->event->cost = 0;
        $this->assertEquals('無料', $this->event->cost_display);

        // Paid event
        $this->event->cost = 1000;
        $this->assertStringContainsString('1,000円', $this->event->cost_display);

        // With notes
        $this->event->cost_notes = '材料費含む';
        $this->assertStringContainsString('材料費含む', $this->event->cost_display);
    }

    public function test_role_restricted_events_visibility(): void
    {
        // Create event visible only to reijikai
        $restrictedEvent = Event::create([
            'title' => 'Committee Only Event',
            'starts_at' => now()->addDays(7),
            'location' => 'Test',
            'category' => 'meeting',
            'status' => 'published',
            'registration_required' => true,
            'visible_to_roles' => ['reijikai'],
            'organizer_id' => $this->staffUser->id,
            'created_by' => $this->staffUser->id,
        ]);

        // Volunteer user shouldn't see it in query
        $visibleEvents = Event::visibleTo($this->user)->get();
        $this->assertFalse($visibleEvents->contains($restrictedEvent));

        // Create reijikai user
        $reijiUser = User::factory()->create();
        $reijiUser->assignRole('reijikai');

        $visibleToReiji = Event::visibleTo($reijiUser)->get();
        $this->assertTrue($visibleToReiji->contains($restrictedEvent));
    }
}
