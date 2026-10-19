<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
masuk(['admin']);
$halaman = max(1, (int) ($_GET['halaman'] ?? 1));
$awal = ($halaman-1)*50;
$hasil = mysqli_query($conn, "SELECT * FROM anggota ORDER BY id_anggota DESC LIMIT $awal,50");

buka_halaman('Data Anggota');
?>
<h2>Data Anggota</h2>
<a href="anggota-form.php">Tambah Anggota</a><br><br>
<div class="tabel-scroll" role="region" aria-label="Tabel data" tabindex="0">
    <table>
        <tr>
            <th>ID Anggota</th>
            <th>Nama Lengkap</th>
            <th>Alamat</th>
            <th>Aksi</th>
        </tr>
        <?php if (mysqli_num_rows($hasil) == 0) { ?>
            <tr>
                <td colspan="4">Belum ada data anggota pada halaman ini.</td>
            </tr>
        <?php } ?>
        <?php while ($data = mysqli_fetch_assoc($hasil)) { ?>
            <tr>
                <td><?php echo aman($data['nomor_anggota']); ?></td>
                <td><?php echo aman($data['nama']); ?></td>
                <td><?php echo aman($data['alamat']); ?></td>
                <td><a href="anggota-form.php?id=<?php echo $data['id_anggota']; ?>">Edit</a> | <a href="anggota-hapus.php?id=<?php echo $data['id_anggota']; ?>">Hapus</a>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>
<p><a href="?halaman=<?php echo max(1,$halaman-1); ?>">Sebelumnya</a> | <?php echo $halaman; ?> | <a href="?halaman=<?php echo $halaman+1; ?>">Berikutnya</a>
</p>
<?php tutup_halaman(); ?>
