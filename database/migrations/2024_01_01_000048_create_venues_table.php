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
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->text('description')->nullable();
            $table->text('address');
            $table->string('city', 100);
            $table->string('state', 100)->nullable();
            $table->string('country', 100);
            $table->string('postal_code', 20)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('capacity')->nullable();
            $table->json('facilities')->nullable();
            $table->json('images')->nullable();
            $table->string('contact_name', 255)->nullable();
            $table->string('contact_email', 255)->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->string('website', 255)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');

            $table->index('slug');
            $table->index('deleted_at');
            $table->index('city');
            $table->index('country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venues');
    }
};

