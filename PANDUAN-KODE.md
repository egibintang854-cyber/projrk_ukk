# Panduan Kode Aplikasi Peminjaman Alat

Panduan ini menjelaskan bagian utama aplikasi dengan bahasa sederhana. Kode tetap memakai PHP prosedural supaya alurnya mudah diikuti.

## Cara kerja aplikasi

1. File halaman memanggil `config/connection.php`.
2. File koneksi menyiapkan fungsi umum, lalu menghubungkan PHP ke MySQL.
3. Halaman memeriksa login dan hak akses.
4. Jika form dikirim, PHP memeriksa isinya lalu menjalankan perintah database.
5. Setelah berhasil, halaman biasanya kembali ke daftar data.

## Folder

- `akun`: login, daftar anggota, pengguna, dan kata sandi.
- `alat`: data alat dan kategori.
- `peminjaman`: pengajuan dan persetujuan peminjaman.
- `pengembalian`: pengajuan dan verifikasi pengembalian.
- `laporan`: laporan peminjaman dan aktivitas.
- `config/connection.php`: pengaturan koneksi database.
- `config/fungsi.php`: fungsi yang dipakai bersama, seperti login, validasi, dan token form.
- `index.php`: beranda, menu, dan kerangka tampilan halaman.
- `assets/style.css`: tampilan halaman.
- `database`: berkas SQL dan alat pemulihan untuk pengembangan lokal.

## Koneksi database

Di `config/connection.php`, empat nilai ini menentukan koneksi:

```php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'egi_xii1_paket1_';
```

Di XAMPP, nilai bawaan tersebut biasanya dipakai. Di hosting, ganti dengan hostname, username, password, dan nama database yang diberikan panel hosting.

## Contoh alur: tambah alat

1. `alat/tambah.php` memeriksa bahwa pengguna adalah admin.
2. Saat tombol Simpan ditekan, kode memeriksa token form.
3. Kode membaca ID, nama, kategori, stok, dan kondisi alat.
4. Kode menyimpan data ke tabel `alat` dan mencatat aktivitas.
5. Browser diarahkan kembali ke `alat/tampil.php`.

## Istilah yang sering muncul

- `$conn`: sambungan PHP ke database MySQL.
- `$_POST`: nilai yang dikirim melalui form.
- `$_GET`: nilai yang dikirim melalui alamat halaman, misalnya `?id=3`.
- `$_SESSION`: data sementara pengguna yang sedang login.
- `mysqli_query(...)`: menjalankan perintah SQL.
- `header('Location: ...')`: mengarahkan browser ke halaman lain.
- `aman(...)`: mengamankan teks sebelum ditampilkan di HTML.
- `masuk([...])`: memastikan pengguna sudah login dan memiliki peran yang sesuai.
- `token()` dan `cek_token()`: memastikan form berasal dari sesi pengguna yang benar.

## Kenapa ada transaksi database?

Persetujuan peminjaman mengubah status peminjaman sekaligus mengurangi stok. Pengembalian terverifikasi mengubah status sekaligus menambah stok. Transaksi membuat semua perubahan disimpan bersama-sama; jika salah satu langkah gagal, perubahan dibatalkan agar stok tidak keliru.
