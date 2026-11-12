<?php
require __DIR__ . '/../config/connection.php';
// Hanya admin yang boleh menghapus akun pengguna.
masuk(['admin']);
// Baca ID akun atau profil anggota lama yang belum terhubung.
$id = id_dari_url();
$id_anggota = $id ? 0 : id_dari_url('anggota_id');
if ($id) {
    // Ambil ringkasan akun dan profil untuk halaman konfirmasi.
    $hasil = mysqli_query(
        $conn,
        "SELECT u.id_user, u.username, u.nama, a.nomor_anggota
        FROM user u LEFT JOIN anggota a ON a.id_user=u.id_user WHERE u.id_user=$id",
    );
    $data = mysqli_fetch_assoc($hasil);
} elseif ($id_anggota) {
    // Profil anggota tanpa akun hanya boleh dihapus selama belum ditautkan.
    $hasil = mysqli_query(
        $conn,
        "SELECT id_anggota, nomor_anggota, nama FROM anggota WHERE id_anggota=$id_anggota AND id_user IS NULL",
    );
    $data = mysqli_fetch_assoc($hasil);
} else {
    $data = null;
}
if (!$data) {
    gagal('Data pengguna atau profil anggota tidak ditemukan.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Periksa token sebelum menghapus akun atau profil anggota.
    cek_token();
    if (!$id) {
        // Hapus profil anggota yang masih belum terhubung ke akun.
        mysqli_begin_transaction($conn);
        $stmt = mysqli_prepare($conn, 'DELETE FROM anggota WHERE id_anggota=? AND id_user IS NULL');
        mysqli_stmt_bind_param($stmt, 'i', $id_anggota);
        mysqli_stmt_execute($stmt);
        if (mysqli_stmt_affected_rows($stmt) !== 1) {
            gagal('Profil sudah terhubung atau tidak ditemukan. Muat ulang halaman.');
        }
        catat('Menghapus profil anggota #' . $id_anggota);
        mysqli_commit($conn);
        header('Location: pengguna-tampil.php');
        exit();
    }

    // Cegah penghapusan akun yang sedang dipakai atau memiliki riwayat transaksi.
    mysqli_begin_transaction($conn);
    mysqli_query($conn, 'SELECT id_user FROM user ORDER BY id_user FOR UPDATE');
    if ($id === (int) $_SESSION['id_user']) {
        gagal('Akun yang sedang digunakan tidak boleh dihapus.');
    }
    $riwayat = mysqli_query($conn, "SELECT id_peminjam FROM peminjam WHERE id_user=$id LIMIT 1");
    if (mysqli_num_rows($riwayat)) {
        gagal('Akun memiliki riwayat peminjaman sehingga tidak dapat dihapus.');
    }
    mysqli_query($conn, "DELETE FROM anggota WHERE id_user=$id");
    mysqli_query($conn, "DELETE FROM user WHERE id_user=$id");
    catat('Menghapus akun dan profil anggota #' . $id);
    mysqli_commit($conn);
    header('Location: pengguna-tampil.php');
    exit();
}

// Gunakan judul dan keterangan berbeda sesuai jenis data yang akan dihapus.
buka_halaman($id ? 'Hapus Data Pengguna' : 'Hapus Profil Anggota');
?>
<!-- Minta konfirmasi sebelum penghapusan akun dijalankan. -->
<h2><?php echo $id ? 'Hapus Data Pengguna' : 'Hapus Profil Anggota'; ?></h2>
<?php if ($id) { ?>
<p>Hapus akun <strong><?php echo aman(
    $data['username'],
); ?></strong> atas nama <strong><?php echo aman(
    $data['nama'],
); ?></strong> beserta profil anggotanya?</p>
<p>Data akun yang memiliki riwayat peminjaman tidak dapat dihapus.</p>
<?php } else { ?>
<p>Hapus profil anggota <strong><?php echo aman($data['nomor_anggota']); ?> — <?php echo aman(
     $data['nama'],
 ); ?></strong> yang belum terhubung ke akun?</p>
<?php } ?>
<form method="post">
    <?php token(); ?>
    <button><?php echo $id ? 'Hapus Data' : 'Hapus Profil'; ?></button>
    <a href="pengguna-tampil.php">Batal</a>
</form>
<?php tutup_halaman(); ?>
