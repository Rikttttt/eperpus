<!-- header.php -->
<header>
    <img src="gambar/logo.png" alt="Logo" class="logo">
    <h1>📚 e-Perpustakaan Digital</h1>
</header>

<nav>
    <a href="index.php">Beranda</a>
    <a href="user/pinjaman.php">Pinjaman Saya</a>
    <?php if ($_SESSION['role'] == 'admin'): ?>
        <a href="admin/dashboard.php">Admin Panel</a>
        <a href="admin/kelola_buku.php">Kelola Buku</a>
        <a href="admin/daftar_pinjaman.php">Semua Pinjaman</a>
    <?php endif; ?>
    <a href="logout.php">Logout</a>
</nav>