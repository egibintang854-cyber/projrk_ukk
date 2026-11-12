<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
// Perubahan data peminjaman merupakan bagian CRUD admin.
masuk(['admin']);
// Siapkan batas tanggal dan ambil transaksi yang akan diedit.
$hari_ini = date('Y-m-d');
$akhir_tahun = date('Y-12-31');

// Ambil ID peminjaman yang dipilih dari alamat halaman.
$id = id_dari_url();

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
    // Validasi data perubahan sebelum mengunci dan memperbarui transaksi.
    cek_token();

    $id_alat = angka('id_alat');
    $tanggal_pinjam = tanggal('tanggal_pinjam');
    $jumlah_pinjam = angka('jumlah_pinjam');

    $batas_kembali = tanggal('batas_kembali');
    cek_rentang_tanggal_peminjaman($tanggal_pinjam, $batas_kembali);
    $nama_peminjam = mysqli_real_escape_string($conn, $peminjam['nama_peminjam']);
    // Mulai transaksi: perubahan disimpan bersama setelah semua langkah berhasil.
    mysqli_begin_transaction($conn);
    $terkunci = data_pinjam($id);
    cek_pemilik($terkunci);
    cek_pinjam($id_alat, $jumlah_pinjam, $tanggal_pinjam, $batas_kembali);
    if (!in_array($terkunci['status'], ['menunggu', 'ditolak'])) {
        gagal('Hanya pengajuan menunggu atau ditolak yang dapat diedit.');
    }

    mysqli_query(
        $conn,
        "UPDATE peminjam SET
        nama_peminjam='$nama_peminjam',
        id_alat='$id_alat',
        tanggal_pinjam='$tanggal_pinjam',
        jumlah_pinjam='$jumlah_pinjam',
        batas_kembali='$batas_kembali', status='menunggu'
        WHERE id_peminjam=$id
    ",
    );

    catat('Mengedit peminjam');
    // Semua langkah berhasil, simpan perubahan ke database.
    mysqli_commit($conn);
    header('Location: tampil.php');
    exit();
}

buka_halaman('Edit Data Peminjam');
?>
<!-- Formulir edit hanya untuk pengajuan yang belum disetujui atau ditolak. -->
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
    <?php pemilih_tanggal(
        'tanggal_pinjam',
        $peminjam['tanggal_pinjam'],
        $hari_ini,
        $akhir_tahun,
    ); ?>

    <br><br>

    Jumlah Pinjam:
    <br>
    <input type="number" name="jumlah_pinjam" min="1" required value="<?php echo aman(
        $peminjam['jumlah_pinjam'],
    ); ?>">

    <br><br>

    Batas Pengembalian:<br>
    <?php pemilih_tanggal(
        'batas_kembali',
        $peminjam['batas_kembali'] ?? min(date('Y-m-d', strtotime('+7 days')), $akhir_tahun),
        $hari_ini,
        $akhir_tahun,
        'tanggal_pinjam',
    ); ?><br><br>
    <p>Stok berkurang setelah petugas menyetujui.</p>
    <button type="submit" name="update">
        Update
    </button>
</form>

<br>

<a href="tampil.php">Kembali</a>
<?php tutup_halaman(); ?>
