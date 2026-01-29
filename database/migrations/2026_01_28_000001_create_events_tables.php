<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Events table
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            
            // Basic Information
            $table->string('title');
            $table->string('title_en')->nullable();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('description_en')->nullable();
            $table->longText('content')->nullable(); // Rich text content
            $table->json('content_json')->nullable(); // Tiptap JSON for editing
            
            // Date & Time
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->boolean('is_all_day')->default(false);
            
            // Location
            $table->string('location')->nullable(); // Venue name
            $table->string('location_en')->nullable();
            $table->string('address')->nullable(); // Full address
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_online')->default(false);
            $table->string('online_url')->nullable(); // For virtual events
            
            // Capacity & Registration
            $table->unsignedInteger('capacity')->nullable(); // null = unlimited
            $table->boolean('registration_required')->default(true);
            $table->dateTime('registration_opens_at')->nullable();
            $table->dateTime('registration_closes_at')->nullable();
            $table->boolean('waitlist_enabled')->default(false);
            
            // Categorization
            $table->enum('category', [
                'general',      // 一般
                'workshop',     // ワークショップ
                'meeting',      // 会議
                'social',       // 交流会
                'volunteer',    // ボランティア
                'cooking',      // 料理教室
                'lecture',      // 講座
                'other'         // その他
            ])->default('general');
            
            // Display
            $table->string('featured_image')->nullable();
            $table->string('featured_image_alt')->nullable();
            $table->string('color')->default('primary'); // Tailwind color theme
            
            // Status & Visibility
            $table->enum('status', ['draft', 'published', 'cancelled', 'completed'])->default('draft');
            $table->json('visible_to_roles')->nullable(); // Restrict visibility by role
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_pinned')->default(false);
            
            // Metadata
            $table->foreignId('organizer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->unsignedInteger('view_count')->default(0);
            
            // Cost (optional)
            $table->unsignedInteger('cost')->default(0); // In yen, 0 = free
            $table->string('cost_notes')->nullable(); // e.g., "材料費含む"
            
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('category');
            $table->index('starts_at');
            $table->index('organizer_id');
            $table->index(['status', 'starts_at']);
        });

        // Event Registrations (pivot table)
        Schema::create('event_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Registration Status
            $table->enum('status', [
                'registered',   // 登録済み
                'waitlisted',   // キャンセル待ち
                'cancelled',    // キャンセル
                'attended',     // 参加済み
                'no_show'       // 欠席
            ])->default('registered');
            
            // Additional Info
            $table->text('notes')->nullable(); // User's registration notes
            $table->text('admin_notes')->nullable(); // Admin-only notes
            $table->unsignedInteger('guests')->default(0); // Additional guests
            
            // Timestamps
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('attended_at')->nullable();
            $table->timestamps();
            
            $table->unique(['event_id', 'user_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_user');
        Schema::dropIfExists('events');
    }
};
