<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\Attributes\Modelable;

class TiptapEditor extends Component
{
    #[Modelable]
    public string $content = '';
    
    public string $contentJson = '';
    public string $placeholder = '';
    public string $editorId;
    public bool $autofocus = false;
    public string $minHeight = '200px';
    public bool $showToolbar = true;
    public array $enabledExtensions = [
        'bold', 'italic', 'underline', 'strike',
        'heading', 'bulletList', 'orderedList',
        'blockquote', 'codeBlock', 'horizontalRule',
        'link', 'image', 'textAlign',
        'undo', 'redo'
    ];

    public function mount(
        string $content = '',
        string $contentJson = '',
        string $placeholder = '',
        bool $autofocus = false,
        string $minHeight = '200px',
        bool $showToolbar = true,
        array $enabledExtensions = []
    ): void {
        $this->content = $content;
        $this->contentJson = $contentJson;
        $this->placeholder = $placeholder ?: __('ここに内容を入力...');
        $this->autofocus = $autofocus;
        $this->minHeight = $minHeight;
        $this->showToolbar = $showToolbar;
        $this->editorId = 'tiptap-' . uniqid();
        
        if (!empty($enabledExtensions)) {
            $this->enabledExtensions = $enabledExtensions;
        }
    }

    public function updatedContent($value): void
    {
        $this->dispatch('tiptap-content-updated', content: $value);
    }

    public function render()
    {
        return view('livewire.components.tiptap-editor');
    }
}
