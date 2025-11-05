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
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->enum('discount_type', ['percentage', 'fixed', 'free_ticket']);
            $table->decimal('discount_value', 10, 2);

            // Applicable Events
            $table->enum('applicable_to', ['all', 'specific_events', 'specific_categories']);

            // Limits
            $table->integer('max_uses')->nullable();
            $table->integer('max_uses_per_user')->default(1);
            $table->integer('current_uses')->default(0);
            $table->decimal('min_order_amount', 10, 2)->nullable();

            // Validity
            $table->dateTime('valid_from');
            $table->dateTime('valid_until');

            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('code');
            $table->index(['valid_from', 'valid_until']);
        });

        // Add foreign key constraint for orders.promo_code_id
        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('promo_code_id')->references('id')->on('promo_codes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['promo_code_id']);
        });

        Schema::dropIfExists('promo_codes');
    }
};

