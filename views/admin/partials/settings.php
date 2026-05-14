<div class="animate-fade-in-up max-w-3xl mx-auto space-y-6">
    <div class="mb-2">
        <h2 class="text-2xl font-bold text-gray-100 mb-1">Pengaturan Toko</h2>
        <p class="text-gray-400 text-sm">Informasi toko, notifikasi email, dan keamanan akun admin.</p>
    </div>

    <!-- Card: Profil Toko -->
    <div class="bg-dark-surface rounded-2xl border border-dark-border shadow-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-dark-border">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gold-500/10 border border-gold-500/20 flex items-center justify-center text-gold-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M4 6l1 13h14l1-13M9 10v6m6-6v6M8 6l1-2h6l1 2" />
                    </svg>
                </div>
                <div>
                    <div class="font-bold text-gray-100 text-sm">Profil Toko</div>
                    <div class="text-xs text-gray-500">Nama, WhatsApp, dan pesan default</div>
                </div>
            </div>
            <button onclick="openStoreProfileModal()" class="flex items-center gap-2 text-xs font-bold bg-gold-500/10 text-gold-500 border border-gold-500/30 hover:bg-gold-500 hover:text-gray-900 px-3 py-2 rounded-lg transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75a2.121 2.121 0 113 3L7 19.25l-4 1 1-4L16.5 3.75z"/></svg>
                Ubah
            </button>
        </div>
        <div class="p-6 grid sm:grid-cols-2 gap-4">
            <div class="settings-display-card">
                <div class="label">Nama Toko</div>
                <div class="value" id="disp_store_name"></div>
            </div>
            <div class="settings-display-card">
                <div class="label">WhatsApp Admin</div>
                <div class="value mono" id="disp_wa_admin"></div>
            </div>
            <div class="settings-display-card">
                <div class="label">Nama Admin</div>
                <div class="value" id="disp_admin_name"></div>
            </div>
            <div class="settings-display-card">
                <div class="label">Email Toko</div>
                <div class="value" id="disp_admin_email"></div>
            </div>
            <div class="settings-display-card sm:col-span-2">
                <div class="label">Alamat Toko</div>
                <div class="value text-xs leading-relaxed whitespace-pre-line" id="disp_store_address"></div>
            </div>
            <div class="settings-display-card sm:col-span-2">
                <div class="label">Pesan Default Pembeli</div>
                <div class="value text-xs leading-relaxed whitespace-pre-line" id="disp_wa_template"></div>
            </div>
        </div>
    </div>

    <!-- Card: Notifikasi Email -->
    <div class="bg-dark-surface rounded-2xl border border-dark-border shadow-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-dark-border">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2zm0 0l8 6 8-6" />
                    </svg>
                </div>
                <div>
                    <div class="font-bold text-gray-100 text-sm">Notifikasi Email</div>
                    <div class="text-xs text-gray-500">Konfigurasi SMTP / API untuk email otomatis</div>
                </div>
            </div>
            <button onclick="openEmailSettingsModal()" class="flex items-center gap-2 text-xs font-bold bg-gold-500/10 text-gold-500 border border-gold-500/30 hover:bg-gold-500 hover:text-gray-900 px-3 py-2 rounded-lg transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75a2.121 2.121 0 113 3L7 19.25l-4 1 1-4L16.5 3.75z"/></svg>
                Ubah
            </button>
        </div>
        <div class="p-6 grid sm:grid-cols-2 gap-4">
            <div class="settings-display-card">
                <div class="label">Status</div>
                <div id="disp_email_enabled" class="mt-1">
                    <span class="px-3 py-1 bg-gray-500/15 text-gray-400 border border-gray-500/30 rounded-full text-xs font-bold">Tidak Aktif</span>
                </div>
            </div>
            <div class="settings-display-card">
                <div class="label">Driver</div>
                <div class="value" id="disp_email_driver">SMTP (PHPMailer)</div>
            </div>
            <div class="settings-display-card" id="disp_card_email_host">
                <div class="label">SMTP Host</div>
                <div class="value mono" id="disp_email_host"></div>
            </div>
            <div class="settings-display-card" id="disp_card_email_port">
                <div class="label">SMTP Port</div>
                <div class="value mono" id="disp_email_port"></div>
            </div>
            <div class="settings-display-card" id="disp_card_email_user">
                <div class="label">Username</div>
                <div class="value" id="disp_email_user"></div>
            </div>
            <div class="settings-display-card" id="disp_card_email_encryption">
                <div class="label">Enkripsi</div>
                <div class="value" id="disp_email_encryption"></div>
            </div>
            <div class="settings-display-card hidden" id="disp_card_email_api">
                <div class="label">API Credential</div>
                <div class="value" id="disp_email_api_status">-</div>
            </div>
            <div class="settings-display-card">
                <div class="label">Nama Pengirim</div>
                <div class="value" id="disp_email_from_name"></div>
            </div>
            <div class="settings-display-card">
                <div class="label">Email Pengirim</div>
                <div class="value" id="disp_email_from_address"></div>
            </div>
        </div>
    </div>

    <!-- Card: Keamanan -->
    <div class="bg-dark-surface rounded-2xl border border-dark-border shadow-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-dark-border">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-red-500/10 border border-red-500/20 flex items-center justify-center text-red-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-7-6V9a7 7 0 0114 0v2m-9 4h8a2 2 0 012 2v3a2 2 0 01-2 2H6a2 2 0 01-2-2v-3a2 2 0 012-2z" />
                    </svg>
                </div>
                <div>
                    <div class="font-bold text-gray-100 text-sm">Keamanan Akun</div>
                    <div class="text-xs text-gray-500">Ubah password login admin</div>
                </div>
            </div>
            <button onclick="openAdminPasswordModal()" class="flex items-center gap-2 text-xs font-bold bg-red-500/10 text-red-400 border border-red-500/30 hover:bg-red-500 hover:text-white px-3 py-2 rounded-lg transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75a2.121 2.121 0 113 3L7 19.25l-4 1 1-4L16.5 3.75z"/></svg>
                Ubah Password
            </button>
        </div>
        <div class="p-6">
            <div class="flex items-center gap-4 text-sm text-gray-400">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m0 0v2m0-2h2m-2 0H10m9-7V9a7 7 0 10-14 0v1M5 12h14a1 1 0 011 1v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6a1 1 0 011-1z"/></svg>
                Password disimpan secara aman menggunakan hashing. Gunakan minimal 6 karakter.
            </div>
        </div>
    </div>

    <!-- Info: Aturan Otomasi Pesanan -->
    <div class="bg-dark-surface rounded-2xl border border-dark-border shadow-xl overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-dark-border">
            <div class="w-9 h-9 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2z" />
                </svg>
            </div>
            <div>
                <div class="font-bold text-gray-100 text-sm">Aturan Otomasi Pesanan</div>
                <div class="text-xs text-gray-500">Berjalan otomatis di server (via cron job)</div>
            </div>
        </div>
        <div class="p-6 space-y-3">
            <div class="flex items-start gap-3 text-sm">
                <span class="mt-0.5 w-5 h-5 rounded-full bg-yellow-500/15 text-yellow-500 flex items-center justify-center text-xs shrink-0 font-bold"></span>
                <div><span class="text-gray-200 font-semibold">Auto-cancel pembayaran</span> <span class="text-gray-400"> Pesanan non-Cash on Pick Up yang tidak dibayar dalam <span class="text-yellow-400 font-bold">24 jam</span> akan otomatis dibatalkan.</span></div>
            </div>
            <div class="flex items-start gap-3 text-sm">
                <span class="mt-0.5 w-5 h-5 rounded-full bg-green-500/15 text-green-500 flex items-center justify-center text-xs shrink-0 font-bold"></span>
                <div><span class="text-gray-200 font-semibold">Auto-complete pesanan</span> <span class="text-gray-400"> Pesanan berstatus "Siap Diambil" yang tidak dikonfirmasi pembeli dalam <span class="text-green-400 font-bold">3 hari</span> akan otomatis diselesaikan.</span></div>
            </div>
            <div class="flex items-start gap-3 text-sm">
                <span class="mt-0.5 w-5 h-5 rounded-full bg-blue-500/15 text-blue-400 flex items-center justify-center text-xs shrink-0 font-bold"></span>
                <div><span class="text-gray-200 font-semibold">Pembatalan pesanan</span> <span class="text-gray-400"> Admin hanya dapat membatalkan pesanan yang belum dibayar (status <span class="text-blue-400 font-mono text-xs">pending</span> atau <span class="text-blue-400 font-mono text-xs">waiting_payment</span>).</span></div>
            </div>
        </div>
    </div>
</div>
