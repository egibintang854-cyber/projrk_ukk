<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
masuk(['admin', 'petugas']);
$mulai = $_GET['mulai'] ?? date('Y-m-01');
$selesai = $_GET['selesai'] ?? date('Y-m-d');
if (!cek_tanggal($mulai) || !cek_tanggal($selesai) || $selesai < $mulai) {
    gagal('Rentang tanggal tidak valid.');
}
// Gabungkan data peminjaman, alat, dan pengembalian untuk laporan.
$stmt = mysqli_prepare($conn, "SELECT p.*, a.nama_alat, k.tanggal_kembali, k.denda, k.diverifikasi
    FROM peminjam p
    LEFT JOIN alat a ON a.id_alat = p.id_alat
    LEFT JOIN pengembalian k ON k.id_peminjam = p.id_peminjam AND k.arsip = 0
    WHERE p.tanggal_pinjam BETWEEN ? AND ?
    ORDER BY p.id_peminjam DESC
    LIMIT 1000");
mysqli_stmt_bind_param($stmt, 'ss', $mulai, $selesai);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);
$data_laporan = mysqli_fetch_all($hasil, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
buka_halaman('Laporan Peminjaman');
?>
<h2>Laporan Peminjaman dan Pengembalian</h2>
<form method="get" class="tanpa-cetak">
    <label>Dari tanggal pinjam <input type="date" name="mulai" value="<?php echo aman($mulai); ?>" required>
    </label>
    <label>Sampai <input type="date" name="selesai" value="<?php echo aman($selesai); ?>" required>
    </label>
    <button>Tampilkan</button>
    <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
</form>
<p>Periode tanggal pinjam: <?php echo aman($mulai); ?> sampai <?php echo aman($selesai); ?>.</p>
<p>Maksimal 1.000 baris. Persempit periode jika jumlah baris mencapai batas.</p>
<div class="tabel-scroll">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Peminjam</th>
                <th>Alat</th>
                <th>Jumlah</th>
                <th>Pinjam</th>
                <th>Batas</th>
                <th>Kembali</th>
                <th>Status</th>
                <th>Denda</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$data_laporan) { ?>
                <tr>
                    <td colspan="9">Tidak ada transaksi pada periode ini.</td>
                </tr>
            <?php } ?>
            <?php foreach ($data_laporan as $data) { ?>
                <tr>
                    <td><?php echo (int) $data['id_peminjam']; ?></td>
                    <td><?php echo aman($data['nama_peminjam']); ?></td>
                    <td><?php echo aman($data['nama_alat'] ?? 'Alat tidak tersedia'); ?></td>
                    <td><?php echo (int) $data['jumlah_pinjam']; ?></td>
                    <td><?php echo aman($data['tanggal_pinjam']); ?></td>
                    <td><?php echo aman($data['batas_kembali'] ?? '-'); ?></td>
                    <td><?php echo aman($data['tanggal_kembali'] ?? '-'); ?></td>
                    <td><?php echo aman($data['status']); ?></td>
                    <td>Rp<?php echo number_format((int) $data['denda'], 0, ',', '.'); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<p>Dicetak oleh <?php echo aman($_SESSION['nama']); ?>. Jumlah baris: <?php echo count($data_laporan); ?>.</p>
<?php tutup_halaman(); ?>
