<?php

namespace App\Livewire\Knowledge;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\KnowledgeAttachment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

#[Layout('layouts.app')]
class KnowledgeCreate extends Component
{
    use WithFileUploads;

    #[Validate('required|string|max:255')]
    public string $title = '';

    public string $titleEn = '';

    #[Validate('nullable|string|max:500')]
    public string $excerpt = '';

    #[Validate('required|string|min:10')]
    public string $content = '';

    public string $contentJson = '';

    #[Validate('required|exists:knowledge_categories,id')]
    public ?int $categoryId = null;

    public array $tags = [];
    public string $newTag = '';

    #[Validate('required|in:article,faq,guide,recipe,manual,external_link')]
    public string $type = 'article';

    // External link fields
    public string $externalUrl = '';
    public string $externalSource = '';

    // Publishing
    public string $status = 'draft';
    public bool $isFeatured = false;
    public bool $isPinned = false;
    public int $sortOrder = 0;

    // File uploads
    public array $attachments = [];

    // UI State
    public bool $showPreview = false;

    public function mount(): void
    {
        $this->authorize('create', KnowledgeArticle::class);
        
        // Set default category if available
        $defaultCategory = KnowledgeCategory::active()->ordered()->first();
        if ($defaultCategory) {
            $this->categoryId = $defaultCategory->id;
        }
    }

    #[Computed]
    public function categories()
    {
        return KnowledgeCategory::active()->ordered()->get();
    }

    #[Computed]
    public function types(): array
    {
        return KnowledgeArticle::TYPES;
    }

    #[On('tiptap-updated')]
    public function handleTiptapUpdate(string $html, string $json): void
    {
        $this->content = $html;
        $this->contentJson = $json;
    }

    public function addTag(): void
    {
        $tag = trim($this->newTag);
        if ($tag && !in_array($tag, $this->tags)) {
            $this->tags[] = $tag;
        }
        $this->newTag = '';
    }

    public function removeTag(int $index): void
    {
        unset($this->tags[$index]);
        $this->tags = array_values($this->tags);
    }

    public function updatedAttachments(): void
    {
        $this->validateOnly('attachments.*', [
            'attachments.*' => 'file|max:10240', // 10MB max per file
        ]);
    }

    public function removeAttachment(int $index): void
    {
        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);
    }

    public function generateExcerpt(): void
    {
        if (empty($this->content)) {
            return;
        }
        $text = strip_tags($this->content);
        $this->excerpt = Str::limit($text, 200);
    }

    public function togglePreview(): void
    {
        $this->showPreview = !$this->showPreview;
    }

    public function saveDraft(): void
    {
        $this->status = 'draft';
        $this->save();
    }

    public function publish(): void
    {
        $this->status = 'published';
        $this->save();
    }

    public function save(): void
    {
        // Additional validation for external links
        if ($this->type === 'external_link') {
            $this->validate([
                'externalUrl' => 'required|url',
            ]);
        }

        $this->validate();

        $article = KnowledgeArticle::create([
            'uuid' => Str::uuid(),
            'title' => $this->title,
            'title_en' => $this->titleEn ?: null,
            'slug' => Str::slug($this->title) . '-' . Str::random(6),
            'excerpt' => $this->excerpt ?: null,
            'content' => $this->content,
            'content_json' => $this->contentJson ? json_decode($this->contentJson, true) : null,
            'category_id' => $this->categoryId,
            'tags' => !empty($this->tags) ? $this->tags : null,
            'type' => $this->type,
            'external_url' => $this->type === 'external_link' ? $this->externalUrl : null,
            'external_source' => $this->type === 'external_link' ? $this->externalSource : null,
            'status' => $this->status,
            'published_at' => $this->status === 'published' ? now() : null,
            'is_featured' => $this->isFeatured,
            'is_pinned' => $this->isPinned,
            'sort_order' => $this->sortOrder,
            'author_id' => Auth::id(),
        ]);

        // Handle file attachments
        foreach ($this->attachments as $index => $file) {
            $path = $file->store('knowledge/attachments', 'public');
            
            KnowledgeAttachment::create([
                'uuid' => Str::uuid(),
                'article_id' => $article->id,
                'filename' => $file->getClientOriginalName(),
                'path' => $path,
                'disk' => 'public',
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'sort_order' => $index,
            ]);
        }

        $message = $this->status === 'published' 
            ? __('記事を公開しました。')
            : __('下書きを保存しました。');

        session()->flash('success', $message);

        $this->redirect(route('knowledge.show', $article), navigate: true);
    }

    public function render()
    {
        return view('livewire.knowledge.knowledge-create');
    }
}
