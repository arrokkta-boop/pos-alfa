<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'POS Kasir Desktop') - Toko Retail ALFA</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            user-select: none;
        }
        .mono {
            font-family: 'JetBrains Mono', monospace;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
        }
        .print-only {
            display: none;
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col antialiased overflow-hidden">
    <!-- Top Header / Cashier Status Bar -->
    <header class="bg-slate-950 border-b border-slate-800 px-4 py-2.5 flex items-center justify-between no-print z-20">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-red-600 flex items-center justify-center font-black text-white text-lg tracking-wider">
                A
            </div>
            <div>
                <div class="font-bold text-sm tracking-wide text-white flex items-center gap-2">
                    <span>ALFA POS TERMINAL</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">ONLINE</span>
                </div>
                <div class="text-[11px] text-slate-400">Desktop Fast Cashier Mode</div>
            </div>
        </div>

        <!-- Shortcuts Banner -->
        <div class="hidden md:flex items-center gap-2 text-xs">
            <span class="px-2 py-1 rounded bg-slate-800 border border-slate-700 text-slate-300 font-mono text-[11px]">
                <strong class="text-amber-400">[F2]</strong> Scan Barcode
            </span>
            <span class="px-2 py-1 rounded bg-slate-800 border border-slate-700 text-slate-300 font-mono text-[11px]">
                <strong class="text-emerald-400">[F4]</strong> Bayar
            </span>
            <span class="px-2 py-1 rounded bg-slate-800 border border-slate-700 text-slate-300 font-mono text-[11px]">
                <strong class="text-rose-400">[F8]</strong> Hapus Keranjang
            </span>
            <span class="px-2 py-1 rounded bg-slate-800 border border-slate-700 text-slate-300 font-mono text-[11px]">
                <strong class="text-sky-400">[F11]</strong> Fullscreen
            </span>
        </div>

        <!-- Right Cashier Info & Actions -->
        <div class="flex items-center gap-4">
            <!-- Clock -->
            <div class="text-right hidden sm:block">
                <div id="live-clock" class="text-xs font-mono font-bold text-slate-200">00:00:00</div>
                <div id="live-date" class="text-[10px] text-slate-400">-</div>
            </div>

            <!-- Cashier Profile -->
            <div class="flex items-center gap-2 bg-slate-900 border border-slate-800 rounded-lg px-3 py-1.5">
                <i class="fa-solid fa-user-circle text-red-500 text-base"></i>
                <div class="text-left">
                    <div class="text-xs font-semibold text-white leading-tight">{{ Auth::user()->name }}</div>
                    <div class="text-[10px] text-slate-400">Kasir ID #{{ Auth::id() }}</div>
                </div>
            </div>

            <!-- Fullscreen button -->
            <button onclick="toggleFullscreen()" title="Fullscreen Mode" class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 transition">
                <i class="fa-solid fa-expand text-xs"></i>
            </button>

            <!-- Logout -->
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" onclick="return confirm('Apakah Anda yakin ingin logout dari Kasir?')" class="p-2 rounded-lg bg-red-600/20 hover:bg-red-600/30 text-red-400 border border-red-500/30 transition text-xs font-medium flex items-center gap-1.5" title="Logout">
                    <i class="fa-solid fa-power-off"></i>
                    <span class="hidden sm:inline">Keluar</span>
                </button>
            </form>
        </div>
    </header>

    <!-- Main POS Interface Container -->
    <main class="flex-1 flex overflow-hidden relative">
        @yield('content')
    </main>

    <!-- Global Clock & Fullscreen Script -->
    <script>
        function updateClock() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', { hour12: false });
            const dateStr = now.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
            
            const clockEl = document.getElementById('live-clock');
            const dateEl = document.getElementById('live-date');
            if (clockEl) clockEl.innerText = timeStr;
            if (dateEl) dateEl.innerText = dateStr;
        }
        setInterval(updateClock, 1000);
        updateClock();

        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {});
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
