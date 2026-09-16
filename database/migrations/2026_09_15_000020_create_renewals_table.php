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
        Schema::create('renewals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions')->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('partner_id')->nullable()->constrained('partners')->restrictOnDelete();
            $table->foreignId('sub_partner_id')->nullable()->constrained('partners')->restrictOnDelete();
            $table->date('due_date');
            $table->string('status', 50)->default('pending');
            $table->timestamp('reminder_30d_sent_at')->nullable();
            $table->timestamp('reminder_15d_sent_at')->nullable();
            $table->timestamp('reminder_7d_sent_at')->nullable();
            $table->timestamp('reminder_1d_sent_at')->nullable();
            $table->foreignId('renewed_subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->timestamps();

            $table->index('due_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('renewals');
    }
};
