<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained('rentals')->onDelete('cascade');
            $table->string('order_id')->unique(); // Order ID unik untuk Midtrans (misal: RENT-12345)
            $table->string('snap_token')->nullable();
            $table->decimal('gross_amount', 12, 2);
            $table->string('payment_type')->nullable(); // bank_transfer, qris, gopay, dll.
            $table->enum('payment_status', [
                'pending',
                'settlement',
                'deny',
                'expire',
                'cancel'
            ])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};