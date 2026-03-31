<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4 animate-fade-in-up">
    <div>
        <h2 class="text-2xl font-bold text-gray-100 mb-1">Metode Pembayaran</h2>
        <p class="text-gray-400 text-sm">Kelola opsi pembayaran (QRIS, COD, Bank) yang tersedia di toko.</p>
    </div>
    <button onclick="openPaymentMethodModal()" class="bg-gold-500 text-gray-900 px-5 py-2.5 rounded-lg font-bold hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(245,158,11,0.3)] transition flex items-center gap-2 whitespace-nowrap">
        <span class="text-xl leading-none">+</span> Tambah Metode
    </button>
</div>

<div class="bg-dark-surface rounded-xl border border-dark-border overflow-x-auto shadow-md animate-fade-in-up">
    <table class="w-full text-left border-collapse min-w-[700px] whitespace-nowrap">
        <thead>
            <tr class="bg-black/20 text-gray-400 text-sm border-b-2 border-dark-border">
                <th class="p-4 font-semibold">Nama Metode</th>
                <th class="p-4 font-semibold">Tipe</th>
                <th class="p-4 font-semibold">Info / Rekening</th>
                <th class="p-4 font-semibold">Status</th>
                <th class="p-4 font-semibold text-center">Aksi</th>
            </tr>
        </thead>
        <tbody id="payments-table-body">
            <tr>
                <td colspan="5" class="text-center p-10 text-gray-500">Memuat data...</td>
            </tr>
        </tbody>
    </table>
</div>