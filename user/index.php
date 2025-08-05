<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit();
}

$nama = $_SESSION['nama'];
$user_id = $_SESSION['user_id'];

// Ambil jumlah pinjaman aktif
$pinjam_result = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE id_user = $user_id AND status = 'dipinjam'");
$pinjam_data = $pinjam_result->fetch_assoc();
$total_pinjam = $pinjam_data['total'];

// Ambil daftar buku yang tersedia
$buku_result = $conn->query("SELECT * FROM buku WHERE jumlah > 0 ORDER BY judul");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - e-Perpus</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f4f8;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }

        /* Tombol Navigasi Atas */
        .top-nav {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 20px 0 30px;
            flex-wrap: wrap;
        }

        .top-nav .btn {
            padding: 10px 20px;
            background: #2c3e50;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .top-nav .btn:hover {
            background: #1a252f;
            transform: translateY(-2px);
        }

        .top-nav .btn.logout {
            background: #e74c3c;
        }

        .top-nav .btn.logout:hover {
            background: #c0392b;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 30px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .welcome {
            text-align: center;
            padding: 20px;
            background: #e8f5e9;
            border-radius: 12px;
            margin-bottom: 30px;
            border: 1px solid #c8e6c9;
        }

        .stats {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 30px 0;
            flex-wrap: wrap;
        }

        .stat-card {
            background: white;
            padding: 20px;
            min-width: 180px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
            border: 1px solid #e0e0e0;
        }

        .stat-card h3 {
            color: #7f8c8d;
            margin-bottom: 10px;
        }

        .stat-card p {
            font-size: 2em;
            font-weight: bold;
            color: #2c3e50;
        }

        /* Daftar Buku */
        .book-list {
            margin: 30px 0;
        }

        .book-item {
            display: flex;
            gap: 15px;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 12px;
            background: #fdfdfd;
            margin-bottom: 15px;
            align-items: center;
            transition: all 0.3s ease;
        }

        .book-item:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .book-cover {
            width: 80px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .book-info {
            flex: 1;
        }

        .book-info h3 {
            margin: 0 0 6px;
            color: #2c3e50;
            font-size: 1.2em;
        }

        .book-info p {
            margin: 4px 0;
            color: #555;
            font-size: 0.95em;
        }

        .book-actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .book-actions .btn {
            padding: 8px 16px;
            background: #27ae60;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            text-align: center;
            transition: background 0.3s;
        }

        .book-actions .btn:hover {
            background: #219653;
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 13px;
        }

        .no-books {
            text-align: center;
            padding: 30px;
            color: #7f8c8d;
            font-style: italic;
            background: #f8f9fa;
            border-radius: 12px;
            margin: 20px 0;
        }
    </style>
</head>
<body>

    <!-- Tombol Navigasi Atas -->
    <div class="top-nav">
        <a href="pinjaman.php" class="btn">📌 Pinjaman Saya</a>
        <a href="pinjam_buku.php" class="btn">📘 Pinjam Buku</a>
        <a href="../logout.php" class="btn logout" onclick="return confirm('Yakin logout?')">🚪 Logout</a>
    </div>

    <!-- Konten Utama -->
    <div class="container">
        <h2>🏠 Halo, <?= htmlspecialchars($nama) ?>!</h2>

        <div class="welcome">
            <p>Selamat datang di e-Perpustakaan Digital.</p>
        </div>

        <!-- Statistik -->
        <div class="stats">
            <div class="stat-card">
                <h3>Pinjaman Aktif</h3>
                <p><?= $total_pinjam ?></p>
            </div>
        </div>

        <!-- Daftar Buku Tersedia -->
        <h3 style="color: #2c3e50; margin: 30px 0 15px;">📚 Buku yang Tersedia</h3>

        <?php if ($buku_result->num_rows > 0): ?>
            <div class="book-list">
                <?php while ($buku = $buku_result->fetch_assoc()): ?>
                    <div class="book-item">
                        <!-- Sampul Buku -->
                        <img src="../gambar/buku/<?= $buku['gambar'] ?: 'book-placeholder.png' ?>" 
                             alt="Sampul" class="book-cover">

                        <!-- Info Buku -->
                        <div class="book-info">
                            <h3><?= htmlspecialchars($buku['judul']) ?></h3>
                            <p><strong>Pengarang:</strong> <?= htmlspecialchars($buku['pengarang']) ?></p>
                            <p><strong>Penerbit:</strong> <?= htmlspecialchars($buku['penerbit']) ?> (<?= $buku['tahun_terbit'] ?>)</p>
                            <p><strong>Stok:</strong> <?= $buku['jumlah'] ?> tersedia</p>
                        </div>

                        <!-- Tombol Pinjam -->
                        <div class="book-actions">
                            <a href="pinjam_buku.php" class="btn">➕ Pinjam Buku Ini</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-books">
                Saat ini tidak ada buku yang tersedia untuk dipinjam.
            </div>
        <?php endif; ?>
    </div>

</body>
</html>