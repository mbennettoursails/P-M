<div 
    wire:ignore
    x-data="tiptapEditor({
        content: @entangle('content'),
        contentJson: @entangle('contentJson'),
        editorId: '{{ $editorId }}',
        placeholder: '{{ $placeholder }}',
        autofocus: {{ $autofocus ? 'true' : 'false' }},
        minHeight: '{{ $minHeight }}',
        showToolbar: {{ $showToolbar ? 'true' : 'false' }},
        enabledExtensions: {{ json_encode($enabledExtensions) }}
    })"
    x-init="initEditor()"
    class="tiptap-editor-wrapper"
>
    {{-- Toolbar --}}
    @if($showToolbar)
    <div class="tiptap-toolbar bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-t-lg p-2 flex flex-wrap gap-1">
        {{-- Text Formatting --}}
        <div class="flex items-center gap-1 pr-2 border-r border-gray-200 dark:border-gray-600">
            @if(in_array('bold', $enabledExtensions))
            <button 
                type="button"
                @click="toggleBold()"
                :class="{ 'bg-gray-200 dark:bg-gray-600': isActive('bold') }"
                class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                title="{{ __('太字') }} (Ctrl+B)"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z"></path>
                </svg>
            </button>
            @endif

            @if(in_array('italic', $enabledExtensions))
            <button 
                type="button"
                @click="toggleItalic()"
                :class="{ 'bg-gray-200 dark:bg-gray-600': isActive('italic') }"
                class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                title="{{ __('斜体') }} (Ctrl+I)"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 4h4m-2 0v16m-4 0h8"></path>
                </svg>
            </button>
            @endif

            @if(in_array('underline', $enabledExtensions))
            <button 
                type="button"
                @click="toggleUnderline()"
                :class="{ 'bg-gray-200 dark:bg-gray-600': isActive('underline') }"
                class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                title="{{ __('下線') }} (Ctrl+U)"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8v8a5 5 0 0010 0V8M5 21h14"></path>
                </svg>
            </button>
            @endif

            @if(in_array('strike', $enabledExtensions))
            <button 
                type="button"
                @click="toggleStrike()"
                :class="{ 'bg-gray-200 dark:bg-gray-600': isActive('strike') }"
                class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                title="{{ __('取り消し線') }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 12H7m10-4H7m10 8H7"></path>
                </svg>
            </button>
            @endif
        </div>

        {{-- Headings --}}
        @if(in_array('heading', $enabledExtensions))
        <div class="flex items-center gap-1 pr-2 border-r border-gray-200 dark:border-gray-600">
            <button 
                type="button"
                @click="toggleHeading(2)"
                :class="{ 'bg-gray-200 dark:bg-gray-600': isActive('heading', { level: 2 }) }"
                class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition text-sm font-bold"
                title="{{ __('見出し') }} 2"
            >
                H2
            </button>
            <button 
                type="button"
                @click="toggleHeading(3)"
                :class="{ 'bg-gray-200 dark:bg-gray-600': isActive('heading', { level: 3 }) }"
                class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition text-sm font-bold"
                title="{{ __('見出し') }} 3"
            >
                H3
            </button>
        </div>
        @endif

        {{-- Lists --}}
        <div class="flex items-center gap-1 pr-2 border-r border-gray-200 dark:border-gray-600">
            @if(in_array('bulletList', $enabledExtensions))
            <button 
                type="button"
                @click="toggleBulletList()"
                :class="{ 'bg-gray-200 dark:bg-gray-600': isActive('bulletList') }"
                class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                title="{{ __('箇条書き') }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
            @endif

            @if(in_array('orderedList', $enabledExtensions))
            <button 
                type="button"
                @click="toggleOrderedList()"
                :class="{ 'bg-gray-200 dark:bg-gray-600': isActive('orderedList') }"
                class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                title="{{ __('番号付きリスト') }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h10M7 16h10M3 8h.01M3 12h.01M3 16h.01"></path>
                </svg>
            </button>
            @endif
        </div>

        {{-- Block Elements --}}
        <div class="flex items-center gap-1 pr-2 border-r border-gray-200 dark:border-gray-600">
            @if(in_array('blockquote', $enabledExtensions))
            <button 
                type="button"
                @click="toggleBlockquote()"
                :class="{ 'bg-gray-200 dark:bg-gray-600': isActive('blockquote') }"
                class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                title="{{ __('引用') }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-4l-4 4z"></path>
                </svg>
            </button>
            @endif

            @if(in_array('horizontalRule', $enabledExtensions))
            <button 
                type="button"
                @click="setHorizontalRule()"
                class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                title="{{ __('水平線') }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"></path>
                </svg>
            </button>
            @endif
        </div>

        {{-- Link --}}
        @if(in_array('link', $enabledExtensions))
        <div class="flex items-center gap-1 pr-2 border-r border-gray-200 dark:border-gray-600">
            <button 
                type="button"
                @click="setLink()"
                :class="{ 'bg-gray-200 dark:bg-gray-600': isActive('link') }"
                class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                title="{{ __('リンク') }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                </svg>
            </button>
        </div>
        @endif

        {{-- Text Alignment --}}
        @if(in_array('textAlign', $enabledExtensions))
        <div class="flex items-center gap-1 pr-2 border-r border-gray-200 dark:border-gray-600">
            <button 
                type="button"
                @click="setTextAlign('left')"
                :class="{ 'bg-gray-200 dark:bg-gray-600': isActive({ textAlign: 'left' }) }"
                class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                title="{{ __('左揃え') }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h14"></path>
                </svg>
            </button>
            <button 
                type="button"
                @click="setTextAlign('center')"
                :class="{ 'bg-gray-200 dark:bg-gray-600': isActive({ textAlign: 'center' }) }"
                class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                title="{{ __('中央揃え') }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M5 18h14"></path>
                </svg>
            </button>
            <button 
                type="button"
                @click="setTextAlign('right')"
                :class="{ 'bg-gray-200 dark:bg-gray-600': isActive({ textAlign: 'right' }) }"
                class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                title="{{ __('右揃え') }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M10 12h10M6 18h14"></path>
                </svg>
            </button>
        </div>
        @endif

        {{-- Undo/Redo --}}
        @if(in_array('undo', $enabledExtensions) || in_array('redo', $enabledExtensions))
        <div class="flex items-center gap-1">
            @if(in_array('undo', $enabledExtensions))
            <button 
                type="button"
                @click="undo()"
                class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                title="{{ __('元に戻す') }} (Ctrl+Z)"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                </svg>
            </button>
            @endif

            @if(in_array('redo', $enabledExtensions))
            <button 
                type="button"
                @click="redo()"
                class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                title="{{ __('やり直し') }} (Ctrl+Y)"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6"></path>
                </svg>
            </button>
            @endif
        </div>
        @endif
    </div>
    @endif

    {{-- Editor Content Area --}}
    <div 
        id="{{ $editorId }}"
        class="tiptap-content bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 {{ $showToolbar ? 'border-t-0 rounded-b-lg' : 'rounded-lg' }} p-4 prose prose-sm dark:prose-invert max-w-none focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
        style="min-height: {{ $minHeight }}"
    ></div>

    {{-- Hidden input for form submission --}}
    <input type="hidden" name="content" :value="content">
</div>

@push('styles')
<style>
    /* Tiptap Editor Styles */
    .tiptap-content .ProseMirror {
        outline: none;
        min-height: inherit;
    }
    
    .tiptap-content .ProseMirror p.is-editor-empty:first-child::before {
        content: attr(data-placeholder);
        float: left;
        color: #9ca3af;
        pointer-events: none;
        height: 0;
    }
    
    .tiptap-content .ProseMirror:focus {
        outline: none;
    }
    
    /* Heading styles */
    .tiptap-content h2 {
        font-size: 1.5rem;
        font-weight: 700;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
    }
    
    .tiptap-content h3 {
        font-size: 1.25rem;
        font-weight: 600;
        margin-top: 1.25rem;
        margin-bottom: 0.5rem;
    }
    
    /* List styles */
    .tiptap-content ul,
    .tiptap-content ol {
        padding-left: 1.5rem;
        margin: 0.75rem 0;
    }
    
    .tiptap-content ul {
        list-style-type: disc;
    }
    
    .tiptap-content ol {
        list-style-type: decimal;
    }
    
    /* Blockquote */
    .tiptap-content blockquote {
        border-left: 4px solid #10b981;
        padding-left: 1rem;
        margin: 1rem 0;
        color: #6b7280;
        font-style: italic;
    }
    
    /* Code block */
    .tiptap-content pre {
        background: #1f2937;
        color: #e5e7eb;
        padding: 1rem;
        border-radius: 0.5rem;
        overflow-x: auto;
    }
    
    /* Horizontal rule */
    .tiptap-content hr {
        border: none;
        border-top: 2px solid #e5e7eb;
        margin: 1.5rem 0;
    }
    
    /* Links */
    .tiptap-content a {
        color: #10b981;
        text-decoration: underline;
    }
    
    .tiptap-content a:hover {
        color: #059669;
    }
    
    /* Images */
    .tiptap-content img {
        max-width: 100%;
        height: auto;
        border-radius: 0.5rem;
    }
</style>
@endpush
