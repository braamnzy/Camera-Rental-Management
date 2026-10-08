<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Camera extends Model
{
    use HasFactory;

    /**
     * Atribut yang dapat diisi secara masal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'brand',
        'daily_rate',
        'stock',
        'image',
        'description',
        'status',
    ];

    /**
     * Konversi tipe data atribut (casting).
     *
     * @var array<string, string>
     */
    protected $casts = [
        'daily_rate' => 'decimal:2',
        'stock'      => 'integer',
    ];

    /**
     * Appends atribut kustom yang otomatis disertakan saat dikonversi ke JSON/Array.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'image_url',
    ];

    /**
     * Accessor untuk mendapatkan URL publik lengkap dari gambar kamera.
     * Mengembalikan URL dari storage public atau null jika tidak ada gambar.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        return asset('storage/' . $this->image);
    }

    /**
     * Relasi ke transaksi penyewaan (Kamera dapat berada di banyak transaksi Rental).
     */
    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    /**
     * Scope untuk memfilter kamera yang berstatus tersedia ('available').
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
}