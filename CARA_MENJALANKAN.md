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
   cd c:\ibadurrahman
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

Jika Anda ingin melakukan pembersihan cache atau memperbarui database di kemudian hari, Anda dapat membuka terminal baru di folder `c:\ibadurrahman` dan menjalankan:

* **Membersihkan Cache Laravel** (jika ada halaman yang tidak update setelah perubahan):
  ```cmd
  php artisan optimize:clear
  ```
* **Mereset Database & Mengisi Ulang Data Default** (PENTING: Ini akan menghapus data absensi yang sudah ada dan mengulang dari nol):
  ```cmd
  php artisan migrate:fresh --seed
  ```
