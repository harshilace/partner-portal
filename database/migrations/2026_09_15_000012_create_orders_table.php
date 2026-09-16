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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('partner_id')->nullable()->constrained('partners')->restrictOnDelete();
            $table->foreignId('sub_partner_id')->nullable()->constrained('partners')->restrictOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->string('status', 50)->default('pending');
            $table->string('payment_status', 50)->default('unpaid');
            $table->timestamp('ordered_at');
            $table->timestamps();

            $table->check('total_amount >= 0');
            $table->index('status');
            $table->index('ordered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
