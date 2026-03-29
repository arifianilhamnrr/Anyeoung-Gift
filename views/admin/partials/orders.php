<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-100 mb-1">Manajemen Pesanan</h2>
        <p class="text-gray-400 text-sm">Pantau pesanan masuk dan perbarui status pengiriman.</p>
    </div>
    <button onclick="simulateNewOrder()"
        class="bg-transparent text-gold-500 border border-dashed border-gold-500 px-4 py-2.5 rounded-lg font-bold text-sm hover:bg-gold-500/10 transition duration-300 whitespace-nowrap">
        🧪 Simulasi Pesanan Masuk
    </button>
</div>

<div class="bg-dark-surface rounded-xl border border-dark-border overflow-x-auto shadow-md">
    <table class="w-full text-left border-collapse min-w-[800px]">
        <thead>
            <tr class="bg-black/20 text-gray-400 text-xs uppercase tracking-wider border-b-2 border-dark-border">
                <th class="p-4 font-semibold">ID Pesanan</th>
                <th class="p-4 font-semibold">Pelanggan</th>
                <th class="p-4 font-semibold">Total Harga</th>
                <th class="p-4 font-semibold">Tanggal</th>
                <th class="p-4 font-semibold">Status</th>
                <th class="p-4 font-semibold">Aksi</th>
            </tr>
        </thead>
        <tbody id="orders-table-body">
            <tr>
                <td colspan="6" class="text-center p-10 text-gray-500">Memuat data pesanan...</td>
            </tr>
        </tbody>
    </table>
</div>