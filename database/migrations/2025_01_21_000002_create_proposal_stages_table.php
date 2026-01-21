<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained()->onDelete('cascade');
            
            $table->enum('stage_type', [
                'draft', 'feedback', 'refinement', 'voting', 'closed', 'archived'
            ]);
            
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->text('notes')->nullable();
            $table->foreignId('transitioned_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            $table->index(['proposal_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_stages');
    }
};
