<?php
require __DIR__ . '/../config/connection.php';
// Operasi penghapusan inventaris hanya tersedia untuk admin.
masuk(['admin']);
// Cari nama alat untuk ditampilkan pada halaman konfirmasi.
$id = id_dari_url();
$stmt = mysqli_prepare($conn, 'SELECT nama_alat FROM alat WHERE id_alat = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);
$alat = mysqli_fetch_assoc($hasil);
if (!$alat) {
    gagal('Alat tidak ditemukan.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kunci alat, pastikan tidak memiliki riwayat, lalu hapus dalam satu transaksi.
    cek_token();
    mysqli_begin_transaction($conn);
    $stmt = mysqli_prepare($conn, 'SELECT id_alat FROM alat WHERE id_alat = ? FOR UPDATE');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    if (!mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))) {
        gagal('Alat tidak ditemukan. Muat ulang daftar alat.');
    }
    $stmt = mysqli_prepare($conn, 'SELECT id_peminjam FROM peminjam WHERE id_alat = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    if (mysqli_num_rows(mysqli_stmt_get_result($stmt))) {
        gagal('Alat masih memiliki riwayat peminjaman.');
    }
    $stmt = mysqli_prepare($conn, 'DELETE FROM alat WHERE id_alat = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    catat('Menghapus alat #' . $id);
    mysqli_commit($conn);
    header('Location: tampil.php');
    exit();
}

buka_halaman('Hapus Data Alat');
?>
<!-- Minta konfirmasi admin sebelum alat dihapus secara permanen. -->
<h2>Hapus Data Alat</h2>
<p>Yakin ingin menghapus alat <strong><?php echo aman(
    $alat['nama_alat'],
); ?></strong> (ID <?php echo $id; ?>)?</p>
<p>Alat yang memiliki riwayat peminjaman tidak dapat dihapus.</p>
<form method="post">
    <?php token(); ?>
    <button type="submit">Hapus</button> <a href="tampil.php">Batal</a>
</form>
<?php tutup_halaman(); ?>
