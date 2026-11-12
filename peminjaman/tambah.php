<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
// Admin membuat transaksi untuk anggota; peminjam mengajukan untuk akunnya sendiri.
masuk(['admin', 'peminjam']);
// Tetapkan tanggal hari ini serta tanggal batas pengembalian awal.
$hari_ini = date('Y-m-d');
$akhir_tahun = date('Y-12-31');
$tanggal_default_kembali = min(date('Y-m-d', strtotime('+7 days')), $akhir_tahun);

if (isset($_POST['simpan'])) {
    // Validasi token dan nilai formulir sebelum memproses pengajuan.
    cek_token();

    $id_alat = angka('id_alat');
    $tanggal_pinjam = tanggal('tanggal_pinjam');
    $jumlah_pinjam = angka('jumlah_pinjam');

    $batas_kembali = tanggal('batas_kembali');
    cek_rentang_tanggal_peminjaman($tanggal_pinjam, $batas_kembali);
    $id_user = 'NULL';
    // Ambil identitas peminjam dari sesi atau validasi pilihan anggota oleh admin.
    if ($_SESSION['level'] == 'peminjam') {
        $id_user = (int) $_SESSION['id_user'];
        $nama_peminjam = mysqli_real_escape_string($conn, $_SESSION['nama']);
    } else {
        $id_user = angka('id_user');
        $hasil = mysqli_query(
            $conn,
            "SELECT a.nama FROM anggota a JOIN user u ON u.id_user=a.id_user WHERE a.id_user=$id_user AND u.level='peminjam'",
        );
        $anggota = mysqli_fetch_assoc($hasil);
        if (!$anggota) {
            gagal('Pilih anggota yang valid.');
        }
        $nama_peminjam = mysqli_real_escape_string($conn, $anggota['nama']);
    }
    // Mulai transaksi: perubahan disimpan bersama setelah semua langkah berhasil.
    mysqli_begin_transaction($conn);
    // Pastikan alat dan stok masih sesuai sebelum pengajuan dicatat.
    cek_pinjam($id_alat, $jumlah_pinjam, $tanggal_pinjam, $batas_kembali);

    mysqli_query(
        $conn,
        "INSERT INTO peminjam
    (nama_peminjam, id_alat, tanggal_pinjam, jumlah_pinjam, id_user, batas_kembali, status)
    VALUES
    ('$nama_peminjam', '$id_alat', '$tanggal_pinjam', '$jumlah_pinjam', $id_user, '$batas_kembali', 'menunggu')",
    );

    catat('Menambah peminjam');
    // Semua langkah berhasil, simpan perubahan ke database.
    mysqli_commit($conn);
    header('Location: tampil.php');
    exit();
}

buka_halaman('Tambah Data Peminjam');
?>
<!-- Formulir pengajuan menampilkan identitas sesuai peran yang sedang login. -->
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
            <?php $anggota = mysqli_query(
                $conn,
                "SELECT a.* FROM anggota a JOIN user u ON u.id_user=a.id_user WHERE LOWER(u.level)='peminjam' ORDER BY a.id_anggota",
            ); ?>
            <?php while ($orang = mysqli_fetch_assoc($anggota)) { ?>
                <option value="<?php echo (int) $orang['id_user']; ?>"><?php echo aman(
    $orang['nomor_anggota'] . ' - ' . $orang['nama'],
); ?></option>
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
    <?php pemilih_tanggal('tanggal_pinjam', $hari_ini, $hari_ini, $akhir_tahun); ?>
    <br><br>

    Jumlah Pinjam:
    <br>
    <input type="number" name="jumlah_pinjam" min="1" required>
    <br><br>

    Batas Pengembalian:<br>
    <?php pemilih_tanggal(
        'batas_kembali',
        $tanggal_default_kembali,
        $hari_ini,
        $akhir_tahun,
        'tanggal_pinjam',
    ); ?><br><br>
    <p>Stok berkurang setelah petugas menyetujui.</p>
    <button type="submit" name="simpan">
        Simpan
    </button>
</form>

<br>

<a href="tampil.php">Kembali</a>
<?php tutup_halaman(); ?>
