<?php
require __DIR__ . '/../config/connection.php';
// Batasi pengelolaan akun hanya untuk admin.
masuk(['admin']);

// Baca ID akun atau profil lama dari URL, lalu siapkan nilai awal formulir.
$id = id_dari_url();
$id_anggota_lama = $id ? 0 : id_dari_url('anggota_id');
$judul = $id ? 'Edit Data Pengguna' : 'Tambah Data Pengguna';
$data = [
    'username' => '',
    'nama' => '',
    'level' => $_GET['level'] ?? 'peminjam',
    'nomor_anggota' => '',
    'alamat' => '',
    'telepon' => '',
    'id_anggota' => null,
];
if ($id) {
    // Saat mengedit, ambil data akun beserta profil anggota yang terhubung.
    $hasil = mysqli_query(
        $conn,
        "SELECT u.id_user, u.username, u.nama, u.level,
        a.id_anggota, a.nomor_anggota, a.alamat, a.telepon
        FROM user u LEFT JOIN anggota a ON a.id_user=u.id_user WHERE u.id_user=$id",
    );
    $tersimpan = mysqli_fetch_assoc($hasil);
    if (!$tersimpan) {
        gagal('Pengguna tidak ditemukan.');
    }
    $data = array_merge($data, $tersimpan);
} elseif ($id_anggota_lama) {
    // Jika berasal dari profil lama, muat nilainya untuk ditautkan ke akun baru.
    $hasil = mysqli_query(
        $conn,
        "SELECT * FROM anggota WHERE id_anggota=$id_anggota_lama AND id_user IS NULL",
    );
    $anggota_lama = mysqli_fetch_assoc($hasil);
    if (!$anggota_lama) {
        gagal('Profil anggota yang akan dihubungkan tidak ditemukan.');
    }
    $data['nomor_anggota'] = $anggota_lama['nomor_anggota'];
    $data['nama'] = $anggota_lama['nama'];
    $data['alamat'] = $anggota_lama['alamat'];
    $data['telepon'] = $anggota_lama['telepon'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi token formulir dan identitas akun sebelum menyimpan perubahan.
    cek_token();
    $username = teks('username', 50);
    $nama = teks('nama', 100);
    $level = teks('level', 20);
    if (!in_array($level, ['admin', 'petugas', 'peminjam'], true)) {
        gagal('Peran tidak valid.');
    }

    $username_asli = trim((string) ($_POST['username'] ?? ''));
    $nomor_anggota = trim((string) ($_POST['nomor_anggota'] ?? ''));
    if ($level === 'peminjam' && $nomor_anggota === '') {
        $nomor_anggota = $username_asli;
    }
    if (strlen($nomor_anggota) > 50) {
        gagal('ID Anggota maksimal 50 karakter.');
    }
    $nomor_anggota_sql = mysqli_real_escape_string($conn, $nomor_anggota);
    $alamat = trim((string) ($_POST['alamat'] ?? ''));
    $telepon = trim((string) ($_POST['telepon'] ?? ''));
    if (strlen($alamat) > 255 || strlen($telepon) > 30) {
        gagal('Alamat maksimal 255 karakter dan telepon maksimal 30 karakter.');
    }
    $alamat_sql = mysqli_real_escape_string($conn, $alamat);
    $telepon_sql = mysqli_real_escape_string($conn, $telepon);
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    if ((!$id && $password === '') || strlen($password) > 72) {
        gagal('Password wajib untuk akun baru dan maksimal 72 byte.');
    }

    mysqli_begin_transaction($conn);
    try {
        // Kunci baris akun dan profil terkait untuk mencegah perubahan bersamaan.
        mysqli_query($conn, 'SELECT id_user FROM user ORDER BY id_user FOR UPDATE');
        $anggota_tersimpan = null;
        if ($id) {
            $hasil = mysqli_query($conn, "SELECT * FROM user WHERE id_user=$id FOR UPDATE");
            $akun_lama = mysqli_fetch_assoc($hasil);
            if (!$akun_lama) {
                gagal('Pengguna tidak ditemukan.');
            }
            if ($id === (int) $_SESSION['id_user'] && $level !== 'admin') {
                gagal('Peran akun admin yang sedang digunakan tidak boleh diubah.');
            }
            $hasil = mysqli_query($conn, "SELECT * FROM anggota WHERE id_user=$id FOR UPDATE");
            $anggota_tersimpan = mysqli_fetch_assoc($hasil);
        } elseif ($id_anggota_lama) {
            $hasil = mysqli_query(
                $conn,
                "SELECT * FROM anggota WHERE id_anggota=$id_anggota_lama AND id_user IS NULL FOR UPDATE",
            );
            $anggota_tersimpan = mysqli_fetch_assoc($hasil);
            if (!$anggota_tersimpan) {
                gagal('Profil anggota sudah dihubungkan ke akun lain. Muat ulang halaman.');
            }
        }

        $cek = mysqli_query(
            $conn,
            "SELECT id_user FROM user WHERE username='$username' AND id_user<>$id",
        );
        if (mysqli_num_rows($cek)) {
            gagal('Username sudah digunakan.');
        }

        $simpan_profil = $level === 'peminjam' || $anggota_tersimpan;
        if ($simpan_profil) {
            // Pastikan ID anggota unik sebelum profil disimpan.
            if ($nomor_anggota_sql === '') {
                gagal('ID Anggota wajib diisi untuk profil anggota.');
            }
            $id_anggota_dikecualikan = (int) ($anggota_tersimpan['id_anggota'] ?? $id_anggota_lama);
            $cek = mysqli_query(
                $conn,
                "SELECT id_anggota FROM anggota
                WHERE nomor_anggota='$nomor_anggota_sql' AND id_anggota<>$id_anggota_dikecualikan AND (id_user IS NULL OR id_user<>$id)",
            );
            if (mysqli_num_rows($cek)) {
                gagal('ID Anggota sudah digunakan.');
            }
        }

        if ($id) {
            // Perbarui akun yang ada; password hanya diubah jika kolomnya diisi.
            $ubah_password = '';
            if ($password !== '') {
                $password_sql = mysqli_real_escape_string($conn, buat_password($password));
                $ubah_password = ", password='$password_sql'";
            }
            mysqli_query(
                $conn,
                "UPDATE user SET username='$username', nama='$nama', level='$level' $ubah_password WHERE id_user=$id",
            );
        } else {
            // Buat akun baru dan simpan ID yang dihasilkan database.
            $password_sql = mysqli_real_escape_string($conn, buat_password($password));
            mysqli_query(
                $conn,
                "INSERT INTO user (username, password, nama, level)
                VALUES ('$username', '$password_sql', '$nama', '$level')",
            );
            $id = mysqli_insert_id($conn);
        }

        if ($simpan_profil) {
            // Hubungkan profil lama, perbarui profil, atau buat profil anggota baru.
            if ($id_anggota_lama) {
                mysqli_query(
                    $conn,
                    "UPDATE anggota SET nomor_anggota='$nomor_anggota_sql', nama='$nama',
                    alamat='$alamat_sql', telepon='$telepon_sql', id_user=$id WHERE id_anggota=$id_anggota_lama AND id_user IS NULL",
                );
            } elseif ($anggota_tersimpan) {
                mysqli_query(
                    $conn,
                    "UPDATE anggota SET nomor_anggota='$nomor_anggota_sql', nama='$nama',
                    alamat='$alamat_sql', telepon='$telepon_sql' WHERE id_user=$id",
                );
            } else {
                mysqli_query(
                    $conn,
                    "INSERT INTO anggota (nomor_anggota, nama, alamat, telepon, id_user)
                    VALUES ('$nomor_anggota_sql', '$nama', '$alamat_sql', '$telepon_sql', $id)",
                );
            }
        }

        catat(
            $tersimpan ?? false
                ? 'Mengedit akun dan profil anggota #' . $id
                : 'Menambah akun #' . $id,
        );
        mysqli_commit($conn);
    } catch (mysqli_sql_exception $error) {
        if (mysqli_errno($conn) === 1062) {
            gagal('Username atau ID Anggota sudah digunakan.');
        }
        throw $error;
    }
    header('Location: pengguna-tampil.php');
    exit();
}

buka_halaman($judul);
?>
<!-- Form menggabungkan pengaturan akun dan profil anggota untuk admin. -->
<h2><?php echo aman($judul); ?></h2>
<p>Data akun dan profil anggota disimpan bersama. Akun dengan peran Peminjam otomatis memiliki profil anggota.</p>
<form method="post">
    <?php token(); ?>
    <label>Username<br>
        <input name="username" maxlength="50" value="<?php echo aman(
            $data['username'],
        ); ?>" required>
    </label><br><br>
    <label>Nama Lengkap<br>
        <input name="nama" maxlength="100" value="<?php echo aman($data['nama']); ?>" required>
    </label><br><br>
    <label>Peran Pengguna<br>
        <select name="level" required>
            <?php foreach (['admin', 'petugas', 'peminjam'] as $level) { ?>
                <option value="<?php echo $level; ?>" <?php if ($data['level'] === $level) {
    echo 'selected';
} ?>><?php echo ucfirst($level); ?></option>
            <?php } ?>
        </select>
    </label><br><br>
    <fieldset class="profil-anggota">
        <legend>Profil Anggota</legend>
        <label>ID Anggota<br>
            <input name="nomor_anggota" maxlength="50" value="<?php echo aman(
                $data['nomor_anggota'] ?? '',
            ); ?>">
        </label><br><br>
        <label>Alamat<br>
            <textarea name="alamat" maxlength="255"><?php echo aman(
                $data['alamat'] ?? '',
            ); ?></textarea>
        </label><br><br>
        <label>Telepon<br>
            <input name="telepon" maxlength="30" value="<?php echo aman(
                $data['telepon'] ?? '',
            ); ?>">
        </label>
        <p>Profil ini disimpan untuk akun Peminjam. Jika ID Anggota dikosongkan, username digunakan sebagai ID.</p>
    </fieldset><br>
    <label>Password <?php if ($id) {
        echo '(kosongkan jika tidak diubah)';
    } ?><br>
        <input name="password" type="password" autocomplete="new-password" maxlength="72" <?php if (
            !$id
        ) {
            echo 'required';
        } ?>>
    </label><br><br>
    <button>Simpan</button> <a href="pengguna-tampil.php">Batal</a>
</form>
<?php tutup_halaman(); ?>
