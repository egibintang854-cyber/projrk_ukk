<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
// Semua peran melihat halaman ini; peminjam hanya menerima baris miliknya sendiri.
masuk(['admin', 'petugas', 'peminjam']);

// Siapkan filter pemilik yang digunakan pada hitungan dan daftar transaksi.
$per_halaman = 50;
$filter = '';
$id_user = 0;
if ($_SESSION['level'] === 'peminjam') {
    $filter = ' WHERE id_user = ?';
    $id_user = (int) $_SESSION['id_user'];
}

// Gunakan satu query hitung; filter pemilik hanya ditambahkan untuk peminjam.
$stmt_total = mysqli_prepare($conn, 'SELECT COUNT(*) AS jumlah FROM peminjam' . $filter);
if ($id_user) {
    mysqli_stmt_bind_param($stmt_total, 'i', $id_user);
}
mysqli_stmt_execute($stmt_total);
$total = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_total))['jumlah'];
mysqli_stmt_close($stmt_total);

$halaman_data = atur_paginasi($total, $per_halaman);
$halaman = $halaman_data['halaman'];
$jumlah_halaman = $halaman_data['jumlah'];
$awal = $halaman_data['awal'];
// Ambil satu halaman transaksi dengan prepared statement.
$stmt = mysqli_prepare(
    $conn,
    'SELECT * FROM peminjam' . $filter . ' ORDER BY id_peminjam DESC LIMIT ?, ?',
);
if ($id_user) {
    mysqli_stmt_bind_param($stmt, 'iii', $id_user, $awal, $per_halaman);
} else {
    mysqli_stmt_bind_param($stmt, 'ii', $awal, $per_halaman);
}
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

buka_halaman($_SESSION['level'] === 'peminjam' ? 'Peminjaman Saya' : 'Data Peminjaman');
?>
<!-- Tombol dan aksi baris ditampilkan sesuai kewenangan masing-masing peran. -->
<h2><?php echo $_SESSION['level'] === 'peminjam' ? 'Peminjaman Saya' : 'Data Peminjaman'; ?></h2>

<!-- Admin membuat transaksi untuk anggota, sedangkan peminjam mengajukan miliknya sendiri. -->
<?php if (in_array($_SESSION['level'], ['admin', 'peminjam'], true)) { ?>
    <p><a class="tombol-tautan" href="tambah.php">Ajukan Peminjaman</a></p>
<?php } ?>

<div class="tabel-scroll" role="region" aria-label="Daftar peminjaman" tabindex="0">
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>ID</th>
                <th>Peminjam</th>
                <th>ID Alat</th>
                <th>Tanggal Pinjam</th>
                <th>Jumlah</th>
                <th>Batas Kembali</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <!-- Beri keterangan jika filter tidak menemukan transaksi. -->
            <?php if ($total === 0) { ?>
                <tr><td colspan="9" class="tabel-kosong">Belum ada data peminjaman.</td></tr>
            <?php } ?>
            <!-- Tampilkan setiap transaksi dan aksi yang diizinkan untuk peran aktif. -->
            <?php
            $nomor = $awal + 1;
            while ($data = mysqli_fetch_assoc($hasil)) { ?>
                <tr>
                    <td><?php echo $nomor++; ?></td>
                    <td><?php echo (int) $data['id_peminjam']; ?></td>
                    <td><?php echo aman($data['nama_peminjam']); ?></td>
                    <td><?php echo (int) $data['id_alat']; ?></td>
                    <td><?php echo aman($data['tanggal_pinjam']); ?></td>
                    <td><?php echo (int) $data['jumlah_pinjam']; ?></td>
                    <td><?php echo aman(
                        $data['batas_kembali'] ?: 'Tidak tercatat (arsip lama)',
                    ); ?></td>
                    <td><?php echo aman($data['status']); ?></td>
                    <td class="aksi-tabel">
                        <?php if (
                            $_SESSION['level'] === 'admin' &&
                            in_array($data['status'], ['menunggu', 'ditolak'], true)
                        ) { ?>
                            <a href="edit.php?id=<?php echo (int) $data['id_peminjam']; ?>">Edit</a>
                            <a href="hapus.php?id=<?php echo (int) $data[
                                'id_peminjam'
                            ]; ?>" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                        <?php } ?>
                        <?php if (
                            $_SESSION['level'] === 'petugas' &&
                            $data['status'] === 'menunggu'
                        ) { ?>
                            <a href="proses.php?id=<?php echo (int) $data[
                                'id_peminjam'
                            ]; ?>">Setujui atau Tolak</a>
                        <?php } ?>
                        <?php if (
                            in_array($_SESSION['level'], ['admin', 'peminjam'], true) &&
                            $data['status'] === 'dipinjam'
                        ) { ?>
                            <a href="../pengembalian/tambah.php?id=<?php echo (int) $data[
                                'id_peminjam'
                            ]; ?>">Kembalikan</a>
                        <?php } ?>
                    </td>
                </tr>
            <?php }
            ?>
        </tbody>
    </table>
</div>

<?php tampilkan_paginasi($halaman, $jumlah_halaman, 'daftar peminjaman'); ?>
<?php
mysqli_stmt_close($stmt);
tutup_halaman();
 ?>
