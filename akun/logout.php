<?php
// Logout hanya bergantung pada sesi, sehingga tetap berjalan tanpa koneksi database.
require __DIR__ . '/../config/fungsi.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Tolak logout melalui GET agar perubahan sesi hanya dilakukan lewat formulir.
    http_response_code(405);
    header('Allow: POST');
    exit();
}
cek_token();
// Hapus data sesi dan cookie autentikasi, lalu arahkan kembali ke halaman login.
$_SESSION = [];
$cookie = session_get_cookie_params();
setcookie(session_name(), '', [
    'expires' => time() - 3600,
    'path' => $cookie['path'],
    'domain' => $cookie['domain'],
    'secure' => $cookie['secure'],
    'httponly' => $cookie['httponly'],
    'samesite' => $cookie['samesite'] ?? 'Lax',
]);
session_destroy();
header('Location: ' . url_aplikasi('akun/login.php'));
exit();
