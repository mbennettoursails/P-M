<?php

use App\Livewire\Decisions\ProposalList;
use App\Livewire\Decisions\ProposalCreate;
use App\Livewire\Decisions\ProposalShow;
use App\Livewire\Decisions\ProposalEdit;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Decision Making Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::prefix('decisions')->name('decisions.')->group(function () {
        Route::get('/', ProposalList::class)->name('index');
        Route::get('/create', ProposalCreate::class)->name('create');
        Route::get('/{proposal:uuid}', ProposalShow::class)->name('show');
        Route::get('/{proposal:uuid}/edit', ProposalEdit::class)->name('edit');
    });
    
});
