<?php
// Daftar tahun yang ditawarkan di filter: tahun saat ini ke 5 tahun ke
// belakang. Cukup luas untuk audit/rekap, dan tidak terlalu panjang.
$now = new DateTime('now');
$currentMonth = (int) $now->format('n');
$currentYear = (int) $now->format('Y');
$yearOptions = [];
for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
    $yearOptions[] = $y;
}
$bulanLabels = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
?>
<div class="flex flex-col gap-4 mb-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-100 mb-1">Manajemen Pesanan</h2>
            <p class="text-gray-400 text-sm">Pantau pesanan masuk dan perbarui status pengiriman.</p>
        </div>
    </div>

    <div class="bg-dark-surface border border-dark-border rounded-2xl p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="sm:col-span-2 lg:col-span-2">
            <label class="block text-[11px] uppercase tracking-wider text-gray-500 mb-1">Cari Pesanan</label>
            <input type="text" id="orderSearchInput" oninput="onOrdersFilterChange()"
                placeholder="ID, nama pelanggan, atau metode pembayaran"
                class="w-full p-2.5 bg-dark-base border border-dark-border text-gray-200 rounded-lg text-sm focus:border-gold-500 focus:ring-1 outline-none transition" />
        </div>
        <div>
            <label class="block text-[11px] uppercase tracking-wider text-gray-500 mb-1">Status</label>
            <select id="orderStatusFilter" onchange="onOrdersFilterChange()"
                class="w-full p-2.5 bg-dark-base border border-dark-border text-gray-200 rounded-lg text-sm focus:border-gold-500 focus:ring-1 outline-none transition">
                <option value="all">Semua Status</option>
                <option value="waiting_payment">Belum Dibayar</option>
                <option value="paid">Sudah Dibayar</option>
                <option value="ready_pickup">Siap Diambil</option>
                <option value="completed">Selesai</option>
                <option value="cancelled">Dibatalkan</option>
            </select>
        </div>
        <div>
            <label class="block text-[11px] uppercase tracking-wider text-gray-500 mb-1">Metode Pembayaran</label>
            <select id="orderPaymentMethodFilter" onchange="onOrdersFilterChange()"
                class="w-full p-2.5 bg-dark-base border border-dark-border text-gray-200 rounded-lg text-sm focus:border-gold-500 focus:ring-1 outline-none transition">
                <option value="all">Semua Metode</option>
                <option value="onsite">Cash on Pick Up</option>
            </select>
        </div>
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="block text-[11px] uppercase tracking-wider text-gray-500 mb-1">Bulan</label>
                <select id="orderMonthFilter" onchange="onOrdersFilterChange()"
                    class="w-full p-2.5 bg-dark-base border border-dark-border text-gray-200 rounded-lg text-sm focus:border-gold-500 focus:ring-1 outline-none transition">
                    <option value="all">Semua</option>
                    <?php foreach ($bulanLabels as $idx => $label): $num = $idx + 1; ?>
                        <option value="<?= $num ?>" <?= $num === $currentMonth ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-[11px] uppercase tracking-wider text-gray-500 mb-1">Tahun</label>
                <select id="orderYearFilter" onchange="onOrdersFilterChange()"
                    class="w-full p-2.5 bg-dark-base border border-dark-border text-gray-200 rounded-lg text-sm focus:border-gold-500 focus:ring-1 outline-none transition">
                    <option value="all">Semua</option>
                    <?php foreach ($yearOptions as $y): ?>
                        <option value="<?= $y ?>" <?= $y === $currentYear ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="bg-dark-surface rounded-xl border border-dark-border overflow-x-auto shadow-md">
    <table class="w-full text-left border-collapse min-w-[900px]">
        <thead>
            <tr class="bg-black/20 text-gray-400 text-xs uppercase tracking-wider border-b-2 border-dark-border">
                <th class="p-4 font-semibold">ID Pesanan</th>
                <th class="p-4 font-semibold">Pelanggan</th>
                <th class="p-4 font-semibold">Total Harga</th>
                <th class="p-4 font-semibold">Metode</th>
                <th class="p-4 font-semibold">Tanggal</th>
                <th class="p-4 font-semibold">Status</th>
                <th class="p-4 font-semibold">Aksi</th>
            </tr>
        </thead>
        <tbody id="orders-table-body">
            <tr>
                <td colspan="7" class="text-center p-10 text-gray-500">Memuat data pesanan...</td>
            </tr>
        </tbody>
    </table>
</div>

<div id="orders-pagination" class="mt-4"></div>
