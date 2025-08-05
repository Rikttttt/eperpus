<?php
$host = "localhost";
$user = "root";         // Sesuaikan jika pakai XAMPP/WAMP
$pass = "";             // Biasanya kosong di localhost
$db   = "dbperpus1";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>