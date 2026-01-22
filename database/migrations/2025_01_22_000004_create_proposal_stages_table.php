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
            $table->string('stage_type', 20);
            
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->text('notes')->nullable();
            $table->foreignId('transitioned_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index('proposal_id');
            $table->index(['proposal_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_stages');
    }
};
