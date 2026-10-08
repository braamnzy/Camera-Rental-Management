<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - CamRent</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 font-sans antialiased text-slate-800">
    <div class="min-h-screen flex">
    
        <aside class="w-64 bg-[#1E293B] text-white flex flex-col justify-between fixed top-0 bottom-0 left-0 z-30">
            <div>
                
                <div class="h-16 flex items-center px-6 text-xl font-bold border-b border-slate-700 tracking-wide text-white">
                    CamRent Admin
                </div>

                
                <nav class="mt-6 px-3 space-y-1">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="flex items-center px-4 py-3 text-sm font-medium rounded-md transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-[#0F172A] text-white border-l-4 border-[#2563EB]' : 'text-slate-300 hover:bg-[#0F172A] hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 00-1 1m-6 0h6"/></svg>
                        Dashboard
                    </a>

                    <a href="{{ route('admin.cameras.index') }}" 
                       class="flex items-center px-4 py-3 text-sm font-medium rounded-md transition-all {{ request()->routeIs('admin.cameras.*') ? 'bg-[#0F172A] text-white border-l-4 border-[#2563EB]' : 'text-slate-300 hover:bg-[#0F172A] hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h0.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Inventaris Kamera
                    </a>

                    <a href="{{ route('admin.rentals.index') }}" 
                       class="flex items-center px-4 py-3 text-sm font-medium rounded-md transition-all {{ request()->routeIs('admin.rentals.*') ? 'bg-[#0F172A] text-white border-l-4 border-[#2563EB]' : 'text-slate-300 hover:bg-[#0F172A] hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Kelola Transaksi
                    </a>

                    <a href="{{ route('admin.schedule') }}" 
                       class="flex items-center px-4 py-3 text-sm font-medium rounded-md transition-all {{ request()->routeIs('admin.schedule') ? 'bg-[#0F172A] text-white border-l-4 border-[#2563EB]' : 'text-slate-300 hover:bg-[#0F172A] hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Jadwal Sewa
                    </a>
                </nav>
            </div>

            <!-- Profile & Logout -->
            <div class="p-4 border-t border-slate-700 bg-[#0F172A]/50">
                <div class="text-xs text-slate-400">Admin Login:</div>
                <div class="text-sm font-semibold truncate text-white mb-2">{{ auth()->user()->name ?? 'Admin CamRent' }}</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2 text-xs font-semibold text-rose-400 hover:bg-rose-500/10 rounded-md transition flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Keluar / Logout
                    </button>
                </form>
            </div>
        </aside>

        
        <main class="flex-1 ml-64 p-8 bg-[#FFFFFF] min-h-screen">
            <!-- Header Top Bar -->
            <div class="mb-8 flex justify-between items-center border-b border-slate-200 pb-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">@yield('title')</h1>
                    <p class="text-sm text-slate-500 mt-1">@yield('subtitle', 'Panel Kontrol Manajemen Persewaan Kamera CamRent')</p>
                </div>
            </div>

           
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-md text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Dynamic Content -->
            @yield('content')
        </main>
    </div>
</body>
</html>