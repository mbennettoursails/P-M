<?php

namespace App\Livewire\Knowledge;

use App\Models\KnowledgeArticle;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

#[Layout('layouts.app')]
class KnowledgeShow extends Component
{
    public KnowledgeArticle $article;
    public ?bool $userFeedback = null;

    public function mount(KnowledgeArticle $article): void
    {
        $this->authorize('view', $article);
        
        $this->article = $article;
        $this->article->incrementViewCount();
        
        if (Auth::check()) {
            $this->userFeedback = $article->getUserFeedback(Auth::user());
        }
    }

    #[Computed]
    public function canEdit(): bool
    {
        return Auth::user()?->can('update', $this->article) ?? false;
    }

    #[Computed]
    public function relatedArticles()
    {
        return $this->article->relatedArticles()
            ->published()
            ->take(3)
            ->get();
    }

    #[Computed]
    public function sameCategoryArticles()
    {
        if ($this->relatedArticles->isNotEmpty()) {
            return collect();
        }

        return KnowledgeArticle::published()
            ->where('id', '!=', $this->article->id)
            ->inCategory($this->article->category_id)
            ->orderByDesc('published_at')
            ->take(3)
            ->get();
    }

    public function markHelpful(): void
    {
        if (!Auth::check()) {
            return;
        }

        $this->article->markHelpful(Auth::user());
        $this->article->refresh();
        $this->userFeedback = true;
    }

    public function markNotHelpful(): void
    {
        if (!Auth::check()) {
            return;
        }

        $this->article->markNotHelpful(Auth::user());
        $this->article->refresh();
        $this->userFeedback = false;
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
        return view('livewire.knowledge.knowledge-show');
    }
}
