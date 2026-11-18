<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-brand">
        <svg viewBox="0 0 24 24" width="24" height="24" style="width:24px; height:24px; min-width:24px; fill:currentColor;"><path d="M12 2L2 7l10 5 10-5-10-5zm0 7.5L4.5 6 12 2.5 19.5 6 12 9.5zm0 2.5l-10-5v10l10 5 10-5V7l-10 5z"/></svg>
        <span>Toko Kita</span>
    </div>
    <div class="sidebar-menu">
        <div class="menu-label">Menu Pelanggan</div>
        
        <a href="riwayat_pelanggan.php" class="menu-item <?= ($current_page == 'riwayat_pelanggan.php') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" width="18" height="18" style="width:18px; height:18px; min-width:18px;" fill="currentColor"><path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/></svg>
            <span>Riwayat Belanja</span>
        </a>

        <a href="katalog_pelanggan.php" class="menu-item <?= ($current_page == 'katalog_pelanggan.php') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" width="18" height="18" style="width:18px; height:18px; min-width:18px;" fill="currentColor"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg>
            <span>Belanja Produk</span>
        </a>

        <div class="menu-label" style="margin-top: 25px;">Akun</div>
        
        <a href="logout.php" class="menu-item logout">
            <svg viewBox="0 0 24 24" width="18" height="18" style="width:18px; height:18px; min-width:18px;" fill="currentColor"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
            <span>Logout</span>
        </a>
    </div>
</div>