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

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->foreignId('camera_id')
                  ->constrained('cameras')
                  ->onDelete('cascade');

            // ── Periode Sewa ────────────────────────────────
            $table->date('start_date');
            $table->date('end_date');

            // ── Sistem Pengambilan Barang (BARU) ───────────
            $table->date('pickup_date')->nullable()
                  ->comment('Tanggal customer ambil unit');
            $table->time('pickup_time')->nullable()
                  ->comment('Jam pengambilan (HH:MM)');
            $table->enum('pickup_method', ['pickup', 'delivery'])
                  ->default('pickup')
                  ->comment('pickup = ambil di toko, delivery = dikirim');
            $table->text('pickup_notes')->nullable()
                  ->comment('Catatan tambahan dari customer');
            // ────────────────────────────────────────────────

            // ── Perhitungan Biaya ──────────────────────────
            $table->unsignedInteger('total_days');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('total_price', 12, 2);

            // ── Status ─────────────────────────────────────
            $table->enum('status', [
                'pending_payment',
                'paid',
                'picked_up',
                'returned',
                'cancelled',
                'expired'
            ])->default('pending_payment');

            $table->timestamps();

            // Indexing untuk cek bentrok jadwal & stok
            $table->index(
                ['camera_id', 'start_date', 'end_date', 'status'],
                'rentals_schedule_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};