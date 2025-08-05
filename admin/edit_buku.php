<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'] ?? 0;
if ($id == 0) {
    header("Location: kelola_buku.php");
    exit();
}

// Ambil data buku
$stmt = $conn->prepare("SELECT * FROM buku WHERE id_buku = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$buku = $stmt->get_result()->fetch_assoc();

if (!$buku) {
    header("Location: kelola_buku.php");
    exit();
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = $_POST['judul'];
    $pengarang = $_POST['pengarang'];
    $penerbit = $_POST['penerbit'];
    $tahun = $_POST['tahun_terbit'];
    $isbn = $_POST['isbn'];
    $jumlah = $_POST['jumlah'];
    $gambar = $buku['gambar']; // gambar lama

    // Upload gambar baru (opsional)
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && $_FILES['gambar']['size'] <= 2000000) {
            // Hapus gambar lama
            if ($gambar && file_exists("../gambar/buku/" . $gambar)) {
                unlink("../gambar/buku/" . $gambar);
            }
            // Simpan gambar baru
            $gambar = 'buku_' . time() . '.' . $ext;
            $target = "../gambar/buku/" . $gambar;
            if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $target)) {
                $error = "Gagal upload gambar baru.";
                $gambar = $buku['gambar']; // kembali ke gambar lama
            }
        } else {
            $error = "Format gambar tidak didukung atau ukuran terlalu besar.";
        }
    }

    // Update data buku
    $stmt = $conn->prepare("UPDATE buku SET judul=?, pengarang=?, penerbit=?, tahun_terbit=?, isbn=?, jumlah=?, gambar=? WHERE id_buku=?");
    $stmt->bind_param("sssiissi", $judul, $pengarang, $penerbit, $tahun, $isbn, $jumlah, $gambar, $id);

    if ($stmt->execute()) {
        $success = "Data buku berhasil diperbarui.";
    } else {
        $error = "Gagal menyimpan perubahan.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku - Admin</title>
    <style>
        /* Reset & Global */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #34495e, #2c3e50);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            color: #333;
        }

        /* Tombol Navigasi Atas */
        .top-nav {
            align-self: flex-start;
            margin-bottom: 20px;
            width: 100%;
            max-width: 700px;
        }

        .btn-nav {
            display: inline-block;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .btn-nav:hover {
            background: rgba(255, 255, 255, 0.35);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* Header */
        .page-header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 2.2em;
            font-weight: 700;
            letter-spacing: 1px;
            text-shadow: 0 2px 8px rgba(0,0,0,0.4);
        }

        .page-header p {
            font-size: 1.1em;
            opacity: 0.9;
            margin-top: 8px;
        }

        /* Form Container */
        .form-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.15),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            width: 100%;
            max-width: 600px;
            transition: all 0.4s ease;
        }

        .form-container:hover {
            transform: translateY(-5px);
            box-shadow: 
                0 30px 60px rgba(0, 0, 0, 0.2),
                0 0 0 1px rgba(255, 255, 255, 0.2);
        }

        .form-container h2 {
            color: #2c3e50;
            margin-bottom: 25px;
            font-size: 1.7em;
            font-weight: 600;
            text-align: center;
        }

        /* Input Group */
        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
            font-size: 14px;
        }

        .input-group input,
        .input-group select,
        .input-group textarea {
            width: 100%;
            padding: 14px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #fdfdfd;
        }

        .input-group input:focus,
        .input-group select:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
            transform: scale(1.01);
        }

        /* Tombol Simpan */
        .btn-save {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-save:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(52, 152, 219, 0.3);
        }

        .btn-save:active {
            transform: translateY(0);
        }

        /* Pesan */
        .alert {
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
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

        /* Responsif */
        @media (max-width: 600px) {
            .form-container {
                padding: 30px 20px;
            }

            .top-nav {
                margin-bottom: 15px;
            }

            .page-header h1 {
                font-size: 1.8em;
            }
        }
    </style>
</head>
<body>

    <!-- Tombol Navigasi Atas -->
    <div class="top-nav">
        <a href="kelola_buku.php" class="btn-nav">⬅️ Kembali ke Daftar Buku</a>
    </div>

    <!-- Header -->
    <div class="page-header">
        <h1>📘 Edit Buku</h1>
        <p>Perbarui data buku dengan informasi terbaru</p>
    </div>

    <!-- Form Edit Buku -->
    <div class="form-container">
        <h2>Edit: <?= htmlspecialchars($buku['judul']) ?></h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="input-group">
                <label>Judul Buku</label>
                <input type="text" name="judul" value="<?= htmlspecialchars($buku['judul']) ?>" required>
            </div>

            <div class="input-group">
                <label>Pengarang</label>
                <input type="text" name="pengarang" value="<?= htmlspecialchars($buku['pengarang']) ?>" required>
            </div>

            <div class="input-group">
                <label>Penerbit</label>
                <input type="text" name="penerbit" value="<?= htmlspecialchars($buku['penerbit']) ?>">
            </div>

            <div class="input-group">
                <label>Tahun Terbit</label>
                <input type="number" name="tahun_terbit" value="<?= $buku['tahun_terbit'] ?>" min="1900" max="<?= date('Y') ?>" required>
            </div>

            <div class="input-group">
                <label>ISBN</label>
                <input type="text" name="isbn" value="<?= htmlspecialchars($buku['isbn']) ?>">
            </div>

            <div class="input-group">
                <label>Jumlah Stok</label>
                <input type="number" name="jumlah" value="<?= $buku['jumlah'] ?>" min="1" required>
            </div>

            <div class="input-group">
                <label>Gambar Saat Ini</label>
                <img src="../gambar/buku/<?= $buku['gambar'] ?: 'book-placeholder.png' ?>" 
                     alt="Sampul" width="100" style="border-radius:10px; margin-bottom:10px;">
            </div>

            <div class="input-group">
                <label>Ganti Gambar (Opsional)</label>
                <input type="file" name="gambar" accept="image/*">
            </div>

            <button type="submit" class="btn-save">💾 Simpan Perubahan</button>
        </form>
    </div>

</body>
</html>