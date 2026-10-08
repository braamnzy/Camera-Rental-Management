<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('camera_id')->constrained('cameras')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('total_days');
            $table->unsignedInteger('quantity')->default(1); // Mencegah race condition & melacak stok sewa
            $table->decimal('total_price', 12, 2);
            $table->enum('status', [
                'pending_payment',
                'paid',
                'picked_up',
                'returned',
                'cancelled',
                'expired'
            ])->default('pending_payment');
            $table->timestamps();

            // Indexing untuk mempercepat pengecekan jadwal & stok bentrok
            $table->index(['camera_id', 'start_date', 'end_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};