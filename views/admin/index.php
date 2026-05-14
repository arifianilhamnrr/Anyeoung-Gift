<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Anyeong Gift Admin' ?></title>
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

    <!-- Order Detail Modal -->
    <div id="orderDetailModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[1000] hidden justify-center items-center opacity-0 transition-opacity duration-300">
        <div
            class="bg-dark-surface w-full max-w-3xl max-h-[85vh] overflow-y-auto rounded-2xl p-6 md:p-8 border border-dark-border shadow-2xl m-4 transform scale-95 transition-transform duration-300 custom-scrollbar">
            <div class="modal-header flex justify-between items-center gap-3">
                <div class="min-w-0">
                    <h3 class="text-xl font-bold text-gray-100 flex items-center gap-2">Detail <span
                            id="detail-order-id"
                            class="text-gold-500 bg-gold-500/10 px-2 py-1 rounded-md text-sm"></span></h3>
                    <p id="detail-customer-info" class="text-gray-400 text-sm mt-1"></p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button onclick="printInvoice()" id="btnPrintInvoice"
                        class="hidden sm:inline-flex items-center gap-2 bg-gold-500/10 text-gold-500 border border-gold-500/30 hover:bg-gold-500 hover:text-gray-900 px-3 py-2 rounded-lg text-xs font-bold transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6 9V4h12v5M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v7H6z" />
                        </svg>
                        Cetak Invoice
                    </button>
                    <button onclick="printInvoice()"
                        class="sm:hidden bg-gold-500/10 text-gold-500 border border-gold-500/30 hover:bg-gold-500 hover:text-gray-900 w-9 h-9 rounded-lg flex justify-center items-center transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6 9V4h12v5M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2h-2M6 14h12v7H6z" />
                        </svg>
                    </button>
                    <button onclick="closeOrderDetailModal()"
                        class="bg-dark-hover border border-dark-border text-gray-400 w-9 h-9 rounded-full hover:text-white hover:bg-red-500/20 transition flex justify-center items-center">&times;</button>
                </div>
            </div>
            <div id="order-detail-content" class="text-gray-300 space-y-4"></div>
        </div>
    </div>

    <!-- Product Detail Modal -->
    <div id="productDetailModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[1000] hidden justify-center items-center opacity-0 transition-opacity duration-300">
        <div
            class="bg-dark-surface w-full max-w-2xl max-h-[85vh] overflow-y-auto rounded-2xl p-6 md:p-8 border border-dark-border shadow-2xl m-4 transform scale-95 transition-transform duration-300 custom-scrollbar">
            <div class="modal-header flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-100 flex items-center gap-2"> Detail Produk</h3>
                <button onclick="closeProductDetailModal()"
                    class="text-gray-400 hover:text-white w-8 h-8 rounded-full hover:bg-red-500/20 transition">&times;</button>
            </div>
            <div id="product-detail-content" class="text-gray-300"></div>
        </div>
    </div>

    <!-- Payment Confirmation Modal -->
    <div id="paymentModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[1000] hidden justify-center items-center opacity-0 transition-opacity duration-300">
        <div
            class="bg-dark-surface w-full max-w-sm rounded-2xl p-6 md:p-8 border border-dark-border shadow-2xl m-4 transform scale-95 transition-transform duration-300 relative">
            <div class="modal-header flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-100 flex items-center gap-2"> Pembayaran</h3>
                <button onclick="closePaymentModal()"
                    class="text-gray-400 hover:text-white w-8 h-8 rounded-full hover:bg-red-500/20 transition">&times;</button>
            </div>
            <div class="mb-6 space-y-4">
                <div>
                    <p class="text-xs text-gray-500 mb-1 uppercase">Pelanggan</p>
                    <div id="pay-customer-name" class="font-bold text-gray-200 text-lg"></div>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1 uppercase">Total Tagihan</p>
                    <div id="pay-total-amount" class="font-bold text-gold-500 text-3xl"></div>
                </div>
            </div>
            <div class="mb-8">
                <p class="text-xs text-gray-500 mb-3 uppercase">Metode Pembayaran</p>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer group"><input type="radio" name="payment_method" value="qris"
                            class="peer sr-only" checked>
                        <div
                            class="text-center p-3 rounded-xl border border-dark-border bg-dark-base peer-checked:border-gold-500 peer-checked:bg-gold-500/10 peer-checked:text-gold-500 transition font-bold">
                             QRIS</div>
                    </label>
                    <label class="cursor-pointer group"><input type="radio" name="payment_method" value="cod"
                            class="peer sr-only">
                        <div
                            class="text-center p-3 rounded-xl border border-dark-border bg-dark-base peer-checked:border-gold-500 peer-checked:bg-gold-500/10 peer-checked:text-gold-500 transition font-bold">
                             COD</div>
                    </label>
                </div>
            </div>
            <button onclick="submitPaymentConfirmation()" id="btnConfirmPayment"
                class="w-full bg-gold-500 text-gray-900 font-bold py-3.5 rounded-xl hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(245,158,11,0.3)] transition">
                Konfirmasi Lunas</button>
        </div>
    </div>

    <!-- Payment Method Modal -->
    <div id="paymentMethodModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[1000] hidden justify-center items-center opacity-0 transition-opacity duration-300">
        <div
            class="bg-dark-surface w-full max-w-md rounded-2xl p-6 md:p-8 border border-dark-border shadow-2xl m-4 transform scale-95 transition-transform duration-300 relative">
            <div class="modal-header flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-100"> Tambah Metode</h3>
                <button onclick="closePaymentMethodModal()"
                    class="text-gray-400 hover:text-white w-8 h-8 rounded-full hover:bg-red-500/20 transition">&times;</button>
            </div>
            <form id="paymentMethodForm" onsubmit="submitPaymentMethod(event)">
                <div class="space-y-4">
                    <div><label class="block text-sm text-gray-400 font-medium mb-1.5">Nama Metode (Cth: BCA)</label>
                        <input type="text" id="pm_name"
                            class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 font-medium mb-1.5">Tipe Pembayaran</label>
                        <select id="pm_type" onchange="togglePaymentQrisField()"
                            class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition"
                            required>
                            <option value="transfer">Transfer Bank</option>
                            <option value="qris">QRIS</option>
                            <option value="ewallet">E-Wallet (OVO/Dana/dll)</option>
                            <option value="onsite">Bayar di Tempat (COD)</option>
                        </select>
                    </div>
                    <div><label class="block text-sm text-gray-400 font-medium mb-1.5">Info / No. Rekening</label>
                        <input type="text" id="pm_info"
                            class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition"
                            placeholder="Cth: 1234567890 a/n Budi">
                    </div>
                    <div id="pm_qris_wrapper" class="hidden space-y-2">
                        <label class="block text-sm text-gray-400 font-medium mb-1.5">Gambar QRIS</label>
                        <label for="pm_image"
                            class="flex items-center justify-center gap-2 p-3 bg-dark-base border border-dashed border-dark-border text-gray-300 rounded-xl text-sm cursor-pointer hover:border-gold-500 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            <span id="pm_image_label">Pilih gambar QRIS (JPG/PNG)</span>
                        </label>
                        <input type="file" id="pm_image" accept="image/*" class="hidden"
                            onchange="onQrisImageSelected(this)">
                        <div id="pm_image_preview_wrapper" class="hidden">
                            <img id="pm_image_preview" alt="Pratinjau QRIS"
                                class="w-full max-h-48 object-contain rounded-lg border border-dark-border bg-dark-base p-2">
                        </div>
                    </div>
                </div>
                <button type="submit" id="btnSavePaymentMethod"
                    class="w-full mt-8 bg-gold-500 text-gray-900 font-bold py-3.5 rounded-xl hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(245,158,11,0.3)] transition">
                    Simpan Metode</button>
            </form>
        </div>
    </div>

    <!-- ===== SETTINGS MODALS ===== -->

    <!-- Settings: Edit Profil Toko Modal -->
    <div id="storeProfileModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[1000] hidden justify-center items-center opacity-0 transition-opacity duration-300">
        <div
            class="bg-dark-surface w-full max-w-lg rounded-2xl p-6 md:p-8 border border-dark-border shadow-2xl m-4 transform scale-95 transition-transform duration-300 relative">
            <div class="modal-header flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-100"> Edit Profil Toko</h3>
                <button onclick="toggleModal('storeProfileModal', false)"
                    class="text-gray-400 hover:text-white w-8 h-8 rounded-full hover:bg-red-500/20 transition">&times;</button>
            </div>
            <form id="storeProfileForm" onsubmit="submitStoreProfileForm(event)">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-400 font-medium mb-1.5">Nama Toko</label>
                        <input type="text" id="set_store_name"
                            class="w-full p-3.5 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 font-medium mb-1.5">WhatsApp Admin</label>
                        <input type="number" id="set_wa_admin"
                            class="w-full p-3.5 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 font-medium mb-1.5">Pesan Default Pembeli</label>
                        <textarea id="set_wa_template" rows="4"
                            class="w-full p-3.5 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition custom-scrollbar"></textarea>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="toggleModal('storeProfileModal', false)"
                        class="flex-1 py-3 rounded-xl border border-dark-border text-gray-400 hover:text-gray-200 hover:border-gray-500 transition font-semibold text-sm">Batal</button>
                    <button type="submit" id="btnSaveStoreProfile"
                        class="flex-1 bg-gold-500 text-gray-900 font-bold py-3 rounded-xl hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(245,158,11,0.3)] transition">
                        Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Settings: Edit Notifikasi Email Modal -->
    <div id="emailSettingsModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[1000] hidden justify-center items-center opacity-0 transition-opacity duration-300">
        <div
            class="bg-dark-surface w-full max-w-lg max-h-[85vh] overflow-y-auto rounded-2xl p-6 md:p-8 border border-dark-border shadow-2xl m-4 transform scale-95 transition-transform duration-300 custom-scrollbar relative">
            <div class="modal-header flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-100"> Pengaturan Email</h3>
                <button onclick="toggleModal('emailSettingsModal', false)"
                    class="text-gray-400 hover:text-white w-8 h-8 rounded-full hover:bg-red-500/20 transition">&times;</button>
            </div>
            <form id="emailSettingsForm" onsubmit="submitEmailSettingsForm(event)">
                <div class="space-y-4">
                    <p class="text-xs text-gray-500">Gunakan App Password Gmail (smtp.gmail.com) agar pengiriman email
                        berjalan lancar.</p>
                    <label
                        class="flex items-center justify-between bg-dark-base border border-dark-border rounded-xl px-4 py-3 text-sm cursor-pointer">
                        <span class="text-gray-300 font-medium">Aktifkan email notifikasi</span>
                        <input type="checkbox" id="set_email_enabled" class="w-4 h-4 accent-gold-500">
                    </label>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm text-gray-400 font-medium mb-1.5">SMTP Host</label>
                            <input type="text" id="set_email_host" placeholder="smtp.gmail.com"
                                class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 font-medium mb-1.5">SMTP Port</label>
                            <input type="number" id="set_email_port" placeholder="587"
                                class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 font-medium mb-1.5">SMTP Username</label>
                            <input type="email" id="set_email_user" placeholder="email@gmail.com"
                                class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 font-medium mb-1.5">SMTP Password</label>
                            <input type="password" id="set_email_pass" placeholder="App Password"
                                class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 font-medium mb-1.5">Nama Pengirim</label>
                            <input type="text" id="set_email_from_name" placeholder="Anyeong Gift"
                                class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 font-medium mb-1.5">Email Pengirim</label>
                            <input type="email" id="set_email_from_address" placeholder="email@gmail.com"
                                class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm text-gray-400 font-medium mb-1.5">Enkripsi</label>
                            <select id="set_email_encryption"
                                class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition">
                                <option value="tls">TLS (Recommended)</option>
                                <option value="ssl">SSL</option>
                            </select>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500">Kosongkan password jika tidak ingin mengubah kredensial email.</p>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="toggleModal('emailSettingsModal', false)"
                        class="flex-1 py-3 rounded-xl border border-dark-border text-gray-400 hover:text-gray-200 hover:border-gray-500 transition font-semibold text-sm">Batal</button>
                    <button type="submit" id="btnSaveEmailSettings"
                        class="flex-1 bg-gold-500 text-gray-900 font-bold py-3 rounded-xl hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(245,158,11,0.3)] transition">
                        Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Settings: Ubah Password Admin Modal -->
    <div id="adminPasswordModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[1000] hidden justify-center items-center opacity-0 transition-opacity duration-300">
        <div
            class="bg-dark-surface w-full max-w-md rounded-2xl p-6 md:p-8 border border-dark-border shadow-2xl m-4 transform scale-95 transition-transform duration-300 relative">
            <div class="modal-header flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-100"> Ubah Password</h3>
                <button onclick="toggleModal('adminPasswordModal', false)"
                    class="text-gray-400 hover:text-white w-8 h-8 rounded-full hover:bg-red-500/20 transition">&times;</button>
            </div>
            <form id="adminPasswordForm" onsubmit="submitAdminPasswordForm(event)">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-400 font-medium mb-1.5">Password Lama</label>
                        <input type="password" id="admin_current_password"
                            class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 font-medium mb-1.5">Password Baru</label>
                        <input type="password" id="admin_new_password"
                            class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition"
                            minlength="6" required>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 font-medium mb-1.5">Konfirmasi Password Baru</label>
                        <input type="password" id="admin_confirm_password"
                            class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition"
                            minlength="6" required>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="toggleModal('adminPasswordModal', false)"
                        class="flex-1 py-3 rounded-xl border border-dark-border text-gray-400 hover:text-gray-200 hover:border-gray-500 transition font-semibold text-sm">Batal</button>
                    <button type="submit" id="btnAdminPassword"
                        class="flex-1 bg-gold-500 text-gray-900 font-bold py-3 rounded-xl hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(245,158,11,0.3)] transition">
                        Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const BASE_URL = '<?= BASE_URL ?>';

        const views = {
            dashboard: `<?php ob_start();
            include __DIR__ . '/partials/dashboard.php';
            echo str_replace('`', '\\`', ob_get_clean()); ?>`,
            orders: `<?php ob_start();
            include __DIR__ . '/partials/orders.php';
            echo str_replace('`', '\\`', ob_get_clean()); ?>`,
                payments: `<?php ob_start();
                include __DIR__ . '/partials/payments.php';
                echo str_replace('`', '\\`', ob_get_clean()); ?>`,
                    products: `
                <div class="flex flex-col gap-4 mb-6 animate-fade-in-up">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-100 mb-1">Manajemen Produk</h2>
                            <p class="text-gray-400 text-sm">Kelola daftar produk, harga, dan opsi kustomisasi toko Anda.</p>
                        </div>
                        <button onclick="openProductModal()" class="bg-gold-500 text-gray-900 px-5 py-2.5 rounded-lg font-bold hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(245,158,11,0.3)] transition flex items-center gap-2 whitespace-nowrap">
                            <span class="text-xl leading-none">+</span> Tambah Produk
                        </button>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
                        <label class="text-xs uppercase tracking-wider text-gray-500 sm:w-32 shrink-0">Filter Kategori</label>
                        <select id="productCategoryFilter" onchange="renderProductsTable()" class="w-full sm:max-w-xs p-2.5 bg-dark-base border border-dark-border text-gray-200 rounded-lg text-sm focus:border-gold-500 focus:ring-1 outline-none transition">
                            <option value="all">Semua Kategori</option>
                        </select>
                    </div>
                </div>
                <div class="bg-dark-surface rounded-xl border border-dark-border overflow-x-auto shadow-md animate-fade-in-up">
                    <table class="w-full text-left border-collapse min-w-[700px] whitespace-nowrap">
                        <thead>
                            <tr class="bg-black/20 text-gray-400 text-sm border-b-2 border-dark-border">
                                <th class="p-4 font-semibold">Produk</th>
                                <th class="p-4 font-semibold">Kategori</th>
                                <th class="p-4 font-semibold">Harga Dasar</th>
                                <th class="p-4 font-semibold">Status</th>
                                <th class="p-4 font-semibold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="products-table-body">
                            <tr><td colspan="5" class="text-center p-10 text-gray-500">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="products-pagination" class="mt-4"></div>
            `,

                        // ===== SETTINGS VIEW: Display-only cards, edit via modals =====
                        settings: `
                <div class="animate-fade-in-up max-w-3xl mx-auto space-y-6">
                    <div class="mb-2">
                        <h2 class="text-2xl font-bold text-gray-100 mb-1">Pengaturan Toko</h2>
                        <p class="text-gray-400 text-sm">Informasi toko, notifikasi email, dan keamanan akun admin.</p>
                    </div>

                    <!-- Card: Profil Toko -->
                    <div class="bg-dark-surface rounded-2xl border border-dark-border shadow-xl overflow-hidden">
                        <div class="flex items-center justify-between px-6 py-4 border-b border-dark-border">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gold-500/10 border border-gold-500/20 flex items-center justify-center text-gold-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M4 6l1 13h14l1-13M9 10v6m6-6v6M8 6l1-2h6l1 2" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-bold text-gray-100 text-sm">Profil Toko</div>
                                    <div class="text-xs text-gray-500">Nama, WhatsApp, dan pesan default</div>
                                </div>
                            </div>
                            <button onclick="openStoreProfileModal()" class="flex items-center gap-2 text-xs font-bold bg-gold-500/10 text-gold-500 border border-gold-500/30 hover:bg-gold-500 hover:text-gray-900 px-3 py-2 rounded-lg transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75a2.121 2.121 0 113 3L7 19.25l-4 1 1-4L16.5 3.75z"/></svg>
                                Ubah
                            </button>
                        </div>
                        <div class="p-6 grid sm:grid-cols-2 gap-4">
                            <div class="settings-display-card">
                                <div class="label">Nama Toko</div>
                                <div class="value" id="disp_store_name"></div>
                            </div>
                            <div class="settings-display-card">
                                <div class="label">WhatsApp Admin</div>
                                <div class="value mono" id="disp_wa_admin"></div>
                            </div>
                            <div class="settings-display-card">
                                <div class="label">Nama Admin</div>
                                <div class="value" id="disp_admin_name"></div>
                            </div>
                            <div class="settings-display-card">
                                <div class="label">Email Toko</div>
                                <div class="value" id="disp_admin_email"></div>
                            </div>
                            <div class="settings-display-card sm:col-span-2">
                                <div class="label">Alamat Toko</div>
                                <div class="value text-xs leading-relaxed whitespace-pre-line" id="disp_store_address"></div>
                            </div>
                            <div class="settings-display-card sm:col-span-2">
                                <div class="label">Pesan Default Pembeli</div>
                                <div class="value text-xs leading-relaxed whitespace-pre-line" id="disp_wa_template"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Notifikasi Email -->
                    <div class="bg-dark-surface rounded-2xl border border-dark-border shadow-xl overflow-hidden">
                        <div class="flex items-center justify-between px-6 py-4 border-b border-dark-border">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2zm0 0l8 6 8-6" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-bold text-gray-100 text-sm">Notifikasi Email</div>
                                    <div class="text-xs text-gray-500">Konfigurasi SMTP untuk email otomatis</div>
                                </div>
                            </div>
                            <button onclick="openEmailSettingsModal()" class="flex items-center gap-2 text-xs font-bold bg-gold-500/10 text-gold-500 border border-gold-500/30 hover:bg-gold-500 hover:text-gray-900 px-3 py-2 rounded-lg transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75a2.121 2.121 0 113 3L7 19.25l-4 1 1-4L16.5 3.75z"/></svg>
                                Ubah
                            </button>
                        </div>
                        <div class="p-6 grid sm:grid-cols-2 gap-4">
                            <div class="settings-display-card sm:col-span-2">
                                <div class="label">Status</div>
                                <div id="disp_email_enabled" class="mt-1">
                                    <span class="px-3 py-1 bg-gray-500/15 text-gray-400 border border-gray-500/30 rounded-full text-xs font-bold">Tidak Aktif</span>
                                </div>
                            </div>
                            <div class="settings-display-card">
                                <div class="label">SMTP Host</div>
                                <div class="value mono" id="disp_email_host"></div>
                            </div>
                            <div class="settings-display-card">
                                <div class="label">SMTP Port</div>
                                <div class="value mono" id="disp_email_port"></div>
                            </div>
                            <div class="settings-display-card">
                                <div class="label">Username</div>
                                <div class="value" id="disp_email_user"></div>
                            </div>
                            <div class="settings-display-card">
                                <div class="label">Enkripsi</div>
                                <div class="value" id="disp_email_encryption"></div>
                            </div>
                            <div class="settings-display-card">
                                <div class="label">Nama Pengirim</div>
                                <div class="value" id="disp_email_from_name"></div>
                            </div>
                            <div class="settings-display-card">
                                <div class="label">Email Pengirim</div>
                                <div class="value" id="disp_email_from_address"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Keamanan -->
                    <div class="bg-dark-surface rounded-2xl border border-dark-border shadow-xl overflow-hidden">
                        <div class="flex items-center justify-between px-6 py-4 border-b border-dark-border">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-red-500/10 border border-red-500/20 flex items-center justify-center text-red-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-7-6V9a7 7 0 0114 0v2m-9 4h8a2 2 0 012 2v3a2 2 0 01-2 2H6a2 2 0 01-2-2v-3a2 2 0 012-2z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-bold text-gray-100 text-sm">Keamanan Akun</div>
                                    <div class="text-xs text-gray-500">Ubah password login admin</div>
                                </div>
                            </div>
                            <button onclick="openAdminPasswordModal()" class="flex items-center gap-2 text-xs font-bold bg-red-500/10 text-red-400 border border-red-500/30 hover:bg-red-500 hover:text-white px-3 py-2 rounded-lg transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75a2.121 2.121 0 113 3L7 19.25l-4 1 1-4L16.5 3.75z"/></svg>
                                Ubah Password
                            </button>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center gap-4 text-sm text-gray-400">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m0 0v2m0-2h2m-2 0H10m9-7V9a7 7 0 10-14 0v1M5 12h14a1 1 0 011 1v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6a1 1 0 011-1z"/></svg>
                                Password disimpan secara aman menggunakan hashing. Gunakan minimal 6 karakter.
                            </div>
                        </div>
                    </div>

                    <!-- Info: Aturan Otomasi Pesanan -->
                    <div class="bg-dark-surface rounded-2xl border border-dark-border shadow-xl overflow-hidden">
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-dark-border">
                            <div class="w-9 h-9 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2z" />
                                </svg>
                            </div>
                            <div>
                                <div class="font-bold text-gray-100 text-sm">Aturan Otomasi Pesanan</div>
                                <div class="text-xs text-gray-500">Berjalan otomatis di server (via cron job)</div>
                            </div>
                        </div>
                        <div class="p-6 space-y-3">
                            <div class="flex items-start gap-3 text-sm">
                                <span class="mt-0.5 w-5 h-5 rounded-full bg-yellow-500/15 text-yellow-500 flex items-center justify-center text-xs shrink-0 font-bold"></span>
                                <div><span class="text-gray-200 font-semibold">Auto-cancel pembayaran</span> <span class="text-gray-400"> Pesanan non-COD yang tidak dibayar dalam <span class="text-yellow-400 font-bold">24 jam</span> akan otomatis dibatalkan.</span></div>
                            </div>
                            <div class="flex items-start gap-3 text-sm">
                                <span class="mt-0.5 w-5 h-5 rounded-full bg-green-500/15 text-green-500 flex items-center justify-center text-xs shrink-0 font-bold"></span>
                                <div><span class="text-gray-200 font-semibold">Auto-complete pesanan</span> <span class="text-gray-400"> Pesanan berstatus "Siap Diambil" yang tidak dikonfirmasi pembeli dalam <span class="text-green-400 font-bold">3 hari</span> akan otomatis diselesaikan.</span></div>
                            </div>
                            <div class="flex items-start gap-3 text-sm">
                                <span class="mt-0.5 w-5 h-5 rounded-full bg-blue-500/15 text-blue-400 flex items-center justify-center text-xs shrink-0 font-bold"></span>
                                <div><span class="text-gray-200 font-semibold">Pembatalan pesanan</span> <span class="text-gray-400"> Admin hanya dapat membatalkan pesanan yang belum dibayar (status <span class="text-blue-400 font-mono text-xs">pending</span> atau <span class="text-blue-400 font-mono text-xs">waiting_payment</span>).</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            `
        };

        // --- UTILITY ---
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-gold-500 text-gray-900' : 'bg-red-500 text-white';
            toast.className = `fixed top-5 right-5 z-[9999] flex items-center gap-3 px-5 py-3 rounded-xl shadow-[0_10px_30px_rgba(0,0,0,0.5)] transform transition-all duration-300 translate-x-full opacity-0 ${bgColor} font-bold text-sm`;
            toast.innerHTML = `<span class="text-lg">${type === 'success' ? '' : ''}</span> ${message}`;
            document.body.appendChild(toast);
            setTimeout(() => toast.classList.remove('translate-x-full', 'opacity-0'), 10);
            setTimeout(() => { toast.classList.add('translate-x-full', 'opacity-0'); setTimeout(() => toast.remove(), 300); }, 3000);
        }

        function toggleModal(modalId, show) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            const inner = modal.querySelector('.bg-dark-surface');
            if (show) {
                modal.classList.remove('hidden'); modal.classList.add('flex');
                setTimeout(() => { modal.classList.remove('opacity-0'); if (inner) inner.classList.remove('scale-95'); }, 10);
            } else {
                modal.classList.add('opacity-0'); if (inner) inner.classList.add('scale-95');
                setTimeout(() => { modal.classList.add('hidden'); modal.classList.remove('flex'); }, 300);
            }
        }

        // --- LOADER ADMIN ---
        // Modal loading dengan 3-dot bouncing yang muncul saat aksi konfirmasi
        // / perubahan (simpan, hapus, ubah status, dll). Backdrop redup supaya
        // loader-nya terlihat dan user tidak bisa klik tombol lain.
        function showAdminLoader(title, message) {
            const modal = document.getElementById('adminLoaderModal');
            if (!modal) return;
            const content = document.getElementById('adminLoaderModalContent');
            const titleEl = document.getElementById('adminLoaderTitle');
            const textEl = document.getElementById('adminLoaderText');
            if (titleEl) titleEl.textContent = title || 'Memproses...';
            if (textEl) textEl.textContent = message || 'Mohon tunggu sebentar.';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                if (content) {
                    content.classList.remove('scale-95');
                    content.classList.add('scale-100');
                }
            }, 10);
        }

        function hideAdminLoader() {
            const modal = document.getElementById('adminLoaderModal');
            if (!modal) return;
            const content = document.getElementById('adminLoaderModalContent');
            modal.classList.add('opacity-0');
            if (content) {
                content.classList.remove('scale-100');
                content.classList.add('scale-95');
            }
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        }

        const rowMenuIcons = {
            eye: '<path stroke-linecap="round" stroke-linejoin="round" d="M2.04 12.32a1.01 1.01 0 010-.64C3.42 7.51 7.36 4.5 12 4.5c4.64 0 8.58 3.01 9.96 7.18.07.2.07.43 0 .64C20.58 16.49 16.64 19.5 12 19.5c-4.64 0-8.58-3.01-9.96-7.18z"/><circle cx="12" cy="12" r="3"/>',
            edit: '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75a2.121 2.121 0 113 3L7 19.25l-4 1 1-4L16.5 3.75z"/>',
            pause: '<path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6M14 9v6M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            play: '<path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-5.197-3a1 1 0 00-1.555.832v6a1 1 0 001.555.832l5.197-3a1 1 0 000-1.664z"/><circle cx="12" cy="12" r="9"/>',
            trash: '<path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6M14 11v6M5 7l1 12a2 2 0 002 2h8a2 2 0 002-2l1-12M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2"/>',
            wallet: '<path stroke-linecap="round" stroke-linejoin="round" d="M3 7h14a2 2 0 012 2v9a2 2 0 01-2 2H3V7zm0 0V5a2 2 0 012-2h11a2 2 0 012 2v2M17 13h2"/>',
            kebab: '<circle cx="12" cy="5" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="12" cy="19" r="1.6"/>'
        };

        function renderRowMenu(rowId, items) {
            const payload = encodeURIComponent(JSON.stringify(items));
            return `
                <div class="relative inline-block text-left">
                    <button type="button" data-row-menu-toggle data-row-id="${rowId}" data-items="${payload}"
                        onclick="openRowMenu(event, '${rowId}')"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-400 hover:bg-dark-hover hover:text-gold-500 transition border border-transparent hover:border-dark-border">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">${rowMenuIcons.kebab}</svg>
                    </button>
                </div>`;
        }

        function openRowMenu(ev, rowId) {
            ev.stopPropagation();
            const trigger = ev.currentTarget;
            const already = document.querySelector(`.row-menu[data-row-id="${rowId}"]`);
            document.querySelectorAll('.row-menu').forEach(m => m.remove());
            if (already) return;
            const items = JSON.parse(decodeURIComponent(trigger.getAttribute('data-items')));
            const menu = document.createElement('div');
            menu.className = 'row-menu';
            menu.setAttribute('data-row-id', rowId);
            menu.innerHTML = items.map(it => {
                const iconSvg = it.icon && rowMenuIcons[it.icon]
                    ? `<svg class="ic" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">${rowMenuIcons[it.icon]}</svg>`
                    : '';
                const danger = it.danger ? ' is-danger' : '';
                const accent = it.accent ? ' style="color:#F59E0B"' : '';
                return `<button type="button" class="${danger}"${accent} onclick="document.querySelectorAll('.row-menu').forEach(m=>m.remove()); ${it.onclick}">${iconSvg}<span>${it.label}</span></button>`;
            }).join('');
            document.body.appendChild(menu);
            const rect = trigger.getBoundingClientRect();
            const menuRect = menu.getBoundingClientRect();
            let left = rect.right - menuRect.width;
            if (left < 8) left = 8;
            let top = rect.bottom + 4;
            if (top + menuRect.height > window.innerHeight - 8) top = rect.top - menuRect.height - 4;
            menu.style.left = left + 'px';
            menu.style.top = top + 'px';
        }

        window.__paginationHandlers = window.__paginationHandlers || {};
        function renderPagination(containerId, currentPage, totalPages, totalItems, onPageChange) {
            const container = document.getElementById(containerId);
            if (!container) return;
            window.__paginationHandlers[containerId] = onPageChange;
            if (totalItems <= 0 || totalPages <= 1) {
                container.innerHTML = totalItems > 0 ? `<div class="text-xs text-gray-500 text-right">Menampilkan ${totalItems} item</div>` : '';
                return;
            }
            const btn = (page, label, disabled, active) => {
                const base = 'min-w-[2.25rem] h-9 px-3 rounded-lg text-xs font-bold transition border';
                const cls = disabled ? `${base} bg-dark-base border-dark-border text-gray-600 cursor-not-allowed`
                    : active ? `${base} bg-gold-500 border-gold-500 text-gray-900`
                        : `${base} bg-dark-base border-dark-border text-gray-300 hover:border-gold-500 hover:text-gold-500`;
                if (disabled || active) return `<button type="button" class="${cls}" ${disabled ? 'disabled' : ''}>${label}</button>`;
                return `<button type="button" class="${cls}" onclick="window.__paginationHandlers['${containerId}'](${page})">${label}</button>`;
            };
            const pages = [];
            const range = 2;
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || Math.abs(i - currentPage) <= range) pages.push(i);
                else if (pages[pages.length - 1] !== '') pages.push('');
            }
            const buttons = pages.map(p => p === ''
                ? `<span class="text-gray-600 px-1"></span>`
                : btn(p, String(p), false, p === currentPage)).join('');
            container.innerHTML = `
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                    <div class="text-xs text-gray-500">Total ${totalItems} item  Halaman ${currentPage} dari ${totalPages}</div>
                    <div class="flex items-center gap-1.5 flex-wrap">
                        ${btn(currentPage - 1, ' Prev', currentPage <= 1, false)}
                        ${buttons}
                        ${btn(currentPage + 1, 'Next ', currentPage >= totalPages, false)}
                    </div>
                </div>`;
        }

        async function handleLogout() {
            if (!confirm('Apakah Anda yakin ingin keluar?')) return;
            showAdminLoader('Keluar...', 'Sedang mengakhiri sesi.');
            try { const res = await fetch(`${BASE_URL}/api/logout`, { method: 'POST' }); const data = await res.json(); if (data.status === 'success') window.location.href = `${BASE_URL}/login`; else hideAdminLoader(); } catch (e) { hideAdminLoader(); showToast('Kesalahan jaringan', 'error'); }
        }

        const pageTitleMap = { dashboard: 'Dashboard', orders: 'Pesanan', products: 'Produk', payments: 'Pembayaran', settings: 'Pengaturan' };

        document.addEventListener('DOMContentLoaded', () => {
            loadView('dashboard');
            document.querySelectorAll('.nav-item').forEach(item => {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('nav-active'));
                    e.currentTarget.classList.add('nav-active');
                    const key = e.currentTarget.getAttribute('data-target');
                    document.getElementById('page-title').innerText = pageTitleMap[key] || key;
                    loadView(key);
                    if (window.innerWidth < 768) toggleSidebar();
                });
            });
            document.addEventListener('click', (e) => {
                if (e.target.closest('.row-menu') || e.target.closest('[data-row-menu-toggle]')) return;
                document.querySelectorAll('.row-menu').forEach(m => m.remove());
            });
        });

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
            document.getElementById('sidebar-overlay').classList.toggle('hidden');
        }

        function loadView(viewName) {
            const contentArea = document.getElementById('app-content');
            contentArea.classList.remove('animate-fade-in-up'); void contentArea.offsetWidth; contentArea.classList.add('animate-fade-in-up');
            if (views[viewName]) {
                contentArea.innerHTML = views[viewName];
                if (viewName === 'dashboard') loadDashboardData();
                if (viewName === 'products') loadProductsData();
                if (viewName === 'orders') loadOrdersData();
                if (viewName === 'payments') loadPaymentsData();
                if (viewName === 'settings') loadSettingsData();
            }
        }

        // --- DASHBOARD ---
        async function loadDashboardData() {
            if (document.getElementById('recent-orders-table')) document.getElementById('recent-orders-table').innerHTML = `<tr class="animate-pulse border-b border-dark-border"><td colspan="4" class="p-4"><div class="h-6 bg-dark-hover rounded w-full"></div></td></tr>`;
            try {
                const response = await fetch(`${BASE_URL}/api/dashboard/summary`);
                const result = await response.json();
                if (result.status === 'success') {
                    const data = result.data;
                    document.getElementById('stat-revenue').innerText = 'Rp ' + data.total_revenue.toLocaleString('id-ID');
                    document.getElementById('stat-active-orders').innerText = data.active_orders;
                    document.getElementById('stat-pending').innerText = data.pending_payments;
                    let tableHTML = '';
                    if (data.recent_orders.length === 0) tableHTML = `<tr><td colspan="4" class="text-center p-8 text-gray-500">Belum ada pesanan terbaru.</td></tr>`;
                    else data.recent_orders.forEach(order => {
                        const orderNumberText = 'ORD-' + String(order.id).padStart(5, '0');
                        const badge = ORDER_STATUS_BADGE[order.status] || { label: order.status, cls: 'border-gray-500/30 bg-gray-500/15 text-gray-400' };
                        tableHTML += `<tr class="border-b border-dark-border transition duration-200 hover:bg-dark-hover"><td class="p-4 font-bold text-gold-500">${orderNumberText}</td><td class="p-4">${order.customer_name || 'Anonim'}</td><td class="p-4 font-bold">Rp ${parseInt(order.total_price).toLocaleString('id-ID')}</td><td class="p-4"><span class="px-3 py-1.5 rounded-full text-xs font-bold border ${badge.cls}">${badge.label}</span></td></tr>`;
                    });
                    if (document.getElementById('recent-orders-table')) document.getElementById('recent-orders-table').innerHTML = tableHTML;
                }
            } catch (e) { console.error(e); }
        }

        // ==========================================
        // --- PRODUCTS ---
        // ==========================================
        let optionCounter = 0;
        let editProductId = null;

        function toggleDynamicPrice() {
            const isDynamic = document.getElementById('p_is_dynamic').checked;
            const priceInput = document.getElementById('p_price');
            if (isDynamic) { priceInput.disabled = true; priceInput.required = false; priceInput.value = ''; }
            else { priceInput.disabled = false; priceInput.required = true; }
        }

        function openProductModal() {
            editProductId = null;
            document.querySelector('#productModal h3').innerHTML = ' Tambah Produk';
            document.getElementById('btnSaveProduct').innerHTML = ' Simpan Produk Baru';
            toggleModal('productModal', true);
            document.getElementById('productForm').reset();
            document.getElementById('optionsContainer').innerHTML = '';
            optionCounter = 0;
            document.getElementById('imagePreview').classList.add('hidden');
            document.getElementById('uploadText').classList.remove('hidden');
            document.getElementById('p_is_dynamic').checked = false;
            toggleDynamicPrice();
        }

        async function openEditProductModal(id) {
            editProductId = id;
            document.querySelector('#productModal h3').innerHTML = ' Edit Produk';
            document.getElementById('btnSaveProduct').innerHTML = ' Perbarui Produk';
            toggleModal('productModal', true);
            document.getElementById('productForm').reset();
            document.getElementById('optionsContainer').innerHTML = '<div class="text-center text-gold-500 animate-pulse">Memuat data produk...</div>';
            document.getElementById('imagePreview').classList.add('hidden');
            document.getElementById('uploadText').classList.remove('hidden');
            try {
                const res = await fetch(`${BASE_URL}/api/products/details?id=${id}`);
                const result = await res.json();
                if (result.status === 'success') {
                    const p = result.data;
                    document.getElementById('p_name').value = p.name;
                    document.getElementById('p_category').value = p.category;
                    if (p.base_price == 0 || !p.base_price) { document.getElementById('p_price').value = ''; document.getElementById('p_is_dynamic').checked = true; }
                    else { document.getElementById('p_price').value = p.base_price; document.getElementById('p_is_dynamic').checked = false; }
                    toggleDynamicPrice();
                    if (p.image && p.image !== '') { document.getElementById('imagePreview').src = `${BASE_URL}/uploads/products/${p.image}`; document.getElementById('imagePreview').classList.remove('hidden'); document.getElementById('uploadText').classList.add('hidden'); }
                    document.getElementById('optionsContainer').innerHTML = '';
                    optionCounter = 0;
                    if (p.options && p.options.length > 0) {
                        p.options.forEach(opt => {
                            const optId = optionCounter++;
                            const groupHtml = `<div class="option-group bg-dark-base p-4 rounded-xl border border-dark-border relative overflow-hidden group" id="opt_group_${optId}"><div class="absolute top-0 left-0 w-1 h-full bg-gold-500"></div><div class="flex flex-col sm:flex-row gap-4 mb-4 items-end"><div class="flex-1 w-full"><label class="block text-xs text-gray-400 font-medium tracking-wide mb-1.5">Nama Opsi</label><input type="text" class="opt-name w-full p-2.5 bg-dark-surface border border-dark-border text-gray-200 rounded-lg text-sm focus:border-gold-500 focus:ring-1 outline-none transition" value="${opt.option_name}" required></div><button type="button" onclick="this.parentElement.parentElement.remove()" class="bg-red-500/10 text-red-500 border border-red-500/30 px-4 py-2.5 rounded-lg hover:bg-red-500 hover:text-white transition font-bold text-sm h-[42px]">Hapus</button></div><div class="values-container ml-2 pl-4 border-l-2 border-dark-border space-y-3"></div><button type="button" onclick="addOptionValue(${optId})" class="mt-4 ml-2 text-gray-400 border border-dashed border-dark-border px-3 py-1.5 rounded-md text-xs hover:text-gold-500 hover:border-gold-500 transition">+ Pilihan Harga</button></div>`;
                            document.getElementById('optionsContainer').insertAdjacentHTML('beforeend', groupHtml);
                            const valContainer = document.querySelector(`#opt_group_${optId} .values-container`);
                            opt.values.forEach(v => { valContainer.insertAdjacentHTML('beforeend', `<div class="option-value flex gap-3 items-center"><div class="flex-[2]"><input type="text" class="val-name w-full p-2 bg-dark-surface border border-dark-border text-gray-200 rounded-md text-sm outline-none" value="${v.value_name}" required></div><div class="flex-1 relative"><span class="absolute left-3 top-2 text-gray-500 text-sm">+Rp</span><input type="number" class="val-price w-full p-2 pl-9 bg-dark-surface border border-dark-border text-gray-200 rounded-md text-sm outline-none" value="${v.additional_price}" required></div><button type="button" onclick="this.parentElement.remove()" class="text-gray-500 hover:text-red-500 text-xl font-bold px-2">&times;</button></div>`); });
                        });
                    }
                }
            } catch (e) { showToast('Gagal memuat data edit', 'error'); }
        }

        function closeProductModal() { toggleModal('productModal', false); }
        function previewImage(input) { const preview = document.getElementById('imagePreview'); const uploadText = document.getElementById('uploadText'); if (input.files && input.files[0]) { const reader = new FileReader(); reader.onload = function (e) { preview.src = e.target.result; preview.classList.remove('hidden'); uploadText.classList.add('hidden'); }; reader.readAsDataURL(input.files[0]); } }
        function addOptionGroup() { const container = document.getElementById('optionsContainer'); const optId = optionCounter++; container.insertAdjacentHTML('beforeend', `<div class="option-group bg-dark-base p-4 rounded-xl border border-dark-border relative overflow-hidden group" id="opt_group_${optId}"><div class="absolute top-0 left-0 w-1 h-full bg-gold-500"></div><div class="flex flex-col sm:flex-row gap-4 mb-4 items-end"><div class="flex-1 w-full"><label class="block text-xs text-gray-400 font-medium tracking-wide mb-1.5">Nama Opsi</label><input type="text" class="opt-name w-full p-2.5 bg-dark-surface border border-dark-border text-gray-200 rounded-lg text-sm focus:border-gold-500 focus:ring-1 outline-none transition" required></div><button type="button" onclick="this.parentElement.parentElement.remove()" class="bg-red-500/10 text-red-500 border border-red-500/30 px-4 py-2.5 rounded-lg hover:bg-red-500 hover:text-white transition font-bold text-sm h-[42px]">Hapus</button></div><div class="values-container ml-2 pl-4 border-l-2 border-dark-border space-y-3"></div><button type="button" onclick="addOptionValue(${optId})" class="mt-4 ml-2 text-gray-400 border border-dashed border-dark-border px-3 py-1.5 rounded-md text-xs hover:text-gold-500 hover:border-gold-500 transition">+ Pilihan Harga</button></div>`); addOptionValue(optId); }
        function addOptionValue(optId) { const valContainer = document.querySelector(`#opt_group_${optId} .values-container`); if (!valContainer) return; valContainer.insertAdjacentHTML('beforeend', `<div class="option-value flex gap-3 items-center"><div class="flex-[2]"><input type="text" class="val-name w-full p-2 bg-dark-surface border border-dark-border text-gray-200 rounded-md text-sm outline-none" placeholder="Nama Pilihan" required></div><div class="flex-1 relative"><span class="absolute left-3 top-2 text-gray-500 text-sm">+Rp</span><input type="number" class="val-price w-full p-2 pl-9 bg-dark-surface border border-dark-border text-gray-200 rounded-md text-sm outline-none" value="0" required></div><button type="button" onclick="this.parentElement.remove()" class="text-gray-500 hover:text-red-500 text-xl font-bold px-2">&times;</button></div>`); }

        async function submitProductForm(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSaveProduct');
            btn.innerText = 'Menyimpan...'; btn.disabled = true;
            const isDynamic = document.getElementById('p_is_dynamic')?.checked || false;
            const payload = {
                name: document.getElementById('p_name').value,
                category: document.getElementById('p_category').value,
                base_price: isDynamic ? 0 : (parseInt(document.getElementById('p_price').value) || 0),
                options: []
            };
            document.querySelectorAll('.option-group').forEach(group => {
                const optName = group.querySelector('.opt-name').value;
                const values = [];
                group.querySelectorAll('.option-value').forEach(val => { values.push({ value_name: val.querySelector('.val-name').value, additional_price: parseInt(val.querySelector('.val-price').value) || 0 }); });
                if (values.length > 0) payload.options.push({ option_name: optName, values });
            });
            const formData = new FormData();
            formData.append('product_data', JSON.stringify(payload));
            const imageInput = document.getElementById('p_image');
            if (imageInput.files.length > 0) formData.append('image', imageInput.files[0]);
            if (editProductId) formData.append('product_id', editProductId);
            const apiUrl = editProductId ? `${BASE_URL}/api/products/update` : `${BASE_URL}/api/products`;
            showAdminLoader(editProductId ? 'Memperbarui produk...' : 'Menyimpan produk...', 'Mohon tunggu sebentar.');
            try {
                const res = await fetch(apiUrl, { method: 'POST', body: formData });
                const data = await res.json();
                if (data.status === 'success') { showToast(data.message || 'Produk tersimpan!', 'success'); closeProductModal(); loadProductsData(); }
                else showToast(data.message, 'error');
            } catch (err) { showToast('Error Jaringan', 'error'); }
            finally { hideAdminLoader(); btn.innerText = editProductId ? ' Perbarui Produk' : ' Simpan Produk Baru'; btn.disabled = false; }
        }

        let productsCache = [];
        let productsCurrentPage = 1;
        const PRODUCTS_PAGE_SIZE = 10;

        async function loadProductsData() {
            const tbody = document.getElementById('products-table-body');
            if (tbody) tbody.innerHTML = `<tr class="animate-pulse border-b border-dark-border"><td colspan="5" class="p-4"><div class="h-6 bg-dark-hover rounded w-full"></div></td></tr>`;
            try {
                const res = await fetch(`${BASE_URL}/api/products`);
                const result = await res.json();
                if (result.status === 'success') { productsCache = result.data || []; productsCurrentPage = 1; populateProductCategoryFilter(); renderProductsTable(); }
            } catch (e) { console.error(e); }
        }

        function populateProductCategoryFilter() {
            const select = document.getElementById('productCategoryFilter');
            if (!select) return;
            const prev = select.value || 'all';
            const categories = Array.from(new Set(productsCache.map(p => (p.category || '').trim()).filter(Boolean))).sort((a, b) => a.localeCompare(b));
            select.innerHTML = `<option value="all">Semua Kategori</option>` + categories.map(c => `<option value="${escapeHtml(c)}">${escapeHtml(c)}</option>`).join('');
            select.value = categories.includes(prev) ? prev : 'all';
        }

        function renderProductsTable() {
            const tbody = document.getElementById('products-table-body');
            if (!tbody) return;
            const filter = document.getElementById('productCategoryFilter')?.value || 'all';
            const filtered = filter === 'all' ? productsCache : productsCache.filter(p => (p.category || '') === filter);
            const totalPages = Math.max(1, Math.ceil(filtered.length / PRODUCTS_PAGE_SIZE));
            if (productsCurrentPage > totalPages) productsCurrentPage = totalPages;
            const start = (productsCurrentPage - 1) * PRODUCTS_PAGE_SIZE;
            const pageRows = filtered.slice(start, start + PRODUCTS_PAGE_SIZE);
            if (filtered.length === 0) { tbody.innerHTML = `<tr><td colspan="5" class="text-center p-10 text-gray-500">Belum ada produk yang cocok.</td></tr>`; }
            else {
                tbody.innerHTML = pageRows.map(p => {
                    const isActive = p.is_active == 1;
                    const menuItems = [
                        { label: 'Lihat detail', onclick: `openProductDetailModal(${p.id})`, icon: 'eye' },
                        { label: 'Edit produk', onclick: `openEditProductModal(${p.id})`, icon: 'edit' },
                        { label: isActive ? 'Nonaktifkan' : 'Aktifkan', onclick: `toggleProductStatus(${p.id}, ${isActive ? 0 : 1})`, icon: isActive ? 'pause' : 'play' },
                        { label: 'Hapus', onclick: `deleteProduct(${p.id})`, icon: 'trash', danger: true }
                    ];
                    return `<tr class="border-b border-dark-border transition duration-200 hover:bg-dark-hover">
                        <td class="p-4"><div class="font-bold text-gray-200">${escapeHtml(p.name)}</div><div class="text-xs text-gray-500 mt-1">${p.total_options || 0} Opsi</div></td>
                        <td class="p-4 capitalize text-gray-300">${escapeHtml(p.category || '-')}</td>
                        <td class="p-4 font-bold">${p.base_price ? 'Rp ' + parseInt(p.base_price).toLocaleString('id-ID') : 'Dinamis'}</td>
                        <td class="p-4">${isActive ? '<span class="px-3 py-1 bg-green-500/15 text-green-500 border border-green-500/30 rounded-full text-xs font-bold">Aktif</span>' : '<span class="px-3 py-1 bg-red-500/15 text-red-500 border border-red-500/30 rounded-full text-xs font-bold">Nonaktif</span>'}</td>
                        <td class="p-4 text-center">${renderRowMenu(`prod-${p.id}`, menuItems)}</td>
                    </tr>`;
                }).join('');
            }
            renderPagination('products-pagination', productsCurrentPage, totalPages, filtered.length, (page) => { productsCurrentPage = page; renderProductsTable(); });
        }

        async function toggleProductStatus(id, status) {
            if (!confirm('Yakin ubah status?')) return;
            showAdminLoader('Mengubah status...', 'Mohon tunggu sebentar.');
            try {
                const res = await fetch(`${BASE_URL}/api/products/toggle-status`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id, status }) });
                const data = await res.json();
                if (data.status === 'success') { showToast('Status diubah', 'success'); loadProductsData(); }
            } catch (e) { showToast('Kesalahan jaringan', 'error'); }
            finally { hideAdminLoader(); }
        }
        async function deleteProduct(id) {
            if (!confirm('Hapus permanen produk ini?')) return;
            showAdminLoader('Menghapus produk...', 'Mohon tunggu sebentar.');
            try {
                const res = await fetch(`${BASE_URL}/api/products/delete`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id }) });
                const data = await res.json();
                if (data.status === 'success') { showToast('Produk dihapus', 'success'); loadProductsData(); }
                else showToast(data.message, 'error');
            } catch (e) { showToast('Kesalahan jaringan', 'error'); }
            finally { hideAdminLoader(); }
        }

        function openProductDetailModal(id) { document.getElementById('product-detail-content').innerHTML = `<div class="animate-pulse h-40 bg-dark-hover rounded-xl w-full"></div>`; toggleModal('productDetailModal', true); fetchProductDetails(id); }
        function closeProductDetailModal() { toggleModal('productDetailModal', false); }

        async function fetchProductDetails(id) {
            try {
                const res = await fetch(`${BASE_URL}/api/products/details?id=${id}`);
                const result = await res.json();
                if (result.status === 'success') {
                    const p = result.data;
                    const imgUrl = (p.image && p.image !== '') ? `${BASE_URL}/uploads/products/${p.image}` : `https://via.placeholder.com/400x400/1E1E1E/555555?text=No+Image`;
                    let html = `<div class="flex flex-col md:flex-row gap-6 mb-8"><div class="w-full md:w-1/2 shrink-0"><img src="${imgUrl}" alt="${p.name}" class="w-full h-auto rounded-xl border-4 border-dark-border object-cover aspect-square shadow-2xl transition-transform duration-300 hover:scale-105" onerror="this.src='https://via.placeholder.com/400x400/1E1E1E/red?text=Image+Not+Found'"><p class="text-xs text-gray-600 mt-2 text-center">Ukuran disarankan: Persegi (1:1)</p></div><div class="w-full md:w-1/2 flex flex-col justify-between py-2"><div><div class="inline-block px-3 py-1 bg-gold-500/10 border border-gold-500/30 rounded-full text-xs text-gold-500 mb-3 capitalize tracking-wider">${p.category}</div><h4 class="text-3xl font-extrabold text-gray-100 mb-3 leading-tight">${p.name}</h4><p class="text-xs text-gray-500 mb-1">Harga Dasar:</p><div class="text-4xl font-black text-gold-500 mb-6">Rp ${parseInt(p.base_price).toLocaleString('id-ID')}</div></div><div class="bg-dark-base border border-dark-border p-4 rounded-xl text-sm text-gray-400"><div class="flex justify-between items-center mb-2"><span>Status:</span>${p.is_active ? '<span class="px-2 py-0.5 bg-green-500/10 text-green-500 border border-green-500/30 rounded text-xs font-bold">Aktif</span>' : '<span class="px-2 py-0.5 bg-red-500/10 text-red-500 border border-red-500/30 rounded text-xs font-bold">Nonaktif</span>'}</div><div class="flex justify-between items-center"><span>ID Produk:</span><span class="font-mono text-xs text-gray-600">PROD-${String(p.id).padStart(4, '0')}</span></div></div></div></div>`;
                    if (p.options && p.options.length > 0) {
                        html += `<h5 class="font-bold text-gray-200 mb-4 flex items-center gap-2 text-lg"><span class="text-xl"></span> Opsi Kustomisasi</h5><div class="grid grid-cols-1 md:grid-cols-2 gap-4">`;
                        p.options.forEach(opt => { let vals = opt.values.map(v => `<span class="inline-block bg-dark-base border border-dark-border px-3 py-1.5 rounded-lg text-sm text-gray-300 shadow-sm">${v.value_name} <b class="text-gold-500 ml-1">(+Rp ${parseInt(v.additional_price).toLocaleString('id-ID')})</b></span>`).join(' '); html += `<div class="bg-dark-hover border border-dark-border p-5 rounded-2xl relative overflow-hidden"><div class="absolute top-0 right-0 w-20 h-20 bg-gold-500/5 rounded-full blur-2xl"></div><div class="text-xs uppercase tracking-wider text-gray-500 font-bold mb-3 relative z-10">${opt.option_name}</div><div class="flex flex-wrap gap-2 relative z-10">${vals}</div></div>`; });
                        html += `</div>`;
                    } else { html += `<div class="text-sm text-gray-500 italic p-10 bg-dark-base rounded-xl border border-dark-border text-center">Produk ini tidak memiliki opsi kustomisasi tambahan.</div>`; }
                    document.getElementById('product-detail-content').innerHTML = html;
                } else { document.getElementById('product-detail-content').innerHTML = `<div class="text-red-500 text-center py-10">${result.message}</div>`; }
            } catch (e) { document.getElementById('product-detail-content').innerHTML = `<div class="text-red-500 text-center py-10">Gagal mengambil data produk.</div>`; }
        }

        // ==========================================
        // --- ORDERS ---
        // ==========================================

        /**
         * STATUS BADGE MAP (shared between dashboard & orders view)
         */
        const ORDER_STATUS_BADGE = {
            pending: { label: ' Belum Dibayar', cls: 'border-yellow-500/30 bg-yellow-500/15 text-yellow-500' },
            waiting_payment: { label: ' Belum Dibayar', cls: 'border-yellow-500/30 bg-yellow-500/15 text-yellow-500' },
            paid: { label: ' Sudah Dibayar', cls: 'border-blue-500/30 bg-blue-500/15 text-blue-500' },
            ready_pickup: { label: ' Siap Diambil', cls: 'border-purple-500/30 bg-purple-500/15 text-purple-400' },
            completed: { label: ' Selesai', cls: 'border-green-500/30 bg-green-500/15 text-green-500' },
            cancelled: { label: ' Dibatalkan', cls: 'border-red-500/30 bg-red-500/15 text-red-500' }
        };

        /**
         * Menentukan apakah pesanan BOLEH dibatalkan oleh admin.
         * Hanya boleh jika status masih pending / waiting_payment.
         */
        function canCancelOrder(status) {
            return status === 'pending' || status === 'waiting_payment';
        }

        let ordersCache = [];
        let ordersCurrentPage = 1;
        const ORDERS_PAGE_SIZE = 10;

        async function loadOrdersData() {
            const tbody = document.getElementById('orders-table-body');
            if (tbody) tbody.innerHTML = `<tr class="animate-pulse border-b border-dark-border"><td colspan="6" class="p-4"><div class="h-6 bg-dark-hover rounded w-full"></div></td></tr>`;
            try {
                const res = await fetch(`${BASE_URL}/api/orders`);
                const result = await res.json();
                if (result.status === 'success') { ordersCache = result.data || []; ordersCurrentPage = 1; renderOrdersTable(); }
            } catch (e) { console.error(e); }
        }

        function renderOrdersTable() {
            const tbody = document.getElementById('orders-table-body');
            if (!tbody) return;
            const filterEl = document.getElementById('orderStatusFilter');
            const filter = filterEl ? filterEl.value : 'all';
            const filtered = filter === 'all' ? ordersCache : ordersCache.filter(o => {
                if (filter === 'waiting_payment') return o.status === 'pending' || o.status === 'waiting_payment';
                return o.status === filter;
            });
            const totalPages = Math.max(1, Math.ceil(filtered.length / ORDERS_PAGE_SIZE));
            if (ordersCurrentPage > totalPages) ordersCurrentPage = totalPages;
            const start = (ordersCurrentPage - 1) * ORDERS_PAGE_SIZE;
            const pageRows = filtered.slice(start, start + ORDERS_PAGE_SIZE);

            if (filtered.length === 0) { tbody.innerHTML = `<tr><td colspan="6" class="text-center p-10 text-gray-500">Belum ada pesanan yang cocok.</td></tr>`; }
            else {
                tbody.innerHTML = pageRows.map(o => {
                    const badge = ORDER_STATUS_BADGE[o.status] || { label: o.status, cls: 'border-gray-500/30 bg-gray-500/15 text-gray-400' };
                    const badgeHTML = `<span class="px-3 py-1.5 rounded-full text-xs font-bold whitespace-nowrap inline-block border ${badge.cls}">${badge.label}</span>`;
                    const cName = o.customer_name ? o.customer_name.replace(/'/g, "\\'") : 'Anonim';
                    const isOnsite = (o.payment_method_type || '').toLowerCase() === 'onsite';

                    // Menu aksi per baris
                    const orderMenu = [
                        { label: 'Lihat detail', onclick: `openOrderDetailModal(${o.id}, '${cName}')`, icon: 'eye' }
                    ];
                    // Konfirmasi pembayaran  hanya saat belum dibayar
                    if (o.status === 'pending' || o.status === 'waiting_payment') {
                        if (isOnsite) {
                            orderMenu.push({ label: 'Konfirmasi pesanan', onclick: `confirmOnsiteOrder(${o.id})`, icon: 'wallet', accent: true });
                        } else {
                            orderMenu.push({ label: 'Konfirmasi pembayaran', onclick: `confirmOnlinePayment(${o.id})`, icon: 'wallet', accent: true });
                        }
                    }
                    // Siap diambil  hanya saat sudah dibayar
                    if (o.status === 'paid') {
                        orderMenu.push({ label: 'Tandai pesanan siap', onclick: `setOrderStatus(${o.id}, 'ready_pickup')`, icon: 'play', accent: true });
                    }
                    // NOTE: Tidak ada tombol "Tandai selesai" di admin  diselesaikan oleh user atau auto setelah 3 hari.
                    // Batalkan  HANYA saat belum dibayar
                    if (canCancelOrder(o.status)) {
                        orderMenu.push({ label: 'Batalkan pesanan', onclick: `setOrderStatus(${o.id}, 'cancelled')`, icon: 'trash', danger: true });
                    }

                    return `<tr class="border-b border-dark-border transition duration-200 hover:bg-dark-hover cursor-pointer" onclick="openOrderDetailModal(${o.id}, '${cName}')">
                        <td class="p-4 font-bold text-gold-500">ORD-${String(o.id).padStart(5, '0')}</td>
                        <td class="p-4 font-bold text-gray-200">${escapeHtml(cName)}</td>
                        <td class="p-4 font-bold">Rp ${parseInt(o.total_price).toLocaleString('id-ID')}</td>
                        <td class="p-4 text-gray-400 text-sm">${new Date(o.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })}</td>
                        <td class="p-4">${badgeHTML}</td>
                        <td class="p-4 text-center" onclick="event.stopPropagation()">${renderRowMenu(`ord-${o.id}`, orderMenu)}</td>
                    </tr>`;
                }).join('');
            }
            renderPagination('orders-pagination', ordersCurrentPage, totalPages, filtered.length, (page) => { ordersCurrentPage = page; renderOrdersTable(); });
        }

        async function confirmOnsiteOrder(orderId) {
            if (!confirm('Konfirmasi pesanan COD ini sebagai sudah dibayar?')) return;
            await setOrderStatus(orderId, 'paid');
        }

        async function setOrderStatus(orderId, status) {
            if (status === 'cancelled' && !confirm('Yakin membatalkan pesanan ini?')) return;
            const labels = { paid: 'mengkonfirmasi pesanan', ready_pickup: 'menandai pesanan siap diambil', completed: 'menandai pesanan selesai', cancelled: 'membatalkan pesanan' };
            showAdminLoader('Memperbarui status...', 'Sedang menyimpan perubahan ke server.');
            try {
                const res = await fetch(`${BASE_URL}/api/orders/update-status`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ order_id: orderId, status }) });
                const data = await res.json();
                if (data.status === 'success') { showToast('Berhasil ' + (labels[status] || 'memperbarui status'), 'success'); loadOrdersData(); }
                else showToast(data.message || 'Gagal memperbarui status', 'error');
            } catch (e) { showToast('Kesalahan jaringan', 'error'); }
            finally { hideAdminLoader(); }
        }

        function openOrderDetailModal(orderId, customerName) {
            document.getElementById('detail-order-id').innerText = '#ORD-' + String(orderId).padStart(5, '0');
            document.getElementById('detail-customer-info').innerText = 'Pemesan: ' + customerName;
            document.getElementById('order-detail-content').innerHTML = `<div class="animate-pulse h-20 bg-dark-hover rounded-xl w-full"></div>`;
            toggleModal('orderDetailModal', true);
            fetchOrderDetails(orderId);
        }
        function closeOrderDetailModal() { toggleModal('orderDetailModal', false); }

        function escapeHtml(text) {
            if (text === null || text === undefined) return '';
            return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function formatWhatsappLink(phone, message) {
            if (!phone) return null;
            const digits = String(phone).replace(/\D/g, '');
            if (!digits) return null;
            const normalized = digits.startsWith('0') ? '62' + digits.slice(1) : digits;
            const text = message ? '?text=' + encodeURIComponent(message) : '';
            return `https://wa.me/${normalized}${text}`;
        }

        const WA_TEMPLATES = {
            waiting_payment: 'Halo {nama}, terima kasih telah memesan di Anyeong Gift. Pesanan {kode} sudah kami terima. Mohon segera lakukan pembayaran agar kami dapat memproses pesanan Anda. Terima kasih!',
            pending: 'Halo {nama}, terima kasih telah memesan di Anyeong Gift. Pesanan {kode} sudah kami terima. Mohon segera lakukan pembayaran agar kami dapat memproses pesanan Anda. Terima kasih!',
            paid: 'Halo {nama}, pembayaran untuk pesanan {kode} telah kami terima. Pesanan Anda akan segera kami siapkan. Terima kasih!',
            ready_pickup: 'Halo {nama}, kabar baik! Pesanan {kode} sudah siap untuk diambil di toko kami. Silakan datang sesuai jam operasional. Sampai jumpa!',
            completed: 'Halo {nama}, terima kasih telah berbelanja di Anyeong Gift untuk pesanan {kode}. Semoga puas! Sampai jumpa di pesanan berikutnya!',
            cancelled: 'Halo {nama}, dengan berat hati pesanan {kode} telah dibatalkan. Jika ada pertanyaan, hubungi kami kembali. Terima kasih.'
        };

        function buildWhatsappMessageForStatus(status, recipientName, orderCode) {
            const tpl = WA_TEMPLATES[status] || `Halo {nama}, kami dari Anyeong Gift ingin menginformasikan terkait pesanan {kode}.`;
            return tpl.replace(/{nama}/g, recipientName).replace(/{kode}/g, orderCode);
        }

        function renderCustomerContactSection(order) {
            if (!order) return '';
            const address = order.address || {};
            const recipientName = address.recipient_name || order.customer_name || 'Pelanggan';
            const phone = address.whatsapp_number || '';
            const orderCode = '#ORD-' + String(order.id).padStart(5, '0');
            const waMessage = buildWhatsappMessageForStatus(order.status, recipientName, orderCode);
            const waLink = formatWhatsappLink(phone, waMessage);
            const phoneRow = phone ? `<div class="flex items-center justify-between gap-3 bg-dark-base/60 border border-dark-border rounded-lg px-3 py-2"><span class="text-gray-400">WhatsApp</span><span class="font-mono text-gray-100">${escapeHtml(phone)}</span></div>` : `<div class="bg-dark-base/60 border border-dashed border-dark-border rounded-lg px-3 py-2 text-gray-500 text-center">Nomor WhatsApp tidak tersedia</div>`;
            const emailRow = order.customer_email ? `<div class="flex items-center justify-between gap-3 bg-dark-base/60 border border-dark-border rounded-lg px-3 py-2"><span class="text-gray-400">Email</span><span class="text-gray-100 truncate">${escapeHtml(order.customer_email)}</span></div>` : '';
            const addressRow = address.address_text ? `<div class="bg-dark-base/60 border border-dark-border rounded-lg px-3 py-2"><div class="text-xs text-gray-500 mb-1">Alamat</div><div class="text-gray-200 whitespace-pre-line">${escapeHtml(address.address_text)}</div></div>` : '';
            const notesRow = address.notes ? `<div class="bg-dark-base/60 border border-dark-border rounded-lg px-3 py-2"><div class="text-xs text-gray-500 mb-1">Catatan</div><div class="text-gray-200 italic">${escapeHtml(address.notes)}</div></div>` : '';
            const waButton = waLink ? `<a href="${waLink}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 bg-[#25D366]/10 text-[#25D366] border border-[#25D366]/40 hover:bg-[#25D366] hover:text-white px-4 py-2 rounded-lg text-sm font-bold transition shrink-0"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>Chat WhatsApp</a>` : `<button type="button" disabled class="inline-flex items-center justify-center gap-2 bg-dark-base text-gray-500 border border-dark-border px-4 py-2 rounded-lg text-sm font-bold cursor-not-allowed shrink-0">WhatsApp tidak tersedia</button>`;
            return `<div class="bg-dark-base border border-dark-border rounded-xl p-4 space-y-3"><div class="flex items-start justify-between gap-3 flex-wrap"><div><div class="text-xs text-gray-500 uppercase tracking-wider">Kontak Pelanggan</div><div class="font-bold text-gray-100 text-lg mt-0.5">${escapeHtml(recipientName)}</div></div>${waButton}</div><div class="grid sm:grid-cols-2 gap-2 text-sm">${phoneRow}${emailRow}</div>${addressRow}${notesRow}</div>`;
        }

        function formatRupiah(amount) { const n = parseInt(amount); return 'Rp ' + (isNaN(n) ? '0' : n.toLocaleString('id-ID')); }

        function renderPaymentSection(payment) {
            if (!payment) return '';
            const type = (payment.method_type || '').toLowerCase();
            if (type === 'onsite') return '';
            const methodName = escapeHtml(payment.method_name || 'Pembayaran online');
            const proof = payment.proof_image || null;
            const paymentStatus = (payment.status || 'pending').toLowerCase();
            const statusMap = { pending: { label: 'Menunggu Verifikasi', cls: 'bg-yellow-500/15 text-yellow-400 border-yellow-500/30' }, confirmed: { label: 'Terverifikasi', cls: 'bg-green-500/15 text-green-400 border-green-500/30' }, rejected: { label: 'Ditolak', cls: 'bg-red-500/15 text-red-400 border-red-500/30' } };
            const statusInfo = statusMap[paymentStatus] || statusMap.pending;
            const paidAt = payment.paid_at ? new Date(payment.paid_at).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : null;
            const proofUrl = proof ? `${BASE_URL}/uploads/payments/${encodeURIComponent(proof)}` : null;
            const proofBlock = proofUrl ? `<div class="mt-3"><div class="text-xs text-gray-500 uppercase tracking-wider mb-2">Bukti Pembayaran</div><button type="button" onclick="openProofViewer('${proofUrl}')" class="block w-full rounded-xl overflow-hidden border border-dark-border bg-dark-base hover:border-gold-500/50 transition text-left"><img src="${proofUrl}" alt="Bukti pembayaran" loading="lazy" class="w-full max-h-72 object-contain bg-black/40"/><div class="px-3 py-2 text-xs text-gray-400 flex items-center justify-between"><span>Klik untuk lihat penuh</span><span class="text-gold-500 font-semibold">Buka</span></div></button></div>` : `<div class="mt-3 rounded-xl border border-dashed border-dark-border bg-dark-base/40 p-4 text-center text-sm text-gray-500">Pembeli belum mengunggah bukti pembayaran.</div>`;
            return `<div class="bg-dark-base/50 border border-dark-border rounded-xl p-4 space-y-3"><div class="flex items-start justify-between gap-3"><div><div class="text-xs text-gray-500 uppercase tracking-wider mb-1">Pembayaran</div><div class="font-semibold text-gray-100">${methodName}</div>${paidAt ? `<div class="text-xs text-gray-500 mt-1">Diunggah: ${paidAt}</div>` : ''}</div><span class="shrink-0 px-3 py-1 rounded-full text-[11px] font-bold border ${statusInfo.cls}">${statusInfo.label}</span></div>${proofBlock}</div>`;
        }

        function openProofViewer(url) {
            const overlay = document.createElement('div');
            overlay.className = 'fixed inset-0 z-[2000] bg-black/90 flex items-center justify-center p-4';
            overlay.innerHTML = `<button type="button" class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white text-2xl flex items-center justify-center">&times;</button><img src="${url}" alt="Bukti pembayaran" class="max-w-full max-h-full object-contain rounded-xl shadow-2xl"/>`;
            overlay.addEventListener('click', (e) => { if (e.target === overlay || e.target.tagName === 'BUTTON') overlay.remove(); });
            document.body.appendChild(overlay);
        }

        function renderItemOption(opt) {
            const hasCustom = opt.custom_value !== null && opt.custom_value !== undefined && String(opt.custom_value).trim() !== '';
            const displayValue = hasCustom ? opt.custom_value : (opt.value_name || '-');
            const extraPrice = parseInt(opt.additional_price) || 0;
            const priceTag = extraPrice > 0 ? ` <span class="text-gold-500 font-semibold">(+${formatRupiah(extraPrice)})</span>` : '';
            return `<li class="flex items-start gap-2"><span class="text-gold-500/70 mt-0.5"></span><span class="text-gray-400"><span class="text-gray-500">${escapeHtml(opt.option_name)}:</span> <span class="text-gray-200">${escapeHtml(displayValue)}</span>${priceTag}</span></li>`;
        }

        function renderOrderItem(item) {
            const optionsList = (item.options && item.options.length > 0) ? `<ul class="mt-3 space-y-1 text-xs border-l-2 border-gold-500/30 pl-3">${item.options.map(renderItemOption).join('')}</ul>` : '';
            const basePriceLine = (parseInt(item.price_at_time) || 0) > 0 ? `<div class="text-sm text-gray-500 mt-0.5">Harga Dasar: ${formatRupiah(item.price_at_time)}</div>` : '';
            return `<div class="bg-dark-base border border-dark-border p-4 rounded-xl hover:border-gold-500/30 transition duration-300"><div class="flex justify-between items-start gap-3"><div class="flex-1 min-w-0"><h4 class="font-bold text-gray-100 text-lg break-words">${escapeHtml(item.product_name)}</h4>${basePriceLine}</div><div class="text-right shrink-0"><div class="text-xs text-gray-500 mb-1">Subtotal</div><div class="font-bold text-gold-500 text-lg">${formatRupiah(item.subtotal)}</div></div></div>${optionsList}</div>`;
        }

        let currentInvoiceData = null;

        /**
         * Render tombol aksi di dalam modal detail pesanan.
         *
         * Aturan:
         * - Batalkan  HANYA saat pending / waiting_payment
         * - Tandai Selesai  TIDAK ADA di admin (user yang lakukan, atau auto 3 hari setelah ready_pickup)
         * - Konfirmasi pembayaran  saat pending/waiting_payment
         * - Tandai siap  saat paid
         */
        function renderOrderActionBar(order, payment) {
            if (!order) return '';
            const status = order.status;
            const paymentType = (payment && payment.method_type ? payment.method_type : '').toLowerCase();
            const isOnsite = paymentType === 'onsite';
            const buttons = [];

            const btnPrimary = (label, onclick) =>
                `<button type="button" onclick="${onclick}" class="inline-flex items-center gap-2 bg-gold-500 hover:bg-gold-400 text-gray-900 font-bold px-4 py-2 rounded-lg text-sm transition">${label}</button>`;
            const btnDanger = (label, onclick) =>
                `<button type="button" onclick="${onclick}" class="inline-flex items-center gap-2 bg-red-500/10 border border-red-500/30 text-red-400 hover:bg-red-500 hover:text-white px-4 py-2 rounded-lg text-sm font-bold transition">${label}</button>`;

            // Konfirmasi pembayaran  hanya saat belum dibayar
            if (status === 'pending' || status === 'waiting_payment') {
                if (isOnsite) {
                    buttons.push(btnPrimary(' Konfirmasi Pesanan', `confirmOrderFromModal(${order.id}, 'paid')`));
                } else {
                    buttons.push(btnPrimary(' Konfirmasi Pembayaran', `confirmOrderFromModal(${order.id}, 'paid')`));
                }
            }
            // Tandai siap  saat sudah dibayar
            if (status === 'paid') {
                buttons.push(btnPrimary(' Tandai Pesanan Siap', `confirmOrderFromModal(${order.id}, 'ready_pickup')`));
            }
            // Batalkan  HANYA saat belum dibayar
            if (canCancelOrder(status)) {
                buttons.push(btnDanger(' Batalkan Pesanan', `confirmOrderFromModal(${order.id}, 'cancelled')`));
            }
            // Info: saat ready_pickup atau completed  tampilkan keterangan ringan
            if (status === 'ready_pickup') {
                buttons.push(`<div class="flex items-center gap-2 text-xs text-purple-400 bg-purple-500/10 border border-purple-500/30 rounded-lg px-3 py-2"><svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/></svg>Menunggu konfirmasi pembeli  auto-selesai dalam 3 hari</div>`);
            }
            if (status === 'completed') {
                buttons.push(`<div class="flex items-center gap-2 text-xs text-green-400 bg-green-500/10 border border-green-500/30 rounded-lg px-3 py-2"><svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M12 2a10 10 0 100 20A10 10 0 0012 2z"/></svg>Pesanan telah selesai</div>`);
            }
            if (status === 'cancelled') {
                buttons.push(`<div class="flex items-center gap-2 text-xs text-red-400 bg-red-500/10 border border-red-500/30 rounded-lg px-3 py-2"><svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M12 2a10 10 0 100 20A10 10 0 0012 2z"/></svg>Pesanan dibatalkan</div>`);
            }

            if (buttons.length === 0) return '';
            return `<div class="bg-dark-base border border-dark-border rounded-xl p-4 flex flex-wrap gap-2">${buttons.join('')}</div>`;
        }

        async function confirmOrderFromModal(orderId, status) {
            const confirms = { paid: 'Konfirmasi pesanan ini sebagai sudah dibayar?', ready_pickup: 'Tandai pesanan ini siap untuk diambil?', completed: 'Tandai pesanan ini selesai?', cancelled: 'Yakin membatalkan pesanan ini?' };
            if (confirms[status] && !confirm(confirms[status])) return;
            showAdminLoader('Memperbarui status...', 'Sedang menyimpan perubahan ke server.');
            try {
                const res = await fetch(`${BASE_URL}/api/orders/update-status`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ order_id: orderId, status }) });
                const data = await res.json();
                if (data.status === 'success') { showToast('Status pesanan diperbarui', 'success'); fetchOrderDetails(orderId); if (typeof loadOrdersData === 'function') loadOrdersData(); }
                else showToast(data.message || 'Gagal memperbarui status', 'error');
            } catch (e) { showToast('Kesalahan jaringan', 'error'); }
            finally { hideAdminLoader(); }
        }

        async function confirmOnlinePayment(orderId) {
            if (!confirm('Konfirmasi pembayaran untuk pesanan ini?')) return;
            await setOrderStatus(orderId, 'paid');
        }

        async function fetchOrderDetails(orderId) {
            const container = document.getElementById('order-detail-content');
            currentInvoiceData = null;
            try {
                const res = await fetch(`${BASE_URL}/api/orders/details?id=${orderId}`);
                const result = await res.json();
                if (result.status !== 'success') { container.innerHTML = `<div class="text-red-500 text-center py-5 bg-red-500/10 rounded-xl border border-red-500/20">${escapeHtml(result.message)}</div>`; return; }
                const items = result.data || [];
                const grandTotal = items.reduce((sum, it) => sum + (parseInt(it.subtotal) || 0), 0);
                currentInvoiceData = { orderId, items, grandTotal, order: result.order || null, payment: result.payment || null };
                const itemsHtml = items.length === 0 ? `<div class="text-center py-6 text-gray-500 border border-dashed border-dark-border rounded-xl">Tidak ada detail item.</div>` : items.map(renderOrderItem).join('');
                const totalHtml = items.length > 0 ? `<div class="bg-dark-base border border-gold-500/30 rounded-xl p-4 flex justify-between items-center"><span class="text-gray-300 font-semibold">Total Pembayaran</span><span class="text-gold-500 font-bold text-xl">${formatRupiah(grandTotal)}</span></div>` : '';
                container.innerHTML = `<div class="space-y-4">${renderOrderActionBar(result.order, result.payment)}${renderCustomerContactSection(result.order)}${renderPaymentSection(result.payment)}<div class="space-y-3">${itemsHtml}</div>${totalHtml}</div>`;
            } catch (e) { container.innerHTML = `<div class="text-red-500 text-center py-5 bg-red-500/10 rounded-xl border border-red-500/20">Error. Pastikan rute '/api/orders/details' sudah terdaftar di Router.</div>`; }
        }

        function printInvoice() {
            if (!currentInvoiceData) { showToast('Data invoice belum siap', 'error'); return; }
            const { orderId, items, grandTotal, order, payment } = currentInvoiceData;
            const invoiceNo = 'ORD-' + String(orderId).padStart(5, '0');
            const printedAt = new Date().toLocaleString('id-ID', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
            const orderDate = order && order.created_at ? new Date(order.created_at).toLocaleString('id-ID', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : printedAt;
            const address = (order && order.address) || {};
            const customerName = address.recipient_name || (order && order.customer_name) || 'Anonim';
            const customerPhone = address.whatsapp_number || '-';
            const customerAddress = address.address_text || '';
            const customerEmail = (order && order.customer_email) ? order.customer_email : '';
            const customerNotes = address.notes || '';
            const paymentMethodLabel = payment && payment.method_name ? payment.method_name : ((payment && (payment.method_type || '').toLowerCase() === 'onsite') ? 'Bayar di Tempat' : '-');
            const paymentStatusLabel = (() => { if (!payment) return '-'; const s = (payment.status || '').toLowerCase(); if (s === 'confirmed') return 'Lunas'; if (s === 'rejected') return 'Ditolak'; return 'Menunggu Verifikasi'; })();
            const itemsRows = items.map(item => {
                const optionLines = (item.options || []).map(opt => { const hasCustom = opt.custom_value !== null && opt.custom_value !== undefined && String(opt.custom_value).trim() !== ''; const value = hasCustom ? opt.custom_value : (opt.value_name || '-'); const extra = parseInt(opt.additional_price) || 0; const extraStr = extra > 0 ? ` (+${formatRupiah(extra)})` : ''; return `<div class="opt"><span>${escapeHtml(opt.option_name)}:</span> ${escapeHtml(value)}${extraStr}</div>`; }).join('');
                return `<tr><td><div class="iname">${escapeHtml(item.product_name || '-')}</div>${optionLines ? `<div class="opts">${optionLines}</div>` : ''}</td><td class="qty">${item.quantity || 1}</td><td class="right">${formatRupiah(item.subtotal)}</td></tr>`;
            }).join('');
            const win = window.open('', '_blank', 'width=820,height=1000');
            if (!win) { showToast('Pop-up diblokir browser', 'error'); return; }
            const storeName = <?= json_encode($_SESSION['admin_name'] ?? 'Anyeong Gift') ?>;
            win.document.write(`<!doctype html><html lang="id"><head><meta charset="utf-8"/><title>Invoice ${invoiceNo}</title><style>*{box-sizing:border-box}body{font-family:'Segoe UI',Tahoma,sans-serif;color:#111;margin:0;padding:24px;background:#fff}.wrap{max-width:720px;margin:0 auto}.head{display:flex;justify-content:space-between;align-items:flex-start;border-bottom:2px solid #111;padding-bottom:12px;margin-bottom:18px}.brand{font-size:22px;font-weight:800;letter-spacing:1px}.brand small{display:block;font-size:11px;font-weight:500;color:#555;letter-spacing:2px;text-transform:uppercase}.meta{text-align:right;font-size:12px;color:#333}.meta .no{font-size:16px;font-weight:700;color:#000}.grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px}.card{border:1px solid #ddd;border-radius:6px;padding:12px 14px}.card h4{margin:0 0 6px 0;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:#666}.card p{margin:2px 0;font-size:13px}table{width:100%;border-collapse:collapse;margin-bottom:18px}thead th{background:#111;color:#fff;text-align:left;padding:8px 10px;font-size:12px}thead th.qty,thead th.right{text-align:center}thead th.right{text-align:right}tbody td{padding:10px;border-bottom:1px solid #eee;font-size:13px;vertical-align:top}tbody td.qty{text-align:center}tbody td.right{text-align:right;white-space:nowrap}.iname{font-weight:700}.opts{margin-top:4px;color:#555;font-size:12px}.opt{padding-left:8px}.total-row{display:flex;justify-content:flex-end}.total{min-width:280px;border-top:2px solid #111;padding-top:10px}.total .line{display:flex;justify-content:space-between;padding:4px 0;font-size:13px}.total .grand{font-size:16px;font-weight:800;border-top:1px dashed #aaa;margin-top:6px;padding-top:6px}.foot{margin-top:26px;text-align:center;font-size:11px;color:#666}@media print{body{padding:0}.wrap{max-width:none}}</style></head><body><div class="wrap"><div class="head"><div class="brand">${escapeHtml(storeName)}<small>Invoice Pesanan</small></div><div class="meta"><div class="no">${invoiceNo}</div><div>Tanggal Pesanan: ${escapeHtml(orderDate)}</div><div>Dicetak: ${escapeHtml(printedAt)}</div></div></div><div class="grid"><div class="card"><h4>Pemesan</h4><p><strong>${escapeHtml(customerName)}</strong></p><p>${escapeHtml(customerPhone)}</p>${customerEmail ? `<p>${escapeHtml(customerEmail)}</p>` : ''}${customerAddress ? `<p style="margin-top:6px;white-space:pre-line">${escapeHtml(customerAddress)}</p>` : ''}${customerNotes ? `<p style="margin-top:6px;font-style:italic;color:#555">Catatan: ${escapeHtml(customerNotes)}</p>` : ''}</div><div class="card"><h4>Pembayaran</h4><p><strong>${escapeHtml(paymentMethodLabel)}</strong></p><p>Status: ${escapeHtml(paymentStatusLabel)}</p>${payment && payment.paid_at ? `<p>Diunggah: ${escapeHtml(new Date(payment.paid_at).toLocaleString('id-ID'))}</p>` : ''}</div></div><table><thead><tr><th>Item</th><th class="qty">Qty</th><th class="right">Subtotal</th></tr></thead><tbody>${itemsRows || '<tr><td colspan="3" style="text-align:center;color:#666;padding:18px">Tidak ada item.</td></tr>'}</tbody></table><div class="total-row"><div class="total"><div class="line"><span>Subtotal</span><span>${formatRupiah(grandTotal)}</span></div><div class="line grand"><span>Total</span><span>${formatRupiah(grandTotal)}</span></div></div></div><div class="foot">Terima kasih telah berbelanja di Anyeong Gift.</div></div><script>window.addEventListener('load',()=>{setTimeout(()=>window.print(),250)});<\/script></body></html>`);
            win.document.close();
        }

        // ==========================================
        // --- PAYMENT METHODS ---
        // ==========================================
        let paymentMethodsList = [];
        let editPaymentId = null;

        async function loadPaymentsData() {
            if (document.getElementById('payments-table-body')) document.getElementById('payments-table-body').innerHTML = `<tr class="animate-pulse border-b border-dark-border"><td colspan="5" class="p-4"><div class="h-6 bg-dark-hover rounded w-full"></div></td></tr>`;
            try {
                const res = await fetch(`${BASE_URL}/api/payment-methods`);
                const result = await res.json();
                if (result.status === 'success') {
                    paymentMethodsList = result.data;
                    let html = '';
                    if (result.data.length === 0) { html = `<tr><td colspan="5" class="text-center p-10 text-gray-500">Belum ada metode pembayaran.</td></tr>`; }
                    else {
                        result.data.forEach(m => {
                            const isActive = m.is_active == 1;
                            const menuItems = [{ label: 'Edit metode', onclick: `openEditPaymentMethodModal(${m.id})`, icon: 'edit' }];
                            const infoCell = m.type === 'qris' && m.image ? `<div class="flex items-center gap-3"><img src="${BASE_URL}/uploads/payment_methods/${escapeHtml(m.image)}" alt="QRIS" class="w-12 h-12 rounded-md object-cover border border-dark-border"><span class="text-gray-400 text-xs">${escapeHtml(m.account_info || 'QRIS')}</span></div>` : `<span class="text-gold-500 font-mono text-sm">${escapeHtml(m.account_info || '-')}</span>`;
                            html += `<tr class="border-b border-dark-border transition duration-200 hover:bg-dark-hover"><td class="p-4 font-bold text-gray-200">${escapeHtml(m.name)}</td><td class="p-4 uppercase text-xs text-gray-400 tracking-wider">${escapeHtml(m.type)}</td><td class="p-4">${infoCell}</td><td class="p-4">${isActive ? '<span class="px-3 py-1 bg-green-500/15 text-green-500 border border-green-500/30 rounded-full text-xs font-bold">Aktif</span>' : '<span class="px-3 py-1 bg-red-500/15 text-red-500 border border-red-500/30 rounded-full text-xs font-bold">Nonaktif</span>'}</td><td class="p-4 text-center">${renderRowMenu(`pm-${m.id}`, menuItems)}</td></tr>`;
                        });
                    }
                    if (document.getElementById('payments-table-body')) document.getElementById('payments-table-body').innerHTML = html;
                }
            } catch (e) { console.error(e); }
        }

        function togglePaymentQrisField() { const type = document.getElementById('pm_type').value; const wrapper = document.getElementById('pm_qris_wrapper'); if (!wrapper) return; type === 'qris' ? wrapper.classList.remove('hidden') : wrapper.classList.add('hidden'); }
        function onQrisImageSelected(input) { const wrapper = document.getElementById('pm_image_preview_wrapper'); const img = document.getElementById('pm_image_preview'); const label = document.getElementById('pm_image_label'); if (!input.files || input.files.length === 0) { wrapper.classList.add('hidden'); label.textContent = 'Pilih gambar QRIS (JPG/PNG)'; return; } const file = input.files[0]; label.textContent = file.name; const reader = new FileReader(); reader.onload = (e) => { img.src = e.target.result; wrapper.classList.remove('hidden'); }; reader.readAsDataURL(file); }
        function resetQrisImagePreview(existingFilename) { const wrapper = document.getElementById('pm_image_preview_wrapper'); const img = document.getElementById('pm_image_preview'); const label = document.getElementById('pm_image_label'); const input = document.getElementById('pm_image'); if (input) input.value = ''; if (existingFilename) { img.src = `${BASE_URL}/uploads/payment_methods/${existingFilename}`; wrapper.classList.remove('hidden'); label.textContent = 'Ganti gambar QRIS (opsional)'; } else { wrapper.classList.add('hidden'); label.textContent = 'Pilih gambar QRIS (JPG/PNG)'; } }

        function openPaymentMethodModal() { editPaymentId = null; document.querySelector('#paymentMethodModal h3').innerHTML = ' Tambah Metode'; document.getElementById('btnSavePaymentMethod').innerHTML = ' Simpan Metode'; document.getElementById('paymentMethodForm').reset(); resetQrisImagePreview(null); togglePaymentQrisField(); toggleModal('paymentMethodModal', true); }
        function openEditPaymentMethodModal(id) { const method = paymentMethodsList.find(m => m.id === id); if (!method) return; editPaymentId = id; document.querySelector('#paymentMethodModal h3').innerHTML = ' Edit Metode'; document.getElementById('btnSavePaymentMethod').innerHTML = ' Perbarui Metode'; document.getElementById('pm_name').value = method.name; document.getElementById('pm_type').value = method.type; document.getElementById('pm_info').value = method.account_info || ''; resetQrisImagePreview(method.image || null); togglePaymentQrisField(); toggleModal('paymentMethodModal', true); }
        function closePaymentMethodModal() { toggleModal('paymentMethodModal', false); }

        async function submitPaymentMethod(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSavePaymentMethod');
            btn.innerText = 'Menyimpan...'; btn.disabled = true;
            const formData = new FormData();
            formData.append('name', document.getElementById('pm_name').value);
            formData.append('type', document.getElementById('pm_type').value);
            formData.append('account_info', document.getElementById('pm_info').value || '');
            if (editPaymentId) formData.append('id', editPaymentId);
            const imageInput = document.getElementById('pm_image');
            if (imageInput && imageInput.files && imageInput.files.length > 0) formData.append('image', imageInput.files[0]);
            const apiUrl = editPaymentId ? `${BASE_URL}/api/payment-methods/update` : `${BASE_URL}/api/payment-methods`;
            showAdminLoader(editPaymentId ? 'Memperbarui metode...' : 'Menyimpan metode...', 'Mohon tunggu sebentar.');
            try {
                const res = await fetch(apiUrl, { method: 'POST', body: formData });
                const data = await res.json();
                if (data.status === 'success') { showToast(data.message || 'Metode berhasil disimpan!', 'success'); closePaymentMethodModal(); loadPaymentsData(); }
                else showToast(data.message, 'error');
            } catch (e) { showToast('Error jaringan', 'error'); }
            finally { hideAdminLoader(); btn.innerText = editPaymentId ? ' Perbarui Metode' : ' Simpan Metode'; btn.disabled = false; }
        }

        // ==========================================
        // --- SETTINGS: Display + Modal Logic ---
        // ==========================================

        /** Cache data settings yang terakhir diambil dari server */
        let settingsCache = {};

        async function loadSettingsData() {
            try {
                const res = await fetch(`${BASE_URL}/api/settings`);
                const result = await res.json();
                if (result.status === 'success' && result.data) {
                    settingsCache = result.data;
                    renderSettingsDisplay(result.data);
                }
            } catch (e) { console.error('Gagal mengambil pengaturan', e); }
        }

        /** Tampilkan data ke kartu display (read-only) di halaman settings */
        function renderSettingsDisplay(data) {
            const setText = (id, val, fallback = '') => {
                const el = document.getElementById(id);
                if (!el) return;
                const v = val ? String(val).trim() : '';
                el.textContent = v || fallback;
                if (!v) el.classList.add('muted'); else el.classList.remove('muted');
            };
            setText('disp_store_name', data.store_name);
            setText('disp_wa_admin', data.whatsapp_admin);
            setText('disp_admin_name', data.admin_name);
            setText('disp_admin_email', data.admin_email);
            setText('disp_store_address', data.store_address_text);
            setText('disp_wa_template', data.whatsapp_message_template);
            setText('disp_email_host', data.email_smtp_host);
            setText('disp_email_port', data.email_smtp_port);
            setText('disp_email_user', data.email_smtp_username);
            setText('disp_email_encryption', data.email_smtp_encryption ? data.email_smtp_encryption.toUpperCase() : '');
            setText('disp_email_from_name', data.email_from_name);
            setText('disp_email_from_address', data.email_from_address);

            const emailEnabledEl = document.getElementById('disp_email_enabled');
            if (emailEnabledEl) {
                const enabled = Number(data.email_enabled || 0) === 1;
                emailEnabledEl.innerHTML = enabled
                    ? '<span class="px-3 py-1 bg-green-500/15 text-green-400 border border-green-500/30 rounded-full text-xs font-bold">Aktif</span>'
                    : '<span class="px-3 py-1 bg-gray-500/15 text-gray-400 border border-gray-500/30 rounded-full text-xs font-bold">Tidak Aktif</span>';
            }
        }

        /** Buka modal Edit Profil Toko dan isi form dari cache */
        function openStoreProfileModal() {
            const d = settingsCache;
            document.getElementById('set_store_name').value = d.store_name || '';
            document.getElementById('set_wa_admin').value = d.whatsapp_admin || '';
            document.getElementById('set_wa_template').value = d.whatsapp_message_template || '';
            toggleModal('storeProfileModal', true);
        }

        /** Submit form profil toko */
        async function submitStoreProfileForm(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSaveStoreProfile');
            btn.innerText = 'Menyimpan...'; btn.disabled = true;
            const payload = {
                store_name: document.getElementById('set_store_name').value,
                whatsapp_admin: document.getElementById('set_wa_admin').value,
                whatsapp_message_template: document.getElementById('set_wa_template').value,
                // Teruskan nilai email yang belum diubah dari cache
                email_enabled: settingsCache.email_enabled,
                email_smtp_host: settingsCache.email_smtp_host,
                email_smtp_port: settingsCache.email_smtp_port,
                email_smtp_username: settingsCache.email_smtp_username,
                email_from_name: settingsCache.email_from_name,
                email_from_address: settingsCache.email_from_address,
                email_smtp_encryption: settingsCache.email_smtp_encryption
            };
            showAdminLoader('Menyimpan profil toko...', 'Mohon tunggu sebentar.');
            try {
                const res = await fetch(`${BASE_URL}/api/settings`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                const data = await res.json();
                if (data.status === 'success') {
                    showToast('Profil toko berhasil disimpan!', 'success');
                    toggleModal('storeProfileModal', false);
                    await loadSettingsData();
                } else { showToast(data.message, 'error'); }
            } catch (err) { showToast('Kesalahan jaringan', 'error'); }
            finally { hideAdminLoader(); btn.innerText = ' Simpan'; btn.disabled = false; }
        }

        /** Buka modal Pengaturan Email dan isi form dari cache */
        function openEmailSettingsModal() {
            const d = settingsCache;
            document.getElementById('set_email_enabled').checked = Number(d.email_enabled || 0) === 1;
            document.getElementById('set_email_host').value = d.email_smtp_host || '';
            document.getElementById('set_email_port').value = d.email_smtp_port || '';
            document.getElementById('set_email_user').value = d.email_smtp_username || '';
            document.getElementById('set_email_pass').value = '';
            document.getElementById('set_email_from_name').value = d.email_from_name || '';
            document.getElementById('set_email_from_address').value = d.email_from_address || '';
            document.getElementById('set_email_encryption').value = d.email_smtp_encryption || 'tls';
            toggleModal('emailSettingsModal', true);
        }

        /** Submit form pengaturan email */
        async function submitEmailSettingsForm(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSaveEmailSettings');
            btn.innerText = 'Menyimpan...'; btn.disabled = true;
            const payload = {
                store_name: settingsCache.store_name,
                whatsapp_admin: settingsCache.whatsapp_admin,
                whatsapp_message_template: settingsCache.whatsapp_message_template,
                email_enabled: document.getElementById('set_email_enabled').checked,
                email_smtp_host: document.getElementById('set_email_host').value,
                email_smtp_port: document.getElementById('set_email_port').value,
                email_smtp_username: document.getElementById('set_email_user').value,
                email_smtp_password: document.getElementById('set_email_pass').value,
                email_from_name: document.getElementById('set_email_from_name').value,
                email_from_address: document.getElementById('set_email_from_address').value,
                email_smtp_encryption: document.getElementById('set_email_encryption').value
            };
            showAdminLoader('Menyimpan pengaturan email...', 'Mohon tunggu sebentar.');
            try {
                const res = await fetch(`${BASE_URL}/api/settings`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                const data = await res.json();
                if (data.status === 'success') {
                    showToast('Pengaturan email berhasil disimpan!', 'success');
                    toggleModal('emailSettingsModal', false);
                    document.getElementById('set_email_pass').value = '';
                    await loadSettingsData();
                } else { showToast(data.message, 'error'); }
            } catch (err) { showToast('Kesalahan jaringan', 'error'); }
            finally { hideAdminLoader(); btn.innerText = ' Simpan'; btn.disabled = false; }
        }

        /** Buka modal ubah password admin */
        function openAdminPasswordModal() {
            document.getElementById('adminPasswordForm').reset();
            toggleModal('adminPasswordModal', true);
        }

        /** Submit form ubah password admin */
        async function submitAdminPasswordForm(e) {
            e.preventDefault();
            const btn = document.getElementById('btnAdminPassword');
            btn.innerText = 'Menyimpan...'; btn.disabled = true;
            const payload = {
                current_password: document.getElementById('admin_current_password').value,
                new_password: document.getElementById('admin_new_password').value,
                confirm_password: document.getElementById('admin_confirm_password').value
            };
            try {
                const res = await fetch(`${BASE_URL}/api/admin/password`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                const data = await res.json();
                if (data.status === 'success') {
                    showToast(data.message || 'Password berhasil diperbarui!', 'success');
                    toggleModal('adminPasswordModal', false);
                    document.getElementById('adminPasswordForm').reset();
                } else { showToast(data.message || 'Gagal memperbarui password.', 'error'); }
            } catch (err) { showToast('Kesalahan jaringan', 'error'); }
            finally { hideAdminLoader(); btn.innerText = ' Simpan'; btn.disabled = false; }
        }
    </script>
</body>

</html>

