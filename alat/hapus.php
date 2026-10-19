<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
masuk(['admin']);

// Ambil ID dari alamat halaman, misalnya edit.php?id=1.
$id = 0;
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    cek_token();
    // Mulai transaksi: perubahan disimpan bersama setelah semua langkah berhasil.
    mysqli_begin_transaction($conn);
    mysqli_query($conn, "SELECT id_alat FROM alat WHERE id_alat=$id FOR UPDATE");
    $cek = mysqli_query($conn, "SELECT id_peminjam FROM peminjam WHERE id_alat=$id LIMIT 1");
    if (mysqli_num_rows($cek)) {
        gagal('Alat masih memiliki riwayat peminjaman.');
    }
    mysqli_query($conn, "DELETE FROM alat WHERE id_alat=$id");
    catat('Menghapus alat #' . $id);
    mysqli_commit($conn);

    header("Location: tampil.php");
    exit;
}

buka_halaman('Hapus alat');
?>
<h2>Hapus alat</h2>
<p>Yakin ingin menghapus data #<?php echo $id; ?>?</p>
<form method="post">
    <?php token(); ?>
    <button>Hapus</button>
</form>
<p><a href="tampil.php">Batal</a>
</p>
<?php tutup_halaman(); ?>
