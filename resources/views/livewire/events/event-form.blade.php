<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-header shadow-top-nav">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between safe-x">
            <div class="flex items-center gap-4">
                <a href="{{ $isEdit ? route('events.show', $event->uuid) : route('events.index') }}" 
                   wire:navigate
                   class="touch-target flex items-center justify-center -ml-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-button transition-colors tap-transparent">
                    <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 font-display">
                    {{ $isEdit ? __('events.edit.title') : __('events.create.title') }}
                </h1>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-6 safe-x pb-safe-bottom">
        <form wire:submit="save" class="space-y-6">
            {{-- Basic Information --}}
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 font-display">{{ __('events.form.basic_info') }}</h2>
                
                <div class="space-y-4">
                    {{-- Title --}}
                    <div>
                        <label for="title" class="label">
                            {{ __('events.form.title') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               wire:model="title" 
                               id="title"
                               class="input"
                               placeholder="{{ __('events.form.title_placeholder') }}">
                        @error('title') <span class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Title English --}}
                    <div>
                        <label for="title_en" class="label">
                            {{ __('events.form.title_en') }}
                        </label>
                        <input type="text" 
                               wire:model="title_en" 
                               id="title_en"
                               class="input"
                               placeholder="{{ __('events.form.title_en_placeholder') }}">
                    </div>

                    {{-- Category & Color --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="category" class="label">
                                {{ __('events.form.category') }}
                            </label>
                            <select wire:model="category" 
                                    id="category"
                                    class="input">
                                @foreach($this->categories as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="color" class="label">
                                {{ __('events.form.color') }}
                            </label>
                            <select wire:model="color" 
                                    id="color"
                                    class="input">
                                @foreach($this->colors as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="label">
                            {{ __('events.form.description') }}
                        </label>
                        <textarea wire:model="description" 
                                  id="description"
                                  rows="3"
                                  class="input"
                                  placeholder="{{ __('events.form.description_placeholder') }}"></textarea>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('events.form.description_help') }}</p>
                    </div>
                </div>
            </div>

            {{-- Date & Time --}}
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 font-display">{{ __('events.form.datetime') }}</h2>
                
                <div class="space-y-4">
                    {{-- All Day Toggle --}}
                    <div class="flex items-center min-h-touch">
                        <input type="checkbox" 
                               wire:model.live="is_all_day" 
                               id="is_all_day"
                               class="w-5 h-5 text-primary-600 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500">
                        <label for="is_all_day" class="ml-3 text-sm text-gray-700 dark:text-gray-300">{{ __('events.form.all_day') }}</label>
                    </div>

                    {{-- Start Date/Time --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="starts_at_date" class="label">
                                {{ __('events.form.start_date') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   wire:model="starts_at_date" 
                                   id="starts_at_date"
                                   class="input">
                            @error('starts_at_date') <span class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</span> @enderror
                        </div>
                        @if(!$is_all_day)
                            <div>
                                <label for="starts_at_time" class="label">
                                    {{ __('events.form.start_time') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="time" 
                                       wire:model="starts_at_time" 
                                       id="starts_at_time"
                                       class="input">
                                @error('starts_at_time') <span class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>

                    {{-- End Date/Time --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="ends_at_date" class="label">
                                {{ __('events.form.end_date') }}
                            </label>
                            <input type="date" 
                                   wire:model="ends_at_date" 
                                   id="ends_at_date"
                                   class="input">
                        </div>
                        @if(!$is_all_day)
                            <div>
                                <label for="ends_at_time" class="label">
                                    {{ __('events.form.end_time') }}
                                </label>
                                <input type="time" 
                                       wire:model="ends_at_time" 
                                       id="ends_at_time"
                                       class="input">
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Location --}}
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 font-display">{{ __('events.form.location_section') }}</h2>
                
                <div class="space-y-4">
                    {{-- Online Toggle --}}
                    <div class="flex items-center min-h-touch">
                        <input type="checkbox" 
                               wire:model.live="is_online" 
                               id="is_online"
                               class="w-5 h-5 text-primary-600 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500">
                        <label for="is_online" class="ml-3 text-sm text-gray-700 dark:text-gray-300">{{ __('events.form.is_online') }}</label>
                    </div>

                    @if($is_online)
                        {{-- Online URL --}}
                        <div>
                            <label for="online_url" class="label">
                                {{ __('events.form.online_url') }}
                            </label>
                            <input type="url" 
                                   wire:model="online_url" 
                                   id="online_url"
                                   class="input"
                                   placeholder="https://zoom.us/j/...">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('events.form.online_url_help') }}</p>
                        </div>
                    @else
                        {{-- Venue Name --}}
                        <div>
                            <label for="location" class="label">
                                {{ __('events.form.venue') }}
                            </label>
                            <input type="text" 
                                   wire:model="location" 
                                   id="location"
                                   class="input"
                                   placeholder="{{ __('events.form.venue_placeholder') }}">
                        </div>

                        {{-- Address --}}
                        <div>
                            <label for="address" class="label">
                                {{ __('events.form.address') }}
                            </label>
                            <input type="text" 
                                   wire:model="address" 
                                   id="address"
                                   class="input"
                                   placeholder="{{ __('events.form.address_placeholder') }}">
                        </div>
                    @endif
                </div>
            </div>

            {{-- Registration Settings --}}
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 font-display">{{ __('events.form.registration_section') }}</h2>
                
                <div class="space-y-4">
                    {{-- Registration Required --}}
                    <div class="flex items-center min-h-touch">
                        <input type="checkbox" 
                               wire:model.live="registration_required" 
                               id="registration_required"
                               class="w-5 h-5 text-primary-600 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500">
                        <label for="registration_required" class="ml-3 text-sm text-gray-700 dark:text-gray-300">{{ __('events.form.registration_required') }}</label>
                    </div>

                    @if($registration_required)
                        {{-- Capacity --}}
                        <div>
                            <label for="capacity" class="label">
                                {{ __('events.form.capacity') }}
                            </label>
                            <input type="number" 
                                   wire:model="capacity" 
                                   id="capacity"
                                   min="1"
                                   class="input"
                                   placeholder="{{ __('events.form.capacity_placeholder') }}">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('events.form.capacity_help') }}</p>
                        </div>

                        {{-- Waitlist --}}
                        <div class="flex items-center min-h-touch">
                            <input type="checkbox" 
                                   wire:model="waitlist_enabled" 
                                   id="waitlist_enabled"
                                   class="w-5 h-5 text-primary-600 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500">
                            <label for="waitlist_enabled" class="ml-3 text-sm text-gray-700 dark:text-gray-300">{{ __('events.form.waitlist_enabled') }}</label>
                        </div>

                        {{-- Registration Period --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="registration_opens_at" class="label">
                                    {{ __('events.form.registration_opens') }}
                                </label>
                                <input type="datetime-local" 
                                       wire:model="registration_opens_at" 
                                       id="registration_opens_at"
                                       class="input">
                            </div>
                            <div>
                                <label for="registration_closes_at" class="label">
                                    {{ __('events.form.registration_closes') }}
                                </label>
                                <input type="datetime-local" 
                                       wire:model="registration_closes_at" 
                                       id="registration_closes_at"
                                       class="input">
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Cost --}}
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 font-display">{{ __('events.form.cost_section') }}</h2>
                
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="cost" class="label">
                                {{ __('events.form.cost') }}
                            </label>
                            <div class="relative">
                                <input type="number" 
                                       wire:model="cost" 
                                       id="cost"
                                       min="0"
                                       class="input pr-10">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">円</span>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('events.form.cost_help') }}</p>
                        </div>
                        <div>
                            <label for="cost_notes" class="label">
                                {{ __('events.form.cost_notes') }}
                            </label>
                            <input type="text" 
                                   wire:model="cost_notes" 
                                   id="cost_notes"
                                   class="input"
                                   placeholder="{{ __('events.form.cost_notes_placeholder') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Featured Image --}}
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 font-display">{{ __('events.form.featured_image') }}</h2>
                
                <div class="space-y-4">
                    @if($featured_image || $existing_featured_image)
                        <div class="relative w-full h-48 bg-gray-100 dark:bg-gray-700 rounded-card overflow-hidden">
                            @if($featured_image)
                                <img src="{{ $featured_image->temporaryUrl() }}" class="w-full h-full object-cover">
                            @elseif($existing_featured_image)
                                <img src="{{ asset('storage/' . $existing_featured_image) }}" class="w-full h-full object-cover">
                            @endif
                            <button type="button" 
                                    wire:click="removeFeaturedImage"
                                    class="absolute top-2 right-2 touch-target flex items-center justify-center bg-red-600 text-white rounded-button hover:bg-red-700 tap-transparent">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    @endif

                    <div>
                        <input type="file" 
                               wire:model="featured_image" 
                               id="featured_image"
                               accept="image/*"
                               class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-button file:border-0 file:text-sm file:font-medium file:bg-primary-50 dark:file:bg-primary-900/30 file:text-primary-700 dark:file:text-primary-300 hover:file:bg-primary-100 dark:hover:file:bg-primary-900/50 file:min-h-touch file:cursor-pointer">
                        @error('featured_image') <span class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="featured_image_alt" class="label">
                            {{ __('events.form.image_alt') }}
                        </label>
                        <input type="text" 
                               wire:model="featured_image_alt" 
                               id="featured_image_alt"
                               class="input"
                               placeholder="{{ __('events.form.image_alt_placeholder') }}">
                    </div>
                </div>
            </div>

            {{-- Status & Visibility --}}
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 font-display">{{ __('events.form.status_section') }}</h2>
                
                <div class="space-y-4">
                    {{-- Status --}}
                    <div>
                        <label for="status" class="label">
                            {{ __('events.form.status') }}
                        </label>
                        <select wire:model="status" 
                                id="status"
                                class="input">
                            @foreach($this->statuses as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Flags --}}
                    <div class="flex flex-wrap gap-6">
                        <div class="flex items-center min-h-touch">
                            <input type="checkbox" 
                                   wire:model="is_featured" 
                                   id="is_featured"
                                   class="w-5 h-5 text-primary-600 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500">
                            <label for="is_featured" class="ml-3 text-sm text-gray-700 dark:text-gray-300">{{ __('events.form.is_featured') }}</label>
                        </div>
                        <div class="flex items-center min-h-touch">
                            <input type="checkbox" 
                                   wire:model="is_pinned" 
                                   id="is_pinned"
                                   class="w-5 h-5 text-primary-600 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500">
                            <label for="is_pinned" class="ml-3 text-sm text-gray-700 dark:text-gray-300">{{ __('events.form.is_pinned') }}</label>
                        </div>
                    </div>

                    {{-- Role Visibility --}}
                    <div>
                        <label class="label mb-2">
                            {{ __('events.form.visible_to_roles') }}
                        </label>
                        <div class="flex flex-wrap gap-4">
                            @foreach($this->availableRoles as $role => $label)
                                <label class="flex items-center min-h-touch">
                                    <input type="checkbox" 
                                           wire:model="visible_to_roles" 
                                           value="{{ $role }}"
                                           class="w-5 h-5 text-primary-600 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('events.form.visible_to_roles_help') }}</p>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="flex flex-col sm:flex-row gap-3 justify-end safe-bottom">
                <a href="{{ $isEdit ? route('events.show', $event->uuid) : route('events.index') }}" 
                   wire:navigate
                   class="btn btn-secondary text-center tap-transparent">
                    {{ __('events.form.cancel') }}
                </a>
                <button type="submit" 
                        class="btn btn-primary tap-transparent">
                    {{ $isEdit ? __('events.form.update') : __('events.form.create') }}
                </button>
            </div>
        </form>
    </div>
</div>
