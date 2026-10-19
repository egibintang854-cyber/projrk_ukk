<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
masuk(['admin']);
// Tampilkan 50 baris per halaman.
$halaman = 1;
if (isset($_GET['halaman'])) {
    $halaman = max(1, (int) $_GET['halaman']);
}
$awal = ($halaman-1)*50;
$hasil = mysqli_query($conn, "SELECT * FROM kategori ORDER BY id_kategori DESC LIMIT $awal,50");

buka_halaman('Data Kategori');
?>
<h2>Data Kategori</h2>
<a href="kategori-tambah.php">Tambah Kategori</a><br><br>
<div class="tabel-scroll" role="region" aria-label="Tabel data" tabindex="0">
    <table>
        <tr>
            <th>ID</th>
            <th>Nama Kategori</th>
            <th>Aksi</th>
        </tr>
        <?php while ($data = mysqli_fetch_assoc($hasil)) { ?>
            <tr>
                <td><?php echo $data['id_kategori']; ?></td>
                <td><?php echo aman($data['nama_kategori']); ?></td>
                <td><a href="kategori-edit.php?id=<?php echo $data['id_kategori']; ?>">Edit</a> | <a href="kategori-hapus.php?id=<?php echo $data['id_kategori']; ?>">Hapus</a>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>
<p><a href="?halaman=<?php echo max(1,$halaman-1); ?>">Sebelumnya</a> | <?php echo $halaman; ?> | <a href="?halaman=<?php echo $halaman+1; ?>">Berikutnya</a>
</p>
<?php tutup_halaman(); ?>
