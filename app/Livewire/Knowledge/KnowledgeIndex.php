<?php

namespace App\Livewire\Knowledge;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;

#[Layout('layouts.app')]
class KnowledgeIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $type = '';

    #[Url]
    public string $tag = '';

    public int $perPage = 12;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->category = '';
        $this->type = '';
        $this->tag = '';
        $this->resetPage();
    }

    public function selectCategory(string $categoryUuid): void
    {
        $this->category = $categoryUuid;
        $this->resetPage();
    }

    public function selectTag(string $tagName): void
    {
        $this->tag = $tagName;
        $this->resetPage();
    }

    #[Computed]
    public function categories()
    {
        return KnowledgeCategory::active()
            ->rootLevel()
            ->ordered()
            ->withCount(['publishedArticles'])
            ->get();
    }

    #[Computed]
    public function selectedCategory(): ?KnowledgeCategory
    {
        if ($this->category) {
            return KnowledgeCategory::where('uuid', $this->category)->first();
        }
        return null;
    }

    #[Computed]
    public function types(): array
    {
        return KnowledgeArticle::TYPES;
    }

    #[Computed]
    public function popularTags(): array
    {
        // Get most common tags from published articles
        $articles = KnowledgeArticle::published()
            ->whereNotNull('tags')
            ->pluck('tags')
            ->flatten()
            ->countBy()
            ->sortDesc()
            ->take(10)
            ->keys()
            ->toArray();
        
        return $articles;
    }

    #[Computed]
    public function articles()
    {
        $query = KnowledgeArticle::query()
            ->with(['category', 'author:id,name'])
            ->published();

        // Search filter
        if ($this->search) {
            $query->search($this->search);
        }

        // Category filter
        if ($this->category) {
            $category = KnowledgeCategory::where('uuid', $this->category)->first();
            if ($category) {
                $query->inCategory($category->id);
            }
        }

        // Type filter
        if ($this->type) {
            $query->ofType($this->type);
        }

        // Tag filter
        if ($this->tag) {
            $query->withTag($this->tag);
        }

        return $query->ordered()->paginate($this->perPage);
    }

    #[Computed]
    public function featuredArticles()
    {
        return KnowledgeArticle::published()
            ->featured()
            ->with(['category'])
            ->orderByDesc('published_at')
            ->take(3)
            ->get();
    }

    #[Computed]
    public function canManage(): bool
    {
        return Auth::user()?->hasAnyRole(['admin', 'shokuin', 'reijikai']) ?? false;
    }

    public function render()
    {
        return view('livewire.knowledge.knowledge-index');
    }
}
