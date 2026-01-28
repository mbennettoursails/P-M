<?php

namespace App\Livewire\News;

use App\Models\News;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;

#[Layout('layouts.app')]
class NewsList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $status = 'published';

    public int $perPage = 10;

    // For managers to filter their own articles
    public bool $myArticlesOnly = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->category = '';
        $this->status = 'published';
        $this->myArticlesOnly = false;
        $this->resetPage();
    }

    public function toggleMyArticles(): void
    {
        $this->myArticlesOnly = !$this->myArticlesOnly;
        $this->resetPage();
    }

    #[Computed]
    public function canManageNews(): bool
    {
        return Auth::user()?->hasAnyRole(['admin', 'shokuin', 'reijikai']) ?? false;
    }

    #[Computed]
    public function categories(): array
    {
        return News::CATEGORIES;
    }

    #[Computed]
    public function statuses(): array
    {
        return News::STATUSES;
    }

    #[Computed]
    public function news()
    {
        $user = Auth::user();
        
        $query = News::query()
            ->with('author:id,name');

        // Regular users only see published content
        if (!$this->canManageNews) {
            $query->published()->visibleTo($user);
        } else {
            // Managers can filter by status
            if ($this->status === 'published') {
                $query->published();
            } elseif ($this->status === 'draft') {
                $query->draft();
            } elseif ($this->status === 'archived') {
                $query->archived();
            }

            // Filter to only user's articles if toggled
            if ($this->myArticlesOnly) {
                $query->where('author_id', $user->id);
            }
        }

        // Search filter
        if ($this->search) {
            $query->search($this->search);
        }

        // Category filter
        if ($this->category) {
            $query->category($this->category);
        }

        // Order: pinned first, then by published date
        $query->pinnedFirst()
            ->orderByDesc('published_at')
            ->orderByDesc('created_at');

        return $query->paginate($this->perPage);
    }

    #[Computed]
    public function unreadCount(): int
    {
        $user = Auth::user();
        if (!$user) return 0;

        return News::published()
            ->visibleTo($user)
            ->unreadFor($user)
            ->count();
    }

    public function markAsRead(int $newsId): void
    {
        $news = News::findOrFail($newsId);
        $news->markAsReadBy(Auth::user());
    }

    public function delete(int $newsId): void
    {
        $news = News::findOrFail($newsId);
        $this->authorize('delete', $news);
        
        $news->delete();
        
        session()->flash('success', __('ニュースを削除しました。'));
    }

    public function togglePinned(int $newsId): void
    {
        $news = News::findOrFail($newsId);
        $this->authorize('pin', $news);
        
        $news->togglePinned();
        
        $message = $news->is_pinned 
            ? __('ニュースをピン留めしました。') 
            : __('ピン留めを解除しました。');
        session()->flash('success', $message);
    }

    public function render()
    {
        return view('livewire.news.news-list');
    }
}
