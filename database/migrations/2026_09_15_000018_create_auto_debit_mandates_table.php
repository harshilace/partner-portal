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
        Schema::create('auto_debit_mandates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions')->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->string('mandate_reference', 255)->nullable();
            $table->string('status', 50)->default('active');
            $table->foreignId('stopped_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('stopped_at')->nullable();
            $table->text('stop_reason')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_debit_mandates');
    }
};
