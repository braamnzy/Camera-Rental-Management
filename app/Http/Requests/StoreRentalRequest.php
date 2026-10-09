<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRentalRequest extends FormRequest
{
    /**
     * Menentukan apakah user memiliki wewenang untuk membuat request ini.
     * Hanya pengguna dengan role 'customer' yang diizinkan melakukan booking.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'customer';
    }

    /**
     * Aturan validasi untuk pemesanan/booking sewa kamera.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'camera_id'  => ['required', 'integer', 'exists:cameras,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'quantity'   => ['nullable', 'integer', 'min:1'],

            // Field pengambilan
            'pickup_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'pickup_time'   => ['required', 'date_format:H:i'],
            'pickup_method' => ['required', 'in:pickup,delivery'],
            'pickup_notes'  => ['nullable', 'string', 'max:500'],

            // ⬇️ FIELD DELIVERY BARU (kondisional)
            'delivery_location' => [
                'required_if:pickup_method,delivery',
                'nullable',
                'in:dalam_kota,luar_kota',
            ],
        ];
    }

    /**
     * Pesan kustom untuk kegagalan validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Kamera & Tanggal
            'camera_id.required'         => 'Unit kamera wajib dipilih.',
            'camera_id.exists'           => 'Kamera yang dipilih tidak ditemukan dalam katalog.',
            'start_date.required'        => 'Tanggal mulai sewa wajib diisi.',
            'start_date.date'            => 'Format tanggal mulai sewa tidak valid.',
            'start_date.after_or_equal'  => 'Tanggal mulai sewa tidak boleh sebelum hari ini.',
            'end_date.required'          => 'Tanggal selesai sewa wajib diisi.',
            'end_date.date'              => 'Format tanggal selesai sewa tidak valid.',
            'end_date.after_or_equal'    => 'Tanggal selesai sewa harus sama atau setelah tanggal mulai sewa.',
            'quantity.integer'           => 'Jumlah unit harus berupa angka bulat.',
            'quantity.min'               => 'Jumlah unit yang disewa minimal 1.',

            // Pickup
            'pickup_date.required'       => 'Tanggal pengambilan wajib diisi.',
            'pickup_date.date'           => 'Format tanggal pengambilan tidak valid.',
            'pickup_date.after_or_equal' => 'Tanggal pengambilan tidak boleh sebelum tanggal sewa mulai.',
            'pickup_time.required'       => 'Jam pengambilan wajib diisi.',
            'pickup_time.date_format'    => 'Format jam harus HH:MM, contoh: 10:00.',
            'pickup_method.required'     => 'Metode pengambilan wajib dipilih.',
            'pickup_method.in'           => 'Metode pengambilan tidak valid (harus "pickup" atau "delivery").',
            'pickup_notes.string'        => 'Catatan harus berupa teks.',
            'pickup_notes.max'           => 'Catatan maksimal 500 karakter.',

            // ⬇️ PESAN DELIVERY BARU
            'delivery_location.required_if' => 'Lokasi pengantaran wajib dipilih kalau memilih metode "Dikirim".',
            'delivery_location.in'          => 'Lokasi pengantaran tidak valid (harus "dalam_kota" atau "luar_kota").',
        ];
    }
}