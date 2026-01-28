<?php

namespace App\Livewire\News;

use App\Models\News;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

#[Layout('layouts.app')]
class NewsEdit extends Component
{
    use WithFileUploads;

    public News $news;

    // Basic Information
    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('nullable|string|max:500')]
    public string $excerpt = '';

    #[Validate('required|string|min:10')]
    public string $content = '';

    public string $contentJson = '';

    #[Validate('required|in:general,announcement,event,recipe,tips,urgent')]
    public string $category = 'general';

    // Featured Image
    #[Validate('nullable|image|max:5120')]
    public $featuredImage = null;

    public ?string $featuredImagePreview = null;
    public ?string $existingFeaturedImage = null;

    #[Validate('nullable|string|max:255')]
    public string $featuredImageAlt = '';

    // Publishing Options
    public string $status = 'draft';
    public ?string $publishedAt = null;
    public bool $isPinned = false;
    public bool $isFeatured = false;

    // Visibility
    public array $visibleToRoles = [];

    // UI State
    public bool $showPreview = false;

    public function mount(News $news): void
    {
        $this->authorize('update', $news);
        
        $this->news = $news;
        
        // Populate form fields
        $this->title = $news->title;
        $this->excerpt = $news->excerpt ?? '';
        $this->content = $news->content;
        $this->contentJson = $news->content_json ? json_encode($news->content_json) : '';
        $this->category = $news->category;
        $this->existingFeaturedImage = $news->featured_image;
        $this->featuredImageAlt = $news->featured_image_alt ?? '';
        $this->status = $news->status;
        $this->publishedAt = $news->published_at?->format('Y-m-d\TH:i');
        $this->isPinned = $news->is_pinned;
        $this->isFeatured = $news->is_featured;
        $this->visibleToRoles = $news->visible_to_roles ?? [];
    }

    #[Computed]
    public function categories(): array
    {
        return News::CATEGORIES;
    }

    #[Computed]
    public function availableRoles(): array
    {
        return [
            'reijikai' => __('委員会'),
            'shokuin' => __('職員'),
            'volunteer' => __('ボランティア'),
        ];
    }

    #[On('tiptap-updated')]
    public function handleTiptapUpdate(string $html, string $json): void
    {
        $this->content = $html;
        $this->contentJson = $json;
    }

    public function updatedFeaturedImage(): void
    {
        $this->validateOnly('featuredImage');

        if ($this->featuredImage) {
            $this->featuredImagePreview = $this->featuredImage->temporaryUrl();
        }
    }

    public function removeFeaturedImage(): void
    {
        $this->featuredImage = null;
        $this->featuredImagePreview = null;
        $this->existingFeaturedImage = null;
        $this->featuredImageAlt = '';
    }

    public function togglePreview(): void
    {
        $this->showPreview = !$this->showPreview;
    }

    public function generateExcerpt(): void
    {
        if (empty($this->content)) {
            return;
        }

        $text = strip_tags($this->content);
        $this->excerpt = Str::limit($text, 200);
    }

    public function saveDraft(): void
    {
        $this->status = 'draft';
        $this->save();
    }

    public function publish(): void
    {
        $this->status = 'published';
        if (!$this->publishedAt) {
            $this->publishedAt = now()->format('Y-m-d\TH:i');
        }
        $this->save();
    }

    public function save(): void
    {
        $this->validate();

        // Handle featured image upload
        $featuredImagePath = $this->existingFeaturedImage;
        if ($this->featuredImage) {
            $featuredImagePath = '/storage/' . $this->featuredImage->store('news/images', 'public');
        }

        $this->news->update([
            'title' => $this->title,
            'excerpt' => $this->excerpt ?: null,
            'content' => $this->content,
            'content_json' => $this->contentJson ? json_decode($this->contentJson, true) : null,
            'category' => $this->category,
            'featured_image' => $featuredImagePath,
            'featured_image_alt' => $this->featuredImageAlt ?: null,
            'status' => $this->status,
            'published_at' => $this->status === 'published' ? ($this->publishedAt ? \Carbon\Carbon::parse($this->publishedAt) : now()) : null,
            'is_pinned' => $this->isPinned,
            'is_featured' => $this->isFeatured,
            'visible_to_roles' => !empty($this->visibleToRoles) ? $this->visibleToRoles : null,
        ]);

        $message = $this->status === 'published' 
            ? __('ニュースを更新・公開しました。')
            : __('下書きを保存しました。');

        session()->flash('success', $message);

        $this->redirect(route('news.show', $this->news), navigate: true);
    }

    public function render()
    {
        return view('livewire.news.news-edit');
    }
}
