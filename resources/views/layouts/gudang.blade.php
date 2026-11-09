<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Modul Gudang') - Toko Retail ALFA</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col text-slate-800">
    <!-- Navbar Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-8">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center font-black text-white text-lg shadow-md shadow-blue-500/20">
                            A
                        </div>
                        <div>
                            <div class="font-bold text-sm tracking-wide text-slate-900 leading-tight">TOKO RETAIL ALFA</div>
                            <div class="text-[11px] font-semibold text-blue-600">Modul Master Gudang & Stok</div>
                        </div>
                    </div>

                    <nav class="hidden md:flex items-center gap-1">
                        <a href="{{ route('gudang.index') }}"
                            class="px-3.5 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-2 {{ request()->routeIs('gudang.index') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            <i class="fa-solid fa-boxes-stacked"></i>
                            <span>Master Data Barang</span>
                        </a>
                        <a href="{{ route('gudang.logs') }}"
                            class="px-3.5 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-2 {{ request()->routeIs('gudang.logs') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            <i class="fa-solid fa-clipboard-list"></i>
                            <span>Riwayat Log Stok</span>
                        </a>
                    </nav>
                </div>

                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-2.5 bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5">
                        <div class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">
                            UG
                        </div>
                        <div class="text-left">
                            <div class="text-xs font-semibold text-slate-900 leading-tight">{{ Auth::user()->name }}</div>
                            <div class="text-[10px] text-slate-500">Petugas Gudang</div>
                        </div>
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('Apakah Anda yakin ingin logout?')"
                            class="px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-xs font-semibold transition flex items-center gap-1.5" title="Logout">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span class="hidden sm:inline">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if (session('success'))
            <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2.5">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-sm flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2.5">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 text-base"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900">&times;</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-sm shadow-sm">
                <div class="font-bold mb-1 flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                    <span>Perhatian: Silakan periksa kembali input berikut:</span>
                </div>
                <ul class="list-disc list-inside text-xs space-y-0.5 text-rose-700 pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="bg-white border-t border-slate-200 py-4 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center text-xs text-slate-500">
            &copy; 2026 Toko Retail ALFA &bull; Modul Gudang & Inventaris Barang
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
