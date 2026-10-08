@extends('layouts.customer')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow-md border border-gray-100">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Daftar Akun Baru</h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Sudah punya akun? <a href="/login" class="font-medium text-[#2563EB] hover:text-[#1D4ED8]">Masuk di sini</a>
            </p>
        </div>

        <form id="registerForm" class="mt-8 space-y-6">
            <!-- Alert untuk Pesan Error / Sukses -->
            <div id="alert-box" class="hidden px-4 py-3 rounded text-sm mb-4"></div>

            <div class="space-y-4">
                <!-- Nama Lengkap -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap (Sesuai KTP)</label>
                    <input id="name" name="name" type="text" required class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[#2563EB] focus:border-[#2563EB] sm:text-sm" placeholder="Contoh: Arifin Santoso">
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Alamat Email</label>
                    <input id="email" name="email" type="email" required class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[#2563EB] focus:border-[#2563EB] sm:text-sm" placeholder="email@contoh.com">
                </div>

                <!-- Nomor HP / WhatsApp -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Nomor WhatsApp / HP Aktif</label>
                    <input id="phone" name="phone" type="tel" required class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[#2563EB] focus:border-[#2563EB] sm:text-sm" placeholder="Contoh: 081234567890">
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Kata Sandi</label>
                    <input id="password" name="password" type="password" required class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[#2563EB] focus:border-[#2563EB] sm:text-sm" placeholder="Minimal 8 karakter">
                </div>

                <!-- Konfirmasi Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Kata Sandi</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[#2563EB] focus:border-[#2563EB] sm:text-sm" placeholder="Ulangi kata sandi">
                </div>
            </div>

            <div>
                <button type="submit" id="submit-btn" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-[#2563EB] hover:bg-[#1D4ED8] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2563EB] transition">
                    Daftar Sekarang
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@stack('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const registerForm = document.getElementById('registerForm');
        const alertBox = document.getElementById('alert-box');
        const submitBtn = document.getElementById('submit-btn');

        const showAlert = (message, isSuccess = false) => {
            alertBox.textContent = message;
            alertBox.className = `px-4 py-3 rounded text-sm mb-4 block ${isSuccess ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'}`;
        };

        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Sembunyikan alert dan ubah state tombol
            alertBox.classList.add('hidden');
            submitBtn.textContent = 'Memproses...';
            submitBtn.disabled = true;

            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            const password = document.getElementById('password').value;
            const password_confirmation = document.getElementById('password_confirmation').value;

            // Validasi Frontend Sederhana
            if (password !== password_confirmation) {
                showAlert('Kata sandi dan konfirmasi kata sandi tidak cocok.');
                submitBtn.textContent = 'Daftar Sekarang';
                submitBtn.disabled = false;
                return;
            }

            try {
                const response = await fetch('/api/auth/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json' // Memaksa Laravel membalikkan response JSON, bukan redirect halaman
                    },
                    body: JSON.stringify({
                        name,
                        email,
                        phone,
                        password,
                        password_confirmation
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    // Tampilkan sukses
                    showAlert('Pendaftaran berhasil! Mengalihkan ke halaman login...', true);

                    // Jika backend langsung mengirimkan token (Auto-Login)
                    if (data.token) {
                        localStorage.setItem('token', data.token);
                        setTimeout(() => {
                            window.location.href = '/catalog';
                        }, 1500);
                    } else {
                        // Jika tidak auto-login, lempar ke halaman login manual
                        setTimeout(() => {
                            window.location.href = '/login';
                        }, 1500);
                    }
                } else {
                    // Tangani error dari backend (misal: email sudah terpakai)
                    // Mengambil pesan error pertama dari validasi Laravel
                    let errorMessage = data.message || 'Pendaftaran gagal.';
                    if (data.errors) {
                        const firstErrorKey = Object.keys(data.errors)[0];
                        errorMessage = data.errors[firstErrorKey][0];
                    }

                    showAlert(errorMessage);
                    submitBtn.textContent = 'Daftar Sekarang';
                    submitBtn.disabled = false;
                }
            } catch (error) {
                console.error('Error during registration:', error);
                showAlert('Terjadi kesalahan pada sistem/jaringan.');
                submitBtn.textContent = 'Daftar Sekarang';
                submitBtn.disabled = false;
            }
        });
    });
</script>