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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code', 50)->unique();
            $table->string('name');
            $table->string('email');
            $table->string('email_normalized');
            $table->string('mobile', 50);
            $table->string('mobile_normalized', 50);
            $table->foreignId('current_partner_id')->nullable()->constrained('partners')->restrictOnDelete();
            $table->foreignId('current_sub_partner_id')->nullable()->constrained('partners')->restrictOnDelete();
            $table->string('status', 50)->default('active');
            $table->timestamps();

            $table->unique(['email_normalized', 'mobile_normalized']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
