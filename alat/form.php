<?php
require __DIR__ . '/../config/connection.php';
// Hanya admin yang dapat menambah atau mengubah data alat.
masuk(['admin']);

// ID kosong berarti tambah; ID berisi berarti muat data untuk diedit.
$id = id_dari_url();
$alat = null;
if ($id) {
    $stmt = mysqli_prepare($conn, 'SELECT * FROM alat WHERE id_alat=?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $alat = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if (!$alat) {
        gagal('Data alat tidak ditemukan.');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi formulir dan kategori sebelum mengubah database.
    cek_token();
    $nama_alat = teks_tervalidasi('nama_alat', 100);
    $kategori = teks_tervalidasi('kategori', 25);
    $stok = angka('stok', 0);
    $kondisi = teks_tervalidasi('kondisi', 50);
    cek_kategori(mysqli_real_escape_string($conn, $kategori));

    // Pilih query tambah atau ubah berdasarkan keberadaan ID alat.
    if ($id) {
        $stmt = mysqli_prepare(
            $conn,
            'UPDATE alat SET nama_alat=?, kategori=?, stok=?, kondisi=? WHERE id_alat=?',
        );
        mysqli_stmt_bind_param($stmt, 'ssisi', $nama_alat, $kategori, $stok, $kondisi, $id);
        $aktivitas = 'Mengedit alat #' . $id;
    } else {
        $stmt = mysqli_prepare(
            $conn,
            'INSERT INTO alat (nama_alat, kategori, stok, kondisi) VALUES (?, ?, ?, ?)',
        );
        mysqli_stmt_bind_param($stmt, 'ssis', $nama_alat, $kategori, $stok, $kondisi);
        $aktivitas = 'Menambah alat';
    }
    mysqli_stmt_execute($stmt);
    catat($aktivitas);
    header('Location: tampil.php');
    exit();
}

// Judul dan isi formulir menyesuaikan mode tambah atau edit.
$judul = $id ? 'Edit Data Alat' : 'Tambah Data Alat';
buka_halaman($judul);
?>
<h2><?php echo aman($judul); ?></h2>
<form method="post">
    <?php token(); ?>
    <?php if ($id) { ?><label>ID Alat<br><input value="<?php echo (int) $alat[
    'id_alat'
]; ?>" readonly></label><br><br><?php } ?>
    <label>Nama Alat<br><input type="text" name="nama_alat" maxlength="100" value="<?php echo aman(
        $alat['nama_alat'] ?? '',
    ); ?>" required></label><br><br>
    <label>Kategori<br><?php pilihan_kategori($alat['kategori'] ?? ''); ?></label><br><br>
    <label>Stok<br><input type="number" name="stok" min="0" value="<?php echo isset($alat['stok'])
        ? (int) $alat['stok']
        : ''; ?>" required></label><br><br>
    <label>Kondisi<br><textarea name="kondisi" maxlength="50" required><?php echo aman(
        $alat['kondisi'] ?? '',
    ); ?></textarea></label><br><br>
    <button type="submit">Simpan</button> <a href="tampil.php">Kembali</a>
</form>
<?php tutup_halaman(); ?>
