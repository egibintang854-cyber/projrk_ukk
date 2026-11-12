<?php
// Nyalakan pemeriksaan login dan hak akses pada semua halaman aplikasi.
$pakai_login = true;
// Fungsi kecil yang dipakai bersama oleh halaman aplikasi.
// Mulai sesi dengan cookie yang tidak dapat dibaca JavaScript dan perlindungan SameSite.
if (session_status() == PHP_SESSION_NONE) {
    session_start([
        'use_strict_mode' => 1,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
}

// Mendukung instalasi lokal di /pinjam maupun hosting langsung di document root.
function url_aplikasi($path = '')
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
    $dasar = strpos($script, '/pinjam/') === 0 || $script === '/pinjam' ? '/pinjam' : '';
    return $dasar . '/' . ltrim($path, '/');
}

// Mengubah karakter khusus agar tulisan aman ditampilkan di HTML.
function aman($nilai)
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}

// Hitung denda Rp1.000 untuk setiap hari terlambat.
function hitung_denda_php($batas, $kembali)
{
    $hari_terlambat = floor((strtotime($kembali) - strtotime($batas)) / 86400);
    return max(0, $hari_terlambat) * 1000;
}

// Hentikan proses. Perubahan dalam transaksi yang belum selesai dibatalkan.
function gagal($pesan)
{
    global $conn;
    if (isset($conn)) {
        mysqli_rollback($conn);
    }
    http_response_code(400);
    exit(aman($pesan) . '<p><a href="javascript:history.back()">Kembali</a></p>');
}

// Periksa pengguna. Saat login dimatikan, semua menu dapat dibuka.
function masuk($level)
{
    global $conn, $pakai_login;
    header('Cache-Control: no-store');
    if (!$pakai_login) {
        $_SESSION['id_user'] = 0;
        $_SESSION['level'] = 'admin';
        $_SESSION['nama'] = 'Pengguna';
        return;
    }
    if (!isset($_SESSION['id_user'])) {
        header('Location: ' . url_aplikasi('akun/login.php'));
        exit();
    }
    $id = (int) $_SESSION['id_user'];
    $hasil = mysqli_query($conn, "SELECT * FROM user WHERE id_user=$id");
    $user = mysqli_fetch_assoc($hasil);
    if (!$user) {
        session_destroy();
        header('Location: ' . url_aplikasi('akun/login.php'));
        exit();
    }
    $_SESSION['level'] = strtolower($user['level']);
    $_SESSION['nama'] = $user['nama'];
    if (!in_array($_SESSION['level'], $level)) {
        http_response_code(403);
        exit('Anda tidak memiliki hak akses ke halaman ini.');
    }
}

// Token adalah kode acak untuk mengenali form dari sesi pengguna ini.
function token()
{
    if (empty($_SESSION['token'])) {
        $_SESSION['token'] = bin2hex(random_bytes(32));
    }
    echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
}

// Tolak form jika kode yang dikirim tidak sesuai dengan kode dalam sesi.
function cek_token()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        gagal('Form tidak valid. Muat ulang halaman lalu coba lagi.');
    }

    $token_form = $_POST['token'] ?? null;
    $token_sesi = $_SESSION['token'] ?? '';

    if (!is_string($token_form) || empty($token_sesi) || !hash_equals($token_sesi, $token_form)) {
        gagal('Form tidak valid. Muat ulang halaman lalu coba lagi.');
    }
}

// Ambil teks dari form, periksa isinya, lalu siapkan untuk query SQL.
function teks($nama, $maksimal = 100)
{
    global $conn;
    return mysqli_real_escape_string($conn, teks_tervalidasi($nama, $maksimal));
}

// Versi untuk prepared statement: memvalidasi input tanpa menambahkan escape SQL.
function teks_tervalidasi($nama, $maksimal = 100)
{
    $nilai = '';
    if (isset($_POST[$nama]) && is_string($_POST[$nama])) {
        $nilai = trim($_POST[$nama]);
    }
    if ($nilai === '' || strlen($nilai) > $maksimal) {
        gagal('Isi ' . $nama . ' dengan benar (maksimal ' . $maksimal . ' karakter).');
    }
    return $nilai;
}

// Ambil bilangan bulat dari form. Contoh: stok boleh 0, jumlah pinjam minimal 1.
function angka($nama, $minimal = 1)
{
    $nilai = filter_var($_POST[$nama] ?? '', FILTER_VALIDATE_INT);
    if ($nilai === false || $nilai < $minimal) {
        gagal($nama . ' harus berupa bilangan bulat minimal ' . $minimal . '.');
    }
    return $nilai;
}

// Ambil ID bilangan bulat dari parameter URL; ID tidak valid menjadi nol.
function id_dari_url($nama = 'id')
{
    $id = filter_input(INPUT_GET, $nama, FILTER_VALIDATE_INT);
    return $id === false || $id === null ? 0 : max(0, $id);
}

// Hitung nomor halaman dan posisi LIMIT agar daftar data memakai aturan yang sama.
function atur_paginasi($total, $per_halaman)
{
    $jumlah_halaman = max(1, (int) ceil($total / $per_halaman));
    $halaman = filter_input(INPUT_GET, 'halaman', FILTER_VALIDATE_INT);
    $halaman = $halaman === false || $halaman === null ? 1 : max(1, $halaman);
    $halaman = min($halaman, $jumlah_halaman);
    $awal = ($halaman - 1) * $per_halaman;

    return [
        'halaman' => $halaman,
        'jumlah' => $jumlah_halaman,
        'awal' => $awal,
        'akhir' => min($awal + $per_halaman, $total),
    ];
}

// Tampilkan navigasi halaman dengan tampilan dan aturan yang sama di semua daftar.
function tampilkan_paginasi($halaman, $jumlah_halaman, $nama)
{
    if ($jumlah_halaman <= 1) {
        return;
    } ?>
    <nav class="paginasi" aria-label="Navigasi halaman <?php echo aman($nama); ?>">
        <span class="info-paginasi">Halaman <?php echo $halaman; ?> dari <?php echo $jumlah_halaman; ?></span>
        <div class="aksi-paginasi">
            <?php if ($halaman > 1) { ?>
                <a href="?halaman=<?php echo $halaman - 1; ?>" rel="prev">Sebelumnya</a>
            <?php } else { ?>
                <span class="nonaktif">Sebelumnya</span>
            <?php } ?>
            <?php if ($halaman < $jumlah_halaman) { ?>
                <a href="?halaman=<?php echo $halaman + 1; ?>" rel="next">Berikutnya</a>
            <?php } else { ?>
                <span class="nonaktif">Berikutnya</span>
            <?php } ?>
        </div>
    </nav>
    <?php
}

// Pecah tanggal YYYY-MM-DD menjadi tahun, bulan, dan hari.
function cek_tanggal($nilai)
{
    if (!is_string($nilai)) {
        return false;
    }
    $bagian = explode('-', $nilai);
    if (count($bagian) != 3) {
        return false;
    }
    [$tahun, $bulan, $hari] = $bagian;
    if (strlen($tahun) != 4 || strlen($bulan) != 2 || strlen($hari) != 2) {
        return false;
    }
    if (!ctype_digit($tahun . $bulan . $hari)) {
        return false;
    }
    return checkdate((int) $bulan, (int) $hari, (int) $tahun);
}

// Ambil tanggal dari form dan pastikan tanggal tersebut benar-benar ada.
function tanggal($nama)
{
    $nilai = $_POST[$nama] ?? '';
    if (!cek_tanggal($nilai)) {
        gagal('Tanggal tidak valid.');
    }
    return $nilai;
}

// Aturan tanggal peminjaman yang sama dipakai pada form tambah dan edit.
function cek_rentang_tanggal_peminjaman($tanggal_pinjam, $batas_kembali)
{
    $hari_ini = date('Y-m-d');
    $akhir_tahun = date('Y-12-31');
    if (
        $tanggal_pinjam < $hari_ini ||
        $tanggal_pinjam > $akhir_tahun ||
        $batas_kembali < $hari_ini ||
        $batas_kembali > $akhir_tahun
    ) {
        gagal(
            'Tanggal peminjaman dan batas pengembalian harus mulai hari ini dan masih dalam tahun ini.',
        );
    }
}

// Tanggal pengembalian harus jatuh setelah pinjam dan tidak boleh melewati hari ini.
function cek_tanggal_kembali($tanggal_kembali, $tanggal_pinjam)
{
    if ($tanggal_kembali < $tanggal_pinjam || $tanggal_kembali > date('Y-m-d')) {
        gagal('Tanggal kembali harus antara tanggal pinjam dan hari ini.');
    }
}

// Tandai halaman yang membutuhkan kalender kustom yang ditampilkan di kerangka.
function pemilih_tanggal($nama, $nilai, $minimal, $maksimal, $minimal_dari = '')
{
    $GLOBALS['ada_pemilih_tanggal'] = true;
    $nilai_valid = cek_tanggal($nilai) && $nilai >= $minimal && $nilai <= $maksimal;
    $teks_tanggal = $nilai_valid ? date('d/m/Y', strtotime($nilai)) : 'Pilih tanggal';
    ?>
    <div class="pemilih-tanggal" data-pemilih-tanggal data-min="<?php echo aman(
        $minimal,
    ); ?>" data-max="<?php echo aman($maksimal); ?>" data-min-dari="<?php echo aman($minimal_dari); ?>">
        <input type="hidden" name="<?php echo aman(
            $nama,
        ); ?>" value="<?php echo $nilai_valid ? aman($nilai) : ''; ?>" data-nilai-tanggal>
        <button type="button" class="pemilih-tanggal-tombol" aria-haspopup="dialog" aria-expanded="false">
            <span data-teks-tanggal><?php echo aman(
                $teks_tanggal,
            ); ?></span><span aria-hidden="true">▦</span>
        </button>
        <section class="pemilih-tanggal-kalender" role="dialog" aria-label="Pilih tanggal" hidden>
            <header class="pemilih-tanggal-kepala">
                <button type="button" data-bulan-sebelumnya aria-label="Bulan sebelumnya">‹</button>
                <strong data-label-bulan></strong>
                <button type="button" data-bulan-berikutnya aria-label="Bulan berikutnya">›</button>
            </header>
            <div class="pemilih-tanggal-hari" aria-hidden="true">
                <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span>
            </div>
            <div class="pemilih-tanggal-grid" role="grid"></div>
        </section>
    </div>
    <?php
}

// Simpan kegiatan pengguna ke tabel log_aktivitas.
function catat($aktivitas)
{
    global $conn;
    $id = (int) ($_SESSION['id_user'] ?? 0);
    $aktivitas = mysqli_real_escape_string($conn, $aktivitas);
    mysqli_query(
        $conn,
        "INSERT INTO log_aktivitas (id_user, aktivitas) VALUES ($id, '$aktivitas')",
    );
}

// Fungsi tampilan ada di bagian bawah file ini.

// Buat daftar alat beserta stoknya dan tandai alat yang sedang dipilih.
function pilihan_alat($terpilih = 0)
{
    global $conn;
    $hasil = mysqli_query($conn, 'SELECT id_alat, nama_alat, stok FROM alat ORDER BY nama_alat');
    ?>
    <select name="id_alat" required>
        <option value="">-- Pilih Alat --</option>
        <?php while ($alat = mysqli_fetch_assoc($hasil)) {
            $selected = (int) $terpilih === (int) $alat['id_alat'] ? ' selected' : ''; ?>
            <option value="<?php echo (int) $alat[
                'id_alat'
            ]; ?>"<?php echo $selected; ?>><?php echo aman(
    $alat['nama_alat'],
); ?> (stok: <?php echo (int) $alat['stok']; ?>)</option>
        <?php
        } ?>
    </select>
    <?php
}

// Buat pilihan kategori dari tabel kategori dan pertahankan nilai terpilih.
function pilihan_kategori($terpilih = '')
{
    global $conn;
    $hasil = mysqli_query($conn, 'SELECT nama_kategori FROM kategori ORDER BY nama_kategori');
    ?>
    <select name="kategori" required>
        <option value="">-- Pilih Kategori --</option>
        <?php while ($kategori = mysqli_fetch_assoc($hasil)) {

            $nama = $kategori['nama_kategori'];
            $selected = $nama === $terpilih ? ' selected' : '';
            ?>
            <option value="<?php echo aman($nama); ?>"<?php echo $selected; ?>><?php echo aman(
    $nama,
); ?></option>
        <?php
        } ?>
    </select>
    <?php
}

// Pastikan kategori yang dipilih masih tercatat sebelum dipakai pada alat.
function cek_kategori($kategori)
{
    global $conn;
    $data = mysqli_query($conn, "SELECT id_kategori FROM kategori WHERE nama_kategori='$kategori'");
    if (mysqli_num_rows($data) == 0) {
        gagal('Kategori belum ada. Tambahkan melalui menu kategori terlebih dahulu.');
    }
}

// FOR UPDATE mengunci baris selama transaksi agar tidak diproses bersamaan.
function data_pinjam($id)
{
    global $conn;
    $hasil = mysqli_query($conn, "SELECT * FROM peminjam WHERE id_peminjam=$id FOR UPDATE");
    $data = mysqli_fetch_assoc($hasil);
    if (!$data) {
        gagal('Data peminjaman tidak ditemukan.');
    }
    return $data;
}

// Cegah peminjam membuka atau mengubah transaksi milik akun lain.
function cek_pemilik($data)
{
    if ($_SESSION['level'] == 'peminjam' && $data['id_user'] != $_SESSION['id_user']) {
        http_response_code(403);
        exit('Peminjaman ini bukan milik Anda.');
    }
}

// Periksa urutan tanggal, keberadaan alat, dan stok di dalam transaksi.
function cek_pinjam($id_alat, $jumlah, $tanggal_pinjam, $batas)
{
    global $conn;
    if ($batas < $tanggal_pinjam) {
        gagal('Batas pengembalian tidak boleh sebelum tanggal pinjam.');
    }
    $hasil = mysqli_query($conn, "SELECT * FROM alat WHERE id_alat=$id_alat FOR UPDATE");
    $alat = mysqli_fetch_assoc($hasil);
    if (!$alat || $jumlah > $alat['stok']) {
        gagal('Alat tidak ditemukan atau stok tidak cukup.');
    }
}

// Akun lama tetap dapat login; akun baru memakai hash satu arah.
function cocok_password($password, $tersimpan)
{
    if (password_get_info($tersimpan)['algo'] !== null) {
        return password_verify($password, $tersimpan);
    }
    return hash_equals($tersimpan, $password);
}

// Buat hash satu arah untuk password baru sebelum disimpan ke database.
function buat_password($password)
{
    if ($password === '' || strlen($password) > 72) {
        gagal('Password harus berisi 1 sampai 72 byte.');
    }
    return password_hash($password, PASSWORD_DEFAULT);
}

// Kerangka halaman dan menu dikelola di index.php.
require_once __DIR__ . '/../index.php';
