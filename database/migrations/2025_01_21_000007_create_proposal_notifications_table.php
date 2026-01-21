<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_notifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('proposal_id')->nullable()->constrained()->onDelete('cascade');
            
            $table->enum('type', [
                'proposal_created',
                'proposal_stage_changed',
                'vote_reminder',
                'deadline_approaching',
                'new_comment',
                'comment_reply',
                'proposal_outcome',
                'invited_to_participate'
            ]);
            
            $table->string('title');
            $table->string('title_en')->nullable();
            $table->text('message');
            $table->text('message_en')->nullable();
            
            $table->string('action_url')->nullable();
            
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_notifications');
    }
};
