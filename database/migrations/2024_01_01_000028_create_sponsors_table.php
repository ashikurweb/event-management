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
        Schema::create('sponsors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('logo', 255)->nullable();
            $table->string('website', 255)->nullable();
            $table->text('description')->nullable();
            $table->enum('tier', ['platinum', 'gold', 'silver', 'bronze', 'partner']);
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
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
        Schema::dropIfExists('sponsors');
    }
};

