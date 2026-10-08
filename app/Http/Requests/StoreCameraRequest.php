<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCameraRequest extends FormRequest
{
    /**
     * Menentukan apakah user memiliki wewenang untuk membuat request ini.
     * Hanya pengguna dengan role 'admin' yang diizinkan.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    /**
     * Aturan validasi untuk data kamera dan unggah berkas gambar.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Pengecekan apakah request merupakan operasi UPDATE
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH') || $this->has('_method');

        return [
            'name'        => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'brand'       => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:100'],
            'daily_rate'  => [$isUpdate ? 'sometimes' : 'required', 'numeric', 'min:0'],
            'stock'       => [$isUpdate ? 'sometimes' : 'required', 'integer', 'min:0'],
            'image'       => [
                $isUpdate ? 'nullable' : 'required', 
                'image', 
                'mimes:jpg,jpeg,png,webp', 
                'max:2048' // Maksimal 2MB (2048 KB)
            ],
            'description' => ['nullable', 'string'],
            'status'      => ['nullable', Rule::in(['available', 'maintenance'])],
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
            'name.required'       => 'Nama kamera wajib diisi.',
            'brand.required'      => 'Merk/Brand kamera wajib diisi.',
            'daily_rate.required' => 'Harga sewa per hari wajib diisi.',
            'daily_rate.numeric'  => 'Harga sewa harus berupa angka.',
            'stock.required'      => 'Jumlah stok kamera wajib diisi.',
            'stock.integer'       => 'Jumlah stok harus berupa bilangan bulat.',
            'image.required'      => 'Foto display kamera wajib diunggah.',
            'image.image'         => 'Berkas yang diunggah harus berupa gambar.',
            'image.mimes'         => 'Format gambar harus berupa JPG, JPEG, PNG, atau WEBP.',
            'image.max'           => 'Ukuran gambar maksimal adalah 2MB.',
            'status.in'           => 'Status kamera harus bernilai available atau maintenance.',
        ];
    }
}