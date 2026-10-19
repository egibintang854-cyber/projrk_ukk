<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'egi_xii1_paket1_';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die('Koneksi gagal: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
date_default_timezone_set('Asia/Jakarta');
include_once __DIR__ . '/fungsi.php';
?>
