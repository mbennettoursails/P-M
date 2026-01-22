@php
    $roleColors = [
        'reijikai' => 'purple',
        'shokuin' => 'indigo',
        'volunteer' => 'cyan',
    ];
    $userRole = $comment->user->roles->first()->name ?? 'volunteer';
    $roleColor = $roleColors[$userRole] ?? 'gray';
    $initials = collect(explode(' ', $comment->user->name ?? 'U'))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->join('');
    $isDeleted = $comment->trashed();
@endphp

<div class="bg-white rounded-xl border border-gray-200 p-4 {{ $depth > 0 ? 'ml-6 sm:ml-10' : '' }}">
    <div class="flex space-x-3">
        {{-- Avatar --}}
        <div class="flex-shrink-0">
            <div class="w-8 h-8 rounded-full bg-{{ $roleColor }}-100 text-{{ $roleColor }}-600 
                        flex items-center justify-center text-sm font-medium">
                {{ $initials }}
            </div>
        </div>
        
        {{-- Content --}}
        <div class="flex-1 min-w-0">
            {{-- Header --}}
            <div class="flex items-center flex-wrap gap-x-2 gap-y-1">
                <span class="font-medium text-gray-900">{{ $comment->user->name ?? 'Unknown' }}</span>
                <span class="text-xs text-gray-500">{{ $comment->time_ago }}</span>
                @if($comment->is_edited)
                    <span class="text-xs text-gray-400">(edited)</span>
                @endif
            </div>
            
            {{-- Comment Content --}}
            @if($editingComment === $comment->id)
                {{-- Edit Mode --}}
                <div class="mt-2">
                    <textarea wire:model="editContent"
                              rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none"></textarea>
                    @error('editContent')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                    <div class="flex justify-end space-x-2 mt-2">
                        <button wire:click="cancelEdit"
                                class="px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">
                            Cancel
                        </button>
                        <button wire:click="submitEdit"
                                class="px-3 py-1.5 text-sm text-white bg-green-600 hover:bg-green-700 rounded-lg">
                            Save
                        </button>
                    </div>
                </div>
            @else
                {{-- Display Mode --}}
                <div class="mt-1 text-gray-700 {{ $isDeleted ? 'italic text-gray-400' : '' }}">
                    {{ $comment->display_content }}
                </div>
            @endif
            
            {{-- Actions --}}
            @if(!$isDeleted && $editingComment !== $comment->id)
                <div class="mt-2 flex items-center space-x-4 text-sm">
                    {{-- Reply Button --}}
                    @if($canComment && $comment->can_have_replies)
                        <button wire:click="startReply({{ $comment->id }})"
                                class="text-gray-500 hover:text-gray-700 flex items-center">
                            <x-heroicon-o-arrow-uturn-left class="w-4 h-4 mr-1" />
                            Reply
                        </button>
                    @endif
                    
                    {{-- Edit Button --}}
                    @if($comment->canUserEdit(auth()->user()))
                        <button wire:click="startEdit({{ $comment->id }})"
                                class="text-gray-500 hover:text-gray-700 flex items-center">
                            <x-heroicon-o-pencil class="w-4 h-4 mr-1" />
                            Edit
                        </button>
                    @endif
                    
                    {{-- Delete Button --}}
                    @if($comment->canUserDelete(auth()->user()))
                        <button wire:click="deleteComment({{ $comment->id }})"
                                wire:confirm="Are you sure you want to delete this comment?"
                                class="text-gray-500 hover:text-red-600 flex items-center">
                            <x-heroicon-o-trash class="w-4 h-4 mr-1" />
                            Delete
                        </button>
                    @endif
                </div>
            @endif
            
            {{-- Reply Form --}}
            @if($replyingTo === $comment->id)
                <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                    <textarea wire:model="replyContent"
                              rows="2"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none"
                              placeholder="Write a reply..."></textarea>
                    @error('replyContent')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                    <div class="flex justify-end space-x-2 mt-2">
                        <button wire:click="cancelReply"
                                class="px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">
                            Cancel
                        </button>
                        <button wire:click="submitReply"
                                class="px-3 py-1.5 text-sm text-white bg-green-600 hover:bg-green-700 rounded-lg">
                            Reply
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
    
    {{-- Nested Replies --}}
    @if($comment->replies->count() > 0 && $depth < 2)
        <div class="mt-4 space-y-3">
            @foreach($comment->replies as $reply)
                @include('livewire.decisions.components.partials.comment-item', [
                    'comment' => $reply,
                    'depth' => $depth + 1
                ])
            @endforeach
        </div>
    @endif
</div>
