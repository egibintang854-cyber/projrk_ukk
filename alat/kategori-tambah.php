<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
masuk(['admin']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    cek_token();
    $nama = teks('nama_kategori',25);
    if (!in_array($nama, ['alat', 'elektronik'], true)) {
        gagal('Pilih kategori Alat atau Elektronik.');
    }
    // Mulai transaksi: perubahan disimpan bersama setelah semua langkah berhasil.
    mysqli_begin_transaction($conn);
    mysqli_query($conn, 'SELECT id_kategori FROM kategori FOR UPDATE');
    $cek = mysqli_query($conn, "SELECT id_kategori FROM kategori WHERE nama_kategori='$nama'");
    if (mysqli_num_rows($cek)) {
        gagal('Kategori sudah ada.');
    }
    mysqli_query($conn, "INSERT INTO kategori (nama_kategori) VALUES ('$nama')");
    catat('Menambah kategori');
    // Semua langkah berhasil, simpan perubahan ke database.
    mysqli_commit($conn);
    header('Location: kategori-tampil.php');
    exit;
}

buka_halaman('Tambah Kategori');
?>
<h2>Tambah Kategori</h2>
<form method="post">
    <?php token(); ?>
    Nama Kategori:<br>
    <select name="nama_kategori" required>
        <option value="" selected disabled>-- Pilih Kategori --</option>
        <option value="alat">Alat</option>
        <option value="elektronik">Elektronik</option>
    </select><br><br>
    <button>Simpan</button>
</form>
<p><a href="kategori-tampil.php">Kembali</a>
</p>
<?php tutup_halaman(); ?>
