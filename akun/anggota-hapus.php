<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
masuk(['admin']);
$id = (int) ($_GET['id'] ?? 0);
$hasil = mysqli_query($conn, "SELECT * FROM anggota WHERE id_anggota=$id");
$data = mysqli_fetch_assoc($hasil);
if (!$data) {
    gagal('Anggota tidak ditemukan.');
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    cek_token();
    mysqli_begin_transaction($conn);
    $akun = (int) $data['id_user'];
    $riwayat = mysqli_query($conn, "SELECT id_peminjam FROM peminjam WHERE id_user=$akun LIMIT 1");
    if (mysqli_num_rows($riwayat)) {
        gagal('Anggota memiliki riwayat peminjaman dan tidak dapat dihapus.');
    }
    mysqli_query($conn, "DELETE FROM anggota WHERE id_anggota=$id");
    mysqli_query($conn, "DELETE FROM user WHERE id_user=$akun AND level='peminjam'");
    catat('Menghapus anggota #' . $id);
    mysqli_commit($conn);
    header('Location: anggota-tampil.php');
    exit;
}

buka_halaman('Hapus Anggota');
?>
<h2>Hapus Anggota</h2>
<p>Hapus anggota <?php echo aman($data['nomor_anggota']); ?> â€” <?php echo aman($data['nama']); ?>?</p>
<form method="post">
    <?php token(); ?>
    <button>Hapus</button>
</form>
<p><a href="anggota-tampil.php">Batal</a>
</p>
<?php tutup_halaman(); ?>
