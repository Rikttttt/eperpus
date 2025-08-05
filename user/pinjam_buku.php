<?php
session_start();
include '../koneksi.php';

// Cek login dan role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = $error = '';

// Ambil daftar buku yang tersedia (jumlah > 0)
$buku_result = $conn->query("SELECT id_buku, judul, jumlah FROM buku WHERE jumlah > 0 ORDER BY judul");

// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_buku = intval($_POST['id_buku']);
    $durasi = intval($_POST['durasi']);

    // Validasi
    if ($id_buku <= 0 || !in_array($durasi, [1, 2, 3, 6])) {
        $error = "Pilihan tidak valid.";
    } else {
        // Cek stok
        $stmt = $conn->prepare("SELECT judul, jumlah FROM buku WHERE id_buku = ?");
        $stmt->bind_param("i", $id_buku);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $error = "Buku tidak ditemukan.";
        } else {
            $buku = $result->fetch_assoc();
            if ($buku['jumlah'] <= 0) {
                $error = "Maaf, stok buku <strong>" . htmlspecialchars($buku['judul']) . "</strong> habis.";
            } else {
                // Hitung tanggal
                $tanggal_pinjam = date('Y-m-d');
                $tanggal_kembali = date('Y-m-d', strtotime("+$durasi months"));

                // Kurangi stok
                $update_stok = $conn->prepare("UPDATE buku SET jumlah = jumlah - 1 WHERE id_buku = ?");
                $update_stok->bind_param("i", $id_buku);
                if ($update_stok->execute()) {
                    // Simpan ke peminjaman
                    $insert = $conn->prepare("INSERT INTO peminjaman (id_user, id_buku, tanggal_pinjam, tanggal_kembali, status) VALUES (?, ?, ?, ?, 'dipinjam')");
                    $insert->bind_param("iiss", $user_id, $id_buku, $tanggal_pinjam, $tanggal_kembali);
                    if ($insert->execute()) {
                        $success = "Buku berhasil dipinjam! Harap kembalikan sebelum <strong>$tanggal_kembali</strong>.";
                    } else {
                        $error = "Gagal menyimpan data peminjaman.";
                    }
                } else {
                    $error = "Gagal mengurangi stok buku.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinjam Buku - e-Perpus</title>
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
            max-width: 600px;
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

        .alert {
            padding: 14px;
            margin: 20px 0;
            border-radius: 8px;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        label {
            display: block;
            margin: 14px 0 6px;
            font-weight: 600;
            color: #2c3e50;
        }

        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            background: white;
        }

        .btn-submit {
            display: block;
            width: 100%;
            padding: 12px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: background 0.3s;
        }

        .btn-submit:hover {
            background: #219653;
        }
    </style>
</head>
<body>

    <!-- Tombol Navigasi Atas -->
    <div class="top-nav">
        <a href="../index.php" class="btn">🏠 Beranda</a>
        <a href="pinjaman.php" class="btn">📌 Pinjaman Saya</a>
        <a href="../logout.php" class="btn logout" onclick="return confirm('Yakin logout?')">🚪 Logout</a>
    </div>

    <!-- Konten Utama -->
    <div class="container">
        <h2>📘 Pinjam Buku</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Pilih Buku</label>
            <select name="id_buku" required>
                <option value="">-- Pilih Buku --</option>
                <?php if ($buku_result && $buku_result->num_rows > 0): ?>
                    <?php while ($buku = $buku_result->fetch_assoc()): ?>
                        <option value="<?= $buku['id_buku'] ?>">
                            <?= htmlspecialchars($buku['judul']) ?> (Tersedia: <?= $buku['jumlah'] ?>)
                        </option>
                    <?php endwhile; ?>
                <?php else: ?>
                    <option disabled>Tidak ada buku tersedia</option>
                <?php endif; ?>
            </select>

            <label>Durasi Peminjaman</label>
            <select name="durasi" required>
                <option value="1">1 Bulan</option>
                <option value="2">2 Bulan</option>
                <option value="3">3 Bulan</option>
                <option value="6">6 Bulan</option>
            </select>

            <button type="submit" class="btn-submit">✅ Pinjam Buku Sekarang</button>
        </form>
    </div>

</body>
</html>