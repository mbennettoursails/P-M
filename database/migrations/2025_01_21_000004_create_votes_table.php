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
            $table->foreignId('proposal_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->string('vote_value', 20);
            $table->text('reason')->nullable();
            $table->boolean('is_anonymous')->default(false);
            
            $table->timestamp('voted_at');
            $table->timestamp('changed_at')->nullable();
            
            $table->timestamps();
            
            $table->unique(['proposal_id', 'user_id']);
            $table->index(['proposal_id', 'vote_value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
