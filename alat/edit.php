<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
masuk(['admin']);

// Ambil ID dari alamat halaman, misalnya edit.php?id=1.
$id = 0;
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
}

$data = mysqli_query($conn, "SELECT * FROM alat WHERE id_alat=$id");

$alat = mysqli_fetch_assoc($data);
if (!$alat) {
    gagal('Data tidak ditemukan.');
}

if (isset($_POST['update'])) {
    cek_token();

    $id_alat = $id; // ID tetap agar riwayat tidak terputus.
    $nama_alat = teks('nama_alat');
    $kategori = teks('kategori', 25);
    $stok = angka('stok', 0);
    $kondisi = teks('kondisi', 50);

    cek_kategori($kategori);
    mysqli_query($conn, "UPDATE alat SET
        id_alat='$id_alat',
        nama_alat='$nama_alat',
        kategori='$kategori',
        stok='$stok',
        kondisi='$kondisi'
        WHERE id_alat=$id
    ");

    catat('Mengedit alat');
    header("Location: tampil.php");
    exit;
}

buka_halaman('Edit Data Alat');
?>
<h2>Edit Data Alat</h2>
<form method="POST">
    <?php token(); ?>

    ID Alat:
    <br>
    <input type="text"
    name="id_alat" readonly
    value="<?php echo aman($alat['id_alat']); ?>">

    <br><br>

    Nama Alat:
    <br>
    <input type="text"
    name="nama_alat"
    value="<?php echo aman($alat['nama_alat']); ?>">

    <br><br>

    Kategori:
    <br>
    <?php pilihan_kategori($alat['kategori']); ?>

    <br><br>

    Stok:
    <br>
    <input type="text"
    name="stok"
    value="<?php echo aman($alat['stok']); ?>">

    <br><br>

    Kondisi:
    <br>
    <textarea name="kondisi"><?php echo aman($alat['kondisi']); ?></textarea>

    <br><br>
    <button type="submit" name="update">
        Update
    </button>
</form>

<br>

<a href="tampil.php">Kembali</a>
<?php tutup_halaman(); ?>
