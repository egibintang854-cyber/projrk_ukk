<?php
require __DIR__ . '/../config/connection.php';
// Hanya admin yang boleh melihat dan mengelola seluruh akun pengguna.
masuk(['admin']);

// Hitung jumlah akun dan profil anggota lama untuk menentukan jumlah halaman.
$per_halaman = 25;
$hasil_total = mysqli_query(
    $conn,
    'SELECT (SELECT COUNT(*) FROM user) +
    (SELECT COUNT(*) FROM anggota WHERE id_user IS NULL) AS jumlah',
);
$total = (int) (mysqli_fetch_assoc($hasil_total)['jumlah'] ?? 0);
$halaman_data = atur_paginasi($total, $per_halaman);
$halaman = $halaman_data['halaman'];
$jumlah_halaman = $halaman_data['jumlah'];
$awal = $halaman_data['awal'];

// Gabungkan akun dengan profilnya, lalu sertakan profil anggota yang belum memiliki akun.
$stmt = mysqli_prepare(
    $conn,
    'SELECT daftar.* FROM (
    SELECT u.id_user, u.username, u.nama, u.level,
        a.id_anggota, a.nomor_anggota, a.alamat, a.telepon
    FROM user u LEFT JOIN anggota a ON a.id_user=u.id_user
    UNION ALL
    SELECT NULL AS id_user, NULL AS username, a.nama, NULL AS level,
        a.id_anggota, a.nomor_anggota, a.alamat, a.telepon
    FROM anggota a WHERE a.id_user IS NULL
) AS daftar
ORDER BY daftar.id_user DESC, daftar.id_anggota DESC LIMIT ?, ?',
);
mysqli_stmt_bind_param($stmt, 'ii', $awal, $per_halaman);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);
$baris_awal = $total ? $awal + 1 : 0;
$baris_akhir = $halaman_data['akhir'];

buka_halaman('Data Pengguna');
?>
<!-- Tabel ini menampilkan akun dan profil anggota dalam satu daftar administrasi. -->
<header class="judul-halaman">
    <div>
        <p class="teks-atasan">DATA MASTER</p>
        <h2>Data Pengguna</h2>
        <p>Kelola akun, peran pengguna, dan profil anggota dari satu halaman.</p>
    </div>
    <a class="tombol-tautan" href="pengguna-form.php">Tambah Data</a>
</header>

<p class="info-tabel">Menampilkan <?php echo $baris_awal; ?>–<?php echo $baris_akhir; ?> dari <?php echo $total; ?> data.</p>
<div class="tabel-scroll" role="region" aria-label="Tabel data anggota dan pengguna" tabindex="0">
    <table>
        <thead>
            <tr>
                <th scope="col">ID Anggota</th>
                <th scope="col">Nama Lengkap</th>
                <th scope="col">Username</th>
                <th scope="col">Peran</th>
                <th scope="col">Alamat</th>
                <th scope="col">Telepon</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <!-- Tampilkan pesan khusus jika belum ada akun atau profil anggota. -->
            <?php if ($total === 0) { ?>
                <tr>
                    <td colspan="7" class="tabel-kosong">Belum ada data anggota atau pengguna.</td>
                </tr>
            <?php } else { ?>
                <!-- Ulangi setiap hasil gabungan sebagai satu baris tabel. -->
                <?php while ($data = mysqli_fetch_assoc($hasil)) { ?>
                    <tr>
                        <td><?php echo aman($data['nomor_anggota'] ?? '—'); ?></td>
                        <td><?php echo aman($data['nama']); ?></td>
                        <td><?php echo aman($data['username'] ?? 'Belum terhubung'); ?></td>
                        <td><?php echo aman(
                            $data['level'] ? ucfirst($data['level']) : 'Profil anggota',
                        ); ?></td>
                        <td><?php echo aman($data['alamat'] ?? '—'); ?></td>
                        <td><?php echo aman($data['telepon'] ?? '—'); ?></td>
                        <td class="aksi-tabel">
                            <!-- Akun terhubung diedit sebagai pengguna; profil lama dapat ditautkan. -->
                            <?php if ($data['id_user']) { ?>
                                <a href="pengguna-form.php?id=<?php echo (int) $data[
                                    'id_user'
                                ]; ?>">Edit</a>
                                <a href="pengguna-hapus.php?id=<?php echo (int) $data[
                                    'id_user'
                                ]; ?>">Hapus</a>
                            <?php } else { ?>
                                <a href="pengguna-form.php?level=peminjam&amp;anggota_id=<?php echo (int) $data[
                                    'id_anggota'
                                ]; ?>">Hubungkan ke Akun</a>
                                <a href="pengguna-hapus.php?anggota_id=<?php echo (int) $data[
                                    'id_anggota'
                                ]; ?>">Hapus</a>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php tampilkan_paginasi($halaman, $jumlah_halaman, 'data pengguna'); ?>

<?php
mysqli_stmt_close($stmt);
tutup_halaman();


?>
