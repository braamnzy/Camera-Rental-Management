@extends('layouts.customer')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow-md border border-gray-100">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Masuk ke Akun</h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Atau <a href="/register" class="font-medium text-[#2563EB] hover:text-[#1D4ED8]">daftar jika belum memiliki akun</a>
            </p>
        </div>
        <form id="loginForm" class="mt-8 space-y-6">
            <div id="error-alert" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded text-sm mb-4"></div>
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="email-address" class="sr-only">Email address</label>
                    <input id="email-address" name="email" type="email" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-[#2563EB] focus:border-[#2563EB] focus:z-10 sm:text-sm" placeholder="Email">
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <input id="password" name="password" type="password" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-[#2563EB] focus:border-[#2563EB] focus:z-10 sm:text-sm" placeholder="Password">
                </div>
            </div>

            <div>
                <button type="submit" id="submit-btn" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-[#2563EB] hover:bg-[#1D4ED8] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2563EB] transition">
                    Masuk
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@stack('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const loginForm = document.getElementById('loginForm');
        const errorAlert = document.getElementById('error-alert');
        const submitBtn = document.getElementById('submit-btn');

        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Sembunyikan error dan ubah teks tombol
            errorAlert.classList.add('hidden');
            submitBtn.textContent = 'Memproses...';
            submitBtn.disabled = true;

            const email = document.getElementById('email-address').value;
            const password = document.getElementById('password').value;

            try {
                const response = await fetch('/api/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        email,
                        password
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    // Simpan token yang didapat dari backend
                    localStorage.setItem('token', data.token);

                    // Cek role, jika admin lempar ke dashboard, jika customer lempar ke katalog
                    if (data.user.role === 'admin') {
                        window.location.href = '/admin/dashboard';
                    } else {
                        window.location.href = '/catalog';
                    }
                } else {
                    // Tampilkan error (misal: kredensial salah)
                    errorAlert.textContent = data.message || 'Login gagal. Periksa email atau password Anda.';
                    errorAlert.classList.remove('hidden');
                    submitBtn.textContent = 'Masuk';
                    submitBtn.disabled = false;
                }
            } catch (error) {
                console.error('Error during login:', error);
                errorAlert.textContent = 'Terjadi kesalahan sistem.';
                errorAlert.classList.remove('hidden');
                submitBtn.textContent = 'Masuk';
                submitBtn.disabled = false;
            }
        });
    });
</script>