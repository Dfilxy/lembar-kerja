# Lembar Kerja Kalibrasi

Aplikasi PHP mandiri untuk mengisi lembar kerja kalibrasi internal atau eksternal
dan mengunduh PDF A4 potret. Template cetak berasal dari proyek calibration-system.

## Menjalankan

Gunakan PHP 8.2 atau lebih baru dan Composer. Dari folder proyek, jalankan:

```powershell
composer install
php -S 127.0.0.1:8083 -t public
```

Buka http://127.0.0.1:8083. Isi formulir, lalu klik **Unduh PDF A4**. Nilai
rata-rata, U 95%, dan pembulatan diisi sesuai hasil pengukuran. Gambar tanda
tangan PNG atau JPEG bersifat opsional.

## Laragon

Nyalakan Apache di Laragon, lalu buka http://localhost/lembar-kerja-kalibrasi/.
Aturan .htaccess mengarahkan permintaan ke formulir dan aset publik.
Jika mengatur virtual host sendiri, arahkan DocumentRoot ke subfolder public.

CSS Tailwind sudah dikompilasi ke public/form.css. Untuk mengubah tampilannya,
edit assets/tailwind.css lalu kompilasi ulang dengan Tailwind CSS 3:

```powershell
npx tailwindcss@3 -i assets/tailwind.css -o public/form.css --content public/index.php --minify
```
