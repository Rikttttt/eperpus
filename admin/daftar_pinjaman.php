<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Proses pengembalian
if (isset($_POST['kembalikan'])) {
    $id_pinjam = intval($_POST['id_pinjam']);
    $stmt = $conn->prepare("SELECT id_buku FROM peminjaman WHERE id_pinjam = ? AND status = 'dipinjam'");
    $stmt->bind_param("i", $id_pinjam);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $pinjam = $result->fetch_assoc();
        $update = $conn->prepare("UPDATE peminjaman SET status = 'dikembalikan' WHERE id_pinjam = ?");
        $update->bind_param("i", $id_pinjam);
        if ($update->execute()) {
            $update_stok = $conn->prepare("UPDATE buku SET jumlah = jumlah + 1 WHERE id_buku = ?");
            $update_stok->bind_param("i", $pinjam['id_buku']);
            $update_stok->execute();
            $success = "Buku berhasil dikembalikan.";
        }
    }
}

// Ambil semua peminjaman
$sql = "SELECT p.id_pinjam, u.nama, b.judul, p.tanggal_pinjam, p.tanggal_kembali, p.status 
        FROM peminjaman p
        JOIN users u ON p.id_user = u.id
        JOIN buku b ON p.id_buku = b.id_buku
        ORDER BY p.tanggal_pinjam DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pinjaman - Admin</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f2f5;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }

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
            margin-bottom: 25px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 14px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #c3e6cb;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table th {
            background: #2c3e50;
            color: white;
            padding: 14px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9em;
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        table tr:hover {
            background: #f8f9ff;
        }

        .status-dipinjam {
            background: #fff3cd;
            color: #856404;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }

        .btn {
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            color: white;
        }

        .btn-success { background: #2ecc71; }
        .btn-success:hover { opacity: 0.9; }
    </style>
</head>
<body>

    <!-- Tombol Navigasi Atas -->
    <div class="top-nav">
        <a href="index.php" class="btn">🏠 Dashboard</a>
        <a href="kelola_buku.php" class="btn">📘 Kelola Buku</a>
        <a href="../logout.php" class="btn logout" onclick="return confirm('Yakin logout?')">🚪 Logout</a>
    </div>

    <!-- Konten Utama -->
    <div class="container">
        <h2>📋 Semua Daftar Peminjaman</h2>

        <?php if (isset($success)): ?>
            <div class="alert-success"><?= $success ?></div>
        <?php endif; ?>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>No</th>
                    <th>Nama Peminjam</th>
                    <th>Judul Buku</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Kembali</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td><?= htmlspecialchars($row['judul']) ?></td>
                    <td><?= $row['tanggal_pinjam'] ?></td>
                    <td><?= $row['tanggal_kembali'] ?: '-' ?></td>
                    <td><span class="status-dipinjam">Dipinjam</span></td>
                    <td>
                        <form method="POST" style="display:inline;" 
                              onsubmit="return confirm('Yakin buku ini sudah dikembalikan?')">
                            <input type="hidden" name="id_pinjam" value="<?= $row['id_pinjam'] ?>">
                            <button type="submit" name="kembalikan" class="btn btn-success">✅ Kembalikan</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p style="text-align:center; color:#7f8c8d;">Belum ada data peminjaman.</p>
        <?php endif; ?>
    </div>

</body>
</html>