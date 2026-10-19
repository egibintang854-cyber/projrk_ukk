<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
masuk(['admin', 'petugas']);
// Ambil ID dari alamat halaman, misalnya edit.php?id=1.
$id = 0;
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    cek_token();
    // Mulai transaksi: perubahan disimpan bersama setelah semua langkah berhasil.
    mysqli_begin_transaction($conn);
    $data = data_pinjam($id);
    if ($data['status'] != 'menunggu') {
        gagal('Peminjaman sudah diproses.');
    }
    $aksi = '';
    if (isset($_POST['aksi'])) {
        $aksi = $_POST['aksi'];
    }
    if ($aksi == 'setujui') {
        $id_alat = (int) $data['id_alat'];
        $jumlah = (int) $data['jumlah_pinjam'];
        if ($jumlah < 1) {
            gagal('Jumlah peminjaman tidak valid.');
        }
        mysqli_query($conn, "UPDATE alat SET stok=stok-$jumlah WHERE id_alat=$id_alat AND stok >= $jumlah");
        if (mysqli_affected_rows($conn) != 1) {
            gagal('Stok alat tidak cukup.');
        }
        mysqli_query($conn, "UPDATE peminjam SET status='dipinjam' WHERE id_peminjam=$id");
        catat('Menyetujui peminjaman #' . $id);
    } elseif ($aksi == 'tolak') {
        mysqli_query($conn, "UPDATE peminjam SET status='ditolak' WHERE id_peminjam=$id");
        catat('Menolak peminjaman #' . $id);
    } else {
        gagal('Pilihan tidak valid.');
    }
    // Semua langkah berhasil, simpan perubahan ke database.
    mysqli_commit($conn);
    header('Location: tampil.php');
    exit;
}
$data = data_pinjam($id);

buka_halaman('Persetujuan Peminjaman');
?>
<h2>Persetujuan Peminjaman #<?php echo $id; ?></h2>
<p>Peminjam: <?php echo aman($data['nama_peminjam']); ?>, jumlah: <?php echo aman($data['jumlah_pinjam']); ?></p>
<form method="post">
    <?php token(); ?>
    <button name="aksi" value="setujui">Setujui</button>
    <button name="aksi" value="tolak">Tolak</button>
</form>
<p><a href="tampil.php">Kembali</a>
</p>
<?php tutup_halaman(); ?>
