<?php
require __DIR__ . '/../config/connection.php';
masuk(['admin']);
$id = (int) ($_GET['id'] ?? 0);
// ID kosong berarti tambah. ID terisi berarti edit.
$judul = 'Tambah Pengguna';
if ($id) {
    $judul = 'Edit Pengguna';
}

$data = ['username' => '', 'nama' => '', 'level' => 'peminjam'];
if ($id) {
    $hasil = mysqli_query($conn, "SELECT * FROM user WHERE id_user=$id");
    $data = mysqli_fetch_assoc($hasil);
    if (!$data) {
        gagal('Pengguna tidak ditemukan.');
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_token();
    $username = teks('username', 50);
    $nama = teks('nama');
    $level = teks('level', 20);
    if (!in_array($level, ['admin', 'petugas', 'peminjam'], true)) {
        gagal('Peran tidak valid.');
    }
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    if ((!$id && $password === '') || strlen($password) > 72) {
        gagal('Password wajib untuk pengguna baru, maksimal 72 byte.');
    }
    mysqli_begin_transaction($conn);
    // Kunci akun agar perubahan peran dan penghapusan tidak bertabrakan.
    mysqli_query($conn, 'SELECT id_user FROM user ORDER BY id_user FOR UPDATE');
    if ($id) {
        $hasil = mysqli_query($conn, "SELECT * FROM user WHERE id_user=$id");
        $data = mysqli_fetch_assoc($hasil);
        if (!$data) {
            gagal('Pengguna tidak ditemukan.');
        }
        if ($id === (int) $_SESSION['id_user'] && $level !== 'admin') {
            gagal('Peran akun yang sedang digunakan tidak boleh diturunkan.');
        }
    }
    $cek = mysqli_query($conn, "SELECT id_user FROM user WHERE username='$username' AND id_user<>$id");
    if (mysqli_num_rows($cek)) {
        gagal('Username sudah digunakan.');
    }
    $cek = mysqli_query($conn, "SELECT id_anggota FROM anggota WHERE nomor_anggota='$username' AND (id_user IS NULL OR id_user<>$id)");
    if (mysqli_num_rows($cek)) {
        gagal('ID anggota sudah digunakan.');
    }
    $password_sql = '';
    if ($password !== '') {
        $hash_password = buat_password($password);
        $password_sql = mysqli_real_escape_string($conn, $hash_password);
    }
    if ($id) {
        $ubah_password = '';
        if ($password !== '') {
            $ubah_password = ", password='$password_sql'";
        }
        mysqli_query($conn, "UPDATE user SET username='$username', nama='$nama', level='$level' $ubah_password WHERE id_user=$id");
    } else {
        mysqli_query($conn, "INSERT INTO user(username,password,nama,level) VALUES ('$username','$password_sql','$nama','$level')");
        $id = mysqli_insert_id($conn);
    }
    // Profil anggota tetap disimpan saat peran berubah agar riwayat tidak hilang.
    mysqli_query($conn, "UPDATE anggota SET nomor_anggota='$username', nama='$nama' WHERE id_user=$id");
    if ($level === 'peminjam') {
        mysqli_query($conn, "INSERT INTO anggota(nomor_anggota,nama,id_user) SELECT '$username','$nama',$id WHERE NOT EXISTS (SELECT 1 FROM anggota WHERE id_user=$id)");
    }
    catat('Menyimpan pengguna #' . $id);
    mysqli_commit($conn);
    header('Location: pengguna-tampil.php');
    exit;
}
buka_halaman($judul);
?>
<h2><?php echo aman($judul); ?></h2>
<form method="post">
    <?php token(); ?>
    <label>Username<br>
        <input name="username" maxlength="50" value="<?php echo aman($data['username']); ?>" required>
    </label><br><br>
    <label>Nama<br>
        <input name="nama" maxlength="100" value="<?php echo aman($data['nama']); ?>" required>
    </label><br><br>
    <label>Peran<br>
        <select name="level" required>
            <?php foreach (['admin', 'petugas', 'peminjam'] as $level) { ?>
                <option value="<?php echo $level; ?>" <?php if ($data['level'] === $level) { echo 'selected'; } ?>><?php echo ucfirst($level); ?></option>
            <?php } ?>
        </select>
    </label><br><br>
    <label>Password <?php if ($id) { echo '(kosongkan jika tidak diubah)'; } ?><br>
        <input name="password" type="password" autocomplete="new-password" maxlength="72" <?php if (!$id) { echo 'required'; } ?>>
    </label><br><br>
    <button>Simpan</button> <a href="pengguna-tampil.php">Batal</a>
</form>
<?php tutup_halaman(); ?>
