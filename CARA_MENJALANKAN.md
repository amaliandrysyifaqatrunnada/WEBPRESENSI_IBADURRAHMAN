# Panduan Cara Menjalankan Sistem Presensi PKBM Ibadurrahman

Berikut adalah panduan lengkap agar Anda dapat menjalankan aplikasi ini secara mandiri kapan saja di komputer lokal Anda.

---

## 1. Persiapan Awal (Setiap kali Menyalakan Komputer)

Aplikasi ini membutuhkan database MySQL dan server Apache dari **XAMPP** agar dapat berjalan.

1. Buka aplikasi **XAMPP Control Panel** di Windows.
2. Klik tombol **`Start`** pada baris **Apache**.
3. Klik tombol **`Start`** pada baris **MySQL**.
4. Pastikan keduanya berwarna hijau.

---

## 2. Menjalankan Server Aplikasi (Laravel Serve)

Agar aplikasi dapat diakses di browser, Anda harus menyalakan server lokal Laravel:

1. Buka **Command Prompt (cmd)** atau **PowerShell**.
2. Masuk ke folder proyek dengan mengetik perintah berikut dan tekan Enter:
   ```cmd
   cd c:\xampp\htdocs\ibadurrahman
   ```
3. Jalankan perintah server:
   ```cmd
   php artisan serve
   ```
4. Setelah muncul tulisan `INFO Server running on [http://127.0.0.1:8000]`, **jangan tutup** jendela cmd/PowerShell tersebut selama Anda menggunakan aplikasi.

---

## 3. Cara Mengakses Aplikasi di Browser

Buka Google Chrome atau browser pilihan Anda, lalu akses alamat berikut:

* **Portal Utama (Halaman Absensi Guru & Login)**:  
  👉 [**`http://localhost:8000`**](http://localhost:8000)

* **Halaman Login Admin / Superadmin**:  
  👉 [**`http://localhost:8000/admin/login`**](http://localhost:8000/admin/login)

---

## 4. Kredensial Login Bawaan (Default Credentials)

Gunakan akun berikut untuk mencoba login ke sistem:

### A. Akun Superadmin (Akses Semua Unit)
* **Email**: `superadmin@ibadurrahman.sch.id`
* **Password**: `password`

### B. Akun Admin Unit (Contoh: Unit Paket A)
* **Email**: `admin@ibadurrahman.sch.id`
* **Password**: `password`

### C. Akun Tenaga Pendidik (Contoh: Guru Matematika)
* **Email**: `teacher@ibadurrahman.sch.id`
* **Password**: `password`

---

## 5. Perintah Tambahan yang Berguna (Opsional)

Jika Anda ingin melakukan pembersihan cache atau memperbarui database di kemudian hari, Anda dapat membuka terminal baru di folder `c:\xampp\htdocs\ibadurrahman` dan menjalankan:

* **Membersihkan Cache Laravel** (jika ada halaman yang tidak update setelah perubahan):
  ```cmd
  php artisan optimize:clear
  ```
* **Mereset Database & Mengisi Ulang Data Default** (PENTING: Ini akan menghapus data absensi yang sudah ada dan mengulang dari nol):
  ```cmd
  php artisan migrate:fresh --seed
  ```

---

## 6. Panduan Pindahan (Migrasi) ke Komputer / Server Sekolah

Jika Anda ingin memindahkan sistem ini ke laptop lain atau ke server hosting sekolah menggunakan Flashdisk, ikuti langkah-langkah terstruktur berikut:

### Langkah A: Ekspor Database dari Komputer Ini
1. Buka browser dan masuk ke **`http://localhost/phpmyadmin`**.
2. Pilih database bernama **`ibadurrahman`** di panel sebelah kiri.
3. Klik tab **`Export`** (Ekspor) di bagian atas.
4. Klik tombol **`Export`** (atau **`Go`**) untuk mengunduh berkas `.sql` database Anda. Simpan berkas ini di Flashdisk.

### Langkah B: Salin Folder Proyek ke Flashdisk
1. Buka folder **`C:\xampp\htdocs\`**.
2. Salin (*copy*) seluruh folder **`ibadurrahman`** ke dalam Flashdisk Anda.
   *(Folder ini sudah memuat seluruh pembaruan sistem presensi, termasuk unit selector, login dengan email/nama, dan bypass landmarks).*

### Langkah C: Impor Database di Komputer / Server Baru
1. Pastikan komputer baru sudah terpasang XAMPP dan Apache & MySQL sudah menyala (`Start`).
2. Masuk ke **`http://localhost/phpmyadmin`** di komputer baru tersebut.
3. Buat database baru dengan mengklik **`New`**, beri nama database **`ibadurrahman`**, lalu klik **`Create`**.
4. Klik database **`ibadurrahman`** yang baru dibuat, masuk ke tab **`Import`** (Impor).
5. Klik **`Choose File`** (Pilih File), pilih berkas `.sql` yang Anda simpan di Flashdisk tadi, lalu klik **`Import`** (atau **`Go`**).

### Langkah D: Letakkan Folder Proyek di Komputer Baru
1. Salin folder **`ibadurrahman`** dari Flashdisk ke folder target di komputer baru (biasanya di **`C:\xampp\htdocs\`**).
2. Jika konfigurasi database di komputer baru berbeda (misalnya ada password database), buka file **`.env`** di root folder proyek menggunakan Notepad, lalu sesuaikan isinya:
   ```env
   DB_DATABASE=ibadurrahman
   DB_USERNAME=root
   DB_PASSWORD=isi_password_jika_ada
   ```
3. Buka terminal (CMD) di folder proyek komputer baru tersebut, lalu jalankan perintah berikut untuk menghubungkan folder penyimpanan berkas presensi/foto:
   ```cmd
   php artisan storage:link
   ```
4. Jalankan server di komputer baru menggunakan file **`jalankan_presensi.bat`** (cukup klik dua kali berkas tersebut). Aplikasi siap digunakan!
