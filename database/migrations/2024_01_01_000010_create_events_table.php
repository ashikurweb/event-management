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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organizer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->longText('description')->nullable();
            $table->text('short_description')->nullable();
            $table->enum('event_type', ['online', 'offline', 'hybrid']);
            $table->enum('status', ['draft', 'published', 'cancelled', 'completed', 'postponed'])->default('draft');
            $table->enum('visibility', ['public', 'private', 'unlisted'])->default('public');

            // Date & Time
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('timezone', 50);
            $table->dateTime('registration_start')->nullable();
            $table->dateTime('registration_end')->nullable();

            // Location (for offline/hybrid)
            $table->string('venue_name', 255)->nullable();
            $table->text('venue_address')->nullable();
            $table->string('venue_city', 100)->nullable();
            $table->string('venue_state', 100)->nullable();
            $table->string('venue_country', 100)->nullable();
            $table->string('venue_postal_code', 20)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Online Details (for online/hybrid)
            $table->text('meeting_url')->nullable();
            $table->string('meeting_platform', 50)->nullable();
            $table->string('meeting_id', 255)->nullable();
            $table->string('meeting_password', 255)->nullable();

            // Capacity & Limits
            $table->integer('max_attendees')->nullable();
            $table->integer('min_attendees')->nullable();
            $table->integer('current_attendees')->default(0);
            $table->boolean('waitlist_enabled')->default(false);

            // Media
            $table->string('featured_image', 255)->nullable();
            $table->string('banner_image', 255)->nullable();
            $table->text('video_url')->nullable();

            // SEO & Marketing
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();

            // Settings
            $table->boolean('is_featured')->default(false);
            $table->boolean('allow_comments')->default(true);
            $table->boolean('allow_reviews')->default(true);
            $table->boolean('require_approval')->default(false);

            // Stats
            $table->integer('view_count')->default(0);
            $table->integer('share_count')->default(0);

            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('status');
            $table->index(['start_date', 'end_date']);
            $table->index('organizer_id');
            $table->index('category_id');
            $table->fullText(['title', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

