<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
// Perubahan data pengembalian yang sudah tercatat merupakan hak admin.
masuk(['admin']);
// Ambil ID pengembalian yang akan diedit dari alamat halaman.
$id = id_dari_url();
$hasil = mysqli_query($conn, "SELECT * FROM pengembalian WHERE id_pengembalian=$id");
$data = mysqli_fetch_assoc($hasil);
// Data arsip lama hanya untuk riwayat sehingga tidak dapat diedit.
if (!$data || $data['arsip']) {
    gagal('Data tidak ditemukan atau merupakan arsip lama.');
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi tanggal dan kondisi, lalu hitung ulang denda sebelum menyimpan.
    cek_token();
    $tanggal_kembali = tanggal('tanggal_kembali');
    $kondisi = teks('kondisi_alat', 50);
    // Mulai transaksi: perubahan disimpan bersama setelah semua langkah berhasil.
    mysqli_begin_transaction($conn);
    $hasil = mysqli_query($conn, "SELECT * FROM pengembalian WHERE id_pengembalian=$id FOR UPDATE");
    $data = mysqli_fetch_assoc($hasil);
    if (!$data || $data['arsip']) {
        gagal('Pengembalian tidak ditemukan.');
    }
    $id_pinjam = (int) $data['id_peminjam'];
    $pinjam = data_pinjam($id_pinjam);
    cek_tanggal_kembali($tanggal_kembali, $pinjam['tanggal_pinjam']);
    $denda = hitung_denda_php($pinjam['batas_kembali'], $tanggal_kembali);
    mysqli_query(
        $conn,
        "UPDATE pengembalian SET tanggal_kembali='$tanggal_kembali', kondisi_alat='$kondisi',
        denda=$denda WHERE id_pengembalian=$id",
    );
    catat('Mengedit pengembalian #' . $id);
    // Semua langkah berhasil, simpan perubahan ke database.
    mysqli_commit($conn);
    header('Location: tampil.php');
    exit();
}

buka_halaman('Edit Pengembalian');
?>
<!-- Formulir admin untuk memperbaiki tanggal dan kondisi pengembalian. -->
<h2>Edit Pengembalian</h2>
<form method="post">
    <?php token(); ?>
    Tanggal Kembali:<br>
    <input type="date" name="tanggal_kembali" min="<?php echo aman(
        $pinjam['tanggal_pinjam'],
    ); ?>" max="<?php echo aman(date('Y-m-d')); ?>" value="<?php echo aman(
    $data['tanggal_kembali'],
); ?>" required><br><br>
    Kondisi Alat:<br>
    <input name="kondisi_alat" maxlength="50" value="<?php echo aman(
        $data['kondisi_alat'],
    ); ?>" required><br><br>
    <button>Simpan</button>
</form>
<p><a href="tampil.php">Kembali</a>
</p>
<?php tutup_halaman(); ?>
