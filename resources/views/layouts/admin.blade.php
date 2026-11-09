<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Admin Dashboard Mobile') - Toko Retail ALFA</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        .mono {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen flex flex-col pb-20 md:pb-6">
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-2xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-purple-700 to-indigo-600 flex items-center justify-center font-black text-white text-lg shadow-md shadow-indigo-500/20">
                    A
                </div>
                <div>
                    <div class="font-bold text-sm text-slate-900 leading-tight flex items-center gap-2">
                        <span>ALFA ANALYTICS</span>
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-purple-100 text-purple-700 uppercase">Read-Only</span>
                    </div>
                    <div class="text-[10px] text-slate-500">Dashboard Eksekutif Mobile</div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <div class="text-right hidden sm:block">
                    <div class="text-xs font-semibold text-slate-800">{{ Auth::user()->name }}</div>
                    <div class="text-[10px] text-slate-400">Owner / Administrator</div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" onclick="return confirm('Keluar dari akun Admin?')" class="p-2 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 transition" title="Logout">
                        <i class="fa-solid fa-power-off text-xs"></i>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="flex-1 max-w-2xl w-full mx-auto px-4 py-4 space-y-4">
        @yield('content')
    </main>

    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 px-6 py-2 flex items-center justify-around z-40 shadow-lg shadow-slate-900/10">
        <a href="{{ route('admin.dashboard') }}"
            class="flex flex-col items-center gap-1 py-1 text-center transition {{ request()->routeIs('admin.dashboard') ? 'text-purple-600 font-bold' : 'text-slate-400 hover:text-slate-600' }}">
            <i class="fa-solid fa-chart-pie text-base"></i>
            <span class="text-[10px]">Ringkasan</span>
        </a>

        <a href="{{ route('admin.low-stock') }}"
            class="flex flex-col items-center gap-1 py-1 text-center transition relative {{ request()->routeIs('admin.low-stock') ? 'text-amber-600 font-bold' : 'text-slate-400 hover:text-slate-600' }}">
            <i class="fa-solid fa-triangle-exclamation text-base"></i>
            <span class="text-[10px]">Stok Kritis</span>
        </a>

        <a href="{{ route('admin.dashboard', ['filter' => 'month']) }}"
            class="flex flex-col items-center gap-1 py-1 text-center text-slate-400 hover:text-slate-600 transition">
            <i class="fa-solid fa-calendar-days text-base"></i>
            <span class="text-[10px]">Bulan Ini</span>
        </a>
    </nav>

    @stack('scripts')
</body>
</html>
