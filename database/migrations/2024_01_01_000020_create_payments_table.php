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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('transaction_id', 255)->unique()->nullable();
            $table->enum('payment_gateway', ['stripe', 'paypal', 'razorpay', 'bkash', 'nagad', 'ssl_commerz']);
            $table->enum('payment_method', ['card', 'bank_transfer', 'mobile_banking', 'cash', 'other'])->nullable();

            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');

            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded'])->default('pending');

            // Gateway Response
            $table->json('gateway_response')->nullable();
            $table->text('failure_reason')->nullable();

            // Timestamps
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('transaction_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

