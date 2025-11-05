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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('certificate_number', 100)->unique();
            $table->string('template_path', 255)->nullable();
            $table->string('file_path', 255)->nullable();
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamp('downloaded_at')->nullable();
            $table->string('verification_code', 100)->unique();
            $table->timestamps();

            $table->index('event_id');
            $table->index('user_id');
            $table->index('verification_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};

