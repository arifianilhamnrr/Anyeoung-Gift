    <!-- Order Detail Modal -->
    <div id="orderDetailModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[1000] hidden justify-center items-center opacity-0 transition-opacity duration-300">
        <div
            class="bg-dark-surface w-full max-w-3xl max-h-[85vh] overflow-y-auto rounded-2xl p-6 md:p-8 border border-dark-border shadow-2xl m-4 transform scale-95 transition-transform duration-300 custom-scrollbar">
            <div class="modal-header flex justify-between items-center gap-3">
                <div class="min-w-0">
                    <h3 class="text-xl font-bold text-gray-100 flex items-center gap-2">Detail <span
                            id="detail-order-id"
                            class="text-gold-500 bg-gold-500/10 px-2 py-1 rounded-md text-sm"></span></h3>
                    <p id="detail-customer-info" class="text-gray-400 text-sm mt-1"></p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button onclick="printInvoice()" id="btnPrintInvoice"
                        class="hidden sm:inline-flex items-center gap-2 bg-gold-500/10 text-gold-500 border border-gold-500/30 hover:bg-gold-500 hover:text-gray-900 px-3 py-2 rounded-lg text-xs font-bold transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6 9V4h12v5M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v7H6z" />
                        </svg>
                        Cetak Invoice
                    </button>
                    <button onclick="printInvoice()"
                        class="sm:hidden bg-gold-500/10 text-gold-500 border border-gold-500/30 hover:bg-gold-500 hover:text-gray-900 w-9 h-9 rounded-lg flex justify-center items-center transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6 9V4h12v5M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2h-2M6 14h12v7H6z" />
                        </svg>
                    </button>
                    <button onclick="closeOrderDetailModal()"
                        class="bg-dark-hover border border-dark-border text-gray-400 w-9 h-9 rounded-full hover:text-white hover:bg-red-500/20 transition flex justify-center items-center">&times;</button>
                </div>
            </div>
            <div id="order-detail-content" class="text-gray-300 space-y-4"></div>
        </div>
    </div>

    <!-- Product Detail Modal -->
    <div id="productDetailModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[1000] hidden justify-center items-center opacity-0 transition-opacity duration-300">
        <div
            class="bg-dark-surface w-full max-w-2xl max-h-[85vh] overflow-y-auto rounded-2xl p-6 md:p-8 border border-dark-border shadow-2xl m-4 transform scale-95 transition-transform duration-300 custom-scrollbar">
            <div class="modal-header flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-100 flex items-center gap-2"> Detail Produk</h3>
                <button onclick="closeProductDetailModal()"
                    class="text-gray-400 hover:text-white w-8 h-8 rounded-full hover:bg-red-500/20 transition">&times;</button>
            </div>
            <div id="product-detail-content" class="text-gray-300"></div>
        </div>
    </div>

    <!-- Payment Confirmation Modal -->
    <div id="paymentModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[1000] hidden justify-center items-center opacity-0 transition-opacity duration-300">
        <div
            class="bg-dark-surface w-full max-w-sm rounded-2xl p-6 md:p-8 border border-dark-border shadow-2xl m-4 transform scale-95 transition-transform duration-300 relative">
            <div class="modal-header flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-100 flex items-center gap-2"> Pembayaran</h3>
                <button onclick="closePaymentModal()"
                    class="text-gray-400 hover:text-white w-8 h-8 rounded-full hover:bg-red-500/20 transition">&times;</button>
            </div>
            <div class="mb-6 space-y-4">
                <div>
                    <p class="text-xs text-gray-500 mb-1 uppercase">Pelanggan</p>
                    <div id="pay-customer-name" class="font-bold text-gray-200 text-lg"></div>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1 uppercase">Total Tagihan</p>
                    <div id="pay-total-amount" class="font-bold text-gold-500 text-3xl"></div>
                </div>
            </div>
            <div class="mb-8">
                <p class="text-xs text-gray-500 mb-3 uppercase">Metode Pembayaran</p>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer group"><input type="radio" name="payment_method" value="qris"
                            class="peer sr-only" checked>
                        <div
                            class="text-center p-3 rounded-xl border border-dark-border bg-dark-base peer-checked:border-gold-500 peer-checked:bg-gold-500/10 peer-checked:text-gold-500 transition font-bold">
                             QRIS</div>
                    </label>
                    <label class="cursor-pointer group"><input type="radio" name="payment_method" value="cod"
                            class="peer sr-only">
                        <div
                            class="text-center p-3 rounded-xl border border-dark-border bg-dark-base peer-checked:border-gold-500 peer-checked:bg-gold-500/10 peer-checked:text-gold-500 transition font-bold">
                            Cash on Pick Up</div>
                    </label>
                </div>
            </div>
            <button onclick="submitPaymentConfirmation()" id="btnConfirmPayment"
                class="w-full bg-gold-500 text-gray-900 font-bold py-3.5 rounded-xl hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(245,158,11,0.3)] transition">
                Konfirmasi Lunas</button>
        </div>
    </div>

    <!-- Payment Method Modal -->
    <div id="paymentMethodModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[1000] hidden justify-center items-center opacity-0 transition-opacity duration-300">
        <div
            class="bg-dark-surface w-full max-w-md rounded-2xl p-6 md:p-8 border border-dark-border shadow-2xl m-4 transform scale-95 transition-transform duration-300 relative">
            <div class="modal-header flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-100"> Tambah Metode</h3>
                <button onclick="closePaymentMethodModal()"
                    class="text-gray-400 hover:text-white w-8 h-8 rounded-full hover:bg-red-500/20 transition">&times;</button>
            </div>
            <form id="paymentMethodForm" onsubmit="submitPaymentMethod(event)">
                <div class="space-y-4">
                    <div><label class="block text-sm text-gray-400 font-medium mb-1.5">Nama Metode (Cth: BCA)</label>
                        <input type="text" id="pm_name"
                            class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 font-medium mb-1.5">Tipe Pembayaran</label>
                        <select id="pm_type" onchange="togglePaymentQrisField()"
                            class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition"
                            required>
                            <option value="transfer">Transfer Bank</option>
                            <option value="qris">QRIS</option>
                            <option value="ewallet">E-Wallet (OVO/Dana/dll)</option>
                            <option value="onsite">Cash on Pick Up</option>
                        </select>
                    </div>
                    <div><label class="block text-sm text-gray-400 font-medium mb-1.5">Info / No. Rekening</label>
                        <input type="text" id="pm_info"
                            class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition"
                            placeholder="Cth: 1234567890 a/n Budi">
                    </div>
                    <div id="pm_qris_wrapper" class="hidden space-y-2">
                        <label class="block text-sm text-gray-400 font-medium mb-1.5">Gambar QRIS</label>
                        <label for="pm_image"
                            class="flex items-center justify-center gap-2 p-3 bg-dark-base border border-dashed border-dark-border text-gray-300 rounded-xl text-sm cursor-pointer hover:border-gold-500 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            <span id="pm_image_label">Pilih gambar QRIS (JPG/PNG)</span>
                        </label>
                        <input type="file" id="pm_image" accept="image/*" class="hidden"
                            onchange="onQrisImageSelected(this)">
                        <div id="pm_image_preview_wrapper" class="hidden">
                            <img id="pm_image_preview" alt="Pratinjau QRIS"
                                class="w-full max-h-48 object-contain rounded-lg border border-dark-border bg-dark-base p-2">
                        </div>
                    </div>
                </div>
                <button type="submit" id="btnSavePaymentMethod"
                    class="w-full mt-8 bg-gold-500 text-gray-900 font-bold py-3.5 rounded-xl hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(245,158,11,0.3)] transition">
                    Simpan Metode</button>
            </form>
        </div>
    </div>

    <!-- ===== SETTINGS MODALS ===== -->

    <!-- Settings: Edit Profil Toko Modal -->
    <!-- Layout grid disamakan dengan card "Profil Toko" pada views/admin/partials/settings.php
         supaya tata letak input mengikuti tampilan tabel kartu (2 kolom dengan kolom penuh
         untuk Alamat & Pesan Default). -->
    <div id="storeProfileModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[1000] hidden justify-center items-center opacity-0 transition-opacity duration-300">
        <div
            class="bg-dark-surface w-full max-w-2xl max-h-[85vh] overflow-y-auto rounded-2xl p-6 md:p-8 border border-dark-border shadow-2xl m-4 transform scale-95 transition-transform duration-300 custom-scrollbar relative">
            <div class="modal-header flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-100"> Edit Profil Toko</h3>
                <button onclick="toggleModal('storeProfileModal', false)"
                    class="text-gray-400 hover:text-white w-8 h-8 rounded-full hover:bg-red-500/20 transition">&times;</button>
            </div>
            <form id="storeProfileForm" onsubmit="submitStoreProfileForm(event)">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="settings-display-card">
                        <label class="label" for="set_store_name">Nama Toko</label>
                        <input type="text" id="set_store_name"
                            class="w-full mt-1 p-2.5 bg-dark-base border border-dark-border text-gray-200 rounded-lg text-sm focus:border-gold-500 focus:ring-1 outline-none transition"
                            required>
                    </div>
                    <div class="settings-display-card">
                        <label class="label" for="set_wa_admin">WhatsApp Admin</label>
                        <input type="number" id="set_wa_admin"
                            class="w-full mt-1 p-2.5 bg-dark-base border border-dark-border text-gray-200 rounded-lg text-sm focus:border-gold-500 focus:ring-1 outline-none transition mono"
                            required>
                    </div>
                    <div class="settings-display-card">
                        <label class="label" for="set_admin_name">Nama Admin</label>
                        <input type="text" id="set_admin_name"
                            class="w-full mt-1 p-2.5 bg-dark-base border border-dark-border text-gray-200 rounded-lg text-sm focus:border-gold-500 focus:ring-1 outline-none transition">
                    </div>
                    <div class="settings-display-card">
                        <label class="label" for="set_admin_email">Email Toko</label>
                        <input type="email" id="set_admin_email"
                            class="w-full mt-1 p-2.5 bg-dark-base border border-dark-border text-gray-200 rounded-lg text-sm focus:border-gold-500 focus:ring-1 outline-none transition"
                            placeholder="email@domainmu.com">
                    </div>
                    <div class="settings-display-card sm:col-span-2">
                        <label class="label" for="set_store_address">Alamat Toko</label>
                        <textarea id="set_store_address" rows="3"
                            class="w-full mt-1 p-2.5 bg-dark-base border border-dark-border text-gray-200 rounded-lg text-sm focus:border-gold-500 focus:ring-1 outline-none transition custom-scrollbar"
                            placeholder="Jalan, kota, provinsi, kode pos"></textarea>
                    </div>
                    <div class="settings-display-card sm:col-span-2">
                        <label class="label" for="set_wa_template">Pesan Default Pembeli</label>
                        <textarea id="set_wa_template" rows="4"
                            class="w-full mt-1 p-2.5 bg-dark-base border border-dark-border text-gray-200 rounded-lg text-sm focus:border-gold-500 focus:ring-1 outline-none transition custom-scrollbar"></textarea>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="toggleModal('storeProfileModal', false)"
                        class="flex-1 py-3 rounded-xl border border-dark-border text-gray-400 hover:text-gray-200 hover:border-gray-500 transition font-semibold text-sm">Batal</button>
                    <button type="submit" id="btnSaveStoreProfile"
                        class="flex-1 bg-gold-500 text-gray-900 font-bold py-3 rounded-xl hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(245,158,11,0.3)] transition">
                        Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Settings: Edit Notifikasi Email Modal -->
    <div id="emailSettingsModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[1000] hidden justify-center items-center opacity-0 transition-opacity duration-300">
        <div
            class="bg-dark-surface w-full max-w-lg max-h-[85vh] overflow-y-auto rounded-2xl p-6 md:p-8 border border-dark-border shadow-2xl m-4 transform scale-95 transition-transform duration-300 custom-scrollbar relative">
            <div class="modal-header flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-100"> Pengaturan Email</h3>
                <button onclick="toggleModal('emailSettingsModal', false)"
                    class="text-gray-400 hover:text-white w-8 h-8 rounded-full hover:bg-red-500/20 transition">&times;</button>
            </div>
            <form id="emailSettingsForm" onsubmit="submitEmailSettingsForm(event)">
                <div class="space-y-4">
                    <p class="text-xs text-gray-500">Pilih driver email yang tersedia. Driver API (Brevo, MailerSend, SendPulse)
                        berguna sebagai alternatif kalau port SMTP diblokir penyedia hosting.</p>
                    <label
                        class="flex items-center justify-between bg-dark-base border border-dark-border rounded-xl px-4 py-3 text-sm cursor-pointer">
                        <span class="text-gray-300 font-medium">Aktifkan email notifikasi</span>
                        <input type="checkbox" id="set_email_enabled" class="w-4 h-4 accent-gold-500">
                    </label>

                    <!-- Dropdown driver email. Field di bawahnya akan berubah
                         menyesuaikan driver yang dipilih. -->
                    <div>
                        <label class="block text-sm text-gray-400 font-medium mb-1.5">Driver Email</label>
                        <select id="set_email_driver" onchange="updateEmailDriverFields()"
                            class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition">
                            <option value="smtp">SMTP (PHPMailer)</option>
                            <option value="brevo">Brevo API</option>
                            <option value="mailersend">MailerSend API</option>
                            <option value="sendpulse">SendPulse API</option>
                        </select>
                        <p id="set_email_driver_help" class="text-xs text-gray-500 mt-1.5"></p>
                    </div>

                    <!-- Field umum yang dipakai semua driver -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm text-gray-400 font-medium mb-1.5">Nama Pengirim</label>
                            <input type="text" id="set_email_from_name" placeholder="<?= htmlspecialchars(storeNameRaw()); ?>"
                                class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 font-medium mb-1.5">Email Pengirim</label>
                            <input type="email" id="set_email_from_address" placeholder="email@domainmu.com"
                                class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition">
                        </div>
                    </div>

                    <!-- Field khusus SMTP -->
                    <div id="set_email_driver_smtp" class="space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm text-gray-400 font-medium mb-1.5">SMTP Host</label>
                                <input type="text" id="set_email_host" placeholder="smtp.gmail.com"
                                    class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 font-medium mb-1.5">SMTP Port</label>
                                <input type="number" id="set_email_port" placeholder="587"
                                    class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 font-medium mb-1.5">SMTP Username</label>
                                <input type="email" id="set_email_user" placeholder="email@gmail.com"
                                    class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 font-medium mb-1.5">SMTP Password</label>
                                <input type="password" id="set_email_pass" placeholder="App Password"
                                    class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm text-gray-400 font-medium mb-1.5">Enkripsi</label>
                                <select id="set_email_encryption"
                                    class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition">
                                    <option value="tls">TLS (Recommended)</option>
                                    <option value="ssl">SSL</option>
                                </select>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500">Kosongkan password jika tidak ingin mengubah kredensial SMTP.</p>
                    </div>

                    <!-- Field khusus Brevo -->
                    <div id="set_email_driver_brevo" class="space-y-4 hidden">
                        <div>
                            <label class="block text-sm text-gray-400 font-medium mb-1.5">Brevo API Key</label>
                            <input type="password" id="set_email_brevo_api_key" placeholder="xkeysib-..."
                                autocomplete="new-password"
                                class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition">
                            <p id="set_email_brevo_api_key_hint" class="text-xs text-gray-500 mt-1.5">
                                Ambil di Brevo Dashboard → SMTP &amp; API → API Keys. Pastikan domain pengirim sudah diverifikasi.
                            </p>
                        </div>
                    </div>

                    <!-- Field khusus MailerSend -->
                    <div id="set_email_driver_mailersend" class="space-y-4 hidden">
                        <div>
                            <label class="block text-sm text-gray-400 font-medium mb-1.5">MailerSend API Token</label>
                            <input type="password" id="set_email_mailersend_api_key" placeholder="mlsn...."
                                autocomplete="new-password"
                                class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition">
                            <p id="set_email_mailersend_api_key_hint" class="text-xs text-gray-500 mt-1.5">
                                Ambil di MailerSend Dashboard → API Tokens. Pakai domain yang sudah di-verify di MailerSend.
                            </p>
                        </div>
                    </div>

                    <!-- Field khusus SendPulse -->
                    <div id="set_email_driver_sendpulse" class="space-y-4 hidden">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm text-gray-400 font-medium mb-1.5">SendPulse Client ID</label>
                                <input type="text" id="set_email_sendpulse_client_id" placeholder="client_id..."
                                    autocomplete="off"
                                    class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 font-medium mb-1.5">SendPulse Client Secret</label>
                                <input type="password" id="set_email_sendpulse_client_secret" placeholder="client_secret..."
                                    autocomplete="new-password"
                                    class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition">
                            </div>
                        </div>
                        <p id="set_email_sendpulse_hint" class="text-xs text-gray-500">
                            Ambil di SendPulse Dashboard → Settings → API. Sender email harus di-verify di SendPulse SMTP.
                        </p>
                    </div>

                    <p class="text-xs text-gray-500">Kosongkan API key / secret jika tidak ingin mengubah kredensial yang sudah tersimpan.</p>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="toggleModal('emailSettingsModal', false)"
                        class="flex-1 py-3 rounded-xl border border-dark-border text-gray-400 hover:text-gray-200 hover:border-gray-500 transition font-semibold text-sm">Batal</button>
                    <button type="submit" id="btnSaveEmailSettings"
                        class="flex-1 bg-gold-500 text-gray-900 font-bold py-3 rounded-xl hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(245,158,11,0.3)] transition">
                        Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Settings: Ubah Password Admin Modal -->
    <div id="adminPasswordModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[1000] hidden justify-center items-center opacity-0 transition-opacity duration-300">
        <div
            class="bg-dark-surface w-full max-w-md rounded-2xl p-6 md:p-8 border border-dark-border shadow-2xl m-4 transform scale-95 transition-transform duration-300 relative">
            <div class="modal-header flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-100"> Ubah Password</h3>
                <button onclick="toggleModal('adminPasswordModal', false)"
                    class="text-gray-400 hover:text-white w-8 h-8 rounded-full hover:bg-red-500/20 transition">&times;</button>
            </div>
            <form id="adminPasswordForm" onsubmit="submitAdminPasswordForm(event)">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-400 font-medium mb-1.5">Password Lama</label>
                        <input type="password" id="admin_current_password"
                            class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 font-medium mb-1.5">Password Baru</label>
                        <input type="password" id="admin_new_password"
                            class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition"
                            minlength="6" required>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 font-medium mb-1.5">Konfirmasi Password Baru</label>
                        <input type="password" id="admin_confirm_password"
                            class="w-full p-3 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:border-gold-500 focus:ring-1 outline-none transition"
                            minlength="6" required>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="toggleModal('adminPasswordModal', false)"
                        class="flex-1 py-3 rounded-xl border border-dark-border text-gray-400 hover:text-gray-200 hover:border-gray-500 transition font-semibold text-sm">Batal</button>
                    <button type="submit" id="btnAdminPassword"
                        class="flex-1 bg-gold-500 text-gray-900 font-bold py-3 rounded-xl hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(245,158,11,0.3)] transition">
                        Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reusable Confirmation Modal (admin) -->
    <!-- Dipakai oleh helper showConfirmModal() di admin-scripts.php untuk
         menggantikan semua confirm() bawaan browser. Komponen ini dibagi
         dua varian (warning kuning / danger merah) lewat data-tone. -->
    <div id="adminConfirmModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[2000] hidden justify-center items-center opacity-0 transition-opacity duration-300">
        <div
            class="bg-dark-surface w-full max-w-sm rounded-2xl p-6 md:p-7 border border-dark-border shadow-2xl m-4 transform scale-95 transition-transform duration-300 relative">
            <div class="text-center">
                <div id="adminConfirmIconWrap"
                    class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3 border bg-yellow-500/15 text-yellow-400 border-yellow-500/30">
                    <svg id="adminConfirmIcon" class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 id="adminConfirmTitle" class="text-lg font-bold text-gray-100 mb-1">Konfirmasi</h3>
                <p id="adminConfirmMessage" class="text-sm text-gray-400 leading-relaxed">Yakin ingin melanjutkan?</p>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" id="adminConfirmCancelBtn"
                    class="flex-1 py-2.5 rounded-xl border border-dark-border text-gray-300 hover:text-gray-100 hover:border-gray-500 transition font-semibold text-sm">Batal</button>
                <button type="button" id="adminConfirmOkBtn"
                    class="flex-1 py-2.5 rounded-xl bg-gold-500 text-gray-900 font-bold text-sm hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(245,158,11,0.3)] transition">OK</button>
            </div>
        </div>
    </div>
