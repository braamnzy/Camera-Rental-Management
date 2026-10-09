<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'FrameFlow')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <!-- Tailwind CSS (Menggunakan CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Midtrans Snap.js (Sandbox Mode) -->
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-white text-slate-800 flex flex-col min-h-screen">

    <!-- Navbar -->
    <nav class="bg-[#1E293B] text-white sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="/catalog" class="flex items-center gap-2">
                        <img src="{{ asset('images/logoW_40.png') }}"
                            alt="CamRent"
                            class="h-9 w-auto">
                        <span class="text-xl font-bold tracking-wider">FrameFlow</span>
                    </a>
                </div>

                <!-- Menu Navigasi -->
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="/catalog" class="{{ request()->is('catalog*') ? 'bg-[#0F172A]' : 'hover:bg-[#0F172A]' }} px-3 py-2 rounded-md text-sm font-medium transition-colors">Katalog Kamera</a>
                        <a href="/my-rentals" class="{{ request()->is('my-rentals*') ? 'bg-[#0F172A]' : 'hover:bg-[#0F172A]' }} px-3 py-2 rounded-md text-sm font-medium transition-colors">Riwayat Sewa</a>
                    </div>
                </div>

                <!-- Bagian Kanan (Auth / Akun) -->
                <div class="hidden md:block">
                    <div class="ml-4 flex items-center md:ml-6 gap-4" id="auth-section">
                        <!-- Konten di sini akan diisi oleh JavaScript berdasarkan status login -->
                        <span id="user-name" class="text-sm font-medium"></span>
                        <button id="logout-btn" class="hidden text-sm font-medium text-red-400 hover:text-red-300">Logout</button>

                        <div id="guest-links" class="flex items-center gap-4">
                            <a href="/login" class="text-sm font-medium hover:text-gray-300">Login</a>
                            <a href="/register" class="text-sm font-medium bg-blue-600 px-4 py-2 rounded hover:bg-blue-700 transition">
                                Daftar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Konten Utama Halaman -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-[#1E293B] text-white py-6 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-sm text-gray-400">© 2026 FrameFlow. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Global JavaScript untuk menangani Token & Logout -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const token = localStorage.getItem('token');
            const userNameDisplay = document.getElementById('user-name');
            const logoutBtn = document.getElementById('logout-btn');
            const guestLinks = document.getElementById('guest-links');

            if (token) {
                // Sembunyikan link login/register, tampilkan nama user & tombol logout
                guestLinks.classList.add('hidden');
                logoutBtn.classList.remove('hidden');

                // Ambil data user dari API (endpoint me)
                fetch('/api/auth/me', {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.user) {
                            userNameDisplay.textContent = `Halo, ${data.user.name}`;
                        } else {
                            // Jika token tidak valid, bersihkan localStorage
                            localStorage.removeItem('token');
                            window.location.reload();
                        }
                    })
                    .catch(err => console.error("Error fetching user data", err));
            }

            // Fungsi Logout
            if (logoutBtn) {
                logoutBtn.addEventListener('click', () => {
                    fetch('/api/auth/logout', {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    }).then(() => {
                        localStorage.removeItem('token');
                        window.location.href = '/catalog';
                    });
                });
            }
        });
    </script>

    <!-- Tempat untuk script spesifik halaman -->
    @stack('scripts')
</body>

</html>