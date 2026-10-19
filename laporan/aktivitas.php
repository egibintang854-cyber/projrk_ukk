<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
masuk(['admin']);
$halaman = max(1, (int) ($_GET['halaman'] ?? 1));
$awal = ($halaman - 1) * 50;
$hasil = mysqli_query($conn, "SELECT l.*, u.nama FROM log_aktivitas l LEFT JOIN user u ON u.id_user=l.id_user ORDER BY l.id_log DESC LIMIT $awal,50");
buka_halaman('Log Aktivitas');
?>
<h2>Log Aktivitas</h2>
<div class="tabel-scroll">
    <table>
        <tr>
            <th>Waktu</th>
            <th>Pengguna</th>
            <th>Aktivitas</th>
        </tr>
        <?php if (!mysqli_num_rows($hasil)) { ?>
            <tr>
                <td colspan="3">Belum ada aktivitas pada halaman ini.</td>
            </tr>
        <?php } ?>
        <?php while ($data = mysqli_fetch_assoc($hasil)) { ?>
            <tr>
                <td><?php echo aman($data['waktu']); ?></td>
                <td><?php echo aman($data['nama'] ?? ('Akun #' . $data['id_user'] . ' (tidak tersedia)')); ?></td>
                <td><?php echo aman($data['aktivitas']); ?></td>
            </tr>
        <?php } ?>
    </table>
</div>
<p><a href="?halaman=<?php echo max(1, $halaman - 1); ?>">Sebelumnya</a> | Halaman <?php echo $halaman; ?> | <a href="?halaman=<?php echo $halaman + 1; ?>">Berikutnya</a></p>
<?php tutup_halaman(); ?>
