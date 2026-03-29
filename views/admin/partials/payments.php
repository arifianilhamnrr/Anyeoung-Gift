<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-100 mb-1">Verifikasi Pembayaran</h2>
        <p class="text-gray-400 text-sm">Cek mutasi rekening Anda, lalu verifikasi pesanan yang sudah dibayar.</p>
    </div>
    <button onclick="loadPaymentsData()"
        class="bg-dark-hover text-gray-200 border border-dark-border px-4 py-2.5 rounded-lg text-sm hover:bg-dark-border transition duration-300 flex items-center gap-2 whitespace-nowrap">
        🔄 Segarkan Data
    </button>
</div>

<div class="bg-dark-surface rounded-xl border border-dark-border overflow-x-auto shadow-md">
    <table class="w-full text-left border-collapse min-w-[700px] whitespace-nowrap">
        <thead>
            <tr class="bg-black/20 text-gray-400 text-sm border-b-2 border-dark-border">
                <th class="p-4 font-semibold">ID Pesanan</th>
                <th class="p-4 font-semibold">Pelanggan</th>
                <th class="p-4 font-semibold">Tagihan (Rp)</th>
                <th class="p-4 font-semibold">Status Saat Ini</th>
                <th class="p-4 font-semibold">Aksi Verifikasi</th>
            </tr>
        </thead>
        <tbody id="payments-table-body">
            <tr>
                <td colspan="5" class="text-center p-10 text-gray-500">Memuat data tagihan...</td>
            </tr>
        </tbody>
    </table>
</div>