<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
masuk(['admin']);

// Ambil ID dari alamat halaman, misalnya kategori-edit.php?id=1.
$id = 0;
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
}
$hasil = mysqli_query($conn, "SELECT * FROM kategori WHERE id_kategori=$id");
$data = mysqli_fetch_assoc($hasil);
if (!$data) {
    gagal('Kategori tidak ditemukan.');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    cek_token();
    $nama = teks('nama_kategori',25);
    if (!in_array($nama, ['alat', 'elektronik'], true)) {
        gagal('Pilih kategori Alat atau Elektronik.');
    }
    // Mulai transaksi: perubahan disimpan bersama setelah semua langkah berhasil.
    mysqli_begin_transaction($conn);
    mysqli_query($conn, 'SELECT id_kategori FROM kategori FOR UPDATE');
    $cek = mysqli_query($conn, "SELECT id_kategori FROM kategori WHERE nama_kategori='$nama' AND id_kategori<>$id");
    if (mysqli_num_rows($cek)) {
        gagal('Kategori sudah ada.');
    }
    $lama = mysqli_real_escape_string($conn, $data['nama_kategori']);
    mysqli_query($conn, "UPDATE kategori SET nama_kategori='$nama' WHERE id_kategori=$id");
    mysqli_query($conn, "UPDATE alat SET kategori='$nama' WHERE kategori='$lama'");
    catat('Mengedit kategori #' . $id);
    // Semua langkah berhasil, simpan perubahan ke database.
    mysqli_commit($conn);
    header('Location: kategori-tampil.php');
    exit;
}

buka_halaman('Edit Kategori');
?>
<h2>Edit Kategori</h2>
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
