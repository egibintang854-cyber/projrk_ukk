<?php
// Semua halaman memakai kerangka yang sama agar tampilan mudah diubah.
function buka_halaman($judul, $halaman_akun = false)
{
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= aman($judul) ?> | Peminjaman Alat</title>
        <link rel="stylesheet" href="<?= aman(
            url_aplikasi('assets/style.css'),
        ) ?>?v=<?= filemtime(__DIR__ . '/assets/style.css') ?>">
    </head>
    <body class="<?= $halaman_akun ? 'halaman-akun' : 'halaman-aplikasi' ?>">
        <?php if (!$halaman_akun) {
            menu();
        } ?>
        <main id="konten" class="<?= $halaman_akun ? 'kartu-akun' : 'konten' ?>">
    <?php
}

// Tutup struktur HTML dan muat skrip khusus bila halaman memerlukan kalender.
function tutup_halaman()
{
    ?>
        </main>
        <?php if (!empty($GLOBALS['ada_pemilih_tanggal'])) { ?>
        <!-- Skrip kalender hanya dicetak jika halaman memakai pemilih tanggal kustom. -->
        <script>
        (() => {
            // Sediakan nama bulan dan pembentuk tanggal ISO untuk elemen kalender.
            const namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const buatISO = (tahun, bulan, hari) => `${tahun}-${String(bulan + 1).padStart(2, '0')}-${String(hari).padStart(2, '0')}`;
            // Pasang perilaku pada setiap pemilih tanggal di halaman.
            document.querySelectorAll('[data-pemilih-tanggal]').forEach((pemilih) => {
                const input = pemilih.querySelector('[data-nilai-tanggal]');
                const teks = pemilih.querySelector('[data-teks-tanggal]');
                const tombol = pemilih.querySelector('.pemilih-tanggal-tombol');
                const kalender = pemilih.querySelector('.pemilih-tanggal-kalender');
                const grid = pemilih.querySelector('.pemilih-tanggal-grid');
                const labelBulan = pemilih.querySelector('[data-label-bulan]');
                const tombolSebelumnya = pemilih.querySelector('[data-bulan-sebelumnya]');
                const tombolBerikutnya = pemilih.querySelector('[data-bulan-berikutnya]');
                const batasMin = pemilih.dataset.min;
                const batasMax = pemilih.dataset.max;
                const sumberMin = pemilih.dataset.minDari ? document.querySelector(`[name="${pemilih.dataset.minDari}"]`) : null;
                let min = sumberMin && sumberMin.value > batasMin ? sumberMin.value : batasMin;
                const tanggalAwal = input.value >= min && input.value <= batasMax ? input.value : min;
                let [tahun, bulan] = tanggalAwal.split('-').map(Number);
                bulan--;

                // Gambar tanggal pada bulan aktif dan nonaktifkan tanggal di luar batas.
                function gambarKalender() {
                    const kunciBulan = `${tahun}-${String(bulan + 1).padStart(2, '0')}`;
                    labelBulan.textContent = `${namaBulan[bulan]} ${tahun}`;
                    tombolSebelumnya.disabled = kunciBulan <= min.slice(0, 7);
                    tombolBerikutnya.disabled = kunciBulan >= batasMax.slice(0, 7);
                    grid.replaceChildren();
                    const jarakHari = (new Date(tahun, bulan, 1).getDay() + 6) % 7;
                    const jumlahHari = new Date(tahun, bulan + 1, 0).getDate();
                    for (let kosong = 0; kosong < jarakHari; kosong++) grid.append(document.createElement('span'));
                    for (let hari = 1; hari <= jumlahHari; hari++) {
                        const iso = buatISO(tahun, bulan, hari);
                        const pilihHari = document.createElement('button');
                        pilihHari.type = 'button';
                        pilihHari.className = 'tanggal-hari';
                        pilihHari.textContent = String(hari);
                        pilihHari.disabled = iso < min || iso > batasMax;
                        if (iso === input.value) pilihHari.setAttribute('aria-pressed', 'true');
                        pilihHari.addEventListener('click', () => {
                            input.value = iso;
                            teks.textContent = `${String(hari).padStart(2, '0')}/${String(bulan + 1).padStart(2, '0')}/${tahun}`;
                            input.dispatchEvent(new Event('change', { bubbles: true }));
                            kalender.hidden = true;
                            tombol.setAttribute('aria-expanded', 'false');
                            tombol.focus();
                        });
                        grid.append(pilihHari);
                    }
                }

                // Buka kalender, pindah bulan, dan simpan tanggal yang dipilih.
                tombol.addEventListener('click', () => {
                    kalender.hidden = !kalender.hidden;
                    tombol.setAttribute('aria-expanded', String(!kalender.hidden));
                    if (!kalender.hidden) gambarKalender();
                });
                tombolSebelumnya.addEventListener('click', () => { bulan--; if (bulan < 0) { bulan = 11; tahun--; } gambarKalender(); });
                tombolBerikutnya.addEventListener('click', () => { bulan++; if (bulan > 11) { bulan = 0; tahun++; } gambarKalender(); });
                sumberMin?.addEventListener('change', () => {
                    // Sesuaikan batas tanggal kedua jika tanggal pinjam berubah.
                    min = sumberMin.value > batasMin ? sumberMin.value : batasMin;
                    if (input.value && input.value < min) {
                        input.value = min;
                        const [y, m, d] = min.split('-').map(Number);
                        teks.textContent = `${String(d).padStart(2, '0')}/${String(m).padStart(2, '0')}/${y}`;
                    }
                    const [y, m] = min.split('-').map(Number);
                    if (`${tahun}-${String(bulan + 1).padStart(2, '0')}` < `${y}-${String(m).padStart(2, '0')}`) { tahun = y; bulan = m - 1; }
                    gambarKalender();
                });
                // Tutup kalender saat pengguna mengeklik di luar kalender atau menekan Escape.
                document.addEventListener('click', (event) => {
                    if (!pemilih.contains(event.target)) { kalender.hidden = true; tombol.setAttribute('aria-expanded', 'false'); }
                });
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && !kalender.hidden) { kalender.hidden = true; tombol.setAttribute('aria-expanded', 'false'); tombol.focus(); }
                });
            });
        })();
        </script>
        <?php } ?>
    </body>
    </html>
    <?php
}

// Bangun navigasi sesuai peran pengguna yang sedang login.
function menu()
{
    $level = $_SESSION['level'];
    $halaman = $_SERVER['SCRIPT_NAME'];
    // Kelompok menu mengikuti urutan penggunaan aplikasi.
    $kelompok_menu = [
        'Utama' => [url_aplikasi('index.php') => 'Beranda'],
    ];

    // Sesuai pembagian paket: daftar alat untuk admin (kelola) dan peminjam (lihat stok).
    if (in_array($level, ['admin', 'peminjam'], true)) {
        $kelompok_menu['Data'] = [url_aplikasi('alat/tampil.php') => 'Daftar Alat'];
    }

    if ($level === 'admin') {
        // Admin mendapat menu data, kategori, akun, dan log aktivitas.
        $kelompok_menu['Data'][url_aplikasi('alat/kategori-tampil.php')] = 'Kategori Alat';
        $kelompok_menu['Data'][url_aplikasi('akun/pengguna-tampil.php')] = 'Data Pengguna';
        $kelompok_menu['Laporan'][url_aplikasi('laporan/aktivitas.php')] = 'Log Aktivitas';
    }

    $kelompok_menu['Transaksi'] = [
        url_aplikasi('peminjaman/tampil.php') =>
            $level === 'peminjam' ? 'Peminjaman Saya' : 'Peminjaman',
        url_aplikasi('pengembalian/tampil.php') =>
            $level === 'peminjam' ? 'Pengembalian Saya' : 'Pengembalian',
    ];
    if ($level === 'petugas') {
        // Petugas mendapat menu rekap transaksi.
        $kelompok_menu['Laporan'][url_aplikasi('laporan/tampil.php')] = 'Cetak Laporan';
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
                            $aktif =
                                preg_replace($pola, '', $halaman) ===
                                preg_replace($pola, '', $alamat);
                        }
                        ?>
                        <a href="<?= aman($alamat) ?>" <?php if ($aktif) {
    echo 'aria-current="page"';
} ?>>
                            <?= aman($label) ?>
                        </a>
                    <?php
                    } ?>
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
<p>Selamat datang, <strong><?= aman($_SESSION['nama']) ?></strong>.</p>
<?php tutup_halaman(); ?>
