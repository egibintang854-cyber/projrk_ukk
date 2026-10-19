<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
masuk(['admin', 'petugas']);
// Ambil ID dari alamat halaman, misalnya edit.php?id=1.
$id = 0;
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    cek_token();
    // Mulai transaksi: perubahan disimpan bersama setelah semua langkah berhasil.
    mysqli_begin_transaction($conn);
    $hasil = mysqli_query($conn, "SELECT * FROM pengembalian WHERE id_pengembalian=$id FOR UPDATE");
    $data = mysqli_fetch_assoc($hasil);
    if (!$data || $data['arsip'] || $data['diverifikasi']) {
        gagal('Pengembalian tidak dapat diverifikasi lagi.');
    }
    $id_pinjam = (int) $data['id_peminjam'];
    $pinjam = data_pinjam($id_pinjam);
    if ($pinjam['status'] != 'menunggu pengembalian') {
        gagal('Status peminjaman tidak sesuai.');
    }
    $id_alat = (int) $pinjam['id_alat'];
    $jumlah = (int) $pinjam['jumlah_pinjam'];
    mysqli_query($conn, "UPDATE alat SET stok=stok+$jumlah WHERE id_alat=$id_alat");
    if (mysqli_affected_rows($conn) != 1) {
        gagal('Alat tidak ditemukan.');
    }
    mysqli_query($conn, "UPDATE pengembalian SET diverifikasi=1, status='dikembalikan' WHERE id_pengembalian=$id");
    mysqli_query($conn, "UPDATE peminjam SET status='dikembalikan' WHERE id_peminjam=$id_pinjam");
    catat('Memverifikasi pengembalian #' . $id);
    // Semua langkah berhasil, simpan perubahan ke database.
    mysqli_commit($conn);
    header('Location: tampil.php');
    exit;
}

buka_halaman('Verifikasi Pengembalian');
?>
<h2>Verifikasi Pengembalian #<?php echo $id; ?></h2>
<p>Pastikan alat telah diterima dan tanggal serta kondisinya sesuai. Data yang keliru dapat diperbaiki admin.</p>
<form method="post">
    <?php token(); ?>
    <button>Alat Sudah Diterima</button>
</form>
<p><a href="tampil.php">Kembali</a>
</p>
<?php tutup_halaman(); ?>
