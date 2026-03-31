<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-100 mb-1">Overview</h2>
    <p class="text-gray-400 text-sm">Ringkasan performa toko Anyeong Gift hari ini.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
    
    <div class="bg-dark-surface p-6 rounded-xl border border-dark-border shadow-sm flex flex-col">
        <h3 class="text-gray-400 text-sm font-medium mb-3">Total Pendapatan (Bulan Ini)</h3>
        <div class="text-3xl font-bold text-gray-100 mt-auto" id="stat-revenue">Rp 0</div>
    </div>
    
    <div class="bg-dark-surface p-6 rounded-xl border border-dark-border shadow-sm flex flex-col">
        <h3 class="text-gray-400 text-sm font-medium mb-3">Pesanan Aktif</h3>
        <div class="text-3xl font-bold text-gray-100 mt-auto" id="stat-active-orders">0</div>
    </div>
    
    <div class="bg-dark-surface p-6 rounded-xl border border-dark-border shadow-sm flex flex-col">
        <h3 class="text-gray-400 text-sm font-medium mb-3">Menunggu Pembayaran</h3>
        <div class="text-3xl font-bold text-gray-100 mt-auto" id="stat-pending">0</div>
    </div>
    
</div>

<h3 class="text-lg font-bold text-gray-200 mb-4">Pesanan Terbaru</h3>
<div class="bg-dark-surface rounded-xl border border-dark-border overflow-x-auto shadow-sm">
    <table class="w-full text-left border-collapse min-w-[600px] whitespace-nowrap">
        <thead>
            <tr class="bg-black/20 text-gray-400 text-sm border-b border-dark-border">
                <th class="p-4 font-semibold">Order ID</th>
                <th class="p-4 font-semibold">Pelanggan</th>
                <th class="p-4 font-semibold">Total</th>
                <th class="p-4 font-semibold">Status</th>
            </tr>
        </thead>
        <tbody id="recent-orders-table">
            <tr>
                <td colspan="4" class="text-center p-8 text-gray-500">Memuat data...</td>
            </tr>
        </tbody>
    </table>
</div>