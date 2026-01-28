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
class KnowledgeEdit extends Component
{
    use WithFileUploads;

    public KnowledgeArticle $article;

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
    public array $newAttachments = [];
    public array $existingAttachments = [];

    // UI State
    public bool $showPreview = false;

    public function mount(KnowledgeArticle $article): void
    {
        $this->authorize('update', $article);
        
        $this->article = $article;
        
        // Populate form fields
        $this->title = $article->title;
        $this->titleEn = $article->title_en ?? '';
        $this->excerpt = $article->excerpt ?? '';
        $this->content = $article->content;
        $this->contentJson = $article->content_json ? json_encode($article->content_json) : '';
        $this->categoryId = $article->category_id;
        $this->tags = $article->tags ?? [];
        $this->type = $article->type;
        $this->externalUrl = $article->external_url ?? '';
        $this->externalSource = $article->external_source ?? '';
        $this->status = $article->status;
        $this->isFeatured = $article->is_featured;
        $this->isPinned = $article->is_pinned;
        $this->sortOrder = $article->sort_order;
        
        // Load existing attachments
        $this->existingAttachments = $article->attachments->map(function ($attachment) {
            return [
                'id' => $attachment->id,
                'uuid' => $attachment->uuid,
                'filename' => $attachment->filename,
                'title' => $attachment->title,
                'type' => $attachment->type,
                'size' => $attachment->human_size,
                'keep' => true,
            ];
        })->toArray();
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

    public function updatedNewAttachments(): void
    {
        $this->validateOnly('newAttachments.*', [
            'newAttachments.*' => 'file|max:10240',
        ]);
    }

    public function removeNewAttachment(int $index): void
    {
        unset($this->newAttachments[$index]);
        $this->newAttachments = array_values($this->newAttachments);
    }

    public function removeExistingAttachment(int $index): void
    {
        $this->existingAttachments[$index]['keep'] = false;
    }

    public function restoreExistingAttachment(int $index): void
    {
        $this->existingAttachments[$index]['keep'] = true;
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

        $this->article->update([
            'title' => $this->title,
            'title_en' => $this->titleEn ?: null,
            'excerpt' => $this->excerpt ?: null,
            'content' => $this->content,
            'content_json' => $this->contentJson ? json_decode($this->contentJson, true) : null,
            'category_id' => $this->categoryId,
            'tags' => !empty($this->tags) ? $this->tags : null,
            'type' => $this->type,
            'external_url' => $this->type === 'external_link' ? $this->externalUrl : null,
            'external_source' => $this->type === 'external_link' ? $this->externalSource : null,
            'status' => $this->status,
            'published_at' => $this->status === 'published' && !$this->article->published_at ? now() : $this->article->published_at,
            'is_featured' => $this->isFeatured,
            'is_pinned' => $this->isPinned,
            'sort_order' => $this->sortOrder,
            'last_editor_id' => Auth::id(),
            'last_edited_at' => now(),
        ]);

        // Handle removed attachments
        foreach ($this->existingAttachments as $attachment) {
            if (!$attachment['keep']) {
                KnowledgeAttachment::find($attachment['id'])?->delete();
            }
        }

        // Handle new file attachments
        $maxSortOrder = $this->article->attachments()->max('sort_order') ?? 0;
        foreach ($this->newAttachments as $index => $file) {
            $path = $file->store('knowledge/attachments', 'public');
            
            KnowledgeAttachment::create([
                'uuid' => Str::uuid(),
                'article_id' => $this->article->id,
                'filename' => $file->getClientOriginalName(),
                'path' => $path,
                'disk' => 'public',
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'sort_order' => $maxSortOrder + $index + 1,
            ]);
        }

        $message = $this->status === 'published' 
            ? __('記事を更新・公開しました。')
            : __('下書きを保存しました。');

        session()->flash('success', $message);

        $this->redirect(route('knowledge.show', $this->article), navigate: true);
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->article);
        
        $this->article->delete();
        
        session()->flash('success', __('記事を削除しました。'));
        
        $this->redirect(route('knowledge.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.knowledge.knowledge-edit');
    }
}