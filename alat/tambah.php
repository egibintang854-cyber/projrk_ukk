<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
masuk(['admin']);

if (isset($_POST['simpan'])) {
    cek_token();

    $id_alat = angka('id_alat');
    $nama_alat = teks('nama_alat');
    $kategori = teks('kategori', 25);
    $stok = angka('stok', 0);
    $kondisi = teks('kondisi', 50);

    cek_kategori($kategori);
    mysqli_query($conn, "INSERT INTO alat
    (id_alat, nama_alat, kategori, stok, kondisi)
    VALUES
    ('$id_alat', '$nama_alat', '$kategori', '$stok', '$kondisi')");

    catat('Menambah alat');
    header("Location: tampil.php");
    exit;
}

buka_halaman('Tambah Data Alat');
?>
<h2>Tambah Data Alat</h2>
<form method="POST">
    <?php token(); ?>

    ID Alat:
    <br>
    <input type="text" name="id_alat">
    <br><br>

    Nama Alat:
    <br>
    <input type="text" name="nama_alat">
    <br><br>

    Kategori:
    <br>
    <?php pilihan_kategori(); ?>
    <br><br>

    Stok:
    <br>
    <input type="text" name="stok">
    <br><br>

    Kondisi:
    <br>
    <textarea name="kondisi">
    </textarea>
    <br><br>
    <button type="submit" name="simpan">
        Simpan
    </button>
</form>

<br>

<a href="tampil.php">Kembali</a>
<?php tutup_halaman(); ?>
