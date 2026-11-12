<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
// Daftar kategori dan tombol pengelolaannya hanya untuk admin.
masuk(['admin']);
// Hitung halaman yang tersedia dan ambil maksimal 50 kategori.
$per_halaman = 50;
$hasil_total = mysqli_query($conn, 'SELECT COUNT(*) AS jumlah FROM kategori');
$total = (int) mysqli_fetch_assoc($hasil_total)['jumlah'];
$halaman_data = atur_paginasi($total, $per_halaman);
$halaman = $halaman_data['halaman'];
$hasil = mysqli_query(
    $conn,
    'SELECT * FROM kategori ORDER BY id_kategori DESC LIMIT ' .
        $halaman_data['awal'] .
        ', ' .
        $per_halaman,
);

buka_halaman('Data Kategori');
?>
<h2>Data Kategori</h2>
<a href="kategori-form.php">Tambah Kategori</a><br><br>
<div class="tabel-scroll" role="region" aria-label="Tabel data" tabindex="0">
    <!-- Daftar kategori ditampilkan bersama tombol ubah dan hapus untuk admin. -->
    <table>
        <tr>
            <th>ID</th>
            <th>Nama Kategori</th>
            <th>Aksi</th>
        </tr>
        <!-- Ambil hasil query satu per satu untuk membentuk baris tabel. -->
        <?php while ($data = mysqli_fetch_assoc($hasil)) { ?>
            <tr>
                <td><?php echo $data['id_kategori']; ?></td>
                <td><?php echo aman($data['nama_kategori']); ?></td>
                <td><a href="kategori-form.php?id=<?php echo $data[
                    'id_kategori'
                ]; ?>">Edit</a> | <a href="kategori-hapus.php?id=<?php echo $data[
    'id_kategori'
]; ?>">Hapus</a>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>
<?php tampilkan_paginasi($halaman, $halaman_data['jumlah'], 'kategori'); ?>
<?php tutup_halaman(); ?>
