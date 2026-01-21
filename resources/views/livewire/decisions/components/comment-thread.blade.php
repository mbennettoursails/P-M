<div class="space-y-6">
    {{-- New Comment Form --}}
    @if($canComment)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('decisions.comments.add_comment') }}</h2>
            
            <div>
                <textarea wire:model="newComment"
                          rows="3"
                          placeholder="{{ __('decisions.comments.placeholder') }}"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coop-500 focus:border-transparent resize-none"></textarea>
                @error('newComment') 
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mt-3 flex justify-end">
                <button wire:click="addComment"
                        class="px-4 py-2 bg-coop-600 text-white rounded-lg hover:bg-coop-700 text-sm
                               disabled:opacity-50 disabled:cursor-not-allowed"
                        {{ empty($newComment) ? 'disabled' : '' }}>
                    {{ __('decisions.actions.add_comment') }}
                </button>
            </div>
        </div>
    @endif

    {{-- Stage Filter --}}
    @if(count($stageFilters) > 1)
        <div class="flex items-center gap-2 overflow-x-auto pb-2">
            <span class="text-sm text-gray-500 whitespace-nowrap">{{ __('decisions.comments.filter_by_stage') }}:</span>
            <button wire:click="$set('filterStage', '')"
                    class="px-3 py-1 text-sm rounded-full transition-colors whitespace-nowrap
                           {{ $filterStage === '' ? 'bg-coop-100 text-coop-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                すべて
            </button>
            @foreach($stageFilters as $stage => $config)
                <button wire:click="$set('filterStage', '{{ $stage }}')"
                        class="px-3 py-1 text-sm rounded-full transition-colors whitespace-nowrap
                               {{ $filterStage === $stage ? 'bg-' . $config['color'] . '-100 text-' . $config['color'] . '-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    {{ $config['name_ja'] }}
                </button>
            @endforeach
        </div>
    @endif

    {{-- Comments List --}}
    <div class="bg-white rounded-xl shadow-sm">
        @if($comments->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <x-heroicon-o-chat-bubble-left-right class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                <p>{{ __('decisions.comments.no_comments') }}</p>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($comments as $comment)
                    @include('livewire.decisions.components.partials.comment-item', ['comment' => $comment, 'depth' => 0])
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- Comment Item Partial (recursive for replies) --}}
@push('comment-partial')
@endpush
