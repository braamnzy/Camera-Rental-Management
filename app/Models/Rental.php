<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Rental extends Model
{
    use HasFactory;

    /**
     * Atribut yang dapat diisi secara masal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'camera_id',
        'start_date',
        'end_date',
        'pickup_date',
        'pickup_time',
        'pickup_method',
        'pickup_notes',
        'delivery_location',   // ← TAMBAH
        'delivery_fee',        // ← TAMBAH
        'total_days',
        'quantity',
        'total_price',
        'status',
    ];

    /**
     * Konversi tipe data atribut (casting).
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date'     => 'date',
        'end_date'       => 'date',
        'total_days'     => 'integer',
        'quantity'       => 'integer',
        'pickup_date'    => 'date',
        'total_price'    => 'decimal:2',
        'delivery_fee'   => 'decimal:2',   // ← TAMBAH
    ];

    /**
     * Relasi balik ke User (Customer pemilik transaksi sewa).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi balik ke Camera (Unit kamera yang disewa).
     */
    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class);
    }

    /**
     * Relasi satu-ke-satu ke Payment (Satu rental memiliki satu transaksi pembayaran).
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Helper untuk mengecek apakah transaksi sewa sudah lunas.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Helper untuk mengecek apakah unit sedang dibawa oleh customer.
     */
    public function isPickedUp(): bool
    {
        return $this->status === 'picked_up';
    }

    /**
     * Helper untuk mengecek apakah rental pakai delivery.
     */
    public function isDelivery(): bool
    {
        return $this->pickup_method === 'delivery';
    }

    /**
     * Label lokasi pengantaran untuk tampilan.
     */
    public function getDeliveryLocationLabelAttribute(): string
    {
        return match ($this->delivery_location) {
            'dalam_kota' => 'Dalam Kota',
            'luar_kota'  => 'Luar Kota',
            default      => 'Ambil di Toko',
        };
    }
}