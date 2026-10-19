<?php
// Periksa akun, simpan sesi login, lalu arahkan ke dashboard.
require __DIR__ . '/../config/connection.php';
if (!empty($_SESSION['id_user'])) {
    header('Location: ../index.php');
    exit;
}
$pesan = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_token();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $stmt = mysqli_prepare($conn, 'SELECT * FROM user WHERE username=? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $akun = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if (
        $akun
        && cocok_password($password, (string) $akun['password'])
        && in_array(strtolower($akun['level']), ['admin', 'petugas', 'peminjam'], true)
    ) {
        // Tingkatkan password lama menjadi hash setelah login berhasil.
        if (password_get_info($akun['password'])['algo'] === null && strlen($password) <= 72) {
            $hash = mysqli_real_escape_string($conn, buat_password($password));
            $id_akun = (int) $akun['id_user'];
            mysqli_query($conn, "UPDATE user SET password='$hash' WHERE id_user=$id_akun");
        }
        session_regenerate_id(true);
        $_SESSION = [
            'id_user' => (int) $akun['id_user'],
            'nama' => $akun['nama'],
            'level' => strtolower($akun['level']),
        ];
        header('Location: ../index.php');
        exit;
    }
    $pesan = 'Username atau password salah.';
}

buka_halaman('Login Peminjaman Alat', true);
?>
<h2>Login Peminjaman Alat</h2>
<?php if (isset($_SESSION['pendaftaran_username'])) { ?>
    <p role="status">Pendaftaran berhasil. Username / ID Anggota Anda: <strong><?php echo aman($_SESSION['pendaftaran_username']); ?></strong>. Silakan login menggunakan password yang Anda buat.</p>
    <?php unset($_SESSION['pendaftaran_username']); ?>
<?php } ?>
<?php if ($pesan) { ?>
    <p role="alert"><?php echo aman($pesan); ?></p>
<?php } ?>
<form method="post">
    <?php token(); ?>
    <label>Username / ID Anggota:<br>
        <input name="username" maxlength="50" autocomplete="username" required>
    </label><br><br>
    <label>Password:<br>
        <input type="password" name="password" autocomplete="current-password" required>
    </label><br><br>
    <button>Login</button>
</form>
<p>Belum punya akun? <a href="daftar.php">Daftar Akun</a>
</p>
<button type="button" id="tombol-kembali" onclick="window.history.back()" disabled>Kembali</button>
<script>
    document.getElementById('tombol-kembali').disabled = window.history.length <= 1;
</script>
<?php tutup_halaman(); ?>
