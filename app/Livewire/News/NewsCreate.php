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
class NewsCreate extends Component
{
    use WithFileUploads;

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
    #[Validate('nullable|image|max:5120')] // 5MB max
    public $featuredImage = null;

    public ?string $featuredImagePreview = null;

    #[Validate('nullable|string|max:255')]
    public string $featuredImageAlt = '';

    // Publishing Options
    public string $status = 'draft';

    public ?string $publishedAt = null;

    public bool $isPinned = false;

    public bool $isFeatured = false;

    // Visibility
    public array $visibleToRoles = []; // Empty = visible to all

    // UI State
    public bool $showPreview = false;

    public function mount(): void
    {
        $this->authorize('create', News::class);
        
        // Set default publish date to now
        $this->publishedAt = now()->format('Y-m-d\TH:i');
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

    /**
     * Listen for Tiptap content updates
     */
    #[On('tiptap-updated')]
    public function handleTiptapUpdate(string $html, string $json): void
    {
        $this->content = $html;
        $this->contentJson = $json;
    }

    /**
     * Handle featured image upload
     */
    public function updatedFeaturedImage(): void
    {
        $this->validateOnly('featuredImage');

        if ($this->featuredImage) {
            $this->featuredImagePreview = $this->featuredImage->temporaryUrl();
        }
    }

    /**
     * Remove featured image
     */
    public function removeFeaturedImage(): void
    {
        $this->featuredImage = null;
        $this->featuredImagePreview = null;
        $this->featuredImageAlt = '';
    }

    /**
     * Toggle preview mode
     */
    public function togglePreview(): void
    {
        $this->showPreview = !$this->showPreview;
    }

    /**
     * Auto-generate excerpt from content
     */
    public function generateExcerpt(): void
    {
        if (empty($this->content)) {
            return;
        }

        $text = strip_tags($this->content);
        $this->excerpt = Str::limit($text, 200);
    }

    /**
     * Save as draft
     */
    public function saveDraft(): void
    {
        $this->status = 'draft';
        $this->save();
    }

    /**
     * Publish immediately
     */
    public function publish(): void
    {
        $this->status = 'published';
        $this->publishedAt = now()->format('Y-m-d\TH:i');
        $this->save();
    }

    /**
     * Schedule for later
     */
    public function schedule(): void
    {
        $this->validate([
            'publishedAt' => 'required|date|after:now',
        ]);

        $this->status = 'published';
        $this->save();
    }

    /**
     * Save the news article
     */
    public function save(): void
    {
        $this->validate();

        // Handle featured image upload
        $featuredImagePath = null;
        if ($this->featuredImage) {
            $featuredImagePath = $this->featuredImage->store('news/images', 'public');
        }

        $news = News::create([
            'title' => $this->title,
            'slug' => Str::slug($this->title) . '-' . Str::random(6),
            'excerpt' => $this->excerpt ?: null,
            'content' => $this->content,
            'content_json' => $this->contentJson ? json_decode($this->contentJson, true) : null,
            'category' => $this->category,
            'featured_image' => $featuredImagePath ? '/storage/' . $featuredImagePath : null,
            'featured_image_alt' => $this->featuredImageAlt ?: null,
            'status' => $this->status,
            'published_at' => $this->status === 'published' ? ($this->publishedAt ? \Carbon\Carbon::parse($this->publishedAt) : now()) : null,
            'is_pinned' => $this->isPinned,
            'is_featured' => $this->isFeatured,
            'visible_to_roles' => !empty($this->visibleToRoles) ? $this->visibleToRoles : null,
            'author_id' => Auth::id(),
        ]);

        $message = $this->status === 'published' 
            ? __('ニュースを公開しました。')
            : __('下書きを保存しました。');

        session()->flash('success', $message);

        $this->redirect(route('news.show', $news), navigate: true);
    }

    public function render()
    {
        return view('livewire.news.news-create');
    }
}
