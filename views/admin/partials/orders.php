<div class="flex flex-col gap-4 mb-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-100 mb-1">Manajemen Pesanan</h2>
            <p class="text-gray-400 text-sm">Pantau pesanan masuk dan perbarui status pengiriman.</p>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
        <label class="text-xs uppercase tracking-wider text-gray-500 sm:w-32 shrink-0">Filter Status</label>
        <select id="orderStatusFilter" onchange="renderOrdersTable()"
            class="w-full sm:max-w-xs p-2.5 bg-dark-base border border-dark-border text-gray-200 rounded-lg text-sm focus:border-gold-500 focus:ring-1 outline-none transition">
            <option value="all">Semua Status</option>
            <option value="waiting_payment">Belum Dibayar</option>
            <option value="paid">Sudah Dibayar</option>
            <option value="ready_pickup">Siap Diambil</option>
            <option value="completed">Selesai</option>
            <option value="cancelled">Dibatalkan</option>
        </select>
    </div>
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

<div id="orders-pagination" class="mt-4"></div>
