<?php
session_start();
include '../koneksi.php';

// Cek login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

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

        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 30px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
            font-size: 2em;
        }

        /* Statistik Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            margin: 30px 0;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 14px;
            text-align: center;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
        }

        .stat-card h3 {
            color: #7f8c8d;
            margin-bottom: 12px;
            font-size: 1.1em;
        }

        .stat-card p {
            font-size: 2.6em;
            font-weight: bold;
            color: #2c3e50;
            margin: 0;
        }

        /* Tombol Navigasi */
        .nav-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 40px 0 20px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            min-width: 140px;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        /* Menu Admin */
        .admin-menu {
            margin: 30px 0;
            text-align: center;
        }

        .admin-menu a {
            display: inline-block;
            margin: 10px 15px;
            padding: 14px 20px;
            background: #2c3e50;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            min-width: 160px;
        }

        .admin-menu a:hover {
            background: #1a252f;
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 600px) {
            .nav-buttons, .admin-menu {
                flex-direction: column;
                align-items: center;
            }

            .btn, .admin-menu a {
                width: 100%;
                text-align: center;
                margin: 8px 0;
            }
        }
    </style>
</head>
<body>

    <!-- Tombol Navigasi Atas -->
    <div class="nav-buttons">
        <a href="../index.php" class="btn btn-primary">🏠 Beranda</a>
        <a href="../logout.php" class="btn btn-danger" onclick="return confirm('Yakin ingin logout?')">🚪 Logout</a>
    </div>

    <!-- Konten Utama -->
    <div class="container">
        <h2>📊 Dashboard Admin</h2>

        <!-- Statistik -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Buku</h3>
                <p><?= $buku_count['total'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Jumlah Anggota</h3>
                <p><?= $user_count['total'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Buku Dipinjam</h3>
                <p><?= $pinjam_count['total'] ?></p>
            </div>
        </div>

        <!-- Menu Admin -->
        <div class="admin-menu">
            <a href="kelola_buku.php">📘 Kelola Buku</a>
            <a href="daftar_pinjaman.php">📋 Daftar Pinjaman</a>
        </div>

        <!-- Pesan Selamat Datang -->
        <p style="text-align: center; margin-top: 30px; color: #555; font-size: 1.1em;">
            Selamat datang, <strong><?= htmlspecialchars($_SESSION['nama']) ?></strong>!<br>
            Anda login sebagai <span style="color:#2c3e50; font-weight:600;">Admin</span>.
        </p>
    </div>

    <!-- Tombol Navigasi Bawah -->
    <div class="nav-buttons">
        <a href="../index.php" class="btn btn-primary">🏠 Beranda</a>
        <a href="../logout.php" class="btn btn-danger" onclick="return confirm('Yakin ingin logout?')">🚪 Logout</a>
    </div>

</body>
</html>