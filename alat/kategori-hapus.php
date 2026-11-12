<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
// Hanya admin yang boleh menghapus kategori.
masuk(['admin']);
// Ambil ID kategori yang akan dihapus dari alamat halaman.
$id = id_dari_url();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Kunci kategori dan pastikan tidak ada alat yang masih memakai kategori ini.
    cek_token();
    // Mulai transaksi: perubahan disimpan bersama setelah semua langkah berhasil.
    mysqli_begin_transaction($conn);
    $hasil = mysqli_query($conn, "SELECT * FROM kategori WHERE id_kategori=$id FOR UPDATE");
    $data = mysqli_fetch_assoc($hasil);
    if (!$data) {
        gagal('Kategori tidak ditemukan.');
    }
    $nama = mysqli_real_escape_string($conn, $data['nama_kategori']);
    $cek = mysqli_query($conn, "SELECT id_alat FROM alat WHERE kategori='$nama' LIMIT 1");
    if (mysqli_num_rows($cek)) {
        gagal('Kategori masih digunakan oleh alat.');
    }
    mysqli_query($conn, "DELETE FROM kategori WHERE id_kategori=$id");
    catat('Menghapus kategori #' . $id);
    // Semua langkah berhasil, simpan perubahan ke database.
    mysqli_commit($conn);
    header('Location: kategori-tampil.php');
    exit();
}

buka_halaman('Hapus Kategori');
?>
<!-- Tampilkan konfirmasi sebelum kategori dihapus. -->
<h2>Hapus Kategori #<?php echo $id; ?></h2>
<form method="post">
    <?php token(); ?>
    <button>Hapus</button>
</form>
<p><a href="kategori-tampil.php">Batal</a>
</p>
<?php tutup_halaman(); ?>
