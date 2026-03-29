<nav class="fixed top-0 left-0 w-full z-50 bg-black/40 backdrop-blur-md border-b border-gold/20 shadow-lg">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">

        <div class="text-2xl font-title text-gold drop-shadow-sm">
            Anyeoung Gift
        </div>

        <div class="hidden md:flex space-x-8 text-sm font-medium">
            <a href="index.php?page=home" class="text-gray-200 hover:text-gold transition">Home</a>
            <a href="index.php?page=cart" class="text-gray-200 hover:text-gold transition">Keranjang</a>
            <a href="index.php?page=orders" class="text-gray-200 hover:text-gold transition">Pesanan</a>
            <a href="index.php?page=profile" class="text-gray-200 hover:text-gold transition">Akun</a>
        </div>

        <button id="menuBtn" class="md:hidden text-gold text-2xl focus:outline-none">
            ☰
        </button>
    </div>
</nav>

<div class="h-20"></div>

<div id="mobileMenuWrapper" class="fixed inset-0 z-[60] hidden">

    <div id="menuOverlay" class="absolute inset-0 bg-black/20 backdrop-blur-sm transition-opacity"></div>

    <div id="menuContent"
        class="absolute right-0 top-0 h-full w-64 bg-black/50 backdrop-blur-xl border-l border-gold/20 shadow-2xl transform translate-x-full transition-transform duration-300">

        <div class="p-6">
            <div class="flex justify-between items-center mb-8 border-b border-gold/20 pb-4">
                <span class="text-gold font-title text-lg">Menu</span>
                <button id="closeMenu" class="text-white hover:text-gold transition text-2xl">✕</button>
            </div>

            <nav class="space-y-4">
                <a href="index.php?page=home"
                    class="block text-gray-100 hover:text-gold hover:translate-x-2 transition duration-300">Home</a>
                <a href="index.php?page=cart"
                    class="block text-gray-100 hover:text-gold hover:translate-x-2 transition duration-300">Keranjang</a>
                <a href="index.php?page=orders"
                    class="block text-gray-100 hover:text-gold hover:translate-x-2 transition duration-300">Pesanan</a>
                <a href="index.php?page=profile"
                    class="block text-gray-100 hover:text-gold hover:translate-x-2 transition duration-300">Akun</a>
                <a href="actions/logout.php"
                    class="block text-red-700 hover:text-gold hover:translate-x-2 transition duration-300">Keluar</a>
            </nav>
        </div>

    </div>
</div>

<script>
    const menuBtn = document.getElementById('menuBtn');
    const closeMenu = document.getElementById('closeMenu');
    const mobileMenuWrapper = document.getElementById('mobileMenuWrapper');
    const menuContent = document.getElementById('menuContent');
    const menuOverlay = document.getElementById('menuOverlay');

    // Fungsi Buka Menu
    menuBtn.onclick = function () {
        // 1. Tampilkan wrapper
        mobileMenuWrapper.classList.remove('hidden');

        // 2. Tunggu sebentar (10ms) biar animasi slide bisa jalan
        setTimeout(() => {
            menuContent.classList.remove('translate-x-full');
        }, 10);
    }

    // Fungsi Tutup Menu
    function closeTheMenu() {
        // 1. Geser menu ke kanan dulu
        menuContent.classList.add('translate-x-full');

        // 2. Tunggu animasi selesai (300ms), baru sembunyikan wrapper
        setTimeout(() => {
            mobileMenuWrapper.classList.add('hidden');
        }, 300);
    }

    closeMenu.onclick = closeTheMenu;   // Tutup pas klik X
    menuOverlay.onclick = closeTheMenu; // Tutup pas klik area kosong
</script>