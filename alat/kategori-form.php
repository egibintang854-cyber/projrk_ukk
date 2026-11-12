<?php
require __DIR__ . '/../config/connection.php';
// Pengelolaan kategori hanya dapat dilakukan admin.
masuk(['admin']);

// ID kosong berarti tambah kategori; jika ada, muat kategori yang akan diedit.
$id = id_dari_url();
$kategori_lama = null;
if ($id) {
    $hasil = mysqli_query($conn, "SELECT * FROM kategori WHERE id_kategori=$id");
    $kategori_lama = mysqli_fetch_assoc($hasil);
    if (!$kategori_lama) {
        gagal('Kategori tidak ditemukan.');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Periksa token dan pastikan nama kategori termasuk pilihan yang tersedia.
    cek_token();
    $nama = teks('nama_kategori', 25);
    if (!in_array($nama, ['alat', 'elektronik'], true)) {
        gagal('Pilih kategori Alat atau Elektronik.');
    }

    // Kunci daftar kategori agar pemeriksaan duplikat dan penyimpanan aman.
    mysqli_begin_transaction($conn);
    mysqli_query($conn, 'SELECT id_kategori FROM kategori FOR UPDATE');
    $kondisi_id = $id ? " AND id_kategori<>$id" : '';
    $cek = mysqli_query(
        $conn,
        "SELECT id_kategori FROM kategori WHERE nama_kategori='$nama'$kondisi_id",
    );
    if (mysqli_num_rows($cek)) {
        gagal('Kategori sudah ada.');
    }

    // Tambahkan kategori baru atau ubah nama dan perbarui alat yang memakainya.
    if ($id) {
        $data = mysqli_query(
            $conn,
            "SELECT nama_kategori FROM kategori WHERE id_kategori=$id FOR UPDATE",
        );
        $kategori_lama = mysqli_fetch_assoc($data);
        if (!$kategori_lama) {
            gagal('Kategori tidak ditemukan.');
        }
        $lama = mysqli_real_escape_string($conn, $kategori_lama['nama_kategori']);
        mysqli_query($conn, "UPDATE kategori SET nama_kategori='$nama' WHERE id_kategori=$id");
        mysqli_query($conn, "UPDATE alat SET kategori='$nama' WHERE kategori='$lama'");
        $aktivitas = 'Mengedit kategori #' . $id;
    } else {
        mysqli_query($conn, "INSERT INTO kategori (nama_kategori) VALUES ('$nama')");
        $aktivitas = 'Menambah kategori';
    }

    catat($aktivitas);
    mysqli_commit($conn);
    header('Location: kategori-tampil.php');
    exit();
}

// Gunakan judul dan pilihan awal yang sesuai mode formulir.
$judul = $id ? 'Edit Kategori' : 'Tambah Kategori';
buka_halaman($judul);
?>
<h2><?php echo aman($judul); ?></h2>
<form method="post">
    <?php token(); ?>
    <label>Nama Kategori<br>
        <select name="nama_kategori" required>
            <option value="" disabled <?php echo $id
                ? ''
                : 'selected'; ?>>-- Pilih Kategori --</option>
            <?php foreach (
                ['alat' => 'Alat', 'elektronik' => 'Elektronik']
                as $nilai => $label
            ) { ?>
                <option value="<?php echo aman($nilai); ?>" <?php echo ($kategori_lama[
    'nama_kategori'
] ??
    '') ===
$nilai
    ? 'selected'
    : ''; ?>><?php echo aman($label); ?></option>
            <?php } ?>
        </select>
    </label><br><br>
    <button type="submit">Simpan</button> <a href="kategori-tampil.php">Kembali</a>
</form>
<?php tutup_halaman(); ?>
