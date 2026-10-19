<?php
// Sambungkan database.
require __DIR__ . '/../config/connection.php';
masuk(['admin']);
$id = (int) ($_GET['id'] ?? 0);
$hasil = mysqli_query($conn, "SELECT * FROM user WHERE id_user=$id");
$data = mysqli_fetch_assoc($hasil);
if (!$data) {
    gagal('Pengguna tidak ditemukan.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_token();
    mysqli_begin_transaction($conn);
    mysqli_query($conn, 'SELECT id_user FROM user ORDER BY id_user FOR UPDATE');
    if ($id === (int) $_SESSION['id_user']) {
        gagal('Akun yang sedang digunakan tidak boleh dihapus.');
    }
    $riwayat = mysqli_query($conn, "SELECT id_peminjam FROM peminjam WHERE id_user=$id LIMIT 1");
    if (mysqli_num_rows($riwayat)) {
        gagal('Pengguna memiliki riwayat peminjaman.');
    }
    mysqli_query($conn, "DELETE FROM anggota WHERE id_user=$id");
    mysqli_query($conn, "DELETE FROM user WHERE id_user=$id");
    catat('Menghapus pengguna #' . $id);
    mysqli_commit($conn);
    header('Location: pengguna-tampil.php');
    exit;
}
buka_halaman('Hapus Pengguna');
?>
<h2>Hapus Pengguna</h2>
<p>Hapus akun <strong><?php echo aman($data['username']); ?></strong>?</p>
<form method="post"><?php token(); ?><button>Hapus</button> <a href="pengguna-tampil.php">Batal</a>
</form>
<?php tutup_halaman(); ?>
