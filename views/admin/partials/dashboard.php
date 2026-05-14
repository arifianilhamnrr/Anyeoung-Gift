<?php
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
<div class="mb-6 animate-fade-in-up flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-100 mb-1">Overview</h2>
        <p class="text-gray-400 text-sm">Ringkasan performa toko <?= htmlspecialchars(storeNameRaw()); ?>. Pilih bulan untuk rekap pendapatan bulanan.</p>
    </div>
    <div class="flex items-end gap-2">
        <div>
            <label class="block text-[11px] uppercase tracking-wider text-gray-500 mb-1">Bulan</label>
            <select id="dashboardMonthFilter" onchange="loadDashboardData()"
                class="w-full sm:w-40 p-2.5 bg-dark-base border border-dark-border text-gray-200 rounded-lg text-sm focus:border-gold-500 focus:ring-1 outline-none transition">
                <option value="all">Semua</option>
                <?php foreach ($bulanLabels as $idx => $label): $num = $idx + 1; ?>
                    <option value="<?= $num ?>" <?= $num === $currentMonth ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-[11px] uppercase tracking-wider text-gray-500 mb-1">Tahun</label>
            <select id="dashboardYearFilter" onchange="loadDashboardData()"
                class="w-full sm:w-32 p-2.5 bg-dark-base border border-dark-border text-gray-200 rounded-lg text-sm focus:border-gold-500 focus:ring-1 outline-none transition">
                <option value="all">Semua</option>
                <?php foreach ($yearOptions as $y): ?>
                    <option value="<?= $y ?>" <?= $y === $currentYear ? 'selected' : '' ?>><?= $y ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8 animate-fade-in-up">

    <div class="relative bg-dark-surface p-6 rounded-2xl border border-dark-border shadow-sm overflow-hidden">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-gray-400 text-xs font-semibold uppercase tracking-wider">Pendapatan Bulan Ini</h3>
            <div class="w-10 h-10 rounded-xl bg-gold-500/10 border border-gold-500/20 text-gold-500 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-2 0-3 1-3 2s1 2 3 2 3 1 3 2-1 2-3 2m0-8V6m0 12v-2M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
                </svg>
            </div>
        </div>
        <div class="text-3xl font-bold text-gray-100" id="stat-revenue">Rp 0</div>
        <p class="text-xs text-gray-500 mt-1" id="stat-revenue-period">Memuat periode...</p>
    </div>

    <div class="relative bg-dark-surface p-6 rounded-2xl border border-dark-border shadow-sm overflow-hidden">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-gray-400 text-xs font-semibold uppercase tracking-wider">Pesanan Aktif</h3>
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </div>
        </div>
        <div class="text-3xl font-bold text-gray-100" id="stat-active-orders">0</div>
        <p class="text-xs text-gray-500 mt-1">Belum selesai dan belum dibatalkan.</p>
    </div>

    <div class="relative bg-dark-surface p-6 rounded-2xl border border-dark-border shadow-sm overflow-hidden">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-gray-400 text-xs font-semibold uppercase tracking-wider">Menunggu Pembayaran</h3>
            <div class="w-10 h-10 rounded-xl bg-yellow-500/10 border border-yellow-500/20 text-yellow-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
        <div class="text-3xl font-bold text-gray-100" id="stat-pending">0</div>
        <p class="text-xs text-gray-500 mt-1">Perlu konfirmasi/menunggu bukti bayar.</p>
    </div>

</div>

<div class="flex items-center justify-between mb-4 animate-fade-in-up">
    <h3 class="text-lg font-bold text-gray-200">Pesanan Terbaru</h3>
</div>
<div class="bg-dark-surface rounded-2xl border border-dark-border overflow-x-auto shadow-sm animate-fade-in-up">
    <table class="w-full text-left border-collapse min-w-[600px] whitespace-nowrap">
        <thead>
            <tr class="bg-black/20 text-gray-400 text-xs uppercase tracking-wider border-b border-dark-border">
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
