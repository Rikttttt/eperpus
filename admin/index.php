<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$nama = $_SESSION['nama'];

// Ambil statistik
$buku_count = $conn->query("SELECT COUNT(*) as total FROM buku")->fetch_assoc();
$user_count = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc();
$pinjam_count = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dipinjam'")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - e-Perpus</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f2f5;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }

        /* Tombol Logout di pojok kanan atas */
        .top-actions {
            text-align: right;
            margin-bottom: 20px;
        }

        .btn-logout {
            display: inline-block;
            padding: 10px 18px;
            background: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            background: #c0392b;
            transform: translateY(-2px);
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
            background: #e3f2fd;
            border-radius: 12px;
            margin-bottom: 30px;
            border: 1px solid #bbdefb;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .stat-card {
            background: white;
            padding: 20px;
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
            font-size: 2.4em;
            font-weight: bold;
            color: #2c3e50;
        }

        .menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .menu-item {
            padding: 20px;
            background: #2c3e50;
            color: white;
            text-align: center;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .menu-item:hover {
            background: #1a252f;
            transform: translateY(-4px);
        }
    </style>
</head>
<body>

    <!-- Tombol Logout di Atas -->
    <div class="top-actions">
        <a href="../logout.php" class="btn-logout" onclick="return confirm('Yakin ingin logout?')">🚪 Logout</a>
    </div>

    <!-- Konten Utama -->
    <div class="container">
        <h2>🏠 Dashboard Admin</h2>

        <div class="welcome">
            <p>
                Selamat datang, <strong><?= htmlspecialchars($nama) ?></strong>!<br>
                Anda memiliki akses penuh ke sistem perpustakaan.
            </p>
        </div>

        <!-- Statistik -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Buku</h3>
                <p><?= $buku_count['total'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Anggota</h3>
                <p><?= $user_count['total'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Dipinjam</h3>
                <p><?= $pinjam_count['total'] ?></p>
            </div>
        </div>

        <!-- Menu Cepat -->
        <h3 style="text-align:center; margin:30px 0 15px; color:#2c3e50;">🔧 Menu Cepat</h3>
        <div class="menu">
            <a href="kelola_buku.php" class="menu-item">📘 Kelola Buku</a>
            <a href="daftar_pinjaman.php" class="menu-item">📋 Daftar Pinjaman</a>
        </div>
    </div>

</body>
</html>