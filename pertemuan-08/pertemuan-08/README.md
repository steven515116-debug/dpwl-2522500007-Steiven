# UTS P8 DPWL - Manajemen Penyewa (Admin)

Pengembangan aplikasi penyewaan kendaraan: fitur **Manajemen Penyewa** pada sisi Admin
(CRUD data penyewa, pola MVC, MySQLi + prepared statement).

## Berkas yang ditambah / diubah

| Berkas | Perubahan |
|---|---|
| `application/config/routes.php` | Route P8: `admin/penyewa`, `/tambah`, `/ubah/(:any)`, `/hapus/(:any)` |
| `application/views/templates/sidebar.php` | Menu baru **Manajemen Penyewa** |
| `application/models/penyewa_model.php` | Method baru: `getAllAdmin`, `findAdmin`, `createAdmin`, `updateAdmin`, `deleteAdmin` |
| `application/controllers/Mpenyewa_controller.php` | **Baru** - alur CRUD, validasi, unggah foto, PRG |
| `application/views/admin/mpenyewa_view.php` | **Baru** - tabel (DataTables) + form tambah/ubah |
| `uploads/penyewa/` | Folder foto KTP dan SIM A |
| `database/db_dpwl_steiven_P8.sql` | Skema tabel pendukung (ganti dengan export penuh phpMyAdmin) |

## Pembagian tanggung jawab MVC

- **Model** (`Penyewa_model`): seluruh SQL, prepared statement; hash sandi tidak pernah dibaca ke tabel.
- **View** (`mpenyewa_view.php`): tabel dan form dalam satu berkas, semua output di-escape `htmlspecialchars`.
- **Controller** (`Mpenyewa_controller`): cek login admin, validasi, hash password, unggah foto, flash message, redirect.

## Keamanan dan validasi

- Setiap method memanggil `require_admin_login()`.
- Password: `password_hash(PASSWORD_DEFAULT)`, minimal 8 dan maksimal 72 byte, konfirmasi wajib sama; saat ubah, kosong = hash lama dipertahankan.
- Username 1-15 karakter `[A-Za-z0-9._-]`, unik (dicek sebelum INSERT); NIK tepat 16 digit; nama, no. HP, alamat divalidasi seperti halaman profil penyewa.
- Hapus hanya lewat **POST**; method selain GET/POST ditolak (405).
- **PRG**: setelah POST berhasil -> flash message -> redirect ke `admin/penyewa`.

## Foto KTP dan SIM A

- Format JPEG/PNG/WEBP, maksimal 5 MB, dicek `finfo` (MIME asli) dan `getimagesize`.
- Nama file acak: `ft_ktp_<tanggal>_<hex>.ext`; disimpan di `uploads/penyewa/`.
- Bila simpan ke database gagal, file baru dihapus kembali; foto lama baru dihapus setelah UPDATE berhasil; foto ikut terhapus saat data dihapus.

## Pengujian (isi setelah dijalankan di XAMPP)

| No | Skenario | Hasil yang diharapkan | Status |
|---|---|---|---|
| 1 | Klik menu Manajemen Penyewa | Tabel tampil | |
| 2 | Tambah penyewa valid + foto KTP/SIM | Muncul di tabel, foto tampil, hash tersimpan | |
| 3 | Tambah username duplikat | Pesan "Username sudah digunakan." | |
| 4 | NIK bukan 16 digit | Pesan validasi | |
| 5 | Password < 8 karakter / konfirmasi beda | Pesan validasi | |
| 6 | Unggah file bukan gambar / > 5 MB | Ditolak, data tidak tersimpan | |
| 7 | Ubah data tanpa mengisi password | Data berubah, password lama tetap bisa login | |
| 8 | Ubah dengan password baru dan foto baru | Login dengan password baru; foto lama terhapus | |
| 9 | Hapus penyewa tanpa transaksi | Data dan foto terhapus | |
| 10 | Hapus penyewa yang punya transaksi | Pesan gagal, aplikasi tetap berjalan | |
| 11 | Akses `/admin/penyewa` tanpa login | Diarahkan ke halaman login | |
| 12 | Refresh setelah tambah/ubah | Tidak ada pengiriman ulang form (PRG) | |
| 13 | Fitur lama (katalog, keranjang, mobil, admin) | Tetap berfungsi | |

## Basis data

Berkas `.sql` hasil export: `database/db_dpwl_steiven_P8.sql` (timpa dengan export lengkap phpMyAdmin sebelum dikumpulkan).

## Git

```
git add .
git commit -m "P8 UTS: tambah Manajemen Penyewa (CRUD, validasi, unggah KTP/SIM A)"
git push
```
