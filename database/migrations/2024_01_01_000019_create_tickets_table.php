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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 50)->unique();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('ticket_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Attendee Info (can be different from buyer)
            $table->string('attendee_first_name', 100)->nullable();
            $table->string('attendee_last_name', 100)->nullable();
            $table->string('attendee_email', 255)->nullable();
            $table->string('attendee_phone', 20)->nullable();

            // QR Code & Validation
            $table->string('qr_code', 255);
            $table->string('barcode', 100)->nullable();

            // Check-in
            $table->enum('status', ['active', 'used', 'cancelled', 'transferred'])->default('active');
            $table->boolean('checked_in')->default(false);
            $table->timestamp('checked_in_at')->nullable();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->onDelete('set null');

            // Transfer
            $table->foreignId('transferred_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('transferred_at')->nullable();

            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');

            $table->index('ticket_number');
            $table->index('deleted_at');
            $table->index('qr_code');
            $table->index('event_id');
            $table->index('user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};

