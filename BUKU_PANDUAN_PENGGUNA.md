# BUKU PANDUAN PENGGUNA (MANUAL BOOK)
## SISTEM INFORMASI PRESENSI TENAGA PENDIDIK BERBASIS CLOUD WEB
### PKBM IBADURRAHMAN

---

## INFORMASI DOKUMEN & SISTEM

| Atribut | Keterangan |
| :--- | :--- |
| **Nama Aplikasi** | Sistem Presensi Tenaga Pendidik PKBM Ibadurrahman |
| **Alamat URL (Produksi)** | `https://presensi-pkbm-ibadurrahman.vercel.app` |
| **Platform** | Cloud Web Application (Responsive: Mobile Phone, Tablet, Laptop, Smart TV) |
| **Versi Aplikasi** | 1.0 (Cloud Serverless Edition) |
| **Target Pengguna** | Tenaga Pendidik (Guru), Administrator, Koordinator Unit, Operator Kios Sekolah |

---

## DAFTAR ISI

1. [BAB 1: PENDAHULUAN](#bab-1-pendahuluan)
   - 1.1 [Tentang Sistem](#11-tentang-sistem)
   - 1.2 [Hak Akses Pengguna (User Roles)](#12-hak-akses-pengguna-user-roles)
   - 1.3 [Kebutuhan Perangkat & Peramban (Browser)](#13-kebutuhan-perangkat--peramban-browser)
2. [BAB 2: PANDUAN TENAGA PENDIDIK (GURU)](#bab-2-panduan-tenaga-pendidik-guru)
   - 2.1 [Cara Masuk (Login Guru)](#21-cara-masuk-login-guru)
   - 2.2 [Melakukan Presensi Hadir (QR Code & Selfie)](#22-melakukan-presensi-hadir-qr-code--selfie)
   - 2.3 [Pengajuan Izin / Sakit / Cuti](#23-pengajuan-izin--sakit--cuti)
   - 2.4 [Melihat Riwayat Presensi Pribadi](#24-melihat-riwayat-presensi-pribadi)
3. [BAB 3: PANDUAN LAYAR KIOS SEKOLAH (BARCODE & FACE ID)](#bab-3-panduan-layar-kios-sekolah-barcode--face-id)
   - 3.1 [Menampilkan Barcode Dinamis (Layar Sekolah)](#31-menampilkan-barcode-dinamis-layar-sekolah)
   - 3.2 [Mekanisme Rotasi Otomatis (Anti Titip Absen)](#32-mekanisme-rotasi-otomatis-anti-titip-absen)
   - 3.3 [Portal Verifikasi Face ID Sekolah](#33-portal-verifikasi-face-id-sekolah)
4. [BAB 4: PANDUAN ADMINISTRATOR & KOORDINATOR](#bab-4-panduan-administrator--koordinator)
   - 4.1 [Login Administrator](#41-login-administrator)
   - 4.2 [Dashboard Monitoring & Statistik Realtime](#42-dashboard-monitoring--statistik-realtime)
   - 4.3 [Manajemen Data Tenaga Pendidik](#43-manajemen-data-tenaga-pendidik)
   - 4.4 [Pengaturan Jam Kerja & Toleransi Telat](#44-pengaturan-jam-kerja--toleransi-telat)
   - 4.5 [Pengaturan Titik Koordinat GPS & Radius Sekolah](#45-pengaturan-titik-koordinat-gps--radius-sekolah)
   - 4.6 [Verifikasi & Persetujuan Izin / Sakit](#46-verifikasi--persetujuan-izin--sakit)
   - 4.7 [Rekapitulasi & Cetak Laporan (PDF / Excel)](#47-rekapitulasi--cetak-laporan-pdf--excel)
5. [BAB 5: PANDUAN KHUSUS SUPERADMIN](#bab-5-panduan-khusus-superadmin)
   - 5.1 [Manajemen Hari Libur Nasional / Kalender Akademik](#51-manajemen-hari-libur-nasional--kalender-akademik)
   - 5.2 [Manajemen Akun Koordinator](#52-manajemen-akun-koordinator)
6. [BAB 6: PEMECAHAN MASALAH (TROUBLESHOOTING)](#bab-6-pemecahan-masalah-troubleshooting)

---

## BAB 1: PENDAHULUAN

### 1.1 Tentang Sistem
Sistem Presensi PKBM Ibadurrahman adalah aplikasi pencatatan kehadiran digital berbasis web cloud yang dirancang khusus untuk mempermudah, mendisiplinkan, dan mengamankan rekaman kehadiran tenaga pendidik secara transparan dan akurat. Sistem ini dilengkapi dengan teknologi:
1. **Dynamic Rotating QR Code**: Kode QR berganti secara otomatis setiap interval waktu tertentu (default: 30 detik) untuk mencegah kecurangan *screenshot* atau titip absen dari rumah.
2. **Geofencing & GPS Radius Validation**: Memastikan presensi hanya dapat dilakukan jika perangkat guru berada di dalam radius resmi area lingkungan sekolah PKBM Ibadurrahman.
3. **Selfie Verification**: Pengambilan foto kilat sebagai bukti fisik kehadiran nyata di lokasi.
4. **Cloud Infrastructure**: Beroperasi 24/7 di cloud (Vercel & Aiven MySQL) sehingga dapat diakses kapan pun dari perangkat mana pun tanpa bergantung pada komputer server lokal.

### 1.2 Hak Akses Pengguna (User Roles)
Sistem memiliki 4 kategori pengguna:
* **Tenaga Pendidik (Guru)**: Melakukan absensi harian, mengajukan permohonan izin/sakit, dan memantau riwayat pribadi.
* **Petugas / Operator Layar Kios**: Mengoperasikan monitor/TV/tablet sekolah untuk menampilkan QR Code Dinamis dan Kios Face ID.
* **Koordinator Unit**: Memantau kehadiran dan memvalidasi permohonan izin guru pada unit masing-masing.
* **Administrator & Superadmin**: Mengelola seluruh master data guru, jadwal kerja, konfigurasi GPS, kebijakan toleransi waktu, dan mencetak laporan resmi.

### 1.3 Kebutuhan Perangkat & Peramban (Browser)
* **Perangkat Guru**: Smartphone (Android / iOS) dengan fitur Kamera dan GPS Aktif.
* **Browser yang Didukung**: Google Chrome (Sangat direkomendasikan), Safari, Microsoft Edge, atau Mozilla Firefox versi terbaru.
* **Koneksi Internet**: Seluler (4G/5G) atau Wi-Fi sekolah.
* **Izin Browser (Wajib Diberikan)**:
  - Izin Kamera (*Camera Permission*): Diperlukan untuk scan QR / Selfie.
  - Izin Lokasi (*Location / GPS Permission*): Diperlukan untuk validasi radius sekolah.

---

## BAB 2: PANDUAN TENAGA PENDIDIK (GURU)

### 2.1 Cara Masuk (Login Guru)
1. Buka browser di smartphone Anda, lalu akses:  
   👉 **`https://presensi-pkbm-ibadurrahman.vercel.app`**
2. Pada halaman utama (Portal), pilih kartu **"Login Tenaga Pendidik"**.
3. Masukkan **Email** atau kredensial akun yang telah didaftarkan oleh Administrator sekolah.
4. Masukkan **Password**, kemudian tekan tombol **Masuk**.
5. Setelah berhasil, Anda akan langsung diarahkan ke **Dashboard Tenaga Pendidik**.

> [!TIP]
> Agar mudah diakses setiap hari, buat *shortcut* di layar utama HP Anda dengan memilih menu browser (titik tiga di pojok kanan atas) lalu pilih **"Tambahkan ke Layar Utama" (Add to Home screen)**.

---

### 2.2 Melakukan Presensi Hadir (QR Code & Selfie)

1. Pada Dashboard Guru, pilih menu **"Presensi Sekarang"**.
2. Pastikan **GPS / Lokasi HP Anda telah diaktifkan** dengan mode akurasi tinggi.
3. Pilih salah satu metode verifikasi yang tersedia:
   * **Metode 1: Pindai Barcode (Scan QR)**:
     - Arahkan kamera HP ke layar monitor/tablet sekolah yang menampilkan QR Code Dinamis.
     - Sistem akan secara otomatis membaca barcode dan mencocokkan token uniknya.
   * **Metode 2: Selfie Cepat (Kamera Wajah)**:
     - Hadapkan kamera depan HP ke wajah Anda.
     - Tekan tombol potret untuk mengambil foto bukti kehadiran secara cepat.
4. **Validasi Lokasi (GPS)**:
   - Sistem membaca koordinat lokasi perangkat Anda secara otomatis.
   - Jika posisi Anda berada di dalam radius area sekolah (≤ 50 meter dari titik sekolah), presensi akan **DITERIMA**.
   - Jika Anda berada di luar area sekolah, sistem akan menampilkan notifikasi **"Presensi Ditolak: Anda berada di luar jangkauan sekolah"**.
5. **Penentuan Status Waktu**:
   - Jika melakukan presensi sebelum/tepat jam masuk: Status **"Tepat Waktu"**.
   - Jika melakukan presensi melewati batas toleransi masuk: Status **"Terlambat"** beserta jumlah menit keterlambatan.
6. Notifikasi sukses akan muncul dan rekaman kehadiran hari ini langsung tersimpan.

---

### 2.3 Pengajuan Izin / Sakit / Cuti

Apabila berhalangan hadir karena alasan tertentu:
1. Pada Dashboard Guru, pilih menu **"Pengajuan Izin / Sakit"**.
2. Pilih kategori ketidakhadiran:
   - **Izin** (Urusan mendesak/kepentingan keluarga)
   - **Sakit** (Kondisi medis)
   - **Cuti**
3. Tentukan **Tanggal Mulai** dan **Tanggal Selesai**.
4. Tuliskan **Keterangan / Alasan** secara jelas pada kolom yang disediakan.
5. Unggah berkas lampiran (foto surat dokter / surat permohonan izin bertanda tangan).
6. Tekan tombol **Kirim Pengajuan**.
7. Pengajuan akan berstatus **"Menunggu Persetujuan (Pending)"** hingga disetujui oleh Koordinator atau Administrator.

---

### 2.4 Melihat Riwayat Presensi Pribadi
1. Pilih menu **"Riwayat Presensi"** pada navigasi bawah.
2. Anda dapat melihat kalender kehadiran, total hari hadir, rekapitulasi izin/sakit, dan catatan jam masuk/pulang setiap harinya secara transparan.

---

## BAB 3: PANDUAN LAYAR KIOS SEKOLAH (BARCODE & FACE ID)

Menu ini ditujukan untuk perangkat operasional sekolah (seperti Tablet, Laptop piket, atau Smart TV yang dipasang di pintu masuk/lobi sekolah).

### 3.1 Menampilkan Barcode Dinamis (Layar Sekolah)
1. Buka browser pada monitor/layar sekolah dan akses alamat:  
   👉 **`https://presensi-pkbm-ibadurrahman.vercel.app/qr-presensi`**  
   *(Atau melalui Portal Utama ➜ pilih kartu **"Tampilkan Barcode"**)*.
2. Tekan tombol **F11** pada keyboard untuk mengubah tampilan browser menjadi **Layar Penuh (Full Screen)**.
3. Di layar akan tampil jam digital sekolah, informasi tanggal, dan gambar **QR Code Presensi Dinamis**.

---

### 3.2 Mekanisme Rotasi Otomatis (Anti Titip Absen)
* Di bawah gambar QR Code terdapat bilah pengukur waktu mundur (timer 30 detik).
* Setiap kali hitungan mundur selesai, sistem secara otomatis melakukan rotasi dengan menerbitkan **Token QR Baru**.
* **Keamanan**: Jika seorang guru memfoto barcode menggunakan HP lalu mengirimkannya via WhatsApp kepada guru lain di rumah, barcode tersebut sudah kedaluwarsa sebelum sempat dipindai, sehingga aksi titip absen dapat dicegah 100%.

---

### 3.3 Portal Verifikasi Face ID Sekolah
Sekolah juga dapat mengaktifkan kios verifikasi wajah mandiri pada perangkat sekolah di alamat:  
👉 **`https://presensi-pkbm-ibadurrahman.vercel.app/face-id`**  
Guru cukup berdiri di depan kamera kios, sistem mendeteksi wajah yang telah terdaftar, dan presensi tercatat otomatis tanpa perlu menyentuh perangkat.

---

## BAB 4: PANDUAN ADMINISTRATOR & KOORDINATOR

### 4.1 Login Administrator
1. Buka alamat portal utama: `https://presensi-pkbm-ibadurrahman.vercel.app`.
2. Pilih kartu **"Login Admin"** (atau langsung akses `/admin/login`).
3. Masukkan **Email Admin** dan **Password**.
4. Klik tombol **Masuk sebagai Admin**.

---

### 4.2 Dashboard Monitoring & Statistik Realtime
Setelah login, Admin akan disambut oleh ringkasan statistik kehadiran hari ini:
* **Kartu Statistik**: Total Guru Terdaftar, Jumlah Guru Hadir Hari Ini, Hadir Tepat Waktu, Terlambat, Izin/Sakit, dan Belum Hadir.
* **Tabel Kehadiran Hari Ini**: Menampilkan nama guru, unit tugas, jam presensi masuk, jam pulang, koordinat lokasi saat absen, dan tautan foto selfie.

---

### 4.3 Manajemen Data Tenaga Pendidik
Akses menu **"Tenaga Pendidik"** di bilah menu samping (*sidebar*):
1. **Menambah Guru Baru**:
   - Klik tombol **"+ Tambah Guru"**.
   - Isi formulir: NIP/NIK, Nama Lengkap, Email, Nomor WhatsApp, Unit Tugas, dan Password Awal.
   - Klik **Simpan**.
2. **Mengubah / Edit Data Guru**:
   - Pada baris guru yang diinginkan, klik ikon **Edit (Pensil)**.
   - Perbarui informasi lalu klik **Update**.
3. **Pendaftaran Face ID Guru**:
   - Klik tombol **"Face ID"** pada nama guru untuk mendaftarkan foto referensi wajah guru ke sistem.
4. **Import & Export Massal via Excel**:
   - Untuk memasukkan data puluhan guru sekaligus: Klik tombol **"Import Excel"** ➜ unduh template spreadsheet ➜ isi data ➜ upload file kembali.
   - Untuk mencadangkan data: Klik tombol **"Export Data"**.
5. **Menonaktifkan / Menghapus Akun**:
   - Guru yang berhenti dapat dihapus (*soft delete*). Apabila suatu saat dibutuhkan kembali, Admin dapat membuka tab **Sampah (Trash)** dan menekan tombol **Restore**.

---

### 4.4 Pengaturan Jam Kerja & Toleransi Telat
Akses menu **"Pengaturan" ➜ "Jam Presensi"** (`/admin/settings/attendance`):
* **Jam Batas Presensi Masuk**: Misalnya diatur `07:30 WIB`.
* **Toleransi Keterlambatan (Menit)**: Batas waktu dispensasi sebelum dihitung terlambat (misal: 15 menit).
* **Jam Buka Presensi Pulang**: Waktu tercepat guru diizinkan melakukan presensi pulang (misal: `15:00 WIB`).
* Klik **Simpan Pengaturan** untuk menerapkan perubahan ke seluruh sistem.

---

### 4.5 Pengaturan Titik Koordinat GPS & Radius Sekolah
Akses menu **"Pengaturan" ➜ "Radius Lokasi / GPS"** (`/admin/settings/gps`):
1. **Titik Pusat Sekolah**:
   - Masukkan koordinat **Latitude** dan **Longitude** resmi gedung PKBM Ibadurrahman (atau geser pin pada peta digital yang disediakan).
2. **Radius Toleransi Kehadiran (Meter)**:
   - Tentukan jarak jangkauan maksimal yang diperbolehkan (rekomendasi: **50 meter s/d 100 meter** untuk mengantisipasi ketidakakuratan GPS bawaan HP).
3. Klik **Simpan Lokasi**. Mulai saat itu, sistem akan otomatis menolak presensi di luar radius tersebut.

---

### 4.6 Verifikasi & Persetujuan Izin / Sakit
Akses menu **"Persetujuan Izin"** (`/admin/leaves`):
1. Admin dapat melihat daftar pengajuan izin/sakit yang diajukan oleh para guru.
2. Klik tombol **"Lihat Lampiran"** untuk memeriksa surat dokter atau dokumen bukti.
3. Klik tombol hijau **"Setujui (Approve)"** untuk mengesahkan izin, atau klik tombol merah **"Tolak (Reject)"** dengan menyertakan alasan penolakan.
4. Status pada dashboard guru bersangkutan akan langsung terbarui secara otomatis.

---

### 4.7 Rekapitulasi & Cetak Laporan (PDF / Excel)
Akses menu **"Laporan"** (`/admin/reports`):
1. **Filter Data**:
   - Tentukan rentang **Tanggal Awal** dan **Tanggal Akhir** (misalnya 1 bulan penuh).
   - Pilih unit kerja atau pilih seluruh unit.
2. **Tinjau Data**: Sistem menampilkan tabel ringkasan akumulasi kehadiran setiap guru (Hadir, Sakit, Izin, Terlambat, Alpha).
3. **Ekspor Laporan**:
   - **Download Excel**: Klik tombol **"Export Excel"** untuk diolah kembali di Microsoft Excel atau Google Sheets.
   - **Cetak PDF**: Klik tombol **"Cetak PDF"** untuk mengunduh laporan berformat formal siap cetak lengkap dengan kop surat dan kolom tanda tangan pimpinan.

---

## BAB 5: PANDUAN KHUSUS SUPERADMIN

### 5.1 Manajemen Hari Libur Nasional & Kalender Akademik
Akses menu **"Hari Libur"** (`/admin/holidays`):
* Daftarkan hari libur nasional, libur semester, atau cuti bersama.
* Pada tanggal-tanggal yang terdaftar libur, sistem secara otomatis meliburkan presensi sehingga guru tidak akan tercatat *Alpha / Alpa*.

### 5.2 Manajemen Akun Koordinator
Akses menu **"Koordinator"** (`/admin/coordinators`):
* Menunjuk dan mengelola akun penanggung jawab masing-masing jenjang/unit PKBM Ibadurrahman agar dapat memantau presensi unitnya secara terfokus.

---

## BAB 6: PEMECAHAN MASALAH (TROUBLESHOOTING)

| Masalah yang Sering Terjadi | Penyebab Utama | Solusi Praktis |
| :--- | :--- | :--- |
| **Pesan: "Di Luar Jangkauan Sekolah" padahal sudah di lokasi.** | Akurasi GPS smartphone belum tepat atau terpantul sinyal dalam ruangan. | 1. Buka aplikasi Google Maps di HP selama 5 detik agar sinyal GPS mengunci posisi akurat.<br>2. Pastikan mode lokasi diatur ke **High Accuracy**.<br>3. Nyalakan Wi-Fi HP (membantu akurasi lokasi).<br>4. Admin dapat menaikkan radius toleransi (misal dari 50m menjadi 75m). |
| **Kamera tidak mau terbuka saat scan QR / selfie.** | Izin kamera pada browser diblokir atau belum disetujui. | 1. Ketuk ikon gembok / perizinan di sebelah kiri kolom URL browser.<br>2. Cari opsi **Kamera (Camera)** lalu ubah statusnya menjadi **Izinkan (Allow)**.<br>3. Muat ulang (*refresh*) halaman web. |
| **Halaman menampilkan pesan error 500 / Tidak bisa terhubung.** | Koneksi ke cloud database Aiven sempat terputus setelah libur panjang. | 1. Buka `https://console.aiven.io/` dan pastikan status database **Running**.<br>2. Monitor otomatis **UptimeRobot** telah dipasang untuk mencegah database tidur otomatis. |
| **Guru lupa password akun.** | Lupa kata sandi masuk. | Hubungi Administrator sekolah untuk melakukan *Reset Password* melalui menu Manajemen Tenaga Pendidik. |

---

*Buku Panduan ini disusun untuk standarisasi operasional Sistem Presensi Tenaga Pendidik PKBM Ibadurrahman.*  
*© 2026 PKBM Ibadurrahman. Seluruh hak cipta dilindungi undang-undang.*
