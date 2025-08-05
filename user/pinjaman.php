<?php
session_start();
include '../koneksi.php';

// Cek login dan role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data peminjaman
$sql = "SELECT p.id_pinjam, b.judul, p.tanggal_pinjam, p.tanggal_kembali, p.status 
        FROM peminjaman p
        JOIN buku b ON p.id_buku = b.id_buku
        WHERE p.id_user = ?
        ORDER BY p.tanggal_pinjam DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinjaman Saya - e-Perpus</title>
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
            max-width: 900px;
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
            font-size: 1.9em;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 15px;
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

        .status-dikembalikan {
            background: #d4edda;
            color: #155724;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }

        .tidak-ada {
            text-align: center;
            padding: 30px;
            color: #7f8c8d;
            font-style: italic;
            background: #f8f9fa;
            border-radius: 12px;
            margin: 20px 0;
            border: 1px dashed #ccc;
        }
    </style>
</head>
<body>

    <!-- Tombol Navigasi Atas -->
    <div class="top-nav">
        <a href="../index.php" class="btn">🏠 Beranda</a>
        <a href="pinjam_buku.php" class="btn">📘 Pinjam Buku</a>
        <a href="../logout.php" class="btn logout" onclick="return confirm('Yakin logout?')">🚪 Logout</a>
    </div>

    <!-- Konten Utama -->
    <div class="container">
        <h2>📌 Daftar Pinjaman Saya</h2>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>No</th>
                    <th>Judul Buku</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Kembali</th>
                    <th>Status</th>
                </tr>
                <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['judul']) ?></td>
                    <td><?= $row['tanggal_pinjam'] ?></td>
                    <td><?= $row['tanggal_kembali'] ?: '-' ?></td>
                    <td>
                        <span class="status-<?php echo strtolower($row['status']); ?>">
                            <?= $row['status'] == 'dipinjam' ? 'Dipinjam' : 'Dikembalikan' ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <div class="tidak-ada">
                Anda belum pernah meminjam buku.
            </div>
        <?php endif; ?>
    </div>

</body>
</html>