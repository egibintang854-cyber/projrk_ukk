<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
// Penghapusan transaksi peminjaman hanya dapat dilakukan oleh admin.
masuk(['admin']);

// Ambil ID peminjaman yang akan dihapus dari alamat halaman.
$id = id_dari_url();

$pengajuan = data_pinjam($id);
// Hanya izinkan penghapusan pengajuan yang belum aktif dan belum memiliki riwayat kembali.
cek_pemilik($pengajuan);
if (!in_array($pengajuan['status'], ['menunggu', 'ditolak'], true)) {
    gagal('Hanya pengajuan menunggu atau ditolak yang dapat dihapus.');
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Periksa ulang status setelah transaksi dimulai agar data tetap konsisten.
    cek_token();
    // Mulai transaksi: perubahan disimpan bersama setelah semua langkah berhasil.
    mysqli_begin_transaction($conn);
    $data = data_pinjam($id);
    cek_pemilik($data);
    if (!in_array($data['status'], ['menunggu', 'ditolak'])) {
        gagal('Transaksi aktif, selesai, dan arsip harus dipertahankan.');
    }
    $cek = mysqli_query($conn, "SELECT id_pengembalian FROM pengembalian WHERE id_peminjam=$id");
    if (mysqli_num_rows($cek)) {
        gagal('Peminjaman memiliki riwayat pengembalian.');
    }
    mysqli_query($conn, "DELETE FROM peminjam WHERE id_peminjam=$id");
    catat('Menghapus peminjam #' . $id);
    mysqli_commit($conn);

    header('Location: tampil.php');
    exit();
}

buka_halaman('Hapus peminjam');
?>
<!-- Minta konfirmasi sebelum pengajuan dihapus. -->
<h2>Hapus peminjam</h2>
<p>Yakin ingin menghapus data #<?php echo $id; ?>?</p>
<form method="post">
    <?php token(); ?>
    <button>Hapus</button>
</form>
<p><a href="tampil.php">Batal</a>
</p>
<?php tutup_halaman(); ?>
