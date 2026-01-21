<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained()->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            
            $table->string('title');
            $table->string('title_en')->nullable();
            
            $table->string('file_path')->nullable();
            $table->string('external_url', 2048)->nullable();
            
            $table->enum('document_type', [
                'pdf', 'image', 'spreadsheet', 'document', 'link', 'other'
            ])->default('other');
            
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            
            $table->timestamps();
            
            $table->index('proposal_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_documents');
    }
};
