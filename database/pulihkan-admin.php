<?php
// Jalankan dari terminal: C:\xampp\php\php.exe database\pulihkan-admin.php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Hanya dapat dijalankan dari terminal.');
}
require __DIR__ . '/../config/connection.php';

mysqli_begin_transaction($conn);
mysqli_query($conn, 'SELECT id_user FROM user ORDER BY id_user FOR UPDATE');
$admin = mysqli_query($conn, "SELECT id_user FROM user WHERE LOWER(level)='admin'");
if (mysqli_num_rows($admin)) {
    mysqli_rollback($conn);
    exit("Akun admin masih tersedia. Tidak ada perubahan.\n");
}
$username = 'admin';
$cek = mysqli_query($conn, "SELECT id_user FROM user WHERE username='admin' UNION ALL SELECT id_user FROM anggota WHERE nomor_anggota='admin'");
if (mysqli_num_rows($cek)) {
    mysqli_rollback($conn);
    fwrite(STDERR, "Username admin sudah digunakan. Tidak ada perubahan.\n");
    exit(1);
}
$password = bin2hex(random_bytes(8));
$hash = buat_password($password);
$stmt = mysqli_prepare($conn, "INSERT INTO user (username,password,nama,level) VALUES (?,?,'Administrator','admin')");
mysqli_stmt_bind_param($stmt, 'ss', $username, $hash);
mysqli_stmt_execute($stmt);
$_SESSION['id_user'] = mysqli_insert_id($conn);
catat('Memulihkan akun admin melalui terminal');
mysqli_commit($conn);
echo "Akun admin berhasil dibuat.\nUsername: $username\nPassword: $password\nSegera ubah melalui menu Ubah Kata Sandi.\n";
