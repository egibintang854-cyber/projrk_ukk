<?php
require __DIR__ . '/../config/connection.php';
// Admin mengelola inventaris; peminjam hanya melihat daftar dan stok alat.
masuk(['admin', 'peminjam']);

// Tentukan halaman aktif dan hitung jumlah alat untuk kontrol paginasi.
$per_halaman = 50;
$hasil_total = mysqli_query($conn, 'SELECT COUNT(*) AS jumlah FROM alat');
$total = (int) mysqli_fetch_assoc($hasil_total)['jumlah'];
$halaman_data = atur_paginasi($total, $per_halaman);
$halaman = $halaman_data['halaman'];
$jumlah_halaman = $halaman_data['jumlah'];
$awal = $halaman_data['awal'];
// Ambil baris alat untuk halaman aktif menggunakan parameter LIMIT.
$stmt = mysqli_prepare($conn, 'SELECT * FROM alat ORDER BY id_alat DESC LIMIT ?, ?');
mysqli_stmt_bind_param($stmt, 'ii', $awal, $per_halaman);
mysqli_stmt_execute($stmt);
$daftar_alat = mysqli_stmt_get_result($stmt);

buka_halaman('Data Alat');
?>
<!-- Aksi tambah, ubah, dan hapus hanya ditampilkan untuk admin. -->
<h2>Data Alat</h2>
<!-- Admin melihat tombol pengelolaan; peminjam hanya melihat informasi inventaris. -->
<?php if ($_SESSION['level'] === 'admin') { ?>
    <a href="form.php">Tambah Data</a> | <a href="kategori-tampil.php">Kelola Kategori</a>
<?php } ?>
<div class="tabel-scroll" role="region" aria-label="Daftar alat" tabindex="0">
    <table>
        <thead><tr><th>No</th><th>ID Alat</th><th>Nama Alat</th><th>Kategori</th><th>Stok</th><th>Kondisi</th><?php if (
            $_SESSION['level'] === 'admin'
        ) { ?><th>Aksi</th><?php } ?></tr></thead>
        <tbody>
        <!-- Buat satu baris tabel untuk setiap alat pada halaman aktif. -->
        <?php
        $nomor = $awal + 1;
        while ($data = mysqli_fetch_assoc($daftar_alat)) { ?>
            <tr>
                <td><?php echo $nomor++; ?></td><td><?php echo (int) $data['id_alat']; ?></td>
                <td><?php echo aman($data['nama_alat']); ?></td><td><?php echo aman(
    $data['kategori'],
); ?></td>
                <td><?php echo (int) $data['stok']; ?></td><td><?php echo aman(
    $data['kondisi'],
); ?></td>
                <?php if (
                    $_SESSION['level'] === 'admin'
                ) { ?><td><a href="form.php?id=<?php echo (int) $data[
    'id_alat'
]; ?>">Edit</a> | <a href="hapus.php?id=<?php echo (int) $data[
    'id_alat'
]; ?>">Hapus</a></td><?php } ?>
            </tr>
        <?php }
        ?>
        <!-- Tampilkan keadaan kosong bila belum ada inventaris. -->
        <?php if ($total === 0) { ?><tr><td colspan="<?php echo $_SESSION['level'] === 'admin'
    ? 7
    : 6; ?>" class="tabel-kosong">Belum ada data alat.</td></tr><?php } ?>
        </tbody>
    </table>
</div>
<?php tampilkan_paginasi($halaman, $jumlah_halaman, 'daftar alat'); ?>
<?php
mysqli_stmt_close($stmt);
tutup_halaman();
 ?>
