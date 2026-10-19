<?php
// Semua halaman memakai kerangka yang sama agar tampilan mudah diubah.
function buka_halaman($judul, $halaman_akun = false) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= aman($judul) ?> | Peminjaman Alat</title>
        <link rel="stylesheet" href="<?= aman(url_aplikasi('assets/style.css')) ?>?v=<?= filemtime(__DIR__ . '/assets/style.css') ?>">
    </head>
    <body class="<?= $halaman_akun ? 'halaman-akun' : 'halaman-aplikasi' ?>">
        <?php
        if (!$halaman_akun) {
            menu();
        }
        ?>
        <main id="konten" class="<?= $halaman_akun ? 'kartu-akun' : 'konten' ?>">
    <?php
}

function tutup_halaman() {
    ?>
        </main>
    </body>
    </html>
    <?php
}

// Menu disesuaikan dengan hak akses pengguna yang sedang login.
function menu() {
    $level = $_SESSION['level'];
    $halaman = $_SERVER['SCRIPT_NAME'];
    // Kelompok menu mengikuti urutan penggunaan aplikasi.
    $kelompok_menu = [
        'Utama' => [url_aplikasi('index.php') => 'Beranda'],
        'Data' => [url_aplikasi('alat/tampil.php') => 'Daftar Alat'],
    ];

    if ($level === 'admin') {
        $kelompok_menu['Data'][url_aplikasi('alat/kategori-tampil.php')] = 'Kategori Alat';
        $kelompok_menu['Data'][url_aplikasi('akun/anggota-tampil.php')] = 'Data Anggota';
        $kelompok_menu['Data'][url_aplikasi('akun/pengguna-tampil.php')] = 'Data Pengguna';
    }

    $kelompok_menu['Transaksi'] = [
        url_aplikasi('peminjaman/tampil.php') => $level === 'peminjam' ? 'Peminjaman Saya' : 'Peminjaman',
        url_aplikasi('pengembalian/tampil.php') => $level === 'peminjam' ? 'Pengembalian Saya' : 'Pengembalian',
    ];
    if (in_array($level, ['admin', 'petugas'], true)) {
        $kelompok_menu['Laporan'][url_aplikasi('laporan/tampil.php')] = 'Cetak Laporan';
    }
    if ($level === 'admin') {
        $kelompok_menu['Laporan'][url_aplikasi('laporan/aktivitas.php')] = 'Log Aktivitas';
    }
    $kelompok_menu['Akun'] = [url_aplikasi('akun/password.php') => 'Ubah Kata Sandi'];
    ?>
    <aside class="sidebar">
        <a class="merek" href="<?= aman(url_aplikasi('index.php')) ?>">Peminjaman Alat</a>
        <nav aria-label="Navigasi utama">
            <?php foreach ($kelompok_menu as $kelompok => $daftar_menu) { ?>
                <div class="kelompok-menu">
                    <p class="keterangan-menu"><?= aman($kelompok) ?></p>
                    <?php foreach ($daftar_menu as $alamat => $label) {
                        // Halaman tambah, edit, dan hapus mengikuti menu foldernya.
                        $aktif = $halaman === $alamat;
                        if (str_ends_with($alamat, 'tampil.php')) {
                            $pola = '~[/-](tampil|tambah|edit|hapus|form|proses|verifikasi)\.php$~';
                            $aktif = preg_replace($pola, '', $halaman) === preg_replace($pola, '', $alamat);
                        }
                        ?>
                        <a href="<?= aman($alamat) ?>" <?php if ($aktif) { echo 'aria-current="page"'; } ?>>
                            <?= aman($label) ?>
                        </a>
                    <?php } ?>
                </div>
            <?php } ?>
        </nav>
        <div class="profil">
            <strong><?= aman($_SESSION['nama']) ?></strong>
            <span><?= aman(ucfirst($level)) ?></span>
            <form action="<?= aman(url_aplikasi('akun/logout.php')) ?>" method="post">
                <?php token(); ?>
                <button type="submit">Keluar</button>
            </form>
        </div>
    </aside>
    <?php
}

// Saat dipanggil halaman lain, cukup muat fungsi tampilan di atas.
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') !== __FILE__) {
    return;
}

require __DIR__ . '/config/connection.php';
masuk(['admin', 'petugas', 'peminjam']);
buka_halaman('Dashboard');
?>
<h2>Dashboard</h2>
<p>Selamat datang, <strong><?= aman($_SESSION['nama']); ?></strong>.</p>
<?php tutup_halaman(); ?>
