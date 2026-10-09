<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    /**
     * Atribut yang dapat diisi secara masal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'rental_id',
        'order_id',
        'snap_token',
        'gross_amount',
        'payment_type',
        'payment_status',
    ];

    /**
     * Konversi tipe data atribut (casting).
     *
     * @var array<string, string>
     */
    protected $casts = [
        'gross_amount' => 'decimal:2',
    ];

    /**
     * Relasi balik ke Rental (Pembayaran dimiliki oleh satu transaksi rental).
     */
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    /**
     * Helper untuk mengecek apakah pembayaran sukses (settlement).
     */
    public function isSettlement(): bool
    {
        return $this->payment_status === 'settlement';
    }
}