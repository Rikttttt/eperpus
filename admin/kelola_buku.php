<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$success = $error = '';

// Proses tambah buku
if (isset($_POST['tambah'])) {
    $judul = $_POST['judul'];
    $pengarang = $_POST['pengarang'];
    $penerbit = $_POST['penerbit'];
    $tahun = $_POST['tahun_terbit'];
    $isbn = $_POST['isbn'];
    $jumlah = $_POST['jumlah'];
    $gambar = '';

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && $_FILES['gambar']['size'] <= 2000000) {
            $gambar = 'buku_' . time() . '.' . $ext;
            $target = "../gambar/buku/" . $gambar;
            move_uploaded_file($_FILES['gambar']['tmp_name'], $target);
        } else {
            $error = "Format gambar tidak didukung atau ukuran terlalu besar.";
        }
    }

    $stmt = $conn->prepare("INSERT INTO buku (judul, pengarang, penerbit, tahun_terbit, isbn, jumlah, gambar) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiiss", $judul, $pengarang, $penerbit, $tahun, $isbn, $jumlah, $gambar);
    if ($stmt->execute()) {
        $success = "Buku berhasil ditambahkan.";
    } else {
        $error = "Gagal menyimpan data.";
    }
}

// Proses hapus buku
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $stmt = $conn->prepare("SELECT gambar FROM buku WHERE id_buku = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $buku = $result->fetch_assoc();

    if ($buku['gambar'] && file_exists("../gambar/buku/" . $buku['gambar'])) {
        unlink("../gambar/buku/" . $buku['gambar']);
    }

    $stmt2 = $conn->prepare("DELETE FROM buku WHERE id_buku = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    header("Location: kelola_buku.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Buku - Admin</title>
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

        .form-container {
            background: #f9fafa;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .form-container label {
            display: block;
            margin: 14px 0 6px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-container input, .form-container select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }

        .form-container button {
            margin-top: 20px;
            padding: 12px 24px;
            background: #2c3e50;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
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

        .btn-action {
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            color: white;
        }

        .btn-warning { background: #f39c12; }
        .btn-danger { background: #e74c3c; }
    </style>
</head>
<body>

    <!-- Tombol Navigasi Atas -->
    <div class="top-nav">
        <a href="index.php" class="btn">🏠 Dashboard</a>
        <a href="daftar_pinjaman.php" class="btn">📋 Daftar Pinjaman</a>
        <a href="../logout.php" class="btn logout" onclick="return confirm('Yakin logout?')">🚪 Logout</a>
    </div>

    <!-- Konten Utama -->
    <div class="container">
        <h2>📘 Kelola Data Buku</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <!-- Form Tambah Buku -->
        <div class="form-container">
            <h3>Tambah Buku Baru</h3>
            <form method="POST" enctype="multipart/form-data">
                <label>Judul</label>
                <input type="text" name="judul" required>
                <label>Pengarang</label>
                <input type="text" name="pengarang" required>
                <label>Penerbit</label>
                <input type="text" name="penerbit">
                <label>Tahun Terbit</label>
                <input type="number" name="tahun_terbit" min="1900" max="<?= date('Y') ?>" value="<?= date('Y') ?>">
                <label>ISBN</label>
                <input type="text" name="isbn">
                <label>Jumlah</label>
                <input type="number" name="jumlah" min="1" value="1">
                <label>Gambar Sampul</label>
                <input type="file" name="gambar" accept="image/*">
                <button type="submit" name="tambah">Tambah Buku</button>
            </form>
        </div>

        <!-- Daftar Buku -->
        <h3>Daftar Buku</h3>
        <table>
            <tr>
                <th>Gambar</th>
                <th>Judul</th>
                <th>Pengarang</th>
                <th>Penerbit</th>
                <th>Tahun</th>
                <th>ISBN</th>
                <th>Jumlah</th>
                <th>Aksi</th>
            </tr>
            <?php
            $result = $conn->query("SELECT * FROM buku ORDER BY judul");
            while ($buku = $result->fetch_assoc()):
            ?>
            <tr>
                <td>
                    <img src="../gambar/buku/<?= $buku['gambar'] ?: 'book-placeholder.png' ?>" 
                         alt="Sampul" width="60" style="border-radius:6px;">
                </td>
                <td><?= htmlspecialchars($buku['judul']) ?></td>
                <td><?= htmlspecialchars($buku['pengarang']) ?></td>
                <td><?= htmlspecialchars($buku['penerbit']) ?></td>
                <td><?= $buku['tahun_terbit'] ?></td>
                <td><?= $buku['isbn'] ?></td>
                <td><?= $buku['jumlah'] ?></td>
                <td>
                    <a href="edit_buku.php?id=<?= $buku['id_buku'] ?>" class="btn-action btn-warning">Edit</a> |
                    <a href="?hapus=<?= $buku['id_buku'] ?>" class="btn-action btn-danger" 
                       onclick="return confirm('Hapus buku ini?')">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

</body>
</html>