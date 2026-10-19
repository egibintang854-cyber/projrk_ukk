<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
masuk(['admin','peminjam']);
// Ambil ID dari alamat halaman, misalnya edit.php?id=1.
$id = 0;
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
}
$pinjam = data_pinjam($id);
cek_pemilik($pinjam);
if ($pinjam['status'] !== 'dipinjam') {
    gagal('Hanya peminjaman berstatus dipinjam yang dapat dikembalikan.');
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    cek_token();
    $tanggal_kembali = tanggal('tanggal_kembali');
    $kondisi = teks('kondisi_alat',50);
    // Mulai transaksi: perubahan disimpan bersama setelah semua langkah berhasil.
    mysqli_begin_transaction($conn);
    $pinjam = data_pinjam($id);
    cek_pemilik($pinjam);
    if ($pinjam['status'] != 'dipinjam') {
        gagal('Peminjaman belum disetujui atau sudah dikembalikan.');
    }
    if ($tanggal_kembali < $pinjam['tanggal_pinjam'] || $tanggal_kembali > date('Y-m-d')) {
        gagal('Tanggal kembali harus antara tanggal pinjam dan hari ini.');
    }
    $denda = hitung_denda_php($pinjam['batas_kembali'], $tanggal_kembali);
    mysqli_query($conn, "INSERT INTO pengembalian
        (id_peminjam, tanggal_kembali, kondisi_alat, status, denda, diverifikasi, arsip)
        VALUES ($id, '$tanggal_kembali', '$kondisi', 'menunggu', $denda, 0, 0)");
    mysqli_query($conn, "UPDATE peminjam SET status='menunggu pengembalian' WHERE id_peminjam=$id");
    catat('Mengajukan pengembalian peminjaman #' . $id);
    // Semua langkah berhasil, simpan perubahan ke database.
    mysqli_commit($conn);
    header('Location: tampil.php');
    exit;
}

buka_halaman('Kembalikan Alat');
?>
<h2>Kembalikan Alat</h2>
<p>Peminjaman #<?php echo $id; ?>. Denda keterlambatan Rp1.000 per hari per transaksi. Pengembalian harus diverifikasi petugas sebelum stok bertambah.</p>
<form method="post">
    <?php token(); ?>
    Tanggal Kembali:<br>
    <input type="date" name="tanggal_kembali" value="<?php echo date('Y-m-d'); ?>" required><br><br>
    Kondisi Alat:<br>
    <input name="kondisi_alat" maxlength="50" required><br><br>
    <button>Simpan Pengembalian</button>
</form>
<p><a href="../peminjaman/tampil.php">Kembali</a>
</p>
<?php tutup_halaman(); ?>
