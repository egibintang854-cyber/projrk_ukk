<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
masuk(['admin']);
$halaman = max(1, (int) ($_GET['halaman'] ?? 1));
$awal = ($halaman - 1) * 50;
$hasil = mysqli_query($conn, "SELECT id_user, username, nama, level FROM user ORDER BY id_user DESC LIMIT $awal, 50");
buka_halaman('Data Pengguna');
?>
<h2>Data Pengguna</h2>
<p><a href="pengguna-form.php">Tambah Pengguna</a></p>
<div class="tabel-scroll">
    <table>
        <tr>
            <th>Username</th>
            <th>Nama</th>
            <th>Peran</th>
            <th>Aksi</th>
        </tr>
        <?php while ($data = mysqli_fetch_assoc($hasil)) { ?>
            <tr>
                <td><?php echo aman($data['username']); ?></td>
                <td><?php echo aman($data['nama']); ?></td>
                <td><?php echo aman(ucfirst($data['level'])); ?></td>
                <td><a href="pengguna-form.php?id=<?php echo $data['id_user']; ?>">Edit</a> |
                    <a href="pengguna-hapus.php?id=<?php echo $data['id_user']; ?>">Hapus</a>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>
<p><a href="?halaman=<?php echo max(1, $halaman - 1); ?>">Sebelumnya</a> |
    Halaman <?php echo $halaman; ?> | <a href="?halaman=<?php echo $halaman + 1; ?>">Berikutnya</a></p>
<?php tutup_halaman(); ?>
