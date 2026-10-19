<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
masuk(['admin', 'peminjam']);

if (isset($_POST['simpan'])) {
    cek_token();

    $id_alat = angka('id_alat');
    $tanggal_pinjam = tanggal('tanggal_pinjam');
    $jumlah_pinjam = angka('jumlah_pinjam');

    $batas_kembali = tanggal('batas_kembali');
    $id_user = 'NULL';
    if ($_SESSION['level'] == 'peminjam') {
        $id_user = (int) $_SESSION['id_user'];
        $nama_peminjam = mysqli_real_escape_string($conn, $_SESSION['nama']);
    } else {
        $id_user = angka('id_user');
        $hasil = mysqli_query($conn, "SELECT a.nama FROM anggota a JOIN user u ON u.id_user=a.id_user WHERE a.id_user=$id_user AND u.level='peminjam'");
        $anggota = mysqli_fetch_assoc($hasil);
        if (!$anggota) {
            gagal('Pilih anggota yang valid.');
        }
        $nama_peminjam = mysqli_real_escape_string($conn, $anggota['nama']);
    }
    // Mulai transaksi: perubahan disimpan bersama setelah semua langkah berhasil.
    mysqli_begin_transaction($conn);
    cek_pinjam($id_alat, $jumlah_pinjam, $tanggal_pinjam, $batas_kembali);

    mysqli_query($conn, "INSERT INTO peminjam
    (nama_peminjam, id_alat, tanggal_pinjam, jumlah_pinjam, id_user, batas_kembali, status)
    VALUES
    ('$nama_peminjam', '$id_alat', '$tanggal_pinjam', '$jumlah_pinjam', $id_user, '$batas_kembali', 'menunggu')");

    catat('Menambah peminjam');
    // Semua langkah berhasil, simpan perubahan ke database.
    mysqli_commit($conn);
    header("Location: tampil.php");
    exit;
}

buka_halaman('Tambah Data Peminjam');
?>
<h2>Tambah Data Peminjam</h2>
<form method="POST">
    <?php token(); ?>

    Nama Peminjam:
    <br>
    <?php if ($_SESSION['level'] == 'peminjam') { ?>
        <input value="<?php echo aman($_SESSION['nama']); ?>" readonly>
    <?php } else { ?>
        <select name="id_user" required>
            <option value="" selected disabled>-- Pilih Anggota --</option>
            <?php $anggota = mysqli_query($conn, "SELECT a.* FROM anggota a JOIN user u ON u.id_user=a.id_user WHERE LOWER(u.level)='peminjam' ORDER BY a.id_anggota"); ?>
            <?php while ($orang = mysqli_fetch_assoc($anggota)) { ?>
                <option value="<?php echo (int) $orang['id_user']; ?>"><?php echo aman($orang['nomor_anggota'] . ' - ' . $orang['nama']); ?></option>
            <?php } ?>
        </select>
    <?php } ?>
    <br><br>

    ID Alat:
    <br>
    <?php pilihan_alat(); ?>
    <br><br>

    Tanggal Pinjam:
    <br>
    <input type="date" name="tanggal_pinjam" required value="<?php echo date('Y-m-d'); ?>">
    <br><br>

    Jumlah Pinjam:
    <br>
    <input type="number" name="jumlah_pinjam" min="1" required>
    <br><br>

    Batas Pengembalian:<br>
    <input type="date" name="batas_kembali" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" required><br><br>
    <p>Stok berkurang setelah petugas menyetujui.</p>
    <button type="submit" name="simpan">
        Simpan
    </button>
</form>

<br>

<a href="tampil.php">Kembali</a>
<?php tutup_halaman(); ?>
