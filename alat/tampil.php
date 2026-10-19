<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
masuk(['admin', 'petugas', 'peminjam']);

// Tampilkan 50 baris per halaman.
$halaman = max(1, (int) ($_GET['halaman'] ?? 1));
$awal = ($halaman - 1) * 50;
$query = mysqli_query($conn, "SELECT * FROM alat ORDER BY id_alat DESC LIMIT $awal, 50");

buka_halaman('Data Alat');
?>
<h2>Data Alat</h2>

<?php if ($_SESSION['level'] == 'admin') { ?>
    <a href="tambah.php">Tambah Data</a> | <a href="kategori-tampil.php">Kelola Kategori</a>
<?php } ?>

<br><br>
<div class="tabel-scroll" role="region" aria-label="Tabel data" tabindex="0">
    <table>
        <tr>
            <th>No</th>
            <th>ID Alat</th>
            <th>Nama Alat</th>
            <th>Kategori</th>
            <th>Stok</th>
            <th>Kondisi</th>
            <?php if ($_SESSION['level'] == 'admin') { ?>
            <th>Aksi</th><?php } ?>
        </tr>

        <?php
        $no = $awal + 1;

        while ($data = mysqli_fetch_assoc($query)) {
            ?>

            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo aman($data['id_alat']); ?></td>
                <td><?php echo aman($data['nama_alat']); ?></td>
                <td><?php echo aman($data['kategori']); ?></td>
                <td><?php echo aman($data['stok']); ?></td>
                <td><?php echo aman($data['kondisi']); ?></td>

                <?php if ($_SESSION['level'] == 'admin') { ?>
                    <td>
                        <a href="edit.php?id=<?php echo aman($data['id_alat']); ?>">Edit</a> |
                        <a href="hapus.php?id=<?php echo aman($data['id_alat']); ?>"
                           onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                    </td>
                <?php } ?>
            </tr>
        <?php } ?>
    </table>
</div>
<p><a href="?halaman=<?php echo max(1, $halaman-1); ?>">Sebelumnya</a> | Halaman <?php echo $halaman; ?> | <a href="?halaman=<?php echo $halaman+1; ?>">Berikutnya</a>
</p>
<?php tutup_halaman(); ?>
