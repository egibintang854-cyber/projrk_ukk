<?php
// Daftarkan anggota dan buat username secara otomatis.
require __DIR__ . '/../config/connection.php';
if (!empty($_SESSION['id_user'])) {
    header('Location: ../index.php');
    exit;
}
$pesan = '';
$nama = '';
$alamat = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_token();
    $nama = is_string($_POST['nama'] ?? null) ? trim($_POST['nama']) : '';
    $alamat = is_string($_POST['alamat'] ?? null) ? trim($_POST['alamat']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $konfirmasi = is_string($_POST['konfirmasi'] ?? null) ? $_POST['konfirmasi'] : '';
    if ($nama === '' || strlen($nama) > 100 || strlen($alamat) > 255) {
        $pesan = 'Nama wajib diisi (maksimal 100 karakter), alamat maksimal 255 karakter.';
    } elseif ($password === '' || strlen($password) > 72) {
        $pesan = 'Password wajib diisi, maksimal 72 byte.';
    } elseif ($password !== $konfirmasi) {
        $pesan = 'Konfirmasi password tidak sama.';
    } else {
        $kunci = mysqli_fetch_row(mysqli_query($conn, "SELECT GET_LOCK('pinjam_daftar_anggota', 10)"))[0];
        if ($kunci != 1) {
            $pesan = 'Pendaftaran sedang sibuk. Silakan coba lagi.';
        } else {
            try {
                mysqli_begin_transaction($conn);
                $urut = (int) mysqli_fetch_row(mysqli_query($conn, "SELECT COALESCE(MAX(CAST(SUBSTRING(nomor_anggota,3) AS UNSIGNED)),0) FROM anggota WHERE nomor_anggota REGEXP '^AG[0-9]+$'"))[0];
                do {
                    $username = 'AG' . ++$urut;
                    $cek = mysqli_prepare($conn, 'SELECT id_user FROM user WHERE username=?');
                    mysqli_stmt_bind_param($cek, 's', $username);
                    mysqli_stmt_execute($cek);
                } while (mysqli_num_rows(mysqli_stmt_get_result($cek)) > 0);
                $stmt = mysqli_prepare($conn, "INSERT INTO user(username,password,nama,level) VALUES (?,?,?,'peminjam')");
                $hash = buat_password($password);
                mysqli_stmt_bind_param($stmt, 'sss', $username, $hash, $nama);
                mysqli_stmt_execute($stmt);
                $id_user = mysqli_insert_id($conn);
                $stmt = mysqli_prepare($conn, 'INSERT INTO anggota(nomor_anggota,nama,alamat,id_user) VALUES (?,?,?,?)');
                mysqli_stmt_bind_param($stmt, 'sssi', $username, $nama, $alamat, $id_user);
                mysqli_stmt_execute($stmt);
                mysqli_commit($conn);
                $_SESSION['pendaftaran_username'] = $username;
                header('Location: login.php');
            } catch (mysqli_sql_exception $error) {
                mysqli_rollback($conn);
                if ($error->getCode() != 1062) {
                    throw $error;
                }
                $pesan = 'ID anggota baru saja digunakan. Silakan daftar kembali.';
            } finally {
                mysqli_query($conn, "SELECT RELEASE_LOCK('pinjam_daftar_anggota')");
            }
            if ($pesan === '') {
                exit;
            }
        }
    }
}

buka_halaman('Daftar Akun Anggota', true);
?>
<h2>Daftar Akun Anggota</h2>
<p>ID anggota sekaligus username dibuat otomatis setelah pendaftaran.</p>
<?php if ($pesan) { ?>
    <p role="alert"><?php echo aman($pesan); ?></p><?php } ?>
<form method="post">
    <?php token(); ?>
    <label>Nama Lengkap:<br>
        <input name="nama" maxlength="100" autocomplete="name" value="<?php echo aman($nama); ?>" required>
    </label><br><br>
    <label>Alamat:<br>
        <textarea name="alamat" maxlength="255" autocomplete="street-address"><?php echo aman($alamat); ?></textarea>
    </label><br><br>
    <label>Password:<br>
        <input type="password" name="password" maxlength="72" autocomplete="new-password" required>
    </label><br><br>
    <label>Konfirmasi Password:<br>
        <input type="password" name="konfirmasi" maxlength="72" autocomplete="new-password" required>
    </label><br><br>
    <button>Daftar</button>
</form>
<p>Sudah punya akun? <a href="login.php">Login</a>
</p>
<?php tutup_halaman(); ?>
