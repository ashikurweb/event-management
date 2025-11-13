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
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider'); // google, facebook, github
            $table->string('provider_id'); // Provider's user ID
            $table->string('email')->nullable();
            $table->string('avatar')->nullable();
            $table->text('token')->nullable(); // OAuth token
            $table->text('refresh_token')->nullable(); // OAuth refresh token
            $table->timestamp('expires_at')->nullable();
            $table->json('provider_data')->nullable(); // Store additional provider data
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');

            // Unique constraint: one provider account per user
            $table->index('deleted_at');
            $table->unique(['user_id', 'provider']);
            // Index for quick lookups
            $table->index(['provider', 'provider_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
