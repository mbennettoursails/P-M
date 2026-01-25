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
            
            // Content
            $table->string('title', 255);
            $table->text('description');
            
            // Decision Configuration
            $table->string('decision_type', 20)->default('democratic');
            $table->string('current_stage', 20)->default('draft');
            $table->unsignedTinyInteger('quorum_percentage')->default(50);
            $table->unsignedTinyInteger('pass_threshold')->default(50);
            
            // Settings
            $table->boolean('allow_anonymous_voting')->default(false);
            $table->boolean('show_results_during_voting')->default(true);
            $table->json('allowed_roles')->default('["reijikai"]');
            $table->boolean('is_invite_only')->default(false);
            
            // Timeline
            $table->timestamp('feedback_deadline')->nullable();
            $table->timestamp('voting_deadline')->nullable();
            $table->timestamp('closed_at')->nullable();
            
            // Outcome
            $table->string('outcome', 20)->nullable();
            $table->text('outcome_summary')->nullable();
            
            // Relations
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('current_stage');
            $table->index('decision_type');
            $table->index('author_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
