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
        Schema::create('speakers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name', 255);
            $table->string('email', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('bio')->nullable();
            $table->string('photo', 255)->nullable();
            $table->string('title', 255)->nullable();
            $table->string('company', 255)->nullable();
            $table->string('website', 255)->nullable();
            $table->json('social_links')->nullable();
            $table->json('specialties')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');

            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('speakers');
    }
};

