<?php

namespace Database\Seeders;

use App\Models\Camera;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Admin Default
        User::updateOrCreate(
            ['email' => 'admin@frameflow.com'],
            [
                'name'     => 'Admin FrameFlow',
                'password' => Hash::make('password123'),
                'phone'    => '081234567890',
                'role'     => 'admin',
            ]
        );

        // 2. Akun Customer Default
        User::updateOrCreate(
            ['email' => 'customer@frameflow.com'],
            [
                'name'     => 'Customer Utama',
                'password' => Hash::make('password123'),
                'phone'    => '089876543210',
                'role'     => 'customer',
            ]
        );
    }
}