<style>
    /* CSS Minimal untuk Transisi Modal JS */
    .modal-overlay {
        display: none;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .modal-overlay.active {
        display: flex;
        opacity: 1;
        z-index: 1000;
    }

    .modal-content {
        transform: translateY(20px) scale(0.95);
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .modal-overlay.active .modal-content {
        transform: translateY(0) scale(1);
    }
</style>

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-100 mb-1">Katalog Produk</h2>
        <p class="text-gray-400 text-sm">Kelola inventaris, harga dasar, dan opsi kustomisasi produk Anda.</p>
    </div>
    <button onclick="openProductModal()"
        class="bg-gold-500 text-gray-900 px-5 py-2.5 rounded-lg font-bold hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(245,158,11,0.3)] transition-all duration-300 flex items-center gap-2 whitespace-nowrap">
        <span class="text-xl leading-none">+</span> Tambah Produk
    </button>
</div>

<div class="bg-dark-surface rounded-xl border border-dark-border overflow-x-auto shadow-md">
    <table class="w-full text-left border-collapse min-w-[800px]">
        <thead>
            <tr class="bg-black/20 text-gray-400 text-xs uppercase tracking-wider border-b-2 border-dark-border">
                <th class="p-4 font-semibold">Nama Produk</th>
                <th class="p-4 font-semibold">Kategori</th>
                <th class="p-4 font-semibold">Harga Dasar</th>
                <th class="p-4 font-semibold">Status</th>
                <th class="p-4 font-semibold text-center">Aksi</th>
            </tr>
        </thead>
        <tbody id="products-table-body">
            <tr>
                <td colspan="5" class="text-center p-10 text-gray-500">Memuat data produk...</td>
            </tr>
        </tbody>
    </table>
</div>

<div id="productModal" class="modal-overlay fixed inset-0 bg-black/60 backdrop-blur-sm justify-center items-center">
    <div
        class="modal-content bg-dark-surface w-full max-w-2xl max-h-[85vh] overflow-y-auto rounded-2xl p-6 md:p-8 border border-dark-border shadow-2xl m-4 custom-scrollbar">

        <div class="flex justify-between items-center mb-6 pb-4 border-b border-dark-border">
            <h3 class="text-xl font-bold text-gray-100">📦 Tambah Produk Custom</h3>
            <button onclick="closeProductModal()"
                class="bg-dark-hover border border-dark-border text-gray-400 w-8 h-8 rounded-full flex items-center justify-center hover:text-white hover:bg-red-500/20 hover:border-red-500/50 transition duration-300">
                &times;
            </button>
        </div>

        <form id="productForm" onsubmit="submitProductForm(event)">
            <div class="mb-5">
                <label class="block text-sm text-gray-400 font-medium tracking-wide mb-1.5">Nama Produk <span
                        class="text-red-500">*</span></label>
                <input type="text" id="p_name"
                    class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-lg text-sm focus:outline-none focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 transition duration-300"
                    placeholder="Contoh: Buket Mawar Premium" required>
            </div>

            <div class="flex flex-col sm:flex-row gap-5 mb-5">
                <div class="flex-1">
                    <label class="block text-sm text-gray-400 font-medium tracking-wide mb-1.5">Kategori</label>
                    <input type="text" id="p_category"
                        class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-lg text-sm focus:outline-none focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 transition duration-300"
                        placeholder="Contoh: Bouquet">
                </div>
                <div class="flex-1">
                    <label class="block text-sm text-gray-400 font-medium tracking-wide mb-1.5">Harga Dasar (Rp) <span
                            class="text-red-500">*</span></label>
                    <input type="number" id="p_price"
                        class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-lg text-sm focus:outline-none focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 transition duration-300"
                        placeholder="0" required>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm text-gray-400 font-medium tracking-wide mb-1.5">Foto Produk Utama</label>
                <div onclick="document.getElementById('p_image').click()"
                    class="mt-2 relative w-full h-40 border-2 border-dashed border-gold-500/50 rounded-xl bg-gold-500/5 flex flex-col items-center justify-center cursor-pointer overflow-hidden transition-all duration-300 hover:bg-gold-500/10 group">
                    <input type="file" id="p_image" accept="image/jpeg, image/png, image/webp" class="hidden"
                        onchange="previewImage(this)">
                    <img id="imagePreview" src="" alt="Preview"
                        class="hidden w-full h-full object-cover absolute inset-0 z-10">

                    <div id="uploadText" class="text-center z-0 relative">
                        <div class="text-3xl mb-2 transform group-hover:scale-110 transition duration-300">📸</div>
                        <div class="text-gold-500 font-bold text-sm mb-1">Klik untuk unggah foto</div>
                        <div class="text-gray-500 text-xs">Mendukung JPG, PNG, WEBP (Maks. 2MB)</div>
                    </div>
                </div>
            </div>

            <hr class="border-dark-border my-8">

            <div class="flex justify-between items-center mb-5">
                <div>
                    <h4 class="text-gray-100 font-semibold text-base mb-1">Opsi Kustomisasi</h4>
                    <p class="text-gray-500 text-xs m-0">Tambahkan varian seperti Ukuran, Warna Kertas, dll.</p>
                </div>
                <button type="button" onclick="addOptionGroup()"
                    class="border border-gold-500 text-gold-500 px-4 py-2 rounded-md text-sm font-bold hover:bg-gold-500/10 transition duration-300">
                    + Tambah Opsi
                </button>
            </div>

            <div id="optionsContainer" class="flex flex-col gap-4">
            </div>

            <button type="submit" id="btnSaveProduct"
                class="w-full mt-8 bg-gold-500 text-gray-900 py-3.5 rounded-lg font-bold text-base hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(245,158,11,0.3)] transition-all duration-300 flex justify-center items-center gap-2">
                💾 Simpan Produk ke Database
            </button>
        </form>
    </div>
</div>