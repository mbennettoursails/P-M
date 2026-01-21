<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            
            // Core Content
            $table->string('title');
            $table->string('title_en')->nullable();
            $table->text('description');
            $table->text('description_en')->nullable();
            
            // Decision Configuration
            $table->enum('decision_type', ['democratic', 'consensus', 'consent'])->default('democratic');
            $table->enum('current_stage', [
                'draft', 'feedback', 'refinement', 'voting', 'closed', 'archived'
            ])->default('draft');
            
            // Thresholds & Rules
            $table->unsignedTinyInteger('quorum_percentage')->default(50);
            $table->unsignedTinyInteger('pass_threshold')->default(50);
            $table->boolean('allow_anonymous_voting')->default(false);
            $table->boolean('show_results_during_voting')->default(false);
            
            // Participant Scope
            $table->json('allowed_roles')->nullable();
            $table->boolean('is_invite_only')->default(false);
            
            // Timing
            $table->timestamp('feedback_deadline')->nullable();
            $table->timestamp('voting_deadline')->nullable();
            $table->timestamp('closed_at')->nullable();
            
            // Outcome
            $table->enum('outcome', ['passed', 'rejected', 'no_quorum', 'blocked', 'withdrawn'])->nullable();
            $table->text('outcome_summary')->nullable();
            $table->text('outcome_summary_en')->nullable();
            
            // Relationships
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['current_stage', 'decision_type']);
            $table->index('author_id');
            $table->index('voting_deadline');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
