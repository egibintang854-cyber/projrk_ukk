<?php
// Akhiri sesi pengguna setelah memeriksa token formulir.
require __DIR__ . '/../config/connection.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}
cek_token();
$_SESSION = [];
$cookie = session_get_cookie_params();
setcookie(session_name(), '', time()-3600, $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
session_destroy();
header('Location: login.php');
exit;
