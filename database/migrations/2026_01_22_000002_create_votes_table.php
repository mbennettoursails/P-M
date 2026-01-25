<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            
            $table->foreignId('proposal_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->string('vote_value', 20);
            $table->text('reason')->nullable();
            $table->boolean('is_anonymous')->default(false);
            
            // Change tracking
            $table->string('previous_vote_value', 20)->nullable();
            $table->timestamp('changed_at')->nullable();
            
            $table->timestamps();
            
            // Each user can only vote once per proposal
            $table->unique(['proposal_id', 'user_id']);
            
            // Indexes
            $table->index('proposal_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
