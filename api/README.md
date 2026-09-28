# API PHP

Endpoint publik yang tersedia:

- `GET /api/get_berita.php`
- `GET /api/get_berita.php?id=<id>`
- `GET /api/get_kurikulum.php`

Endpoint aplikasi (dipakai oleh `admin/assets/js/api-client.js`):

- `POST /api/data.php` untuk pembacaan data publik dan CRUD admin.
- `POST /api/login.php`, `/api/logout.php`, serta `GET /api/session.php` untuk sesi admin.
- `POST /api/upload.php` untuk unggahan berkas admin ke folder `uploads/`.

Sebelum unggah ke hosting, salin `config.local.php.example` menjadi
`config.local.php` dan isi kredensial MySQL cPanel, atau set variabel
lingkungan `DB_HOST`, `DB_NAME`, `DB_USER`, dan `DB_PASS` pada server.

Impor `schema.sql` melalui phpMyAdmin sebelum mengakses aplikasi. Setelah itu,
buat akun admin dengan password hash PHP, misalnya jalankan sekali melalui PHP:
`password_hash('password-anda', PASSWORD_DEFAULT)`, lalu simpan hasilnya di
kolom `users.password_hash`. Jangan simpan password teks biasa.

Tabel `berita` harus menyediakan kolom `id`, `judul`, `slug`, `isi`,
`foto_url`, `kategori`, dan `tanggal`. Tabel `kurikulum_url` memakai
kolom `id` dan `url_kurikulum`.

## Keamanan

- Folder `uploads/` punya `.htaccess` yang memblokir eksekusi PHP/script
  di dalamnya. Pastikan file ini ikut ter-upload ke hosting (jangan
  dihapus saat deploy manual via FTP).
- Login admin dibatasi maksimal 5 percobaan gagal per 15 menit (per
  kombinasi IP + email), dicatat di tabel `login_attempts` dari
  `schema.sql`.
- Semua aksi tulis (`insert`/`update`/`delete` di `data.php`, serta
  `upload.php`) mewajibkan header `X-CSRF-Token` yang cocok dengan token
  di sesi admin. `admin/assets/js/api-client.js` sudah menangani ini
  otomatis — token diambil dari respons `session.php`/`login.php` dan
  disertakan pada setiap request tulis.
