<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Toko Retail ALFA</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Brand Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-red-600 to-red-500 shadow-lg shadow-red-500/30 text-white text-3xl font-black mb-3 tracking-wider">
                A
            </div>
            <h1 class="text-2xl font-bold text-slate-800">TOKO RETAIL <span class="text-red-600">ALFA</span></h1>
            <p class="text-slate-500 text-sm mt-1">Sistem Manajemen POS, Gudang & Analytics</p>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-500"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 p-8">
            <h2 class="text-lg font-bold text-slate-800 mb-5 pb-3 border-b border-slate-100 flex items-center justify-between">
                <span>Masuk ke Akun</span>
                <span class="text-xs font-normal text-slate-400">Pilih role sesuai tugas</span>
            </h2>

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="username" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Username</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <i class="fa-regular fa-user"></i>
                        </span>
                        <input type="text" name="username" id="username" value="{{ old('username') }}" required autofocus
                            placeholder="Contoh: kasir / gudang / admin"
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm transition">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" name="password" id="password" required
                            placeholder="••••••••"
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm transition">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center text-sm text-slate-600 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500">
                        <span class="ml-2 text-xs">Ingat saya</span>
                    </label>
                </div>

                <button type="submit"
                    class="w-full py-3 px-4 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white font-semibold rounded-xl shadow-md shadow-red-500/25 transition flex items-center justify-center gap-2 text-sm mt-2">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    <span>Masuk ke Sistem</span>
                </button>
            </form>

            <!-- Quick Demo Helper Accounts -->
            <div class="mt-8 pt-6 border-t border-slate-100">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider text-center mb-3">Akun Default Demo (Klik untuk Isi Otomatis)</p>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" onclick="fillLogin('kasir', 'kasir123')"
                        class="p-2.5 rounded-xl border border-slate-200 hover:border-red-400 hover:bg-red-50 text-center transition group">
                        <div class="text-xs font-bold text-slate-700 group-hover:text-red-700">UKasir</div>
                        <div class="text-[10px] text-slate-400">Desktop POS</div>
                    </button>
                    <button type="button" onclick="fillLogin('gudang', 'gudang123')"
                        class="p-2.5 rounded-xl border border-slate-200 hover:border-blue-400 hover:bg-blue-50 text-center transition group">
                        <div class="text-xs font-bold text-slate-700 group-hover:text-blue-700">UGudang</div>
                        <div class="text-[10px] text-slate-400">Kelola Stok</div>
                    </button>
                    <button type="button" onclick="fillLogin('admin', 'admin123')"
                        class="p-2.5 rounded-xl border border-slate-200 hover:border-emerald-400 hover:bg-emerald-50 text-center transition group">
                        <div class="text-xs font-bold text-slate-700 group-hover:text-emerald-700">UAdmin</div>
                        <div class="text-[10px] text-slate-400">Mobile Reports</div>
                    </button>
                </div>
            </div>
        </div>

        <div class="text-center mt-6 text-xs text-slate-400">
            &copy; 2026 Toko Retail ALFA. POS & Inventory Management System.
        </div>
    </div>

    <script>
        function fillLogin(username, password) {
            document.getElementById('username').value = username;
            document.getElementById('password').value = password;
        }
    </script>
</body>
</html>
