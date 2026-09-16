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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('product_plan_id')->nullable()->constrained('product_plans')->restrictOnDelete();
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('total_price', 15, 2)->default(0.00);
            $table->timestamps();

            $table->check('unit_price >= 0');
            $table->check('total_price >= 0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
