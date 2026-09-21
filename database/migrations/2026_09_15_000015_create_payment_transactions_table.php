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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->restrictOnDelete();
            $table->string('gateway_name', 50)->nullable();
            $table->string('transaction_reference', 255)->nullable();
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->string('status', 50)->default('initiated');
            $table->json('gateway_response')->nullable();
            $table->timestamp('transacted_at');
            $table->timestamps();

            $table->index('transaction_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
