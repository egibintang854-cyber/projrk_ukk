<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
// Admin dan petugas melihat seluruh pengembalian; peminjam hanya melihat miliknya.
masuk(['admin', 'petugas', 'peminjam']);
// Batasi daftar ke 50 baris dan sembunyikan arsip dari transaksi aktif.
$per_halaman = 50;
$filter = ' WHERE k.arsip=0';
if ($_SESSION['level'] == 'peminjam') {
    $filter .= ' AND p.id_user=' . (int) $_SESSION['id_user'];
}

// Hitung jumlah hasil agar navigasi halaman sesuai dengan data yang terlihat.
$hasil_total = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS jumlah FROM pengembalian k
    LEFT JOIN peminjam p ON p.id_peminjam=k.id_peminjam $filter",
);
$total = (int) mysqli_fetch_assoc($hasil_total)['jumlah'];
$halaman_data = atur_paginasi($total, $per_halaman);
$halaman = $halaman_data['halaman'];
$jumlah_halaman = $halaman_data['jumlah'];
$awal = $halaman_data['awal'];

// Ambil hanya pengembalian pada halaman aktif.
$hasil = mysqli_query(
    $conn,
    "SELECT k.*, p.nama_peminjam, a.nama_alat FROM pengembalian k
    LEFT JOIN peminjam p ON p.id_peminjam=k.id_peminjam
    LEFT JOIN alat a ON a.id_alat=p.id_alat $filter
    ORDER BY k.id_pengembalian DESC LIMIT $awal, $per_halaman",
);

buka_halaman('Data Pengembalian');
?>
<!-- Tabel menampilkan status pengembalian dan aksi yang diizinkan untuk setiap peran. -->
<h2>Data Pengembalian</h2>
<!-- Peminjam diarahkan ke transaksi aktif untuk mengajukan pengembalian. -->
<?php if ($_SESSION['level'] != 'petugas') { ?>
    <p><a href="../peminjaman/tampil.php">Pilih peminjaman untuk menambah pengembalian</a>
</p><?php } ?>
<div class="tabel-scroll" role="region" aria-label="Tabel data" tabindex="0">
    <table>
        <tr>
            <th>ID</th>
            <th>ID Pinjam</th>
            <th>Peminjam</th>
            <th>Alat</th>
            <th>Tanggal Kembali</th>
            <th>Kondisi</th>
            <th>Denda</th>
            <th>Status</th><?php if ($_SESSION['level'] != 'peminjam') { ?>
            <th>Aksi</th><?php } ?></tr>
        <!-- Tampilkan pesan bila belum ada transaksi pengembalian. -->
        <?php if (mysqli_num_rows($hasil) == 0) { ?>
            <tr>
                <td colspan="<?php echo $_SESSION['level'] == 'peminjam'
                    ? 8
                    : 9; ?>">Belum ada pengembalian. Alat yang sedang dipinjam dapat dilihat pada halaman peminjaman.</td>
            </tr>
        <?php } ?>
        <!-- Tampilkan setiap pengembalian berikut status dan aksi sesuai peran. -->
        <?php while ($data = mysqli_fetch_assoc($hasil)) { ?>
            <tr>
                <td><?php echo aman($data['id_pengembalian']); ?></td>
                <td><?php echo aman($data['id_peminjam']); ?></td>
                <td><?php echo aman(
                    $data['nama_peminjam'] ?? 'Data peminjaman lama tidak tersedia',
                ); ?></td>
                <td><?php echo aman($data['nama_alat'] ?? 'Data alat lama tidak tersedia'); ?></td>
                <td><?php echo aman($data['tanggal_kembali']); ?></td>
                <td><?php echo aman($data['kondisi_alat']); ?></td>
                <td><?php echo aman($data['denda']); ?></td>
                <td>
                    <?php if ($data['arsip']) {
                        echo 'arsip lama';
                    } else {
                        echo aman($data['status']);
                    } ?>
                </td>
                <?php if ($_SESSION['level'] != 'peminjam') { ?>
                    <td>
                        <?php if ($_SESSION['level'] == 'admin' && !$data['arsip']) { ?>
                            <a href="edit.php?id=<?php echo $data['id_pengembalian']; ?>">Edit</a> |
                            <a href="hapus.php?id=<?php echo $data['id_pengembalian']; ?>">Hapus</a>
                        <?php } ?>
                        <?php if (
                            $_SESSION['level'] === 'petugas' &&
                            !$data['arsip'] &&
                            !$data['diverifikasi']
                        ) { ?>
                            <a href="verifikasi.php?id=<?php echo $data[
                                'id_pengembalian'
                            ]; ?>">Verifikasi</a>
                        <?php } ?>
                </td><?php } ?></tr>
        <?php } ?>
    </table>
</div>
<?php tampilkan_paginasi($halaman, $jumlah_halaman, 'data pengembalian'); ?>
<?php tutup_halaman(); ?>
