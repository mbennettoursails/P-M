<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    {{-- Success Message Toast --}}
    @if($showSuccessMessage)
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => { show = false; $wire.dismissSuccessMessage() }, 4000)"
             x-transition:enter="animate-slide-in-right"
             x-transition:leave="animate-fade-out"
             class="toast fixed top-4 right-4 z-toast bg-primary-600 text-white px-6 py-3 flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="text-japanese">{{ $successMessage }}</span>
            <button @click="show = false; $wire.dismissSuccessMessage()" class="ml-2 hover:opacity-75 tap-transparent touch-target">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- Header with Featured Image --}}
    @if($event->featured_image)
        <div class="relative h-48 sm:h-64 bg-gray-200 dark:bg-gray-800">
            <img src="{{ $event->featured_image_url }}" 
                 alt="{{ $event->featured_image_alt ?? $event->display_title }}"
                 class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
            
            {{-- Back Button --}}
            <a href="{{ route('events.index') }}" 
               wire:navigate
               class="absolute top-4 left-4 touch-target flex items-center justify-center bg-white/90 dark:bg-gray-800/90 backdrop-blur rounded-button hover:bg-white dark:hover:bg-gray-800 transition-colors tap-transparent safe-top safe-left">
                <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            
            @if($this->canEdit)
                <a href="{{ route('events.edit', $event->uuid) }}" 
                   wire:navigate
                   class="absolute top-4 right-4 touch-target flex items-center justify-center bg-white/90 dark:bg-gray-800/90 backdrop-blur rounded-button hover:bg-white dark:hover:bg-gray-800 transition-colors tap-transparent safe-top safe-right">
                    <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </a>
            @endif
        </div>
    @else
        {{-- Simple Header without Image --}}
        <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-top-nav">
            <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between safe-x">
                <a href="{{ route('events.index') }}" 
                   wire:navigate
                   class="touch-target flex items-center justify-center -ml-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-button transition-colors tap-transparent">
                    <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                
                @if($this->canEdit)
                    <a href="{{ route('events.edit', $event->uuid) }}" 
                       wire:navigate
                       class="touch-target flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 rounded-button transition-colors tap-transparent">
                        <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    @endif

    <div class="max-w-4xl mx-auto px-4 py-6 safe-x">
        {{-- Event Card --}}
        <div class="card overflow-hidden {{ $event->featured_image ? '-mt-12 relative z-10' : '' }}">
            <div class="p-6">
                {{-- Status Badges --}}
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    @if($event->status === 'cancelled')
                        <span class="badge badge-danger">
                            {{ __('events.status.cancelled') }}
                        </span>
                    @endif
                    @if($event->is_pinned)
                        <span class="badge badge-warning">
                            📌 {{ __('events.badges.pinned') }}
                        </span>
                    @endif
                    @if($event->is_featured)
                        <span class="badge bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300">
                            ⭐ {{ __('events.badges.featured') }}
                        </span>
                    @endif
                    <span class="badge badge-primary">
                        {{ $event->category_label }}
                    </span>
                </div>

                {{-- Title --}}
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4 font-display text-japanese {{ $event->status === 'cancelled' ? 'line-through' : '' }}">
                    {{ $event->display_title }}
                </h1>

                {{-- Key Info Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    {{-- Date & Time --}}
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-button flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('events.details.datetime') }}</p>
                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $event->formatted_date }}（{{ $event->day_of_week }}）</p>
                            <p class="text-gray-700 dark:text-gray-300">{{ $event->formatted_time }}</p>
                        </div>
                    </div>

                    {{-- Location --}}
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-button flex items-center justify-center flex-shrink-0">
                            @if($event->is_online)
                                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('events.details.location') }}</p>
                            @if($event->is_online)
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ __('events.online') }}</p>
                                @if($event->online_url && ($this->isRegistered || $this->canEdit))
                                    <a href="{{ $event->online_url }}" target="_blank" class="text-primary-600 dark:text-primary-400 hover:underline text-sm tap-transparent">
                                        {{ __('events.join_online') }} →
                                    </a>
                                @endif
                            @else
                                <p class="font-medium text-gray-900 dark:text-gray-100 text-japanese">{{ $event->display_location }}</p>
                                @if($event->address)
                                    <p class="text-sm text-gray-600 dark:text-gray-400 text-japanese">{{ $event->address }}</p>
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- Capacity --}}
                    @if($event->registration_required)
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-button flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('events.details.capacity') }}</p>
                                <p class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ $event->registration_count }}/{{ $event->capacity ?? '∞' }}{{ __('events.participants') }}
                                </p>
                                @if($event->remaining !== null && $event->remaining > 0)
                                    <p class="text-sm text-primary-600 dark:text-primary-400">{{ __('events.remaining', ['count' => $event->remaining]) }}</p>
                                @elseif($event->is_full)
                                    <p class="text-sm text-amber-600 dark:text-amber-400">{{ $event->waitlist_enabled ? __('events.status.waitlist') : __('events.status.full') }}</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Cost --}}
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-button flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('events.details.cost') }}</p>
                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $event->cost_display }}</p>
                        </div>
                    </div>
                </div>

                {{-- Registration Status / Actions --}}
                @if($event->registration_required && $event->status === 'published')
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        @if($this->isRegistered)
                            <div class="bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-card p-4 mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-primary-100 dark:bg-primary-800 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-primary-800 dark:text-primary-200">{{ __('events.registration.registered') }}</p>
                                        <p class="text-sm text-primary-600 dark:text-primary-400 text-japanese">{{ __('events.registration.registered_message') }}</p>
                                    </div>
                                </div>
                            </div>
                            @if($this->canUnregister)
                                <button wire:click="openCancelModal" 
                                        class="btn btn-outline w-full border-red-300 dark:border-red-700 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 tap-transparent">
                                    {{ __('events.registration.cancel_button') }}
                                </button>
                            @endif
                        @elseif($this->isWaitlisted)
                            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-card p-4 mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-amber-100 dark:bg-amber-800 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-amber-800 dark:text-amber-200">{{ __('events.registration.waitlisted') }}</p>
                                        <p class="text-sm text-amber-600 dark:text-amber-400 text-japanese">{{ __('events.registration.waitlist_message') }}</p>
                                    </div>
                                </div>
                            </div>
                            @if($this->canUnregister)
                                <button wire:click="openCancelModal" 
                                        class="btn btn-secondary w-full tap-transparent">
                                    {{ __('events.registration.leave_waitlist') }}
                                </button>
                            @endif
                        @elseif($this->canRegister)
                            <button wire:click="openRegistrationModal" 
                                    class="btn btn-primary w-full tap-transparent">
                                {{ $event->is_full ? __('events.registration.join_waitlist') : __('events.registration.register_button') }}
                            </button>
                        @elseif(!$event->is_registration_open)
                            <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-card p-4 text-center">
                                <p class="text-gray-600 dark:text-gray-400 text-japanese">{{ __('events.registration.closed') }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Description --}}
                @if($event->description)
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3 font-display">{{ __('events.details.description') }}</h2>
                        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap text-japanese">{{ $event->display_description }}</p>
                    </div>
                @endif

                {{-- Rich Content --}}
                @if($event->content)
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                        <div class="prose prose-gray dark:prose-invert max-w-none">
                            {!! $event->content !!}
                        </div>
                    </div>
                @endif

                {{-- Organizer --}}
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3 font-display">{{ __('events.details.organizer') }}</h2>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center">
                            <span class="text-lg font-medium text-gray-600 dark:text-gray-400">{{ mb_substr($event->organizer->name, 0, 1) }}</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $event->organizer->name }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Registration Modal --}}
    @if($showRegistrationModal)
        <div class="fixed inset-0 z-modal overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="modal-backdrop" wire:click="closeRegistrationModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="modal inline-block align-bottom sm:align-middle">
                    <div class="modal-header">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 font-display">
                            {{ $event->is_full ? __('events.modal.join_waitlist') : __('events.modal.register') }}
                        </h3>
                        <button wire:click="closeRegistrationModal" class="touch-target flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 tap-transparent">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="modal-body space-y-4">
                        {{-- Notes --}}
                        <div>
                            <label for="notes" class="label">
                                {{ __('events.modal.notes') }}
                            </label>
                            <textarea wire:model="notes" 
                                      id="notes"
                                      rows="3"
                                      class="input"
                                      placeholder="{{ __('events.modal.notes_placeholder') }}"></textarea>
                            @error('notes') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                        </div>

                        {{-- Guests --}}
                        @if($event->capacity === null || $event->remaining > 1)
                            <div>
                                <label for="guests" class="label">
                                    {{ __('events.modal.guests') }}
                                </label>
                                <select wire:model="guests" 
                                        id="guests"
                                        class="input">
                                    <option value="0">{{ __('events.modal.no_guests') }}</option>
                                    @for($i = 1; $i <= min(10, ($event->remaining ?? 10) - 1); $i++)
                                        <option value="{{ $i }}">{{ $i }}{{ __('events.modal.guests_count') }}</option>
                                    @endfor
                                </select>
                                @error('guests') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>
                    
                    <div class="modal-footer flex-col sm:flex-row">
                        <button wire:click="closeRegistrationModal" 
                                class="btn btn-secondary w-full sm:w-auto tap-transparent">
                            {{ __('events.modal.cancel') }}
                        </button>
                        <button wire:click="register" 
                                class="btn btn-primary w-full sm:w-auto tap-transparent">
                            {{ $event->is_full ? __('events.modal.confirm_waitlist') : __('events.modal.confirm_register') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Cancel Registration Modal --}}
    @if($showCancelModal)
        <div class="fixed inset-0 z-modal overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="modal-backdrop" wire:click="closeCancelModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="modal inline-block align-bottom sm:align-middle">
                    <div class="modal-body">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 font-display">{{ __('events.modal.cancel_title') }}</h3>
                                <p class="text-gray-600 dark:text-gray-400 text-japanese">{{ __('events.modal.cancel_message') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer flex-col sm:flex-row">
                        <button wire:click="closeCancelModal" 
                                class="btn btn-secondary w-full sm:w-auto tap-transparent">
                            {{ __('events.modal.keep_registration') }}
                        </button>
                        <button wire:click="unregister" 
                                class="btn btn-danger w-full sm:w-auto tap-transparent">
                            {{ __('events.modal.confirm_cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
