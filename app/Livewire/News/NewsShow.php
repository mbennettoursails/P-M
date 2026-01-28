<?php

namespace App\Livewire\News;

use App\Models\News;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

#[Layout('layouts.app')]
class NewsShow extends Component
{
    public News $news;

    public function mount(News $news): void
    {
        $this->authorize('view', $news);
        
        $this->news = $news;

        // Increment view count and mark as read
        $news->incrementViewCount();
        
        if (Auth::check()) {
            $news->markAsReadBy(Auth::user());
        }
    }

    #[Computed]
    public function canEdit(): bool
    {
        return Auth::user()?->can('update', $this->news) ?? false;
    }

    #[Computed]
    public function canDelete(): bool
    {
        return Auth::user()?->can('delete', $this->news) ?? false;
    }

    #[Computed]
    public function relatedNews()
    {
        return News::published()
            ->where('id', '!=', $this->news->id)
            ->where('category', $this->news->category)
            ->visibleTo(Auth::user())
            ->orderByDesc('published_at')
            ->take(3)
            ->get();
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->news);
        
        $this->news->delete();
        
        session()->flash('success', __('ニュースを削除しました。'));
        
        $this->redirect(route('news.index'), navigate: true);
    }

    public function togglePinned(): void
    {
        $this->authorize('pin', $this->news);
        
        $this->news->togglePinned();
        
        $message = $this->news->is_pinned 
            ? __('ニュースをピン留めしました。') 
            : __('ピン留めを解除しました。');
            
        session()->flash('success', $message);
    }

    public function publish(): void
    {
        $this->authorize('publish', $this->news);
        
        $this->news->publish();
        
        session()->flash('success', __('ニュースを公開しました。'));
    }

    public function unpublish(): void
    {
        $this->authorize('publish', $this->news);
        
        $this->news->unpublish();
        
        session()->flash('success', __('ニュースを非公開にしました。'));
    }

    public function render()
    {
        return view('livewire.news.news-show');
    }
}
