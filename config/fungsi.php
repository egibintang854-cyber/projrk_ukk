<?php
$pakai_login = true;
// Fungsi kecil yang dipakai bersama oleh halaman aplikasi.
if (session_status() == PHP_SESSION_NONE) {
    session_start([
        'use_strict_mode' => 1,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
}

// Mendukung instalasi lokal di /pinjam maupun hosting langsung di document root.
function url_aplikasi($path = '') {
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
    $dasar = (strpos($script, '/pinjam/') === 0 || $script === '/pinjam') ? '/pinjam' : '';
    return $dasar . '/' . ltrim($path, '/');
}

// Mengubah karakter khusus agar tulisan aman ditampilkan di HTML.
function aman($nilai) {
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}

// Hitung denda Rp1.000 untuk setiap hari terlambat.
function hitung_denda_php($batas, $kembali) {
    $hari_terlambat = floor((strtotime($kembali) - strtotime($batas)) / 86400);
    return max(0, $hari_terlambat) * 1000;
}

// Hentikan proses. Perubahan dalam transaksi yang belum selesai dibatalkan.
function gagal($pesan) {
    global $conn;
    if (isset($conn)) {
        mysqli_rollback($conn);
    }
    http_response_code(400);
    exit(aman($pesan) . '<p><a href="javascript:history.back()">Kembali</a></p>');
}

// Periksa pengguna. Saat login dimatikan, semua menu dapat dibuka.
function masuk($level) {
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
        exit;
    }
    $id = (int) $_SESSION['id_user'];
    $hasil = mysqli_query($conn, "SELECT * FROM user WHERE id_user=$id");
    $user = mysqli_fetch_assoc($hasil);
    if (!$user) {
        session_destroy();
        header('Location: ' . url_aplikasi('akun/login.php'));
        exit;
    }
    $_SESSION['level'] = strtolower($user['level']);
    $_SESSION['nama'] = $user['nama'];
    if (!in_array($_SESSION['level'], $level)) {
        http_response_code(403);
        exit('Anda tidak memiliki hak akses ke halaman ini.');
    }
}

// Token adalah kode acak untuk mengenali form dari sesi pengguna ini.
function token() {
    if (empty($_SESSION['token'])) {
        $_SESSION['token'] = bin2hex(random_bytes(32));
    }
    echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
}

// Tolak form jika kode yang dikirim tidak sesuai dengan kode dalam sesi.
function cek_token() {
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
function teks($nama, $maksimal = 100) {
    global $conn;
    $nilai = '';
    if (isset($_POST[$nama]) && is_string($_POST[$nama])) {
        $nilai = trim($_POST[$nama]);
    }
    if ($nilai === '' || strlen($nilai) > $maksimal) {
        gagal('Isi ' . $nama . ' dengan benar (maksimal ' . $maksimal . ' karakter).');
    }
    return mysqli_real_escape_string($conn, $nilai);
}

// Ambil bilangan bulat dari form. Contoh: stok boleh 0, jumlah pinjam minimal 1.
function angka($nama, $minimal = 1) {
    $nilai = filter_var($_POST[$nama] ?? '', FILTER_VALIDATE_INT);
    if ($nilai === false || $nilai < $minimal) {
        gagal($nama . ' harus berupa bilangan bulat minimal ' . $minimal . '.');
    }
    return $nilai;
}

// Pecah tanggal YYYY-MM-DD menjadi tahun, bulan, dan hari.
function cek_tanggal($nilai) {
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
function tanggal($nama) {
    $nilai = $_POST[$nama] ?? '';
    if (!cek_tanggal($nilai)) {
        gagal('Tanggal tidak valid.');
    }
    return $nilai;
}

// Simpan kegiatan pengguna ke tabel log_aktivitas.
function catat($aktivitas) {
    global $conn;
    $id = (int) ($_SESSION['id_user'] ?? 0);
    $aktivitas = mysqli_real_escape_string($conn, $aktivitas);
    mysqli_query($conn, "INSERT INTO log_aktivitas (id_user, aktivitas) VALUES ($id, '$aktivitas')");
}

// Fungsi tampilan ada di bagian bawah file ini.

function pilihan_alat($terpilih = 0) {
    global $conn;
    $hasil = mysqli_query($conn, 'SELECT id_alat, nama_alat, stok FROM alat ORDER BY nama_alat');
    ?>
    <select name="id_alat" required>
        <option value="">-- Pilih Alat --</option>
        <?php while ($alat = mysqli_fetch_assoc($hasil)) {
            $selected = (int) $terpilih === (int) $alat['id_alat'] ? ' selected' : '';
            ?>
            <option value="<?php echo (int) $alat['id_alat']; ?>"<?php echo $selected; ?>><?php echo aman($alat['nama_alat']); ?> (stok: <?php echo (int) $alat['stok']; ?>)</option>
        <?php } ?>
    </select>
    <?php
}

function pilihan_kategori($terpilih = '') {
    global $conn;
    $hasil = mysqli_query($conn, 'SELECT nama_kategori FROM kategori ORDER BY nama_kategori');
    ?>
    <select name="kategori" required>
        <option value="">-- Pilih Kategori --</option>
        <?php while ($kategori = mysqli_fetch_assoc($hasil)) {
            $nama = $kategori['nama_kategori'];
            $selected = $nama === $terpilih ? ' selected' : '';
            ?>
            <option value="<?php echo aman($nama); ?>"<?php echo $selected; ?>><?php echo aman($nama); ?></option>
        <?php } ?>
    </select>
    <?php
}

function cek_kategori($kategori) {
    global $conn;
    $data = mysqli_query($conn, "SELECT id_kategori FROM kategori WHERE nama_kategori='$kategori'");
    if (mysqli_num_rows($data) == 0) {
        gagal('Kategori belum ada. Tambahkan melalui menu kategori terlebih dahulu.');
    }
}

// FOR UPDATE mengunci baris selama transaksi agar tidak diproses bersamaan.
function data_pinjam($id) {
    global $conn;
    $hasil = mysqli_query($conn, "SELECT * FROM peminjam WHERE id_peminjam=$id FOR UPDATE");
    $data = mysqli_fetch_assoc($hasil);
    if (!$data) {
        gagal('Data peminjaman tidak ditemukan.');
    }
    return $data;
}

function cek_pemilik($data) {
    if ($_SESSION['level'] == 'peminjam' && $data['id_user'] != $_SESSION['id_user']) {
        http_response_code(403);
        exit('Peminjaman ini bukan milik Anda.');
    }
}

function cek_pinjam($id_alat, $jumlah, $tanggal_pinjam, $batas) {
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
function cocok_password($password, $tersimpan) {
    if (password_get_info($tersimpan)['algo'] !== null) {
        return password_verify($password, $tersimpan);
    }
    return hash_equals($tersimpan, $password);
}

function buat_password($password) {
    if ($password === '' || strlen($password) > 72) {
        gagal('Password harus berisi 1 sampai 72 byte.');
    }
    return password_hash($password, PASSWORD_DEFAULT);
}

// Kerangka halaman dan menu dikelola di index.php.
require_once __DIR__ . '/../index.php';
