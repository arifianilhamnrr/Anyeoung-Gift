<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Anyeong Gift Admin' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
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

        .nav-active {
            background-color: #2C2C2C;
            border-left-width: 4px;
            border-left-color: #F59E0B;
            color: #F59E0B !important;
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
    </style>
</head>

<body
    class="bg-dark-base text-gray-200 flex h-screen overflow-hidden antialiased selection:bg-gold-500 selection:text-gray-900">

    <div id="sidebar-overlay"
        class="fixed inset-0 bg-black/40 backdrop-blur-md z-40 hidden transition-all duration-300 md:hidden"
        onclick="toggleSidebar()"></div>

    <aside id="sidebar"
        class="w-64 bg-dark-surface/80 border-r border-dark-border flex flex-col py-5 fixed inset-y-0 left-0 z-50 transform -translate-x-full md:relative md:translate-x-0 transition-transform duration-300 ease-in-out">
        <div class="text-2xl font-bold text-gold-500 text-center mb-8 tracking-wide">ANYEONG GIFT</div>
        <nav class="flex flex-col gap-1">
            <a href="#"
                class="nav-item nav-active px-6 py-3 cursor-pointer text-gray-300 hover:bg-dark-hover hover:border-l-4 hover:border-gold-500 hover:text-gold-500 transition-all block no-underline"
                data-target="dashboard">📊 Dashboard</a>
            <a href="#"
                class="nav-item px-6 py-3 cursor-pointer text-gray-300 hover:bg-dark-hover hover:border-l-4 hover:border-gold-500 hover:text-gold-500 transition-all block no-underline"
                data-target="orders">📦 Orders</a>
            <a href="#"
                class="nav-item px-6 py-3 cursor-pointer text-gray-300 hover:bg-dark-hover hover:border-l-4 hover:border-gold-500 hover:text-gold-500 transition-all block no-underline"
                data-target="products">🛍️ Products</a>
            <a href="#"
                class="nav-item px-6 py-3 cursor-pointer text-gray-300 hover:bg-dark-hover hover:border-l-4 hover:border-gold-500 hover:text-gold-500 transition-all block no-underline"
                data-target="payments">💳 Payments</a>
            <a href="#"
                class="nav-item px-6 py-3 cursor-pointer text-gray-300 hover:bg-dark-hover hover:border-l-4 hover:border-gold-500 hover:text-gold-500 transition-all block no-underline"
                data-target="settings">⚙️ Settings</a>
        </nav>
        <div class="mt-auto p-4 border-t border-dark-border">
            <button onclick="handleLogout()"
                class="w-full bg-red-500/10 text-red-500 border border-red-500/30 hover:bg-red-500 hover:text-white py-2.5 rounded-xl font-bold transition duration-300 flex justify-center items-center gap-2">🚪
                Keluar</button>
        </div>
    </aside>

    <main class="flex-1 flex flex-col overflow-y-auto w-full relative">
        <header
            class="h-[70px] bg-dark-surface border-b border-dark-border flex items-center justify-between px-4 md:px-8 shrink-0">
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()"
                    class="md:hidden text-gray-300 hover:text-gold-500 text-2xl focus:outline-none">☰</button>
                <h2 id="page-title" class="text-xl md:text-2xl font-semibold">Dashboard</h2>
            </div>
            <div class="user-profile hidden md:block text-sm text-gray-400">
                <span>👋 Halo, <?= $_SESSION['admin_name'] ?? 'Admin' ?></span>
            </div>
        </header>
        <div class="p-4 md:p-8 flex-1" id="app-content"></div>
    </main>

    <div id="productModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[1000] hidden justify-center items-center opacity-0 transition-opacity duration-300">
        <div
            class="bg-dark-surface w-full max-w-2xl max-h-[85vh] overflow-y-auto rounded-2xl p-6 md:p-8 border border-dark-border shadow-2xl m-4 transform scale-95 transition-transform duration-300 custom-scrollbar relative">
            <div class="flex justify-between items-center mb-6 pb-4 border-b border-dark-border">
                <h3 class="text-xl font-bold text-gray-100 flex items-center gap-2">🛍️ Tambah Produk</h3>
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
                    class="w-full bg-gold-500 text-gray-900 font-bold py-3.5 rounded-xl hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(245,158,11,0.3)] transition">💾
                    Simpan Produk ke Database</button>
            </form>
        </div>
    </div>

    <div id="orderDetailModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[1000] hidden justify-center items-center opacity-0 transition-opacity duration-300">
        <div
            class="bg-dark-surface w-full max-w-3xl max-h-[85vh] overflow-y-auto rounded-2xl p-6 md:p-8 border border-dark-border shadow-2xl m-4 transform scale-95 transition-transform duration-300 custom-scrollbar">
            <div class="flex justify-between items-center mb-6 pb-4 border-b border-dark-border">
                <div>
                    <h3 class="text-xl font-bold text-gray-100 flex items-center gap-2">🧾 Detail <span
                            id="detail-order-id"
                            class="text-gold-500 bg-gold-500/10 px-2 py-1 rounded-md text-sm"></span></h3>
                    <p id="detail-customer-info" class="text-gray-400 text-sm mt-1"></p>
                </div>
                <button onclick="closeOrderDetailModal()"
                    class="bg-dark-hover border border-dark-border text-gray-400 w-8 h-8 rounded-full hover:text-white hover:bg-red-500/20 transition">&times;</button>
            </div>
            <div id="order-detail-content" class="text-gray-300 space-y-4"></div>
        </div>
    </div>
    <!-- detail produk button -->
    <div id="productDetailModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[1000] hidden justify-center items-center opacity-0 transition-opacity duration-300">
        <div
            class="bg-dark-surface w-full max-w-2xl max-h-[85vh] overflow-y-auto rounded-2xl p-6 md:p-8 border border-dark-border shadow-2xl m-4 transform scale-95 transition-transform duration-300 custom-scrollbar">
            <div class="flex justify-between items-center mb-6 pb-4 border-b border-dark-border">
                <h3 class="text-xl font-bold text-gray-100 flex items-center gap-2">📦 Detail Produk</h3>
                <button onclick="closeProductDetailModal()"
                    class="text-gray-400 hover:text-white w-8 h-8 rounded-full hover:bg-red-500/20 transition">&times;</button>
            </div>
            <div id="product-detail-content" class="text-gray-300">
            </div>
        </div>
    </div>

    <div id="paymentModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[1000] hidden justify-center items-center opacity-0 transition-opacity duration-300">
        <div
            class="bg-dark-surface w-full max-w-sm rounded-2xl p-6 md:p-8 border border-dark-border shadow-2xl m-4 transform scale-95 transition-transform duration-300 relative">
            <div class="flex justify-between items-center mb-6 pb-4 border-b border-dark-border">
                <h3 class="text-xl font-bold text-gray-100 flex items-center gap-2">💳 Pembayaran</h3>
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
                            📱 QRIS</div>
                    </label>
                    <label class="cursor-pointer group"><input type="radio" name="payment_method" value="cod"
                            class="peer sr-only">
                        <div
                            class="text-center p-3 rounded-xl border border-dark-border bg-dark-base peer-checked:border-gold-500 peer-checked:bg-gold-500/10 peer-checked:text-gold-500 transition font-bold">
                            🚚 COD</div>
                    </label>
                </div>
            </div>
            <button onclick="submitPaymentConfirmation()" id="btnConfirmPayment"
                class="w-full bg-gold-500 text-gray-900 font-bold py-3.5 rounded-xl hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(245,158,11,0.3)] transition">✅
                Konfirmasi Lunas</button>
        </div>
    </div>

    <div id="paymentMethodModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[1000] hidden justify-center items-center opacity-0 transition-opacity duration-300">
        <div
            class="bg-dark-surface w-full max-w-md rounded-2xl p-6 md:p-8 border border-dark-border shadow-2xl m-4 transform scale-95 transition-transform duration-300 relative">
            <div class="flex justify-between items-center mb-6 pb-4 border-b border-dark-border">
                <h3 class="text-xl font-bold text-gray-100">💳 Tambah Metode</h3>
                <button onclick="closePaymentMethodModal()"
                    class="text-gray-400 hover:text-white w-8 h-8 rounded-full hover:bg-red-500/20 transition">&times;</button>
            </div>
            <form id="paymentMethodForm" onsubmit="submitPaymentMethod(event)">
                <div class="space-y-4">
                    <div><label class="block text-sm text-gray-400 font-medium mb-1.5">Nama Metode (Cth:
                            BCA)</label><input type="text" id="pm_name"
                            class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition"
                            required></div>
                    <div>
                        <label class="block text-sm text-gray-400 font-medium mb-1.5">Tipe Pembayaran</label>
                        <select id="pm_type"
                            class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition"
                            required>
                            <option value="transfer">Transfer Bank</option>
                            <option value="qris">QRIS</option>
                            <option value="ewallet">E-Wallet (OVO/Dana/dll)</option>
                            <option value="onsite">Bayar di Tempat (COD)</option>
                        </select>
                    </div>
                    <div><label class="block text-sm text-gray-400 font-medium mb-1.5">Info / No. Rekening</label><input
                            type="text" id="pm_info"
                            class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition"
                            placeholder="Cth: 1234567890 a/n Budi"></div>
                </div>
                <button type="submit" id="btnSavePaymentMethod"
                    class="w-full mt-8 bg-gold-500 text-gray-900 font-bold py-3.5 rounded-xl hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(245,158,11,0.3)] transition">💾
                    Simpan Metode</button>
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
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4 animate-fade-in-up">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-100 mb-1">Manajemen Produk</h2>
                        <p class="text-gray-400 text-sm">Kelola daftar produk, harga, dan opsi kustomisasi toko Anda.</p>
                    </div>
                    <button onclick="openProductModal()" class="bg-gold-500 text-gray-900 px-5 py-2.5 rounded-lg font-bold hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(245,158,11,0.3)] transition flex items-center gap-2 whitespace-nowrap">
                        <span class="text-xl leading-none">+</span> Tambah Produk
                    </button>
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
            `,

            settings: `
                <div class="animate-fade-in-up max-w-2xl mx-auto">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-100 mb-1">Pengaturan Toko</h2>
                        <p class="text-gray-400 text-sm">Sesuaikan nama toko dan informasi kontak WhatsApp.</p>
                    </div>
                    <div class="bg-dark-surface p-6 md:p-8 rounded-2xl border border-dark-border shadow-xl relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-gold-500/5 rounded-full blur-3xl pointer-events-none"></div>
                        <form id="settingsForm" onsubmit="submitSettingsForm(event)">
                            <div class="space-y-5 relative z-10">
                                <div><label class="block text-sm text-gray-400 font-medium mb-1.5">Nama Toko</label><input type="text" id="set_store_name" class="w-full p-3.5 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition" required></div>
                                <div><label class="block text-sm text-gray-400 font-medium mb-1.5">WhatsApp Admin</label><input type="number" id="set_wa_admin" class="w-full p-3.5 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition" required></div>
                                <div><label class="block text-sm text-gray-400 font-medium mb-1.5">Pesan Default Pembeli</label><textarea id="set_wa_template" rows="4" class="w-full p-3.5 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition custom-scrollbar"></textarea></div>
                                <button type="submit" id="btnSaveSettings" class="w-full mt-4 bg-gold-500 text-gray-900 font-bold py-3.5 rounded-xl hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(245,158,11,0.3)] transition">💾 Simpan Pengaturan</button>
                            </div>
                        </form>
                    </div>
                </div>
            `
        };

        // --- UTILITY ---
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-gold-500 text-gray-900' : 'bg-red-500 text-white';
            toast.className = `fixed top-5 right-5 z-[9999] flex items-center gap-3 px-5 py-3 rounded-xl shadow-[0_10px_30px_rgba(0,0,0,0.5)] transform transition-all duration-300 translate-x-full opacity-0 ${bgColor} font-bold text-sm`;
            toast.innerHTML = `<span class="text-lg">${type === 'success' ? '✨' : '⚠️'}</span> ${message}`;
            document.body.appendChild(toast);
            setTimeout(() => toast.classList.remove('translate-x-full', 'opacity-0'), 10);
            setTimeout(() => { toast.classList.add('translate-x-full', 'opacity-0'); setTimeout(() => toast.remove(), 300); }, 3000);
        }

        function toggleModal(modalId, show) {
            const modal = document.getElementById(modalId);
            if (show) {
                modal.classList.remove('hidden'); modal.classList.add('flex');
                setTimeout(() => { modal.classList.remove('opacity-0'); modal.querySelector('.bg-dark-surface').classList.remove('scale-95'); }, 10);
            } else {
                modal.classList.add('opacity-0'); modal.querySelector('.bg-dark-surface').classList.add('scale-95');
                setTimeout(() => { modal.classList.add('hidden'); modal.classList.remove('flex'); }, 300);
            }
        }

        async function handleLogout() {
            if (!confirm('Apakah Anda yakin ingin keluar?')) return;
            try { const res = await fetch(`${BASE_URL}/api/logout`, { method: 'POST' }); const data = await res.json(); if (data.status === 'success') window.location.href = `${BASE_URL}/login`; } catch (e) { showToast('Kesalahan jaringan', 'error'); }
        }

        document.addEventListener('DOMContentLoaded', () => {
            loadView('dashboard');
            document.querySelectorAll('.nav-item').forEach(item => {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('nav-active'));
                    e.currentTarget.classList.add('nav-active');
                    document.getElementById('page-title').innerText = e.currentTarget.getAttribute('data-target').toUpperCase();
                    loadView(e.currentTarget.getAttribute('data-target'));
                    if (window.innerWidth < 768) toggleSidebar();
                });
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
            if (document.getElementById('recent-orders-table')) { document.getElementById('recent-orders-table').innerHTML = `<tr class="animate-pulse border-b border-dark-border"><td colspan="4" class="p-4"><div class="h-6 bg-dark-hover rounded w-full"></div></td></tr>`; }
            try {
                const response = await fetch(`${BASE_URL}/api/dashboard/summary`); const result = await response.json();
                if (result.status === 'success') {
                    const data = result.data;
                    document.getElementById('stat-revenue').innerText = 'Rp ' + data.total_revenue.toLocaleString('id-ID');
                    document.getElementById('stat-active-orders').innerText = data.active_orders;
                    document.getElementById('stat-pending').innerText = data.pending_payments;

                    let tableHTML = '';
                    if (data.recent_orders.length === 0) tableHTML = `<tr><td colspan="4" class="text-center p-8 text-gray-500">Belum ada pesanan terbaru.</td></tr>`;
                    else {
                        data.recent_orders.forEach(order => {
                            let orderNumberText = 'AG-' + String(order.id).padStart(4, '0');
                            let statusBadge = `<span class="px-2 py-1 bg-gray-800 border border-gray-600 rounded text-xs">${order.status.replace('_', ' ')}</span>`;
                            tableHTML += `<tr class="border-b border-dark-border transition duration-200 hover:bg-dark-hover"><td class="p-4 font-bold text-gold-500">${orderNumberText}</td><td class="p-4">${order.customer_name || 'Anonim'}</td><td class="p-4 font-bold">Rp ${parseInt(order.total_price).toLocaleString('id-ID')}</td><td class="p-4">${statusBadge}</td></tr>`;
                        });
                    }
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
            if (isDynamic) {
                priceInput.disabled = true;
                priceInput.required = false;
                priceInput.value = ''; // Kosongkan nilainya
            } else {
                priceInput.disabled = false;
                priceInput.required = true;
            }
        }

        function openProductModal() {
            editProductId = null;
            document.querySelector('#productModal h3').innerHTML = '🛍️ Tambah Produk';
            document.getElementById('btnSaveProduct').innerHTML = '💾 Simpan Produk Baru';

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
            document.querySelector('#productModal h3').innerHTML = '✏️ Edit Produk';
            document.getElementById('btnSaveProduct').innerHTML = '💾 Perbarui Produk';

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
                    document.getElementById('p_price').value = p.base_price;

                    if (p.base_price == 0 || !p.base_price) {
                        document.getElementById('p_price').value = '';
                        document.getElementById('p_is_dynamic').checked = true;
                    } else {
                        document.getElementById('p_price').value = p.base_price;
                        document.getElementById('p_is_dynamic').checked = false;
                    }
                    toggleDynamicPrice();

                    // FIX BUG: Pengecekan gambar yang lebih aman
                    if (p.image && p.image !== '') {
                        document.getElementById('imagePreview').src = `${BASE_URL}/uploads/products/${p.image}`;
                        document.getElementById('imagePreview').classList.remove('hidden');
                        document.getElementById('uploadText').classList.add('hidden');
                    }

                    document.getElementById('optionsContainer').innerHTML = '';
                    optionCounter = 0;
                    if (p.options && p.options.length > 0) {
                        p.options.forEach(opt => {
                            const optId = optionCounter++;
                            const groupHtml = `<div class="option-group bg-dark-base p-4 rounded-xl border border-dark-border relative overflow-hidden group" id="opt_group_${optId}"><div class="absolute top-0 left-0 w-1 h-full bg-gold-500"></div><div class="flex flex-col sm:flex-row gap-4 mb-4 items-end"><div class="flex-1 w-full"><label class="block text-xs text-gray-400 font-medium tracking-wide mb-1.5">Nama Opsi</label><input type="text" class="opt-name w-full p-2.5 bg-dark-surface border border-dark-border text-gray-200 rounded-lg text-sm focus:border-gold-500 focus:ring-1 outline-none transition" value="${opt.option_name}" required></div><button type="button" onclick="this.parentElement.parentElement.remove()" class="bg-red-500/10 text-red-500 border border-red-500/30 px-4 py-2.5 rounded-lg hover:bg-red-500 hover:text-white transition font-bold text-sm h-[42px]">Hapus</button></div><div class="values-container ml-2 pl-4 border-l-2 border-dark-border space-y-3"></div><button type="button" onclick="addOptionValue(${optId})" class="mt-4 ml-2 text-gray-400 border border-dashed border-dark-border px-3 py-1.5 rounded-md text-xs hover:text-gold-500 hover:border-gold-500 transition">+ Pilihan Harga</button></div>`;
                            document.getElementById('optionsContainer').insertAdjacentHTML('beforeend', groupHtml);

                            const valContainer = document.querySelector(`#opt_group_${optId} .values-container`);
                            opt.values.forEach(v => {
                                valContainer.insertAdjacentHTML('beforeend', `<div class="option-value flex gap-3 items-center"><div class="flex-[2]"><input type="text" class="val-name w-full p-2 bg-dark-surface border border-dark-border text-gray-200 rounded-md text-sm outline-none" value="${v.value_name}" required></div><div class="flex-1 relative"><span class="absolute left-3 top-2 text-gray-500 text-sm">+Rp</span><input type="number" class="val-price w-full p-2 pl-9 bg-dark-surface border border-dark-border text-gray-200 rounded-md text-sm outline-none" value="${v.additional_price}" required></div><button type="button" onclick="this.parentElement.remove()" class="text-gray-500 hover:text-red-500 text-xl font-bold px-2">&times;</button></div>`);
                            });
                        });
                    }
                }
            } catch (e) { showToast('Gagal memuat data edit', 'error'); }
        }

        function closeProductModal() { toggleModal('productModal', false); }
        function previewImage(input) { const preview = document.getElementById('imagePreview'); const uploadText = document.getElementById('uploadText'); if (input.files && input.files[0]) { const reader = new FileReader(); reader.onload = function (e) { preview.src = e.target.result; preview.classList.remove('hidden'); uploadText.classList.add('hidden'); }; reader.readAsDataURL(input.files[0]); } }
        function addOptionGroup() { const container = document.getElementById('optionsContainer'); const optId = optionCounter++; container.insertAdjacentHTML('beforeend', `<div class="option-group bg-dark-base p-4 rounded-xl border border-dark-border relative overflow-hidden group" id="opt_group_${optId}"><div class="absolute top-0 left-0 w-1 h-full bg-gold-500"></div><div class="flex flex-col sm:flex-row gap-4 mb-4 items-end"><div class="flex-1 w-full"><label class="block text-xs text-gray-400 font-medium tracking-wide mb-1.5">Nama Opsi</label><input type="text" class="opt-name w-full p-2.5 bg-dark-surface border border-dark-border text-gray-200 rounded-lg text-sm focus:border-gold-500 focus:ring-1 outline-none transition" required></div><button type="button" onclick="this.parentElement.parentElement.remove()" class="bg-red-500/10 text-red-500 border border-red-500/30 px-4 py-2.5 rounded-lg hover:bg-red-500 hover:text-white transition font-bold text-sm h-[42px]">Hapus</button></div><div class="values-container ml-2 pl-4 border-l-2 border-dark-border space-y-3"></div><button type="button" onclick="addOptionValue(${optId})" class="mt-4 ml-2 text-gray-400 border border-dashed border-dark-border px-3 py-1.5 rounded-md text-xs hover:text-gold-500 hover:border-gold-500 transition">+ Pilihan Harga</button></div>`); addOptionValue(optId); }
        function addOptionValue(optId) { const valContainer = document.querySelector(`#opt_group_${optId} .values-container`); if (!valContainer) return; valContainer.insertAdjacentHTML('beforeend', `<div class="option-value flex gap-3 items-center"><div class="flex-[2]"><input type="text" class="val-name w-full p-2 bg-dark-surface border border-dark-border text-gray-200 rounded-md text-sm outline-none" placeholder="Nama Pilihan" required></div><div class="flex-1 relative"><span class="absolute left-3 top-2 text-gray-500 text-sm">+Rp</span><input type="number" class="val-price w-full p-2 pl-9 bg-dark-surface border border-dark-border text-gray-200 rounded-md text-sm outline-none" value="0" required></div><button type="button" onclick="this.parentElement.remove()" class="text-gray-500 hover:text-red-500 text-xl font-bold px-2">&times;</button></div>`); }

        // Fungsi Pintar: Simpan Baru ATAU Perbarui (Tergantung mode)
        async function submitProductForm(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSaveProduct');
            btn.innerText = 'Menyimpan...'; btn.disabled = true;

            // 1. AMBIL STATUS CHECKBOX DINAMIS (Ini yang tadi hilang!)
            const dynamicCheckbox = document.getElementById('p_is_dynamic');
            const isDynamic = dynamicCheckbox ? dynamicCheckbox.checked : false;

            // 2. SUSUN DATA PRODUK
            const payload = {
                name: document.getElementById('p_name').value,
                category: document.getElementById('p_category').value,
                // Jika dinamis kirim 0, jika tidak kirim angka di input
                base_price: isDynamic ? 0 : (parseInt(document.getElementById('p_price').value) || 0),
                options: []
            };

            // 3. SUSUN OPSI KUSTOMISASI
            document.querySelectorAll('.option-group').forEach(group => {
                const optName = group.querySelector('.opt-name').value;
                const values = [];
                group.querySelectorAll('.option-value').forEach(val => {
                    values.push({ value_name: val.querySelector('.val-name').value, additional_price: parseInt(val.querySelector('.val-price').value) || 0 });
                });
                if (values.length > 0) payload.options.push({ option_name: optName, values: values });
            });

            // 4. BUNGKUS KE FORMDATA (Untuk support gambar)
            const formData = new FormData();
            formData.append('product_data', JSON.stringify(payload));
            const imageInput = document.getElementById('p_image');
            if (imageInput.files.length > 0) formData.append('image', imageInput.files[0]);

            // Cek apakah mode Edit atau Tambah Baru
            if (editProductId) {
                formData.append('product_id', editProductId);
            }

            const apiUrl = editProductId ? `${BASE_URL}/api/products/update` : `${BASE_URL}/api/products`;

            // 5. KIRIM KE SERVER
            try {
                const res = await fetch(apiUrl, { method: 'POST', body: formData });
                const data = await res.json();
                if (data.status === 'success') {
                    showToast(data.message || 'Produk tersimpan!', 'success');
                    closeProductModal();
                    loadProductsData();
                } else {
                    showToast(data.message, 'error');
                }
            } catch (err) {
                showToast('Error Jaringan', 'error');
            } finally {
                btn.innerText = editProductId ? '💾 Perbarui Produk' : '💾 Simpan Produk Baru';
                btn.disabled = false;
            }
        }

        async function loadProductsData() {
            if (document.getElementById('products-table-body')) document.getElementById('products-table-body').innerHTML = `<tr class="animate-pulse border-b border-dark-border"><td colspan="5" class="p-4"><div class="h-6 bg-dark-hover rounded w-full"></div></td></tr>`;
            try {
                const res = await fetch(`${BASE_URL}/api/products`); const result = await res.json(); let html = '';
                if (result.status === 'success') {
                    if (result.data.length === 0) html = `<tr><td colspan="5" class="text-center p-10 text-gray-500">Belum ada produk.</td></tr>`;
                    else {
                        result.data.forEach(p => {
                            let isActive = p.is_active == 1;

                            // MENGEMBALIKAN TOMBOL DETAIL DAN EDIT YANG HILANG
                            let actionBtns = `
                                <button onclick="openProductDetailModal(${p.id})" class="bg-blue-500/10 text-blue-400 border border-blue-500/30 hover:bg-blue-500 hover:text-white px-3 py-1.5 rounded-md text-xs font-bold transition">🔍 Detail</button>
                                <button onclick="openEditProductModal(${p.id})" class="bg-yellow-500/10 text-yellow-500 border border-yellow-500/30 hover:bg-yellow-500 hover:text-gray-900 px-3 py-1.5 rounded-md text-xs font-bold transition">✏️ Edit</button>
                                <button onclick="toggleProductStatus(${p.id}, ${isActive ? 0 : 1})" class="border px-3 py-1.5 rounded-md text-xs font-bold transition ${isActive ? 'bg-red-500/10 text-red-500 border-red-500/30 hover:bg-red-500 hover:text-white' : 'bg-green-500/10 text-green-500 border-green-500/30 hover:bg-green-500 hover:text-white'}">${isActive ? 'Nonaktifkan' : 'Aktifkan'}</button>
                                <button onclick="deleteProduct(${p.id})" class="border border-red-500/30 bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white px-3 py-1.5 rounded-md text-xs font-bold transition">Hapus</button>
                            `;

                            html += `<tr class="border-b border-dark-border transition duration-200 hover:bg-dark-hover">
                                <td class="p-4"><div class="font-bold text-gray-200">${p.name}</div><div class="text-xs text-gray-500 mt-1">${p.total_options || 0} Opsi</div></td>
                                <td class="p-4 capitalize text-gray-300">${p.category}</td><td class="p-4 font-bold">${p.base_price ? 'Rp ' + parseInt(p.base_price).toLocaleString('id-ID') : 'Dinamis'}</td>
                                <td class="p-4">${isActive ? '<span class="px-3 py-1 bg-green-500/15 text-green-500 border border-green-500/30 rounded-full text-xs font-bold">Aktif</span>' : '<span class="px-3 py-1 bg-red-500/15 text-red-500 border border-red-500/30 rounded-full text-xs font-bold">Nonaktif</span>'}</td>
                                <td class="p-4 flex items-center justify-center gap-2">${actionBtns}</td>
                            </tr>`;
                        });
                    }
                    if (document.getElementById('products-table-body')) document.getElementById('products-table-body').innerHTML = html;
                }
            } catch (e) { console.error(e); }
        }

        async function toggleProductStatus(id, status) { if (!confirm(`Yakin ubah status?`)) return; try { const res = await fetch(`${BASE_URL}/api/products/toggle-status`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id, status }) }); const data = await res.json(); if (data.status === 'success') { showToast('Status diubah', 'success'); loadProductsData(); } } catch (e) { } }
        async function deleteProduct(id) { if (!confirm('Hapus permanen produk ini?')) return; try { const res = await fetch(`${BASE_URL}/api/products/delete`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id }) }); const data = await res.json(); if (data.status === 'success') { showToast('Produk dihapus', 'success'); loadProductsData(); } else showToast(data.message, 'error'); } catch (e) { } }

        function openProductDetailModal(id) {
            document.getElementById('product-detail-content').innerHTML = `<div class="animate-pulse h-40 bg-dark-hover rounded-xl w-full"></div>`;
            toggleModal('productDetailModal', true);
            fetchProductDetails(id);
        }
        function closeProductDetailModal() { toggleModal('productDetailModal', false); }

        async function fetchProductDetails(id) {
            try {
                const res = await fetch(`${BASE_URL}/api/products/details?id=${id}`);
                const result = await res.json();

                if (result.status === 'success') {
                    const p = result.data;

                    // FIX BUG: Pengecekan gambar yang lebih aman
                    let imgUrl;
                    if (p.image && p.image !== '') {
                        imgUrl = `${BASE_URL}/uploads/products/${p.image}`;
                    } else {
                        imgUrl = `https://via.placeholder.com/400x400/1E1E1E/555555?text=No+Image`;
                    }

                    let html = `
                        <div class="flex flex-col md:flex-row gap-6 mb-8">
                            <div class="w-full md:w-1/2 shrink-0">
                                <img src="${imgUrl}" alt="${p.name}" 
                                    class="w-full h-auto rounded-xl border-4 border-dark-border object-cover aspect-square shadow-2xl transition-transform duration-300 hover:scale-105"
                                    onerror="this.src='https://via.placeholder.com/400x400/1E1E1E/red?text=Image+Not+Found'">
                                <p class="text-xs text-gray-600 mt-2 text-center">Ukuran disarankan: Persegi (1:1)</p>
                            </div>
                            
                            <div class="w-full md:w-1/2 flex flex-col justify-between py-2">
                                <div>
                                    <div class="inline-block px-3 py-1 bg-gold-500/10 border border-gold-500/30 rounded-full text-xs text-gold-500 mb-3 capitalize tracking-wider">${p.category}</div>
                                    <h4 class="text-3xl font-extrabold text-gray-100 mb-3 leading-tight">${p.name}</h4>
                                    <p class="text-xs text-gray-500 mb-1">Harga Dasar:</p>
                                    <div class="text-4xl font-black text-gold-500 mb-6">Rp ${parseInt(p.base_price).toLocaleString('id-ID')}</div>
                                </div>
                                
                                <div class="bg-dark-base border border-dark-border p-4 rounded-xl text-sm text-gray-400">
                                    <div class="flex justify-between items-center mb-2"><span>Status di Toko:</span> ${p.is_active ? '<span class="px-2 py-0.5 bg-green-500/10 text-green-500 border border-green-500/30 rounded text-xs font-bold">Aktif</span>' : '<span class="px-2 py-0.5 bg-red-500/10 text-red-500 border border-red-500/30 rounded text-xs font-bold">Nonaktif</span>'}</div>
                                    <div class="flex justify-between items-center"><span>ID Produk:</span> <span class="font-mono text-xs text-gray-600">PROD-${String(p.id).padStart(4, '0')}</span></div>
                                </div>
                            </div>
                        </div>
                    `;

                    if (p.options && p.options.length > 0) {
                        html += `<h5 class="font-bold text-gray-200 mb-4 flex items-center gap-2 text-lg"><span class="text-xl">⚙️</span> Opsi Kustomisasi</h5><div class="grid grid-cols-1 md:grid-cols-2 gap-4">`;
                        p.options.forEach(opt => {
                            let vals = opt.values.map(v => `<span class="inline-block bg-dark-base border border-dark-border px-3 py-1.5 rounded-lg text-sm text-gray-300 shadow-sm">${v.value_name} <b class="text-gold-500 ml-1">(+Rp ${parseInt(v.additional_price).toLocaleString('id-ID')})</b></span>`).join(' ');
                            html += `<div class="bg-dark-hover border border-dark-border p-5 rounded-2xl relative overflow-hidden"><div class="absolute top-0 right-0 w-20 h-20 bg-gold-500/5 rounded-full blur-2xl"></div><div class="text-xs uppercase tracking-wider text-gray-500 font-bold mb-3 relative z-10">${opt.option_name}</div><div class="flex flex-wrap gap-2 relative z-10">${vals}</div></div>`;
                        });
                        html += `</div>`;
                    } else {
                        html += `<div class="text-sm text-gray-500 italic p-10 bg-dark-base rounded-xl border border-dark-border text-center">Produk ini tidak memiliki opsi kustomisasi tambahan.</div>`;
                    }

                    document.getElementById('product-detail-content').innerHTML = html;
                } else {
                    document.getElementById('product-detail-content').innerHTML = `<div class="text-red-500 text-center py-10 bg-red-500/10 rounded-xl border border-red-500/20">${result.message}</div>`;
                }
            } catch (e) {
                document.getElementById('product-detail-content').innerHTML = `<div class="text-red-500 text-center py-10 bg-red-500/10 rounded-xl border border-red-500/20">Gagal mengambil data produk.</div>`;
            }
        }
        // --- ORDERS ---
        async function loadOrdersData() {
            if (document.getElementById('orders-table-body')) document.getElementById('orders-table-body').innerHTML = `<tr class="animate-pulse border-b border-dark-border"><td colspan="6" class="p-4"><div class="h-6 bg-dark-hover rounded w-full"></div></td></tr>`;
            try {
                const res = await fetch(`${BASE_URL}/api/orders`); const result = await res.json(); let html = '';
                if (result.status === 'success') {
                    if (result.data.length === 0) html = `<tr><td colspan="6" class="text-center p-10 text-gray-500">Belum ada pesanan.</td></tr>`;
                    else {
                        result.data.forEach(o => {
                            let badgeHTML = ''; const bStyle = "px-3 py-1.5 rounded-full text-xs font-bold whitespace-nowrap inline-block border ";
                            switch (o.status) {
                                case 'pending': case 'waiting_payment': badgeHTML = `<span class="${bStyle} border-yellow-500/30 bg-yellow-500/15 text-yellow-500">⏳ Belum Dibayar</span>`; break;
                                case 'paid': badgeHTML = `<span class="${bStyle} border-blue-500/30 bg-blue-500/15 text-blue-500">💳 Sudah Dibayar</span>`; break;
                                case 'ready_pickup': badgeHTML = `<span class="${bStyle} border-purple-500/30 bg-purple-500/15 text-purple-400">🛍️ Siap Diambil</span>`; break;
                                case 'completed': badgeHTML = `<span class="${bStyle} border-green-500/30 bg-green-500/15 text-green-500">✅ Selesai</span>`; break;
                                case 'cancelled': badgeHTML = `<span class="${bStyle} border-red-500/30 bg-red-500/15 text-red-500">❌ Dibatalkan</span>`; break;
                            }
                            let cName = o.customer_name ? o.customer_name.replace(/'/g, "\\'") : 'Anonim';
                            let actions = `<button onclick="openOrderDetailModal(${o.id}, '${cName}')" class="bg-blue-500/10 text-blue-400 border border-blue-500/30 hover:bg-blue-500 hover:text-white px-3 py-2 rounded-md text-xs font-bold transition">🔍 Detail</button>`;
                            if (o.status === 'pending' || o.status === 'waiting_payment') { actions += `<button onclick="openPaymentModal(${o.id}, '${cName}', ${o.total_price})" class="bg-gold-500/10 text-gold-500 border border-gold-500/30 hover:bg-gold-500 hover:text-gray-900 px-3 py-2 rounded-md text-xs font-bold transition">💳 Konfirmasi</button>`; }
                            html += `<tr class="border-b border-dark-border transition duration-200 hover:bg-dark-hover"><td class="p-4 font-bold text-gold-500">AG-${String(o.id).padStart(4, '0')}</td><td class="p-4 font-bold text-gray-200">${cName}</td><td class="p-4 font-bold">Rp ${parseInt(o.total_price).toLocaleString('id-ID')}</td><td class="p-4 text-gray-400 text-sm">${new Date(o.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })}</td><td class="p-4">${badgeHTML}</td><td class="p-4"><div class="flex items-center gap-2">${actions}</div></td></tr>`;
                        });
                    }
                    if (document.getElementById('orders-table-body')) document.getElementById('orders-table-body').innerHTML = html;
                }
            } catch (e) { console.error(e); }
        }

        let currentPaymentOrderId = null;
        function openPaymentModal(orderId, customerName, totalPrice) { currentPaymentOrderId = orderId; document.getElementById('pay-customer-name').innerText = customerName; document.getElementById('pay-total-amount').innerText = 'Rp ' + parseInt(totalPrice).toLocaleString('id-ID'); document.querySelector('input[name="payment_method"][value="qris"]').checked = true; toggleModal('paymentModal', true); }
        function closePaymentModal() { toggleModal('paymentModal', false); currentPaymentOrderId = null; }
        async function submitPaymentConfirmation() {
            if (!currentPaymentOrderId) return; const btn = document.getElementById('btnConfirmPayment'); const method = document.querySelector('input[name="payment_method"]:checked').value; btn.innerText = 'Memproses...'; btn.disabled = true;
            try { const res = await fetch(`${BASE_URL}/api/orders/update-status`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ order_id: currentPaymentOrderId, status: 'paid', payment_method: method }) }); const data = await res.json(); if (data.status === 'success') { showToast('Pembayaran dikonfirmasi via ' + method.toUpperCase(), 'success'); closePaymentModal(); loadOrdersData(); } else showToast(data.message, 'error'); } catch (e) { showToast('Kesalahan jaringan', 'error'); } finally { btn.innerHTML = '✅ Konfirmasi Lunas'; btn.disabled = false; }
        }

        function openOrderDetailModal(orderId, customerName) { document.getElementById('detail-order-id').innerText = '#AG-' + String(orderId).padStart(4, '0'); document.getElementById('detail-customer-info').innerText = 'Pemesan: ' + customerName; document.getElementById('order-detail-content').innerHTML = `<div class="animate-pulse h-20 bg-dark-hover rounded-xl w-full"></div>`; toggleModal('orderDetailModal', true); fetchOrderDetails(orderId); }
        function closeOrderDetailModal() { toggleModal('orderDetailModal', false); }
        async function fetchOrderDetails(orderId) {
            try {
                const res = await fetch(`${BASE_URL}/api/orders/details?id=${orderId}`); const result = await res.json();
                if (result.status === 'success') {
                    let html = '<div class="space-y-3">';
                    if (result.data.length === 0) { html += `<div class="text-center py-6 text-gray-500 border border-dashed border-dark-border rounded-xl">Tidak ada detail item.</div>`; }
                    else {
                        result.data.forEach(item => {
                            let opts = ''; if (item.options && item.options.length > 0) { opts = '<ul class="mt-2 text-xs text-gray-400 space-y-1 border-l-2 border-dark-border pl-3">'; item.options.forEach(o => { opts += `<li>• ${o.option_name}: <span class="text-gray-300">${o.value_name}</span> (+Rp ${parseInt(o.additional_price).toLocaleString('id-ID')})</li>`; }); opts += '</ul>'; }
                            html += `<div class="bg-dark-base border border-dark-border p-4 rounded-xl flex justify-between items-start hover:border-gold-500/30 transition duration-300"><div><h4 class="font-bold text-gray-100 text-lg">${item.product_name}</h4><div class="text-sm text-gray-500 mt-0.5">Harga Dasar: Rp ${parseInt(item.price_at_time).toLocaleString('id-ID')} x ${item.quantity} pcs</div>${opts}</div><div class="text-right"><div class="text-xs text-gray-500 mb-1">Subtotal</div><div class="font-bold text-gold-500 text-lg">Rp ${parseInt(item.subtotal).toLocaleString('id-ID')}</div></div></div>`;
                        });
                    }
                    document.getElementById('order-detail-content').innerHTML = html + '</div>';
                } else document.getElementById('order-detail-content').innerHTML = `<div class="text-red-500 text-center py-5 bg-red-500/10 rounded-xl border border-red-500/20">${result.message}</div>`;
            } catch (e) { document.getElementById('order-detail-content').innerHTML = `<div class="text-red-500 text-center py-5 bg-red-500/10 rounded-xl border border-red-500/20">Error. Pastikan rute '/api/orders/details' sudah terdaftar di Router.</div>`; }
        }

        // ==========================================
        // --- PAYMENT METHODS ---
        // ==========================================
        let paymentMethodsList = []; // Simpan data sementara di memori
        let editPaymentId = null;    // Penanda mode Edit

        async function loadPaymentsData() {
            if (document.getElementById('payments-table-body')) document.getElementById('payments-table-body').innerHTML = `<tr class="animate-pulse border-b border-dark-border"><td colspan="5" class="p-4"><div class="h-6 bg-dark-hover rounded w-full"></div></td></tr>`;
            try {
                const res = await fetch(`${BASE_URL}/api/payment-methods`);
                const result = await res.json();
                let html = '';

                if (result.status === 'success') {
                    paymentMethodsList = result.data; // Simpan data ke variabel global

                    if (result.data.length === 0) {
                        html = `<tr><td colspan="5" class="text-center p-10 text-gray-500">Belum ada metode pembayaran.</td></tr>`;
                    } else {
                        result.data.forEach(m => {
                            let isActive = m.is_active == 1;

                            // TOMBOL AKSI BARU (Edit, Aktifkan/Nonaktifkan, Hapus)
                            let actionBtns = `
                                <button onclick="openEditPaymentMethodModal(${m.id})" class="bg-yellow-500/10 text-yellow-500 border border-yellow-500/30 hover:bg-yellow-500 hover:text-gray-900 px-3 py-1.5 rounded-md text-xs font-bold transition">✏️ Edit</button>
                                <button class="border px-3 py-1.5 rounded-md text-xs font-bold transition ${isActive ? 'bg-red-500/10 text-red-500 border-red-500/30 hover:bg-red-500 hover:text-white' : 'bg-green-500/10 text-green-500 border-green-500/30 hover:bg-green-500 hover:text-white'}">${isActive ? 'Nonaktifkan' : 'Aktifkan'}</button>
                                <button class="border border-red-500/30 bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white px-3 py-1.5 rounded-md text-xs font-bold transition">Hapus</button>
                            `;

                            html += `<tr class="border-b border-dark-border transition duration-200 hover:bg-dark-hover">
                                <td class="p-4 font-bold text-gray-200">${m.name}</td>
                                <td class="p-4 uppercase text-xs text-gray-400 tracking-wider">${m.type}</td>
                                <td class="p-4 text-gold-500 font-mono text-sm">${m.account_info || '-'}</td>
                                <td class="p-4">${isActive ? '<span class="px-3 py-1 bg-green-500/15 text-green-500 border border-green-500/30 rounded-full text-xs font-bold">Aktif</span>' : '<span class="px-3 py-1 bg-red-500/15 text-red-500 border border-red-500/30 rounded-full text-xs font-bold">Nonaktif</span>'}</td>
                                <td class="p-4 flex items-center justify-center gap-2">${actionBtns}</td>
                            </tr>`;
                        });
                    }
                    if (document.getElementById('payments-table-body')) document.getElementById('payments-table-body').innerHTML = html;
                }
            } catch (e) { console.error(e); }
        }

        // Mode Tambah Baru
        function openPaymentMethodModal() {
            editPaymentId = null;
            document.querySelector('#paymentMethodModal h3').innerHTML = '💳 Tambah Metode';
            document.getElementById('btnSavePaymentMethod').innerHTML = '💾 Simpan Metode';

            document.getElementById('paymentMethodForm').reset();
            toggleModal('paymentMethodModal', true);
        }

        // Mode Edit
        function openEditPaymentMethodModal(id) {
            // Cari data metode dari memori berdasarkan ID
            const method = paymentMethodsList.find(m => m.id === id);
            if (!method) return;

            editPaymentId = id;
            document.querySelector('#paymentMethodModal h3').innerHTML = '✏️ Edit Metode';
            document.getElementById('btnSavePaymentMethod').innerHTML = '💾 Perbarui Metode';

            // Isi form dengan data lama
            document.getElementById('pm_name').value = method.name;
            document.getElementById('pm_type').value = method.type;
            document.getElementById('pm_info').value = method.account_info || '';

            toggleModal('paymentMethodModal', true);
        }

        function closePaymentMethodModal() { toggleModal('paymentMethodModal', false); }

        // Fungsi Pintar: Simpan Baru ATAU Perbarui
        async function submitPaymentMethod(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSavePaymentMethod');
            btn.innerText = 'Menyimpan...'; btn.disabled = true;

            const payload = {
                name: document.getElementById('pm_name').value,
                type: document.getElementById('pm_type').value,
                account_info: document.getElementById('pm_info').value
            };

            // Jika mode Edit, tambahkan ID ke payload
            if (editPaymentId) {
                payload.id = editPaymentId;
            }

            const apiUrl = editPaymentId ? `${BASE_URL}/api/payment-methods/update` : `${BASE_URL}/api/payment-methods`;

            try {
                const res = await fetch(apiUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();

                if (data.status === 'success') {
                    showToast(data.message || 'Metode berhasil disimpan!', 'success');
                    closePaymentMethodModal();
                    loadPaymentsData();
                } else {
                    showToast(data.message, 'error');
                }
            } catch (e) {
                showToast('Error jaringan', 'error');
            } finally {
                btn.innerText = editPaymentId ? '💾 Perbarui Metode' : '💾 Simpan Metode';
                btn.disabled = false;
            }
        }

        // --- STORE SETTINGS ---
        async function loadSettingsData() {
            try {
                const res = await fetch(`${BASE_URL}/api/settings`);
                const result = await res.json();
                if (result.status === 'success' && result.data) {
                    document.getElementById('set_store_name').value = result.data.store_name || '';
                    document.getElementById('set_wa_admin').value = result.data.whatsapp_admin || '';
                    document.getElementById('set_wa_template').value = result.data.whatsapp_message_template || '';
                }
            } catch (e) { console.error('Gagal mengambil pengaturan', e); }
        }

        async function submitSettingsForm(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSaveSettings');
            btn.innerText = 'Menyimpan...'; btn.disabled = true;
            const payload = {
                store_name: document.getElementById('set_store_name').value,
                whatsapp_admin: document.getElementById('set_wa_admin').value,
                whatsapp_message_template: document.getElementById('set_wa_template').value
            };
            try {
                const res = await fetch(`${BASE_URL}/api/settings`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                const data = await res.json();
                if (data.status === 'success') showToast('Pengaturan berhasil disimpan!', 'success');
                else showToast(data.message, 'error');
            } catch (error) { showToast('Kesalahan jaringan', 'error'); }
            finally { btn.innerText = '💾 Simpan Pengaturan'; btn.disabled = false; }
        }

        // --- DEV TOOLS (Simulasi Pesanan) ---
        async function simulateNewOrder() {
            try {
                const btn = event.target; const originalText = btn.innerText; btn.innerText = 'Menyuntikkan...';
                const response = await fetch(`${BASE_URL}/api/dev/generate-order`);
                const result = await response.json();
                if (result.status === 'success') { showToast(result.message, 'success'); loadOrdersData(); }
                else { showToast('Gagal: ' + result.message, 'error'); }
                btn.innerText = originalText;
            } catch (error) { showToast('Kesalahan jaringan.', 'error'); }
        }
    </script>
</body>

</html>