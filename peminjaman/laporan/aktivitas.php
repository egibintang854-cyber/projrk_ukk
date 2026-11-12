<?php
require __DIR__ . '/../config/connection.php';
// Log berisi aktivitas sensitif sehingga hanya dapat dilihat admin.
masuk(['admin']);

// Hitung total baris dan tentukan rentang data untuk navigasi halaman.
$per_halaman = 25;
$jumlah_hasil = mysqli_query($conn, 'SELECT COUNT(*) AS jumlah FROM log_aktivitas');
$total = (int) (mysqli_fetch_assoc($jumlah_hasil)['jumlah'] ?? 0);
$halaman_data = atur_paginasi($total, $per_halaman);
$halaman = $halaman_data['halaman'];
$jumlah_halaman = $halaman_data['jumlah'];
$awal = $halaman_data['awal'];

// Ambil aktivitas terbaru beserta nama akun yang melakukan tindakan.
$stmt = mysqli_prepare(
    $conn,
    "SELECT l.id_log, l.waktu, l.id_user, l.aktivitas, u.nama
    FROM log_aktivitas l
    LEFT JOIN user u ON u.id_user = l.id_user
    ORDER BY l.id_log DESC
    LIMIT ?, ?",
);
mysqli_stmt_bind_param($stmt, 'ii', $awal, $per_halaman);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

$nomor_awal = $total ? $awal + 1 : 0;
$nomor_akhir = $halaman_data['akhir'];
buka_halaman('Log Aktivitas');
?>
<!-- Ringkasan menunjukkan jumlah aktivitas dan rentang baris yang sedang tampil. -->
<header class="judul-halaman">
    <div>
        <p class="teks-atasan">ADMINISTRASI</p>
        <h2>Log Aktivitas</h2>
        <p>Riwayat tindakan pengguna yang tercatat di dalam aplikasi.</p>
    </div>
</header>

<section class="ringkasan-aktivitas" aria-label="Ringkasan log">
    <div>
        <span>Total aktivitas</span>
        <strong><?php echo number_format($total, 0, ',', '.'); ?></strong>
    </div>
    <div>
        <span>Baris ditampilkan</span>
        <strong><?php echo number_format($nomor_awal, 0, ',', '.') .
            '–' .
            number_format($nomor_akhir, 0, ',', '.'); ?></strong>
    </div>
</section>

<div class="tabel-scroll">
    <table>
        <thead>
            <tr>
                <th scope="col">Waktu</th>
                <th scope="col">Pengguna</th>
                <th scope="col">Aktivitas</th>
            </tr>
        </thead>
        <tbody>
            <!-- Tampilkan pesan jika belum ada tindakan yang dicatat. -->
            <?php if ($total === 0) { ?>
                <tr>
                    <td colspan="3" class="tabel-kosong">Belum ada aktivitas yang tercatat.</td>
                </tr>
            <?php } else { ?>
                <!-- Tampilkan waktu, pengguna, dan keterangan untuk tiap aktivitas. -->
                <?php while ($data = mysqli_fetch_assoc($hasil)) { ?>
                    <tr>
                        <td class="waktu-aktivitas"><?php echo aman($data['waktu']); ?></td>
                        <td><?php echo aman(
                            $data['nama'] ?? 'Akun #' . $data['id_user'] . ' (tidak tersedia)',
                        ); ?></td>
                        <td><?php echo aman($data['aktivitas']); ?></td>
                    </tr>
                <?php } ?>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php tampilkan_paginasi($halaman, $jumlah_halaman, 'log aktivitas'); ?>

<?php
mysqli_stmt_close($stmt);
tutup_halaman();


?>
