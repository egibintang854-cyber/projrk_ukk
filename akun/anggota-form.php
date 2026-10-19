<?php
require __DIR__ . '/../config/connection.php';
masuk(['admin']);

// Tanpa ID: tambah anggota. Dengan ID: edit anggota.
$judul = 'Tambah Anggota';
$data = ['nomor_anggota' => '', 'nama' => '', 'alamat' => ''];
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $judul = 'Edit Anggota';
    $hasil = mysqli_query($conn, "SELECT * FROM anggota WHERE id_anggota=$id");
    $data = mysqli_fetch_assoc($hasil);
    if (!$data) {
        gagal('Anggota tidak ditemukan.');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_token();
    $nomor = teks('nomor_anggota', 50);
    $nama = teks('nama');
    $alamat = trim($_POST['alamat'] ?? '');
    if (strlen($alamat) > 255) {
        gagal('Alamat maksimal 255 karakter.');
    }
    $alamat = mysqli_real_escape_string($conn, $alamat);
    mysqli_begin_transaction($conn);
    try {
        $akun_lama = isset($id) ? (int) $data['id_user'] : 0;
        $duplikat = mysqli_query($conn, "SELECT id_user FROM user WHERE username='$nomor' AND id_user<>$akun_lama");
        if (mysqli_num_rows($duplikat)) {
            gagal('ID anggota sudah digunakan sebagai username.');
        }
        if (isset($id)) {
            $akun = (int) $data['id_user'];
            mysqli_query($conn, "UPDATE user SET username='$nomor', nama='$nama' WHERE id_user=$akun");
            mysqli_query($conn, "UPDATE anggota SET nomor_anggota='$nomor', nama='$nama', alamat='$alamat' WHERE id_anggota=$id");
            catat('Mengedit anggota #' . $id);
        } else {
            $password = (string) ($_POST['password'] ?? '');
            if ($password === '' || strlen($password) > 72) {
                gagal('Password login wajib diisi, maksimal 72 byte.');
            }
            $password_sql = mysqli_real_escape_string($conn, buat_password($password));
            mysqli_query($conn, "INSERT INTO user (username,password,nama,level) VALUES ('$nomor','$password_sql','$nama','peminjam')");
            $akun = mysqli_insert_id($conn);
            mysqli_query($conn, "INSERT INTO anggota (nomor_anggota, nama, alamat, id_user) VALUES ('$nomor', '$nama', '$alamat', $akun)");
            catat('Menambah anggota #' . mysqli_insert_id($conn));
        }
        mysqli_commit($conn);
    } catch (mysqli_sql_exception $error) {
        if ($error->getCode() == 1062) {
            gagal('ID anggota sudah digunakan.');
        }
        throw $error;
    }
    header('Location: anggota-tampil.php');
    exit;
}

buka_halaman($judul);
?>
<h2><?php echo aman($judul); ?></h2>
<form method="post">
    <?php token(); ?>
    ID Anggota:<br>
    <input name="nomor_anggota" maxlength="50" value="<?php echo aman($data['nomor_anggota']); ?>" required><br><br>
    Nama Lengkap:<br>
    <input name="nama" maxlength="100" value="<?php echo aman($data['nama']); ?>" required><br><br>
    Alamat:<br>
    <textarea name="alamat" maxlength="255"><?php echo aman($data['alamat']); ?></textarea><br><br>
    <?php if (!isset($id)) { ?>
        Password Login:<br>
        <input name="password" type="password" maxlength="72" autocomplete="new-password" required><br><br>
        <p>ID Anggota digunakan sebagai username login.</p>
    <?php } ?>
    <button>Simpan</button>
</form>
<p><a href="anggota-tampil.php">Kembali</a>
</p>

<?php tutup_halaman(); ?>
