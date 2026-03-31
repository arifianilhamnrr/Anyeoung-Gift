<?php
// Ambil nama halaman aktif untuk memberikan warna gold pada menu
$currentPage = $_GET['page'] ?? 'home';
?>

<nav class="fixed top-0 left-0 w-full z-[90] bg-black/60 backdrop-blur-xl border-b border-gold/20 shadow-lg">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">

        <a href="index.php?page=home"
            class="text-2xl font-title text-gold drop-shadow-sm hover:scale-105 transition-transform">
            Anyeoung Gift
        </a>

        <div class="hidden md:flex items-center space-x-8 text-sm font-medium">
            <a href="index.php?page=home"
                class="<?= $currentPage === 'home' ? 'text-gold font-bold' : 'text-gray-200 hover:text-gold' ?> transition">Home</a>
            <a href="index.php?page=cart"
                class="<?= $currentPage === 'cart' ? 'text-gold font-bold' : 'text-gray-200 hover:text-gold' ?> transition">Keranjang</a>
            <a href="index.php?page=orders"
                class="<?= $currentPage === 'orders' ? 'text-gold font-bold' : 'text-gray-200 hover:text-gold' ?> transition">Pesanan</a>
            <a href="index.php?page=profile"
                class="<?= $currentPage === 'profile' ? 'text-gold font-bold' : 'text-gray-200 hover:text-gold' ?> transition">Akun</a>

            <div class="w-px h-4 bg-white/20"></div>

            <a href="actions/logout.php" class="text-red-500 hover:text-red-400 font-bold transition">Keluar</a>
        </div>

        <button id="menuBtn" class="md:hidden text-gold focus:outline-none hover:text-yellow-400 transition-colors p-1">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                </path>
            </svg>
        </button>
    </div>
</nav>

<div class="h-20"></div>

<div id="mobileMenuWrapper" class="fixed inset-0 z-[100] hidden">

    <div id="menuOverlay" class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity"></div>

    <div id="menuContent"
        class="absolute right-0 top-0 h-full w-64 bg-black/60 backdrop-blur-2xl border-l border-white/10 shadow-2xl rounded-l-3xl transform translate-x-full transition-transform duration-300 flex flex-col">

        <div class="p-6 flex-1 flex flex-col">
            <div class="flex justify-between items-center mb-8 border-b border-gold/20 pb-4">
                <span class="text-gold font-title text-xl">Menu</span>
                <button id="closeMenu" class="text-gray-400 hover:text-gold transition-colors p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <nav class="space-y-6 flex-1">
                <a href="index.php?page=home"
                    class="block text-lg <?= $currentPage === 'home' ? 'text-gold font-bold' : 'text-gray-200' ?> hover:text-gold hover:translate-x-2 transition-all duration-300">Home</a>
                <a href="index.php?page=cart"
                    class="block text-lg <?= $currentPage === 'cart' ? 'text-gold font-bold' : 'text-gray-200' ?> hover:text-gold hover:translate-x-2 transition-all duration-300">Keranjang</a>
                <a href="index.php?page=orders"
                    class="block text-lg <?= $currentPage === 'orders' ? 'text-gold font-bold' : 'text-gray-200' ?> hover:text-gold hover:translate-x-2 transition-all duration-300">Pesanan</a>
                <a href="index.php?page=profile"
                    class="block text-lg <?= $currentPage === 'profile' ? 'text-gold font-bold' : 'text-gray-200' ?> hover:text-gold hover:translate-x-2 transition-all duration-300">Akun</a>
            </nav>

            <div class="mt-auto border-t border-white/10 pt-6">
                <a href="actions/logout.php"
                    class="flex items-center gap-2 text-red-500 hover:text-red-400 font-bold hover:translate-x-2 transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                        </path>
                    </svg>
                    Keluar Akun
                </a>
            </div>
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
        mobileMenuWrapper.classList.remove('hidden');
        setTimeout(() => {
            menuContent.classList.remove('translate-x-full');
        }, 10);
    }

    // Fungsi Tutup Menu
    function closeTheMenu() {
        menuContent.classList.add('translate-x-full');
        setTimeout(() => {
            mobileMenuWrapper.classList.add('hidden');
        }, 300);
    }

    closeMenu.onclick = closeTheMenu;   // Tutup pas klik X
    menuOverlay.onclick = closeTheMenu; // Tutup pas klik area kosong di luar sidebar
</script>