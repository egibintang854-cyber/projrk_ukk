<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
masuk(['admin']);
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
    if (!$data || $data['arsip']) {
        gagal('Data tidak ditemukan atau merupakan arsip lama.');
    }
    $id_pinjam = (int) $data['id_peminjam'];
    $pinjam = data_pinjam($id_pinjam);
    if ($data['diverifikasi']) {
        $id_alat = (int) $pinjam['id_alat'];
        $jumlah = (int) $pinjam['jumlah_pinjam'];
        mysqli_query($conn, "UPDATE alat SET stok=stok-$jumlah WHERE id_alat=$id_alat AND stok >= $jumlah");
        if (mysqli_affected_rows($conn) != 1) {
            gagal('Stok tidak cukup untuk membatalkan pengembalian.');
        }
    }
    mysqli_query($conn, "DELETE FROM pengembalian WHERE id_pengembalian=$id");
    mysqli_query($conn, "UPDATE peminjam SET status='dipinjam' WHERE id_peminjam=$id_pinjam");
    catat('Membatalkan pengembalian #' . $id);
    // Semua langkah berhasil, simpan perubahan ke database.
    mysqli_commit($conn);
    header('Location: tampil.php');
    exit;
}

buka_halaman('Hapus Pengembalian');
?>
<h2>Hapus Pengembalian #<?php echo $id; ?></h2>
<p>Peminjaman kembali berstatus dipinjam. Jika sudah diverifikasi, stok akan dikurangi kembali.</p>
<form method="post">
    <?php token(); ?>
    <button>Hapus Pengembalian</button>
</form>
<p><a href="tampil.php">Batal</a>
</p>
<?php tutup_halaman(); ?>
