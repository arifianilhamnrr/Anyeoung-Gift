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
