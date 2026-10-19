<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
masuk(['admin', 'petugas', 'peminjam']);

// Tampilkan 50 baris per halaman.
$halaman = 1;
if (isset($_GET['halaman'])) {
    $halaman = max(1, (int) $_GET['halaman']);
}
$awal = ($halaman - 1) * 50;
$filter = ' WHERE 1=1';
if ($_SESSION['level'] == 'peminjam') {
    $filter .= ' AND id_user=' . (int) $_SESSION['id_user'];
}
$query = mysqli_query($conn, "SELECT * FROM peminjam $filter ORDER BY id_peminjam DESC LIMIT $awal, 50");

buka_halaman('Data Peminjam');
?>
<h2>Data Peminjam</h2>

<?php if (in_array($_SESSION['level'], ['admin','peminjam'])) { ?>
    <a href="tambah.php">Tambah Data</a>
<?php } ?>

<br><br>
<div class="tabel-scroll" role="region" aria-label="Tabel data" tabindex="0">
    <table>
        <tr>
            <th>No</th>
            <th>ID Peminjam</th>
            <th>Nama Peminjam</th>
            <th>ID Alat</th>
            <th>Tanggal Pinjam</th>
            <th>Jumlah Pinjam</th>
            <th>Batas Kembali</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>

        <?php
        $no = $awal + 1;

        if (mysqli_num_rows($query) == 0) {
            echo '<tr><td colspan="9">Belum ada peminjaman pada halaman ini.</td></tr>';
        }

        while ($data = mysqli_fetch_assoc($query)) {
            ?>

            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo aman($data['id_peminjam']); ?></td>
                <td><?php echo aman($data['nama_peminjam']); ?></td>
                <td><?php echo aman($data['id_alat']); ?></td>
                <td><?php echo aman($data['tanggal_pinjam']); ?></td>
                <td><?php echo aman($data['jumlah_pinjam']); ?></td>
                <td><?php echo aman($data['batas_kembali'] ?: 'Tidak tercatat (arsip lama)'); ?></td>
                <td><?php echo aman($data['status']); ?></td>
                <td>
                    <?php if (in_array($_SESSION['level'], ['admin','peminjam']) && in_array($data['status'], ['menunggu','ditolak'])) { ?>
                        <a href="edit.php?id=<?php echo aman($data['id_peminjam']); ?>">Edit</a>

                        |

                        <a href="hapus.php?id=<?php echo aman($data['id_peminjam']); ?>"
                        onclick="return confirm('Yakin ingin menghapus data ini?')">
                        Hapus
                    </a>
                <?php } ?>
                <?php if (in_array($_SESSION['level'], ['admin','petugas']) && $data['status'] == 'menunggu') { ?>
                    <a href="proses.php?id=<?php echo aman($data['id_peminjam']); ?>">Proses</a>
                <?php } ?>
                <?php if (in_array($_SESSION['level'], ['admin','peminjam']) && $data['status'] == 'dipinjam') { ?>
                    <a href="../pengembalian/tambah.php?id=<?php echo aman($data['id_peminjam']); ?>">Kembalikan</a>
                <?php } ?>
            </td>
        </tr>

        <?php
    }
    ?>

</table>
</div>
<p><a href="?halaman=<?php echo max(1, $halaman-1); ?>">Sebelumnya</a> | Halaman <?php echo $halaman; ?> | <a href="?halaman=<?php echo $halaman+1; ?>">Berikutnya</a>
</p>
<?php tutup_halaman(); ?>
