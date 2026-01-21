@props(['comment', 'depth' => 0])

@php
    $maxDepth = 5;
    $indentClass = $depth > 0 ? 'ml-' . min($depth * 4, 16) : '';
@endphp

<div id="comment-{{ $comment->id }}" 
     class="{{ $indentClass }} {{ $depth > 0 ? 'border-l-2 border-gray-100 pl-4' : '' }}">
    <div class="p-4 {{ $depth === 0 ? '' : 'pt-4' }}">
        {{-- Comment Header --}}
        <div class="flex items-start justify-between">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full bg-coop-100 flex items-center justify-center text-coop-700 font-medium text-sm">
                    {{ mb_substr($comment->user->name ?? '?', 0, 1) }}
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">
                        {{ $comment->user->name ?? 'Unknown' }}
                    </p>
                    <div class="flex items-center text-xs text-gray-500">
                        <span>{{ $comment->time_ago }}</span>
                        @if($comment->is_edited)
                            <span class="ml-2">{{ __('decisions.comments.edited') }}</span>
                        @endif
                        <span class="ml-2 px-1.5 py-0.5 bg-{{ $comment->stage_config['color'] ?? 'gray' }}-100 text-{{ $comment->stage_config['color'] ?? 'gray' }}-700 rounded">
                            {{ $comment->stage_config['name_ja'] ?? $comment->stage_context }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Actions Dropdown --}}
            @if($comment->canBeEditedBy(auth()->user()) || $comment->canBeDeletedBy(auth()->user()))
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="p-1 text-gray-400 hover:text-gray-600 rounded">
                        <x-heroicon-o-ellipsis-vertical class="w-5 h-5" />
                    </button>
                    <div x-show="open" 
                         @click.away="open = false"
                         x-transition
                         class="absolute right-0 mt-1 w-32 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                        @if($comment->canBeEditedBy(auth()->user()))
                            <button wire:click="startEdit({{ $comment->id }})"
                                    @click="open = false"
                                    class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 rounded-t-lg">
                                {{ __('decisions.actions.edit') }}
                            </button>
                        @endif
                        @if($comment->canBeDeletedBy(auth()->user()))
                            <button wire:click="deleteComment({{ $comment->id }})"
                                    wire:confirm="このコメントを削除しますか？"
                                    @click="open = false"
                                    class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 rounded-b-lg">
                                {{ __('decisions.actions.delete') }}
                            </button>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Comment Content --}}
        @if($editingComment === $comment->id)
            {{-- Edit Form --}}
            <div class="mt-3">
                <textarea wire:model="editContent"
                          rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coop-500 text-sm"></textarea>
                @error('editContent') 
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <div class="mt-2 flex gap-2">
                    <button wire:click="cancelEdit"
                            class="px-3 py-1 text-sm text-gray-600 hover:text-gray-900">
                        {{ __('decisions.actions.cancel') }}
                    </button>
                    <button wire:click="submitEdit"
                            class="px-3 py-1 text-sm bg-coop-600 text-white rounded hover:bg-coop-700">
                        保存
                    </button>
                </div>
            </div>
        @else
            {{-- Display Content --}}
            <div class="mt-3 text-sm text-gray-700 whitespace-pre-wrap">{{ $comment->content }}</div>
        @endif

        {{-- Reply Button --}}
        @if($comment->can_reply && $canComment)
            @if($replyingTo === $comment->id)
                {{-- Reply Form --}}
                <div class="mt-4 pl-4 border-l-2 border-coop-200">
                    <textarea wire:model="replyContent"
                              rows="2"
                              placeholder="返信を入力..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coop-500 text-sm"></textarea>
                    @error('replyContent') 
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <div class="mt-2 flex gap-2">
                        <button wire:click="cancelReply"
                                class="px-3 py-1 text-sm text-gray-600 hover:text-gray-900">
                            {{ __('decisions.actions.cancel') }}
                        </button>
                        <button wire:click="submitReply"
                                class="px-3 py-1 text-sm bg-coop-600 text-white rounded hover:bg-coop-700">
                            {{ __('decisions.actions.reply') }}
                        </button>
                    </div>
                </div>
            @else
                <button wire:click="startReply({{ $comment->id }})"
                        class="mt-3 text-sm text-coop-600 hover:text-coop-700">
                    <x-heroicon-o-arrow-uturn-left class="w-4 h-4 inline mr-1" />
                    {{ __('decisions.actions.reply') }}
                </button>
            @endif
        @endif
    </div>

    {{-- Nested Replies --}}
    @if($comment->replies && $comment->replies->count() > 0 && $depth < $maxDepth)
        <div class="divide-y divide-gray-50">
            @foreach($comment->replies as $reply)
                @include('livewire.decisions.components.partials.comment-item', ['comment' => $reply, 'depth' => $depth + 1])
            @endforeach
        </div>
    @endif
</div>
