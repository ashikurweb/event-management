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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('company', 255)->nullable();
            $table->string('email', 255);
            $table->string('phone', 20)->nullable();
            $table->string('logo', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('website', 255)->nullable();
            $table->string('category', 100)->nullable();
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->boolean('is_verified')->default(false);
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
        Schema::dropIfExists('vendors');
    }
};

