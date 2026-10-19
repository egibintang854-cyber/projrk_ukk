<?php
// Periksa password lama sebelum menyimpan password baru.
require __DIR__ . '/../config/connection.php';
masuk(['admin','petugas','peminjam']);
$pesan = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_token();
    $id = (int) $_SESSION['id_user'];
    $hasil = mysqli_query($conn, "SELECT password FROM user WHERE id_user=$id");
    $akun = mysqli_fetch_assoc($hasil);
    $baru = (string) ($_POST['baru'] ?? '');
    if (!cocok_password((string) ($_POST['lama'] ?? ''), (string) $akun['password'])) {
        $pesan = 'Password lama salah.';
    } elseif (($baru === '' || strlen($baru) > 72)) {
        $pesan = 'Password baru wajib diisi, maksimal 72 byte.';
    } else {
        $password_sql = mysqli_real_escape_string($conn, buat_password($baru));
        mysqli_query($conn, "UPDATE user SET password='$password_sql' WHERE id_user=$id");
        session_regenerate_id(true);
        $pesan = 'Password berhasil diubah.';
    }
}

buka_halaman('Ubah Password');
?>
<h2>Ubah Password</h2>
<p><?php echo aman($pesan); ?></p>
<form method="post">
    <?php token(); ?>
    <label>Password Lama:<br>
        <input type="password" name="lama" autocomplete="current-password" required>
    </label><br><br>
    <label>Password Baru:<br>
        <input type="password" name="baru" maxlength="72" autocomplete="new-password" required>
    </label><br><br>
    <button>Simpan</button>
</form>
<?php tutup_halaman(); ?>
