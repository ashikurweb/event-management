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
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->enum('type', ['free', 'paid', 'donation']);
            $table->decimal('price', 10, 2)->default(0.00);
            $table->string('currency', 3)->default('USD');

            // Availability
            $table->integer('quantity_total')->nullable();
            $table->integer('quantity_sold')->default(0);
            $table->integer('quantity_reserved')->default(0);
            $table->integer('min_per_order')->default(1);
            $table->integer('max_per_order')->default(10);

            // Sales Period
            $table->dateTime('sale_start')->nullable();
            $table->dateTime('sale_end')->nullable();

            // Settings
            $table->boolean('is_visible')->default(true);
            $table->boolean('requires_approval')->default(false);
            $table->boolean('absorb_fees')->default(false);

            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->index('event_id');
            $table->index(['sale_start', 'sale_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
    }
};

