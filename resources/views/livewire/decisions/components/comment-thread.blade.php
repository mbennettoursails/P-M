<div class="space-y-6">
    {{-- Add Comment Form --}}
    @if($canComment)
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <form wire:submit.prevent="addComment">
                <textarea wire:model="newComment"
                          rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none"
                          placeholder="Add a comment..."></textarea>
                @error('newComment')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
                <div class="flex justify-end mt-2">
                    <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="addComment">Post Comment</span>
                        <span wire:loading wire:target="addComment">Posting...</span>
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Comments List --}}
    <div class="space-y-4">
        @forelse($comments as $comment)
            @include('livewire.decisions.components.partials.comment-item', [
                'comment' => $comment,
                'depth' => 0
            ])
        @empty
            <div class="text-center py-8">
                <x-heroicon-o-chat-bubble-left-right class="w-12 h-12 text-gray-300 mx-auto mb-2" />
                <p class="text-gray-500">No comments yet</p>
                @if($canComment)
                    <p class="text-sm text-gray-400">Be the first to share your thoughts</p>
                @endif
            </div>
        @endforelse
    </div>

    {{-- Comment Count --}}
    @if($commentCount > 0)
        <div class="text-center text-sm text-gray-500">
            {{ $commentCount }} {{ Str::plural('comment', $commentCount) }}
        </div>
    @endif
</div>
