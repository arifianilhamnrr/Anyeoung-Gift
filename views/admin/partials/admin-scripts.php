    <script>
        const BASE_URL = '<?= BASE_URL ?>';

        const views = {
            dashboard: `<?php ob_start();
            include __DIR__ . '/dashboard.php';
            echo str_replace('`', '\\`', ob_get_clean()); ?>`,
            orders: `<?php ob_start();
            include __DIR__ . '/orders.php';
            echo str_replace('`', '\\`', ob_get_clean()); ?>`,
                payments: `<?php ob_start();
                include __DIR__ . '/payments.php';
                echo str_replace('`', '\\`', ob_get_clean()); ?>`,
            products: `<?php ob_start();
            include __DIR__ . '/products.php';
            echo str_replace('`', '\\`', ob_get_clean()); ?>`,

            // ===== SETTINGS VIEW: Display-only cards, edit via modals =====
            settings: `<?php ob_start();
            include __DIR__ . '/settings.php';
            echo str_replace('`', '\\`', ob_get_clean()); ?>`
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

        // --- KONFIRMASI MODAL (REUSABLE) ---
        // Pengganti window.confirm() bawaan supaya konsisten dengan tampilan
        // dashboard. Memakai #adminConfirmModal di admin-modals.php.
        // Pemakaian:  const ok = await showConfirmModal({ title, message, tone, okText, cancelText });
        // tone: 'warning' (default, kuning) | 'danger' (merah) | 'info' (biru).
        function showConfirmModal(opts = {}) {
            return new Promise((resolve) => {
                const modal = document.getElementById('adminConfirmModal');
                if (!modal) { resolve(window.confirm(opts.message || 'Yakin?')); return; }
                const titleEl = document.getElementById('adminConfirmTitle');
                const msgEl = document.getElementById('adminConfirmMessage');
                const okBtn = document.getElementById('adminConfirmOkBtn');
                const cancelBtn = document.getElementById('adminConfirmCancelBtn');
                const iconWrap = document.getElementById('adminConfirmIconWrap');
                const tone = opts.tone || 'warning';
                const toneStyles = {
                    warning: { wrap: 'bg-yellow-500/15 text-yellow-400 border-yellow-500/30', ok: 'bg-gold-500 text-gray-900 hover:shadow-[0_8px_20px_rgba(245,158,11,0.3)]' },
                    danger: { wrap: 'bg-red-500/15 text-red-400 border-red-500/30', ok: 'bg-red-500 text-white hover:shadow-[0_8px_20px_rgba(239,68,68,0.3)]' },
                    info: { wrap: 'bg-blue-500/15 text-blue-400 border-blue-500/30', ok: 'bg-blue-500 text-white hover:shadow-[0_8px_20px_rgba(59,130,246,0.3)]' }
                };
                const style = toneStyles[tone] || toneStyles.warning;
                if (titleEl) titleEl.textContent = opts.title || 'Konfirmasi';
                if (msgEl) msgEl.textContent = opts.message || 'Yakin ingin melanjutkan?';
                if (iconWrap) iconWrap.className = `w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3 border ${style.wrap}`;
                if (okBtn) {
                    okBtn.textContent = opts.okText || 'OK';
                    okBtn.className = `flex-1 py-2.5 rounded-xl font-bold text-sm transition hover:-translate-y-0.5 ${style.ok}`;
                }
                if (cancelBtn) cancelBtn.textContent = opts.cancelText || 'Batal';

                const cleanup = (result) => {
                    okBtn.onclick = null; cancelBtn.onclick = null;
                    toggleModal('adminConfirmModal', false);
                    resolve(result);
                };
                okBtn.onclick = () => cleanup(true);
                cancelBtn.onclick = () => cleanup(false);
                toggleModal('adminConfirmModal', true);
            });
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
            const ok = await showConfirmModal({
                title: 'Keluar dari Admin?',
                message: 'Sesi admin Anda akan diakhiri dan Anda perlu login ulang untuk mengakses dashboard.',
                tone: 'danger',
                okText: 'Ya, Keluar',
                cancelText: 'Batal'
            });
            if (!ok) return;
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
        const BULAN_LABELS_ID = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        async function loadDashboardData() {
            if (document.getElementById('recent-orders-table')) document.getElementById('recent-orders-table').innerHTML = `<tr class="animate-pulse border-b border-dark-border"><td colspan="4" class="p-4"><div class="h-6 bg-dark-hover rounded w-full"></div></td></tr>`;

            const monthEl = document.getElementById('dashboardMonthFilter');
            const yearEl = document.getElementById('dashboardYearFilter');
            const month = monthEl ? monthEl.value : 'all';
            const year = yearEl ? yearEl.value : 'all';

            const params = new URLSearchParams();
            if (month !== 'all' && year !== 'all') {
                params.set('month', month);
                params.set('year', year);
            }
            const url = `${BASE_URL}/api/dashboard/summary` + (params.toString() ? `?${params.toString()}` : '');

            // Update label "Pendapatan ..." sesuai pilihan
            const revenueCardTitle = document.querySelector('#stat-revenue').closest('.bg-dark-surface').querySelector('h3');
            const revenuePeriod = document.getElementById('stat-revenue-period');
            if (month !== 'all' && year !== 'all') {
                const monthLabel = BULAN_LABELS_ID[parseInt(month, 10) - 1] || '';
                if (revenueCardTitle) revenueCardTitle.innerText = `Pendapatan ${monthLabel} ${year}`;
                if (revenuePeriod) revenuePeriod.innerText = `Pesanan yang sudah dibayar pada ${monthLabel} ${year}.`;
            } else {
                if (revenueCardTitle) revenueCardTitle.innerText = 'Total Pendapatan';
                if (revenuePeriod) revenuePeriod.innerText = 'Akumulasi semua pesanan yang sudah dibayar.';
            }

            try {
                const response = await fetch(url);
                const result = await response.json();
                if (result.status === 'success') {
                    const data = result.data;
                    document.getElementById('stat-revenue').innerText = 'Rp ' + (data.total_revenue || 0).toLocaleString('id-ID');
                    document.getElementById('stat-active-orders').innerText = data.active_orders;
                    document.getElementById('stat-pending').innerText = data.pending_payments;
                    let tableHTML = '';
                    if (!data.recent_orders || data.recent_orders.length === 0) tableHTML = `<tr><td colspan="4" class="text-center p-8 text-gray-500">Belum ada pesanan untuk periode ini.</td></tr>`;
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
            const isActivating = String(status) === '1' || status === 1 || status === true;
            const ok = await showConfirmModal({
                title: isActivating ? 'Aktifkan Produk?' : 'Nonaktifkan Produk?',
                message: isActivating
                    ? 'Produk akan tampil di storefront pelanggan dan bisa dipesan.'
                    : 'Produk akan disembunyikan dari storefront dan tidak bisa dipesan.',
                tone: isActivating ? 'info' : 'warning',
                okText: isActivating ? 'Ya, Aktifkan' : 'Ya, Nonaktifkan',
                cancelText: 'Batal'
            });
            if (!ok) return;
            showAdminLoader('Mengubah status...', 'Mohon tunggu sebentar.');
            try {
                const res = await fetch(`${BASE_URL}/api/products/toggle-status`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id, status }) });
                const data = await res.json();
                if (data.status === 'success') { showToast('Status diubah', 'success'); loadProductsData(); }
            } catch (e) { showToast('Kesalahan jaringan', 'error'); }
            finally { hideAdminLoader(); }
        }
        async function deleteProduct(id) {
            const ok = await showConfirmModal({
                title: 'Hapus Produk?',
                message: 'Produk akan dihapus permanen beserta opsi & gambarnya. Tindakan ini tidak dapat dibatalkan.',
                tone: 'danger',
                okText: 'Ya, Hapus',
                cancelText: 'Batal'
            });
            if (!ok) return;
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
            if (tbody) tbody.innerHTML = `<tr class="animate-pulse border-b border-dark-border"><td colspan="7" class="p-4"><div class="h-6 bg-dark-hover rounded w-full"></div></td></tr>`;
            try {
                const res = await fetch(`${BASE_URL}/api/orders`);
                const result = await res.json();
                if (result.status === 'success') {
                    ordersCache = result.data || [];
                    ordersCurrentPage = 1;
                    populateOrdersPaymentMethodFilter();
                    renderOrdersTable();
                }
            } catch (e) { console.error(e); }
        }

        function paymentMethodLabel(o) {
            const type = (o.payment_method_type || '').toLowerCase();
            if (type === 'onsite') return 'Cash on Pick Up';
            return o.payment_method_name || (type ? type.toUpperCase() : '-');
        }

        function populateOrdersPaymentMethodFilter() {
            const select = document.getElementById('orderPaymentMethodFilter');
            if (!select) return;
            const seen = new Set();
            const options = [];
            ordersCache.forEach(o => {
                const type = (o.payment_method_type || '').toLowerCase();
                if (type === 'onsite') return;
                const key = (o.payment_method_name || '').trim();
                if (!key || seen.has(key)) return;
                seen.add(key);
                options.push(`<option value="name:${escapeHtml(key)}">${escapeHtml(key)}</option>`);
            });
            const currentValue = select.value;
            select.innerHTML = `<option value="all">Semua Metode</option><option value="onsite">Cash on Pick Up</option>${options.join('')}`;
            if (currentValue) select.value = currentValue;
        }

        function onOrdersFilterChange() {
            ordersCurrentPage = 1;
            renderOrdersTable();
        }

        function renderOrdersTable() {
            const tbody = document.getElementById('orders-table-body');
            if (!tbody) return;
            const statusFilter = (document.getElementById('orderStatusFilter') || {}).value || 'all';
            const methodFilter = (document.getElementById('orderPaymentMethodFilter') || {}).value || 'all';
            const monthFilter = (document.getElementById('orderMonthFilter') || {}).value || 'all';
            const yearFilter = (document.getElementById('orderYearFilter') || {}).value || 'all';
            const searchTerm = ((document.getElementById('orderSearchInput') || {}).value || '').trim().toLowerCase();

            const filtered = ordersCache.filter(o => {
                if (statusFilter !== 'all') {
                    if (statusFilter === 'waiting_payment') {
                        if (o.status !== 'pending' && o.status !== 'waiting_payment') return false;
                    } else if (o.status !== statusFilter) {
                        return false;
                    }
                }

                if (methodFilter !== 'all') {
                    const type = (o.payment_method_type || '').toLowerCase();
                    if (methodFilter === 'onsite') {
                        if (type !== 'onsite') return false;
                    } else if (methodFilter.startsWith('name:')) {
                        const wantedName = methodFilter.slice(5);
                        if ((o.payment_method_name || '') !== wantedName) return false;
                    }
                }

                if (monthFilter !== 'all' || yearFilter !== 'all') {
                    const d = o.created_at ? new Date(o.created_at) : null;
                    if (!d || isNaN(d.getTime())) return false;
                    if (monthFilter !== 'all' && (d.getMonth() + 1) !== parseInt(monthFilter, 10)) return false;
                    if (yearFilter !== 'all' && d.getFullYear() !== parseInt(yearFilter, 10)) return false;
                }

                if (searchTerm) {
                    const idLabel = 'ord-' + String(o.id).padStart(5, '0');
                    const haystack = [
                        idLabel,
                        String(o.id),
                        (o.customer_name || ''),
                        paymentMethodLabel(o),
                        (o.payment_method_name || ''),
                        (o.payment_method_type || '')
                    ].join(' ').toLowerCase();
                    if (!haystack.includes(searchTerm)) return false;
                }

                return true;
            });

            const totalPages = Math.max(1, Math.ceil(filtered.length / ORDERS_PAGE_SIZE));
            if (ordersCurrentPage > totalPages) ordersCurrentPage = totalPages;
            const start = (ordersCurrentPage - 1) * ORDERS_PAGE_SIZE;
            const pageRows = filtered.slice(start, start + ORDERS_PAGE_SIZE);

            if (filtered.length === 0) { tbody.innerHTML = `<tr><td colspan="7" class="text-center p-10 text-gray-500">Belum ada pesanan yang cocok.</td></tr>`; }
            else {
                tbody.innerHTML = pageRows.map(o => {
                    const badge = ORDER_STATUS_BADGE[o.status] || { label: o.status, cls: 'border-gray-500/30 bg-gray-500/15 text-gray-400' };
                    const badgeHTML = `<span class="px-3 py-1.5 rounded-full text-xs font-bold whitespace-nowrap inline-block border ${badge.cls}">${badge.label}</span>`;
                    const cName = o.customer_name ? o.customer_name.replace(/'/g, "\\'") : 'Anonim';
                    const isOnsite = (o.payment_method_type || '').toLowerCase() === 'onsite';
                    const methodLabel = escapeHtml(paymentMethodLabel(o));
                    const methodCell = isOnsite
                        ? `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold border border-amber-500/30 bg-amber-500/10 text-amber-300 whitespace-nowrap">${methodLabel}</span>`
                        : `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold border border-blue-500/30 bg-blue-500/10 text-blue-300 whitespace-nowrap">${methodLabel}</span>`;

                    const orderMenu = [
                        { label: 'Lihat detail', onclick: `openOrderDetailModal(${o.id}, '${cName}')`, icon: 'eye' }
                    ];
                    if (o.status === 'pending' || o.status === 'waiting_payment') {
                        if (isOnsite) {
                            orderMenu.push({ label: 'Konfirmasi pesanan', onclick: `confirmOnsiteOrder(${o.id})`, icon: 'wallet', accent: true });
                        } else {
                            orderMenu.push({ label: 'Konfirmasi pembayaran', onclick: `confirmOnlinePayment(${o.id})`, icon: 'wallet', accent: true });
                        }
                    }
                    if (o.status === 'paid') {
                        orderMenu.push({ label: 'Tandai pesanan siap', onclick: `setOrderStatus(${o.id}, 'ready_pickup')`, icon: 'play', accent: true });
                    }
                    if (canCancelOrder(o.status)) {
                        orderMenu.push({ label: 'Batalkan pesanan', onclick: `setOrderStatus(${o.id}, 'cancelled')`, icon: 'trash', danger: true });
                    }

                    return `<tr class="border-b border-dark-border transition duration-200 hover:bg-dark-hover cursor-pointer" onclick="openOrderDetailModal(${o.id}, '${cName}')">
                        <td class="p-4 font-bold text-gold-500">ORD-${String(o.id).padStart(5, '0')}</td>
                        <td class="p-4 font-bold text-gray-200">${escapeHtml(cName)}</td>
                        <td class="p-4 font-bold">Rp ${parseInt(o.total_price).toLocaleString('id-ID')}</td>
                        <td class="p-4">${methodCell}</td>
                        <td class="p-4 text-gray-400 text-sm whitespace-nowrap">${new Date(o.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</td>
                        <td class="p-4">${badgeHTML}</td>
                        <td class="p-4 text-center" onclick="event.stopPropagation()">${renderRowMenu(`ord-${o.id}`, orderMenu)}</td>
                    </tr>`;
                }).join('');
            }
            renderPagination('orders-pagination', ordersCurrentPage, totalPages, filtered.length, (page) => { ordersCurrentPage = page; renderOrdersTable(); });
        }

        async function confirmOnsiteOrder(orderId) {
            const ok = await showConfirmModal({
                title: 'Konfirmasi Pesanan COD?',
                message: 'Tandai pesanan Cash on Pick Up ini sebagai sudah dibayar. Pesanan akan masuk antrian untuk diproses.',
                tone: 'info',
                okText: 'Ya, Konfirmasi',
                cancelText: 'Batal'
            });
            if (!ok) return;
            await setOrderStatus(orderId, 'paid', { skipConfirm: true });
        }

        async function setOrderStatus(orderId, status, opts = {}) {
            if (!opts.skipConfirm) {
                const confirms = {
                    paid: { title: 'Konfirmasi Pesanan Dibayar?', message: 'Tandai pesanan ini sebagai sudah dibayar. Pesanan akan masuk antrian untuk diproses.', tone: 'info', okText: 'Ya, Konfirmasi' },
                    ready_pickup: { title: 'Tandai Pesanan Siap?', message: 'Pesanan akan masuk status siap diambil dan pembeli akan dapat menyelesaikan pesanan.', tone: 'info', okText: 'Ya, Tandai Siap' },
                    completed: { title: 'Tandai Pesanan Selesai?', message: 'Pesanan akan dikunci sebagai selesai. Tindakan ini tidak dapat dibatalkan.', tone: 'warning', okText: 'Ya, Selesaikan' },
                    cancelled: { title: 'Batalkan Pesanan?', message: 'Pesanan yang dibatalkan tidak dapat diaktifkan kembali. Pastikan sudah berkoordinasi dengan pembeli.', tone: 'danger', okText: 'Ya, Batalkan' }
                };
                const cfg = confirms[status];
                if (cfg) {
                    const ok = await showConfirmModal({ title: cfg.title, message: cfg.message, tone: cfg.tone, okText: cfg.okText, cancelText: 'Batal' });
                    if (!ok) return;
                }
            }
            const labels = { paid: 'mengkonfirmasi pesanan', ready_pickup: 'menandai pesanan siap diambil', completed: 'menandai pesanan selesai', cancelled: 'membatalkan pesanan' };
            showAdminLoader('Memperbarui status...', 'Sedang menyimpan perubahan ke server.');
            try {
                const res = await fetch(`${BASE_URL}/api/orders/update-status`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ order_id: orderId, status }) });
                const data = await res.json();
                if (data.status === 'success') {
                    showToast('Berhasil ' + (labels[status] || 'memperbarui status'), 'success');
                    loadOrdersData();
                    if (typeof opts.onSuccess === 'function') opts.onSuccess();
                }
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

        // Template WhatsApp untuk setiap status pesanan. {toko} otomatis
        // di-replace dengan store_name dari settingsCache supaya nama toko
        // sesuai dengan pengaturan terbaru.
        const WA_TEMPLATES = {
            waiting_payment: 'Halo {nama}, terima kasih telah memesan di {toko}. Pesanan {kode} sudah kami terima. Mohon segera lakukan pembayaran agar kami dapat memproses pesanan Anda. Terima kasih!',
            pending: 'Halo {nama}, terima kasih telah memesan di {toko}. Pesanan {kode} sudah kami terima. Mohon segera lakukan pembayaran agar kami dapat memproses pesanan Anda. Terima kasih!',
            paid: 'Halo {nama}, pembayaran untuk pesanan {kode} telah kami terima. Pesanan Anda akan segera kami siapkan. Terima kasih!',
            ready_pickup: 'Halo {nama}, kabar baik! Pesanan {kode} sudah siap untuk diambil di toko kami. Silakan datang sesuai jam operasional. Sampai jumpa!',
            completed: 'Halo {nama}, terima kasih telah berbelanja di {toko} untuk pesanan {kode}. Semoga puas! Sampai jumpa di pesanan berikutnya!',
            cancelled: 'Halo {nama}, dengan berat hati pesanan {kode} telah dibatalkan. Jika ada pertanyaan, hubungi kami kembali. Terima kasih.'
        };

        function buildWhatsappMessageForStatus(status, recipientName, orderCode) {
            const storeName = (settingsCache && settingsCache.store_name) || 'Anyeong Gift';
            const tpl = WA_TEMPLATES[status] || `Halo {nama}, kami dari {toko} ingin menginformasikan terkait pesanan {kode}.`;
            return tpl
                .replace(/{nama}/g, recipientName)
                .replace(/{kode}/g, orderCode)
                .replace(/{toko}/g, storeName);
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
            const isOnsite = type === 'onsite';
            const methodName = escapeHtml(isOnsite ? 'Cash on Pick Up' : (payment.method_name || 'Pembayaran online'));
            const proof = payment.proof_image || null;
            const paymentStatus = (payment.status || 'pending').toLowerCase();
            const statusMap = {
                pending: { label: isOnsite ? 'Belum Dibayar' : 'Menunggu Verifikasi', cls: 'bg-yellow-500/15 text-yellow-400 border-yellow-500/30' },
                confirmed: { label: isOnsite ? 'Sudah Dibayar' : 'Terverifikasi', cls: 'bg-green-500/15 text-green-400 border-green-500/30' },
                rejected: { label: 'Ditolak', cls: 'bg-red-500/15 text-red-400 border-red-500/30' }
            };
            const statusInfo = statusMap[paymentStatus] || statusMap.pending;
            const paidAt = payment.paid_at ? new Date(payment.paid_at).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : null;
            const proofUrl = (!isOnsite && proof) ? `${BASE_URL}/uploads/payments/${encodeURIComponent(proof)}` : null;

            const accountInfoBlock = (isOnsite || !payment.method_account)
                ? ''
                : `<div class="mt-3 rounded-xl border border-dark-border bg-dark-base/60 p-3"><div class="text-[11px] text-gray-500 uppercase tracking-wider mb-1">Info Rekening / Akun</div><div class="text-gold-500 font-mono text-sm break-all whitespace-pre-line">${escapeHtml(payment.method_account)}</div></div>`;

            const onsiteInfoBlock = isOnsite
                ? `<div class="mt-3 rounded-xl border border-amber-500/30 bg-amber-500/10 p-3 text-sm text-amber-200"><div class="flex items-start gap-2"><svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/></svg><div><div class="font-semibold text-amber-300 mb-0.5">Bayar di toko saat pengambilan</div><div class="text-xs text-amber-200/80">Pembeli akan melunasi pesanan langsung di toko ketika datang mengambil. Konfirmasi setelah uang diterima.</div></div></div></div>`
                : '';

            const proofBlock = isOnsite
                ? ''
                : (proofUrl
                    ? `<div class="mt-3"><div class="text-xs text-gray-500 uppercase tracking-wider mb-2">Bukti Pembayaran</div><button type="button" onclick="openProofViewer('${proofUrl}')" class="block w-full rounded-xl overflow-hidden border border-dark-border bg-dark-base hover:border-gold-500/50 transition text-left"><img src="${proofUrl}" alt="Bukti pembayaran" loading="lazy" class="w-full max-h-72 object-contain bg-black/40"/><div class="px-3 py-2 text-xs text-gray-400 flex items-center justify-between"><span>Klik untuk lihat penuh</span><span class="text-gold-500 font-semibold">Buka</span></div></button></div>`
                    : `<div class="mt-3 rounded-xl border border-dashed border-dark-border bg-dark-base/40 p-4 text-center text-sm text-gray-500">Pembeli belum mengunggah bukti pembayaran.</div>`);

            const tagLabel = isOnsite ? 'Cash on Pick Up' : 'Pembayaran';
            return `<div class="bg-dark-base/50 border border-dark-border rounded-xl p-4 space-y-3"><div class="flex items-start justify-between gap-3"><div><div class="text-xs text-gray-500 uppercase tracking-wider mb-1">${tagLabel}</div><div class="font-semibold text-gray-100">${methodName}</div>${paidAt ? `<div class="text-xs text-gray-500 mt-1">${isOnsite ? 'Dikonfirmasi' : 'Diunggah'}: ${paidAt}</div>` : ''}</div><span class="shrink-0 px-3 py-1 rounded-full text-[11px] font-bold border ${statusInfo.cls}">${statusInfo.label}</span></div>${onsiteInfoBlock}${accountInfoBlock}${proofBlock}</div>`;
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
            // Reuse setOrderStatus tetapi tetap refresh detail modal setelah sukses.
            await setOrderStatus(orderId, status, { onSuccess: () => fetchOrderDetails(orderId) });
        }

        async function confirmOnlinePayment(orderId) {
            const ok = await showConfirmModal({
                title: 'Konfirmasi Pembayaran?',
                message: 'Tandai pembayaran pesanan ini sebagai terverifikasi. Pesanan akan masuk antrian untuk diproses.',
                tone: 'info',
                okText: 'Ya, Konfirmasi',
                cancelText: 'Batal'
            });
            if (!ok) return;
            await setOrderStatus(orderId, 'paid', { skipConfirm: true });
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
            const paymentMethodLabel = (payment && (payment.method_type || '').toLowerCase() === 'onsite')
                ? 'Cash on Pick Up'
                : (payment && payment.method_name ? payment.method_name : '-');
            const paymentStatusLabel = (() => { if (!payment) return '-'; const s = (payment.status || '').toLowerCase(); if (s === 'confirmed') return 'Lunas'; if (s === 'rejected') return 'Ditolak'; return 'Menunggu Verifikasi'; })();
            const itemsRows = items.map(item => {
                const optionLines = (item.options || []).map(opt => { const hasCustom = opt.custom_value !== null && opt.custom_value !== undefined && String(opt.custom_value).trim() !== ''; const value = hasCustom ? opt.custom_value : (opt.value_name || '-'); const extra = parseInt(opt.additional_price) || 0; const extraStr = extra > 0 ? ` (+${formatRupiah(extra)})` : ''; return `<div class="opt"><span>${escapeHtml(opt.option_name)}:</span> ${escapeHtml(value)}${extraStr}</div>`; }).join('');
                return `<tr><td><div class="iname">${escapeHtml(item.product_name || '-')}</div>${optionLines ? `<div class="opts">${optionLines}</div>` : ''}</td><td class="qty">${item.quantity || 1}</td><td class="right">${formatRupiah(item.subtotal)}</td></tr>`;
            }).join('');
            const win = window.open('', '_blank', 'width=820,height=1000');
            if (!win) { showToast('Pop-up diblokir browser', 'error'); return; }
            // Nama toko untuk invoice diambil dari settingsCache (store_settings)
            // dengan fallback ke nama admin atau "Anyeong Gift".
            const storeName = (settingsCache && settingsCache.store_name)
                || <?= json_encode($_SESSION['admin_name'] ?? 'Anyeong Gift') ?>;
            win.document.write(`<!doctype html><html lang="id"><head><meta charset="utf-8"/><title>Invoice ${invoiceNo}</title><style>*{box-sizing:border-box}body{font-family:'Segoe UI',Tahoma,sans-serif;color:#111;margin:0;padding:24px;background:#fff}.wrap{max-width:720px;margin:0 auto}.head{display:flex;justify-content:space-between;align-items:flex-start;border-bottom:2px solid #111;padding-bottom:12px;margin-bottom:18px}.brand{font-size:22px;font-weight:800;letter-spacing:1px}.brand small{display:block;font-size:11px;font-weight:500;color:#555;letter-spacing:2px;text-transform:uppercase}.meta{text-align:right;font-size:12px;color:#333}.meta .no{font-size:16px;font-weight:700;color:#000}.grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px}.card{border:1px solid #ddd;border-radius:6px;padding:12px 14px}.card h4{margin:0 0 6px 0;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:#666}.card p{margin:2px 0;font-size:13px}table{width:100%;border-collapse:collapse;margin-bottom:18px}thead th{background:#111;color:#fff;text-align:left;padding:8px 10px;font-size:12px}thead th.qty,thead th.right{text-align:center}thead th.right{text-align:right}tbody td{padding:10px;border-bottom:1px solid #eee;font-size:13px;vertical-align:top}tbody td.qty{text-align:center}tbody td.right{text-align:right;white-space:nowrap}.iname{font-weight:700}.opts{margin-top:4px;color:#555;font-size:12px}.opt{padding-left:8px}.total-row{display:flex;justify-content:flex-end}.total{min-width:280px;border-top:2px solid #111;padding-top:10px}.total .line{display:flex;justify-content:space-between;padding:4px 0;font-size:13px}.total .grand{font-size:16px;font-weight:800;border-top:1px dashed #aaa;margin-top:6px;padding-top:6px}.foot{margin-top:26px;text-align:center;font-size:11px;color:#666}@media print{body{padding:0}.wrap{max-width:none}}</style></head><body><div class="wrap"><div class="head"><div class="brand">${escapeHtml(storeName)}<small>Invoice Pesanan</small></div><div class="meta"><div class="no">${invoiceNo}</div><div>Tanggal Pesanan: ${escapeHtml(orderDate)}</div><div>Dicetak: ${escapeHtml(printedAt)}</div></div></div><div class="grid"><div class="card"><h4>Pemesan</h4><p><strong>${escapeHtml(customerName)}</strong></p><p>${escapeHtml(customerPhone)}</p>${customerEmail ? `<p>${escapeHtml(customerEmail)}</p>` : ''}${customerAddress ? `<p style="margin-top:6px;white-space:pre-line">${escapeHtml(customerAddress)}</p>` : ''}${customerNotes ? `<p style="margin-top:6px;font-style:italic;color:#555">Catatan: ${escapeHtml(customerNotes)}</p>` : ''}</div><div class="card"><h4>Pembayaran</h4><p><strong>${escapeHtml(paymentMethodLabel)}</strong></p><p>Status: ${escapeHtml(paymentStatusLabel)}</p>${payment && payment.paid_at ? `<p>Diunggah: ${escapeHtml(new Date(payment.paid_at).toLocaleString('id-ID'))}</p>` : ''}</div></div><table><thead><tr><th>Item</th><th class="qty">Qty</th><th class="right">Subtotal</th></tr></thead><tbody>${itemsRows || '<tr><td colspan="3" style="text-align:center;color:#666;padding:18px">Tidak ada item.</td></tr>'}</tbody></table><div class="total-row"><div class="total"><div class="line"><span>Subtotal</span><span>${formatRupiah(grandTotal)}</span></div><div class="line grand"><span>Total</span><span>${formatRupiah(grandTotal)}</span></div></div></div><div class="foot">Terima kasih telah berbelanja di ${escapeHtml(storeName)}.</div></div><script>window.addEventListener('load',()=>{setTimeout(()=>window.print(),250)});<\/script></body></html>`);
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

            // Driver email + tampilkan kartu yang relevan saja
            const driver = (data.email_driver || 'smtp').toLowerCase();
            const driverLabels = {
                smtp: 'SMTP (PHPMailer)',
                brevo: 'Brevo API',
                mailersend: 'MailerSend API',
                sendpulse: 'SendPulse API'
            };
            setText('disp_email_driver', driverLabels[driver] || driver);
            const smtpCardIds = ['disp_card_email_host', 'disp_card_email_port', 'disp_card_email_user', 'disp_card_email_encryption'];
            smtpCardIds.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.classList.toggle('hidden', driver !== 'smtp');
            });
            const apiCard = document.getElementById('disp_card_email_api');
            const apiStatus = document.getElementById('disp_email_api_status');
            if (apiCard && apiStatus) {
                const apiKeyFlags = {
                    brevo: !!data.email_brevo_api_key_set,
                    mailersend: !!data.email_mailersend_api_key_set,
                    sendpulse: !!data.email_sendpulse_client_secret_set
                };
                if (driver === 'smtp') {
                    apiCard.classList.add('hidden');
                } else {
                    apiCard.classList.remove('hidden');
                    apiStatus.textContent = apiKeyFlags[driver] ? 'Tersimpan' : 'Belum diisi';
                    apiStatus.classList.toggle('muted', !apiKeyFlags[driver]);
                }
            }

            const emailEnabledEl = document.getElementById('disp_email_enabled');
            if (emailEnabledEl) {
                const enabled = Number(data.email_enabled || 0) === 1;
                emailEnabledEl.innerHTML = enabled
                    ? '<span class="px-3 py-1 bg-green-500/15 text-green-400 border border-green-500/30 rounded-full text-xs font-bold">Aktif</span>'
                    : '<span class="px-3 py-1 bg-gray-500/15 text-gray-400 border border-gray-500/30 rounded-full text-xs font-bold">Tidak Aktif</span>';
            }

            // Card pemakaian Brevo hanya muncul saat driver=brevo dan API key
            // sudah terisi. Setiap kali settings di-render ulang, kita evaluasi
            // ulang visibilitas card-nya.
            const brevoCard = document.getElementById('brevo_usage_card');
            if (brevoCard) {
                const driverIsBrevo = driver === 'brevo';
                const hasApiKey = !!data.email_brevo_api_key_set;
                if (driverIsBrevo && hasApiKey) {
                    brevoCard.classList.remove('hidden');
                    loadBrevoUsage(false);
                } else {
                    brevoCard.classList.add('hidden');
                }
            }
        }

        // ==========================================
        // --- BREVO DAILY QUOTA USAGE ---
        // ==========================================
        // Cache singkat supaya tidak hit API Brevo tiap kali settings di-render.
        let brevoUsageCache = { fetchedAt: 0, data: null };
        const BREVO_USAGE_TTL_MS = 60 * 1000; // 1 menit

        async function loadBrevoUsage(force = false) {
            const card = document.getElementById('brevo_usage_card');
            if (!card) return;
            if (card.classList.contains('hidden') && !force) {
                // Card belum kelihatan dan tidak di-force -- tidak perlu fetch.
                return;
            }
            const loadingEl = document.getElementById('brevo_usage_loading');
            const errorEl = document.getElementById('brevo_usage_error');
            const dataEl = document.getElementById('brevo_usage_data');

            // Pakai cache kalau masih segar dan tidak di-force.
            const now = Date.now();
            if (!force && brevoUsageCache.data && (now - brevoUsageCache.fetchedAt) < BREVO_USAGE_TTL_MS) {
                renderBrevoUsage(brevoUsageCache.data);
                return;
            }

            if (loadingEl) loadingEl.classList.remove('hidden');
            if (errorEl) errorEl.classList.add('hidden');
            if (dataEl) dataEl.classList.add('hidden');

            try {
                const res = await fetch(`${BASE_URL}/api/settings/brevo-usage`);
                const result = await res.json();
                if (result.status === 'success' && result.data) {
                    brevoUsageCache = { fetchedAt: now, data: result.data };
                    renderBrevoUsage(result.data);
                } else {
                    showBrevoUsageError(result.message || 'Gagal memuat data Brevo.');
                }
            } catch (e) {
                console.error('Gagal mengambil pemakaian Brevo', e);
                showBrevoUsageError('Gagal menghubungi server.');
            }
        }

        function showBrevoUsageError(message) {
            const loadingEl = document.getElementById('brevo_usage_loading');
            const errorEl = document.getElementById('brevo_usage_error');
            const dataEl = document.getElementById('brevo_usage_data');
            if (loadingEl) loadingEl.classList.add('hidden');
            if (dataEl) dataEl.classList.add('hidden');
            if (errorEl) {
                errorEl.textContent = message;
                errorEl.classList.remove('hidden');
            }
        }

        function renderBrevoUsage(data) {
            const loadingEl = document.getElementById('brevo_usage_loading');
            const errorEl = document.getElementById('brevo_usage_error');
            const dataEl = document.getElementById('brevo_usage_data');
            if (loadingEl) loadingEl.classList.add('hidden');
            if (errorEl) errorEl.classList.add('hidden');
            if (dataEl) dataEl.classList.remove('hidden');

            const setText = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.textContent = val == null || val === '' ? '-' : val;
            };

            setText('brevo_usage_date', data.date || '-');
            const planLabel = (data.plan_name || '').replace(/^./, c => c.toUpperCase()) || '-';
            setText('brevo_usage_plan', planLabel + (data.plan_type === 'free' ? ' (gratis)' : ''));
            setText('brevo_usage_account', data.account_email || '-');
            setText('brevo_usage_used', new Intl.NumberFormat('id-ID').format(data.used_today || 0));
            setText('brevo_usage_delivered', new Intl.NumberFormat('id-ID').format(data.delivered_today || 0));
            setText('brevo_usage_remaining',
                data.remaining_today === null
                    ? 'Tanpa batas harian'
                    : new Intl.NumberFormat('id-ID').format(data.remaining_today) + ' email'
            );

            const progressWrap = document.getElementById('brevo_usage_progress_wrap');
            const bar = document.getElementById('brevo_usage_bar');
            const percentEl = document.getElementById('brevo_usage_percent');
            const limitEl = document.getElementById('brevo_usage_limit');
            if (data.daily_limit && data.daily_limit > 0 && progressWrap) {
                progressWrap.classList.remove('hidden');
                const percent = Math.max(0, Math.min(100, Number(data.percent_used) || 0));
                if (bar) {
                    bar.style.width = percent + '%';
                    // Ubah warna saat mendekati limit supaya admin sadar.
                    bar.classList.remove(
                        'from-emerald-500', 'to-emerald-400',
                        'from-amber-500', 'to-amber-400',
                        'from-red-500', 'to-red-400'
                    );
                    if (percent >= 90) {
                        bar.classList.add('from-red-500', 'to-red-400');
                    } else if (percent >= 70) {
                        bar.classList.add('from-amber-500', 'to-amber-400');
                    } else {
                        bar.classList.add('from-emerald-500', 'to-emerald-400');
                    }
                }
                if (percentEl) percentEl.textContent = percent;
                if (limitEl) limitEl.textContent = new Intl.NumberFormat('id-ID').format(data.daily_limit);
            } else if (progressWrap) {
                // Paket berbayar tanpa limit harian -- sembunyikan progress bar.
                progressWrap.classList.add('hidden');
            }
        }

        /** Tampilkan/sembunyikan field credential di modal Pengaturan Email
         *  berdasarkan driver yang dipilih di dropdown. */
        function updateEmailDriverFields() {
            const select = document.getElementById('set_email_driver');
            if (!select) return;
            const driver = (select.value || 'smtp').toLowerCase();
            const wrappers = {
                smtp: document.getElementById('set_email_driver_smtp'),
                brevo: document.getElementById('set_email_driver_brevo'),
                mailersend: document.getElementById('set_email_driver_mailersend'),
                sendpulse: document.getElementById('set_email_driver_sendpulse')
            };
            Object.entries(wrappers).forEach(([key, el]) => {
                if (!el) return;
                el.classList.toggle('hidden', key !== driver);
            });
            const helps = {
                smtp: 'Pakai SMTP klasik (mis. Gmail App Password). Bisa terblok di hosting tertentu.',
                brevo: 'Kirim via HTTPS ke api.brevo.com — tidak butuh port SMTP.',
                mailersend: 'Kirim via HTTPS ke api.mailersend.com — tidak butuh port SMTP.',
                sendpulse: 'Kirim via HTTPS ke api.sendpulse.com — OAuth2 token diambil otomatis.'
            };
            const helpEl = document.getElementById('set_email_driver_help');
            if (helpEl) helpEl.textContent = helps[driver] || '';
        }

        /** Buka modal Edit Profil Toko dan isi form dari cache */
        function openStoreProfileModal() {
            const d = settingsCache;
            document.getElementById('set_store_name').value = d.store_name || '';
            document.getElementById('set_wa_admin').value = d.whatsapp_admin || '';
            document.getElementById('set_admin_name').value = d.admin_name || '';
            document.getElementById('set_admin_email').value = d.admin_email || '';
            document.getElementById('set_store_address').value = d.store_address_text || '';
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
                admin_name: document.getElementById('set_admin_name').value,
                admin_email: document.getElementById('set_admin_email').value,
                store_address_text: document.getElementById('set_store_address').value,
                // Teruskan nilai email yang belum diubah dari cache. Kolom
                // sensitif (password / API key) dikirim string kosong supaya
                // backend mempertahankan nilai existing-nya.
                email_enabled: settingsCache.email_enabled,
                email_driver: settingsCache.email_driver,
                email_smtp_host: settingsCache.email_smtp_host,
                email_smtp_port: settingsCache.email_smtp_port,
                email_smtp_username: settingsCache.email_smtp_username,
                email_smtp_password: '',
                email_smtp_encryption: settingsCache.email_smtp_encryption,
                email_brevo_api_key: '',
                email_mailersend_api_key: '',
                email_sendpulse_client_id: settingsCache.email_sendpulse_client_id,
                email_sendpulse_client_secret: '',
                email_from_name: settingsCache.email_from_name,
                email_from_address: settingsCache.email_from_address
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

        /** Buka modal Pengaturan Email dan isi form dari cache.
         *  Field credential (password / API key) sengaja dibiarkan kosong
         *  supaya admin tidak salah menimpa nilai yang sudah tersimpan. */
        function openEmailSettingsModal() {
            const d = settingsCache;
            document.getElementById('set_email_enabled').checked = Number(d.email_enabled || 0) === 1;
            document.getElementById('set_email_driver').value = (d.email_driver || 'smtp').toLowerCase();
            // Sender
            document.getElementById('set_email_from_name').value = d.email_from_name || '';
            document.getElementById('set_email_from_address').value = d.email_from_address || '';
            // SMTP
            document.getElementById('set_email_host').value = d.email_smtp_host || '';
            document.getElementById('set_email_port').value = d.email_smtp_port || '';
            document.getElementById('set_email_user').value = d.email_smtp_username || '';
            document.getElementById('set_email_pass').value = '';
            document.getElementById('set_email_pass').placeholder = d.email_smtp_password_set ? '•••••• (tersimpan)' : 'App Password';
            document.getElementById('set_email_encryption').value = d.email_smtp_encryption || 'tls';
            // Brevo
            document.getElementById('set_email_brevo_api_key').value = '';
            document.getElementById('set_email_brevo_api_key').placeholder = d.email_brevo_api_key_set ? '•••••• (tersimpan)' : 'xkeysib-...';
            // MailerSend
            document.getElementById('set_email_mailersend_api_key').value = '';
            document.getElementById('set_email_mailersend_api_key').placeholder = d.email_mailersend_api_key_set ? '•••••• (tersimpan)' : 'mlsn....';
            // SendPulse
            document.getElementById('set_email_sendpulse_client_id').value = d.email_sendpulse_client_id || '';
            document.getElementById('set_email_sendpulse_client_secret').value = '';
            document.getElementById('set_email_sendpulse_client_secret').placeholder = d.email_sendpulse_client_secret_set ? '•••••• (tersimpan)' : 'client_secret...';
            updateEmailDriverFields();
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
                admin_name: settingsCache.admin_name,
                admin_email: settingsCache.admin_email,
                store_address_text: settingsCache.store_address_text,
                email_enabled: document.getElementById('set_email_enabled').checked,
                email_driver: document.getElementById('set_email_driver').value,
                email_from_name: document.getElementById('set_email_from_name').value,
                email_from_address: document.getElementById('set_email_from_address').value,
                email_smtp_host: document.getElementById('set_email_host').value,
                email_smtp_port: document.getElementById('set_email_port').value,
                email_smtp_username: document.getElementById('set_email_user').value,
                email_smtp_password: document.getElementById('set_email_pass').value,
                email_smtp_encryption: document.getElementById('set_email_encryption').value,
                email_brevo_api_key: document.getElementById('set_email_brevo_api_key').value,
                email_mailersend_api_key: document.getElementById('set_email_mailersend_api_key').value,
                email_sendpulse_client_id: document.getElementById('set_email_sendpulse_client_id').value,
                email_sendpulse_client_secret: document.getElementById('set_email_sendpulse_client_secret').value
            };
            showAdminLoader('Menyimpan pengaturan email...', 'Mohon tunggu sebentar.');
            try {
                const res = await fetch(`${BASE_URL}/api/settings`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                const data = await res.json();
                if (data.status === 'success') {
                    showToast('Pengaturan email berhasil disimpan!', 'success');
                    toggleModal('emailSettingsModal', false);
                    document.getElementById('set_email_pass').value = '';
                    document.getElementById('set_email_brevo_api_key').value = '';
                    document.getElementById('set_email_mailersend_api_key').value = '';
                    document.getElementById('set_email_sendpulse_client_secret').value = '';
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
