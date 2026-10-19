<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
masuk(['admin', 'peminjam']);

// Ambil ID dari alamat halaman, misalnya edit.php?id=1.
$id = 0;
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
}

$pengajuan = data_pinjam($id);
cek_pemilik($pengajuan);
if (!in_array($pengajuan['status'], ['menunggu','ditolak'], true)) {
    gagal('Hanya pengajuan menunggu atau ditolak yang dapat dihapus.');
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    cek_token();
    // Mulai transaksi: perubahan disimpan bersama setelah semua langkah berhasil.
    mysqli_begin_transaction($conn);
    $data = data_pinjam($id);
    cek_pemilik($data);
    if (!in_array($data['status'], ['menunggu','ditolak'])) {
        gagal('Transaksi aktif, selesai, dan arsip harus dipertahankan.');
    }
    $cek = mysqli_query($conn, "SELECT id_pengembalian FROM pengembalian WHERE id_peminjam=$id");
    if (mysqli_num_rows($cek)) {
        gagal('Peminjaman memiliki riwayat pengembalian.');
    }
    mysqli_query($conn, "DELETE FROM peminjam WHERE id_peminjam=$id");
    catat('Menghapus peminjam #' . $id);
    mysqli_commit($conn);

    header("Location: tampil.php");
    exit;
}

buka_halaman('Hapus peminjam');
?>
<h2>Hapus peminjam</h2>
<p>Yakin ingin menghapus data #<?php echo $id; ?>?</p>
<form method="post">
    <?php token(); ?>
    <button>Hapus</button>
</form>
<p><a href="tampil.php">Batal</a>
</p>
<?php tutup_halaman(); ?>
