<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
masuk(['admin', 'peminjam']);

// Ambil ID dari alamat halaman, misalnya edit.php?id=1.
$id = 0;
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
}

$data = mysqli_query($conn, "SELECT * FROM peminjam WHERE id_peminjam=$id");

$peminjam = mysqli_fetch_assoc($data);
if (!$peminjam) {
    gagal('Data tidak ditemukan.');
}
cek_pemilik($peminjam);
if (!in_array($peminjam['status'], ['menunggu', 'ditolak'], true)) {
    gagal('Hanya pengajuan menunggu atau ditolak yang dapat diedit.');
}

if (isset($_POST['update'])) {
    cek_token();

    $id_alat = angka('id_alat');
    $tanggal_pinjam = tanggal('tanggal_pinjam');
    $jumlah_pinjam = angka('jumlah_pinjam');

    $batas_kembali = tanggal('batas_kembali');
    $nama_peminjam = mysqli_real_escape_string($conn, $peminjam['nama_peminjam']);
    // Mulai transaksi: perubahan disimpan bersama setelah semua langkah berhasil.
    mysqli_begin_transaction($conn);
    $terkunci = data_pinjam($id);
    cek_pemilik($terkunci);
    cek_pinjam($id_alat, $jumlah_pinjam, $tanggal_pinjam, $batas_kembali);
    if (!in_array($terkunci['status'], ['menunggu', 'ditolak'])) {
        gagal('Hanya pengajuan menunggu atau ditolak yang dapat diedit.');
    }

    mysqli_query($conn, "UPDATE peminjam SET
        nama_peminjam='$nama_peminjam',
        id_alat='$id_alat',
        tanggal_pinjam='$tanggal_pinjam',
        jumlah_pinjam='$jumlah_pinjam',
        batas_kembali='$batas_kembali', status='menunggu'
        WHERE id_peminjam=$id
    ");

    catat('Mengedit peminjam');
    // Semua langkah berhasil, simpan perubahan ke database.
    mysqli_commit($conn);
    header("Location: tampil.php");
    exit;
}

buka_halaman('Edit Data Peminjam');
?>
<h2>Edit Data Peminjam</h2>
<form method="POST">
    <?php token(); ?>

    ID Peminjam:
    <br>
    <input type="text"
    name="id_peminjam" readonly
    value="<?php echo aman($peminjam['id_peminjam']); ?>">

    <br><br>

    Nama Peminjam:
    <br>
    <input type="text"
    name="nama_peminjam" maxlength="100" readonly
    value="<?php echo aman($peminjam['nama_peminjam']); ?>">

    <br><br>

    ID Alat:
    <br>
    <?php pilihan_alat($peminjam['id_alat']); ?>

    <br><br>

    Tanggal Pinjam:
    <br>
    <input type="date"
    name="tanggal_pinjam" required
    value="<?php echo aman($peminjam['tanggal_pinjam']); ?>">

    <br><br>

    Jumlah Pinjam:
    <br>
    <input type="number" name="jumlah_pinjam" min="1" required value="<?php echo aman($peminjam['jumlah_pinjam']); ?>">

    <br><br>

    Batas Pengembalian:<br>
    <input type="date" name="batas_kembali" value="<?php echo aman($peminjam['batas_kembali'] ?? date('Y-m-d', strtotime('+7 days'))); ?>" required><br><br>
    <p>Stok berkurang setelah petugas menyetujui.</p>
    <button type="submit" name="update">
        Update
    </button>
</form>

<br>

<a href="tampil.php">Kembali</a>
<?php tutup_halaman(); ?>
