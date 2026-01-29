<?php

namespace App\Livewire\Events;

use App\Models\Event;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EventShow extends Component
{
    use AuthorizesRequests;

    public Event $event;
    public string $notes = '';
    public int $guests = 0;
    
    public bool $showRegistrationModal = false;
    public bool $showCancelModal = false;
    public bool $showSuccessMessage = false;
    public string $successMessage = '';

    public function mount(Event $event): void
    {
        $this->authorize('view', $event);
        
        $this->event = $event;
        
        // Increment view count
        $this->event->incrementViewCount();

        // Load existing registration data if registered
        $registration = $this->event->getUserRegistration(auth()->user());
        if ($registration) {
            $this->notes = $registration->pivot->notes ?? '';
            $this->guests = $registration->pivot->guests ?? 0;
        }
    }

    #[Computed]
    public function isRegistered(): bool
    {
        return $this->event->isUserRegistered(auth()->user());
    }

    #[Computed]
    public function isWaitlisted(): bool
    {
        return $this->event->isUserWaitlisted(auth()->user());
    }

    #[Computed]
    public function registrationStatus(): ?string
    {
        return $this->event->getUserRegistrationStatus(auth()->user());
    }

    #[Computed]
    public function canRegister(): bool
    {
        return auth()->user()->can('register', $this->event);
    }

    #[Computed]
    public function canUnregister(): bool
    {
        return auth()->user()->can('unregister', $this->event);
    }

    #[Computed]
    public function canEdit(): bool
    {
        return auth()->user()->can('update', $this->event);
    }

    public function openRegistrationModal(): void
    {
        $this->showRegistrationModal = true;
    }

    public function closeRegistrationModal(): void
    {
        $this->showRegistrationModal = false;
    }

    public function openCancelModal(): void
    {
        $this->showCancelModal = true;
    }

    public function closeCancelModal(): void
    {
        $this->showCancelModal = false;
    }

    public function register(): void
    {
        $this->authorize('register', $this->event);

        $this->validate([
            'notes' => 'nullable|string|max:500',
            'guests' => 'integer|min:0|max:10',
        ]);

        $success = $this->event->register(auth()->user(), [
            'notes' => $this->notes,
            'guests' => $this->guests,
        ]);

        if ($success) {
            // Refresh the event data
            $this->event->refresh();
            $this->event->load(['organizer', 'confirmedAttendees', 'waitlist']);

            $status = $this->event->getUserRegistrationStatus(auth()->user());
            
            if ($status === 'waitlisted') {
                $this->successMessage = __('events.registration.waitlisted');
            } else {
                $this->successMessage = __('events.registration.success');
            }
            
            $this->showSuccessMessage = true;
        }

        $this->closeRegistrationModal();
    }

    public function unregister(): void
    {
        $this->authorize('unregister', $this->event);

        $success = $this->event->unregister(auth()->user());

        if ($success) {
            // Refresh the event data
            $this->event->refresh();
            $this->event->load(['organizer', 'confirmedAttendees', 'waitlist']);

            $this->successMessage = __('events.registration.cancelled');
            $this->showSuccessMessage = true;
            
            // Reset form
            $this->notes = '';
            $this->guests = 0;
        }

        $this->closeCancelModal();
    }

    public function dismissSuccessMessage(): void
    {
        $this->showSuccessMessage = false;
    }

    public function render()
    {
        return view('livewire.events.event-show')
            ->layout('layouts.app', ['title' => $this->event->display_title]);
    }
}
