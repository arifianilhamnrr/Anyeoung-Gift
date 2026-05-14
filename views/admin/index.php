<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Anyeong Gift Admin' ?></title>
    <link rel="icon" type="image/svg+xml" href="../assets/images/anyeong-logo.svg">
    <!-- <script src="https://cdn.tailwindcss.com"></script> -->
    <link href="../assets/css/main.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { gold: { 400: '#FBBF24', 500: '#F59E0B', 600: '#D97706' }, dark: { base: '#121212', surface: '#1E1E1E', hover: '#2C2C2C', border: '#333333' } },
                    keyframes: { 'fade-in-up': { '0%': { opacity: '0', transform: 'translateY(15px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } } },
                    animation: { 'fade-in-up': 'fade-in-up 0.4s ease-out' },
                    fontFamily: { sans: ['Segoe UI', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        ::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #121212;
        }

        ::-webkit-scrollbar-thumb {
            background: #333;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #F59E0B;
        }

        .nav-item {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 0.85rem;
            margin: 0 0.5rem;
            border-radius: 0.75rem;
            color: #9CA3AF;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .nav-item:hover {
            background: rgba(245, 158, 11, 0.08);
            color: #F59E0B;
        }

        .nav-item .nav-icon {
            width: 1.1rem;
            height: 1.1rem;
            flex-shrink: 0;
        }

        .nav-active {
            background: linear-gradient(90deg, rgba(245, 158, 11, 0.18), rgba(245, 158, 11, 0.04));
            color: #F59E0B !important;
            box-shadow: inset 3px 0 0 #F59E0B;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #444;
            border-radius: 10px;
        }

        .row-menu {
            position: fixed;
            min-width: 11rem;
            background: #1E1E1E;
            border: 1px solid #333333;
            border-radius: 0.75rem;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.5);
            z-index: 100;
            overflow: hidden;
        }

        .row-menu button {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            width: 100%;
            padding: 0.55rem 0.85rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: #D1D5DB;
            background: transparent;
            border: 0;
            text-align: left;
            cursor: pointer;
            transition: background 0.15s ease;
        }

        .row-menu button:hover {
            background: rgba(245, 158, 11, 0.1);
            color: #F59E0B;
        }

        .row-menu button.is-danger:hover {
            background: rgba(239, 68, 68, 0.12);
            color: #F87171;
        }

        .row-menu button .ic {
            width: 0.9rem;
            height: 0.9rem;
        }

        .modal-header {
            position: sticky;
            top: -1.5rem;
            z-index: 20;
            background: rgba(30, 30, 30, 0.96);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            margin: -1.5rem -1.5rem 1.5rem -1.5rem;
            padding: 1.25rem 1.5rem 1rem 1.5rem;
            border-bottom: 1px solid #333333;
        }

        @media (min-width: 768px) {
            .modal-header {
                top: -2rem;
                margin: -2rem -2rem 1.5rem -2rem;
                padding: 1.5rem 2rem 1rem 2rem;
            }
        }

        /* Settings display cards */
        .settings-display-card {
            background: #1E1E1E;
            border: 1px solid #333333;
            border-radius: 1rem;
            padding: 1.25rem 1.5rem;
        }

        .settings-display-card .label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #6B7280;
            margin-bottom: 0.35rem;
        }

        .settings-display-card .value {
            color: #E5E7EB;
            font-size: 0.925rem;
            font-weight: 500;
            word-break: break-all;
        }

        .settings-display-card .value.mono {
            font-family: monospace;
            color: #F59E0B;
        }

        .settings-display-card .value.muted {
            color: #6B7280;
            font-style: italic;
        }
    </style>
</head>

<body
    class="bg-dark-base text-gray-200 flex h-screen overflow-hidden antialiased selection:bg-gold-500 selection:text-gray-900">

    <!-- Modal loading admin: backdrop redup + 3-dot bouncing loader.
         Muncul saat aksi konfirmasi / perubahan (simpan, hapus, ubah status). -->
    <div id="adminLoaderModal"
        class="fixed inset-0 z-[2000] hidden items-center justify-center p-4 opacity-0 transition-opacity duration-300">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm"></div>
        <div id="adminLoaderModalContent"
            class="relative bg-dark-surface/95 backdrop-blur-2xl border border-dark-border shadow-2xl rounded-2xl w-full max-w-sm overflow-hidden transform scale-95 transition-transform duration-300">
            <div class="p-8 text-center flex flex-col items-center">
                <div class="dot-loader mb-5" role="status" aria-label="Memuat">
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                </div>
                <h3 id="adminLoaderTitle" class="text-lg font-bold text-white mb-1">Memproses...</h3>
                <p id="adminLoaderText" class="text-gray-400 text-sm">Mohon tunggu sebentar.</p>
            </div>
        </div>
    </div>

    <style>
        /* 3-dot bouncing loader - by Javierrocadev (Uiverse.io) */
        .dot-loader {
            display: flex;
            flex-direction: row;
            gap: 0.5rem;
            align-items: center;
            justify-content: center;
        }
        .dot-loader .dot {
            width: 1rem;
            height: 1rem;
            border-radius: 9999px;
            background-color: #1d4ed8; /* tailwind blue-700 */
            animation: dotBounce 1s infinite;
        }
        .dot-loader .dot:nth-child(1) { animation-delay: 0.7s; }
        .dot-loader .dot:nth-child(2) { animation-delay: 0.3s; }
        .dot-loader .dot:nth-child(3) { animation-delay: 0.7s; }
        @keyframes dotBounce {
            0%, 100% {
                transform: translateY(-25%);
                animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
            }
            50% {
                transform: none;
                animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
            }
        }
    </style>

    <div id="sidebar-overlay"
        class="fixed inset-0 bg-black/50 backdrop-blur-md z-40 hidden transition-all duration-300 md:hidden"
        onclick="toggleSidebar()"></div>

    <aside id="sidebar"
        class="w-64 bg-dark-surface/95 backdrop-blur border-r border-dark-border flex flex-col py-5 fixed inset-y-0 left-0 z-50 transform -translate-x-full md:relative md:translate-x-0 transition-transform duration-300 ease-in-out">
        <div class="px-5 mb-7">
            <div class="flex items-center gap-2.5">
                <div
                    class="w-9 h-9 rounded-xl bg-gold-500/15 border border-gold-500/30 text-gold-500 flex items-center justify-center font-bold text-lg">
                    A</div>
                <div class="leading-tight">
                    <div class="text-sm font-bold text-gray-100 tracking-wide">Anyeong Gift</div>
                    <div class="text-[10px] text-gray-500 uppercase tracking-[0.2em]">Admin Panel</div>
                </div>
            </div>
        </div>
        <nav class="flex flex-col gap-0.5">
            <a href="#" data-target="dashboard" class="nav-item nav-active">
                <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10h4v-6h6v6h4V10" />
                </svg>
                Dashboard
            </a>
            <a href="#" data-target="orders" class="nav-item">
                <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                Pesanan
            </a>
            <a href="#" data-target="products" class="nav-item">
                <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                Produk
            </a>
            <a href="#" data-target="payments" class="nav-item">
                <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 10h18M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z" />
                </svg>
                Pembayaran
            </a>
            <a href="#" data-target="settings" class="nav-item">
                <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M10.325 4.317a1.724 1.724 0 013.35 0 1.724 1.724 0 002.573 1.066 1.724 1.724 0 012.37 2.37 1.724 1.724 0 001.066 2.572 1.724 1.724 0 010 3.35 1.724 1.724 0 00-1.066 2.573 1.724 1.724 0 01-2.37 2.37 1.724 1.724 0 00-2.572 1.066 1.724 1.724 0 01-3.35 0 1.724 1.724 0 00-2.573-1.066 1.724 1.724 0 01-2.37-2.37 1.724 1.724 0 00-1.066-2.572 1.724 1.724 0 010-3.35 1.724 1.724 0 001.066-2.573 1.724 1.724 0 012.37-2.37 1.724 1.724 0 002.573-1.066z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
                Pengaturan
            </a>
        </nav>
        <div class="mt-auto px-3">
            <button onclick="handleLogout()"
                class="w-full flex items-center justify-center gap-2 bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500 hover:text-white hover:border-red-500 py-2.5 rounded-xl text-sm font-semibold transition duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Keluar
            </button>
        </div>
    </aside>

    <main class="flex-1 flex flex-col w-full relative overflow-hidden">
        <header
            class="sticky top-0 z-30 bg-dark-surface/90 backdrop-blur-xl border-b border-dark-border flex items-center justify-between px-4 md:px-8 h-[64px] shrink-0">
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()"
                    class="md:hidden text-gray-300 hover:text-gold-500 transition w-9 h-9 rounded-lg flex items-center justify-center hover:bg-gold-500/10 focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <h2 id="page-title" class="text-lg md:text-xl font-semibold text-gray-100 tracking-tight">Dashboard</h2>
            </div>
            <div class="flex items-center gap-3">
                <div
                    class="hidden sm:flex items-center gap-2.5 bg-dark-base/60 border border-dark-border rounded-full pl-1 pr-3 py-1">
                    <span
                        class="w-7 h-7 rounded-full bg-gold-500/15 text-gold-500 flex items-center justify-center text-xs font-bold border border-gold-500/30"><?= strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)) ?></span>
                    <span class="text-xs text-gray-300 font-medium"><?= $_SESSION['admin_name'] ?? 'Admin' ?></span>
                </div>
            </div>
        </header>
        <div class="flex-1 overflow-y-auto custom-scrollbar">
            <div class="p-4 md:p-8" id="app-content"></div>
        </div>
    </main>

    <!-- ===================== MODALS ===================== -->

    <!-- Product Modal -->
    <div id="productModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[1000] hidden justify-center items-center opacity-0 transition-opacity duration-300">
        <div
            class="bg-dark-surface w-full max-w-2xl max-h-[85vh] overflow-y-auto rounded-2xl p-6 md:p-8 border border-dark-border shadow-2xl m-4 transform scale-95 transition-transform duration-300 custom-scrollbar relative">
            <div class="modal-header flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-100 flex items-center gap-2"> Tambah Produk</h3>
                <button onclick="closeProductModal()"
                    class="text-gray-400 hover:text-white w-8 h-8 rounded-full hover:bg-red-500/20 flex justify-center items-center transition">&times;</button>
            </div>
            <form id="productForm" onsubmit="submitProductForm(event)">
                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-sm text-gray-400 font-medium mb-1.5">Nama Produk</label>
                        <input type="text" id="p_name"
                            class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition"
                            required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-400 font-medium mb-1.5">Kategori</label>
                            <input type="text" id="p_category"
                                class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition"
                                placeholder="Cth: Buket Uang" required>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 font-medium mb-1.5">Harga Dasar (Rp)</label>
                            <input type="number" id="p_price"
                                class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition disabled:opacity-50 disabled:bg-dark-hover disabled:cursor-not-allowed"
                                required>
                            <label class="flex items-center gap-2 mt-2 cursor-pointer w-max group">
                                <input type="checkbox" id="p_is_dynamic" onchange="toggleDynamicPrice()"
                                    class="w-4 h-4 accent-gold-500 rounded cursor-pointer">
                                <span class="text-xs text-gray-400 group-hover:text-gray-300 transition">Harga Dinamis
                                    (Atur di Opsi)</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 font-medium mb-1.5">Foto Produk</label>
                        <label
                            class="flex flex-col items-center justify-center w-full h-32 border-2 border-dark-border border-dashed rounded-xl cursor-pointer bg-dark-base hover:border-gold-500/50 hover:bg-dark-hover transition overflow-hidden relative">
                            <div id="uploadText" class="flex flex-col items-center justify-center pt-5 pb-6">
                                <p class="text-sm text-gray-400">Klik untuk upload foto</p>
                            </div>
                            <img id="imagePreview" class="hidden absolute inset-0 w-full h-full object-cover">
                            <input type="file" id="p_image" class="hidden" accept="image/*"
                                onchange="previewImage(this)">
                        </label>
                    </div>
                </div>
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-3">
                        <label class="block text-sm text-gray-400 font-medium">Opsi Kustomisasi (Varian)</label>
                        <button type="button" onclick="addOptionGroup()"
                            class="text-xs bg-gold-500/10 text-gold-500 border border-gold-500/30 px-3 py-1.5 rounded-md hover:bg-gold-500 hover:text-gray-900 transition">+
                            Tambah Grup Opsi</button>
                    </div>
                    <div id="optionsContainer" class="space-y-4"></div>
                </div>
                <button type="submit" id="btnSaveProduct"
                    class="w-full bg-gold-500 text-gray-900 font-bold py-3.5 rounded-xl hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(245,158,11,0.3)] transition">
                    Simpan Produk ke Database</button>
            </form>
        </div>
    </div>

    <?php include __DIR__ . '/partials/admin-modals.php'; ?>

    <?php include __DIR__ . '/partials/admin-scripts.php'; ?>
</body>

</html>

