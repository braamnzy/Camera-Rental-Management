# 📷 CamRent - FrameFlow 
> Web Application for Camera Rental Service

---

## 📌 Informasi Kelompok
- **Nomor Kelompok:** Kelompok 03
- **Shift Praktikum:** Shift D

---

## 👥 Anggota Kelompok
| No | Nama Lengkap | NIM | Shift Awal | Shift Akhir | Jobdesk / Kontribusi | Link Video Penjelasan |
|---|---|---|---|---|---|---|
| 1 | Andyka Zefanya Bramantyo | H1H024039 | A | D | Backend 1, Core, Auth (Sanctum), CRUD Kamera, File Upload, In-App Notification | [YouTube/Drive](https://...) |
| 2 | Ibnu Abbas | H1H024038 | B | D |Backend 2, Booking Logic, Midtrans Integration (Snap & Webhook IPN), Admin Schedule | [YouTube/Drive](https://...) |
| 3 | Arifin Budi Kusuma | H1H024040 | B | D | Frontend 1, Customer Portal, Auth UI, Catalog UI, Booking Form & Midtrans Pop-up | [YouTube/Drive](https://...) |
| 4 | Huriyatun Nur Anajmi | H1H024035 | B | D | Frontend 2, Admin Dashboard, Layout Sidebar Admin, Inventory CRUD UI, Schedule Monitoring | [YouTube/Drive](https://...) |


---

## 📖 Deskripsi Aplikasi
**CamRent** adalah platform penyewaan kamera berbasis web yang dibangun dengan framework **Laravel 13** di sisi backend dan **Blade / Vue / React / HTML-JS** di sisi frontend. Sistem ini dilengkapi dengan otentikasi API berbasis **Sanctum**, integrasi payment gateway **Midtrans Snap**, notifikasi *in-app*, serta sistem *monitoring* jadwal dan inventaris untuk administrator.

---

# ⚙️ Penjelasan Teknis
## 1. Teknologi (Tech Stack)

- **Backend**: Laravel 13 (PHP 8.2+)
- **Database**: MySQL / MariaDB
- **Authentication**: Laravel Sanctum (Bearer Token)
- **Payment Gateway**: Midtrans Snap API (Sandbox Mode)
- **Frontend**: Blade / HTML5 / CSS3 / JavaScript (Vue/React Optional)
- **Storage**: Storage Link Public (`storage/app/public/cameras`)

---

## 2.  Arsitektur & Fitur Utama

### 1. Fitur Penyewa (Customer)
- **Autentikasi**: Registrasi, Login, Logout, & Manajemen Profil.
- **Katalog & Filter**: Pencarian unit kamera, filter berdasarkan kategori/merk, dan sorting harga.
- **Pemesanan (Booking)**: Pemilihan tanggal sewa via *Date Range Picker*, kalkulasi otomatis total hari & harga, serta validasi ketersediaan stok (*anti-overbooking*).
- **Pembayaran Real-time**: Integrasi *Midtrans Snap Pop-up* (QRIS, VA, E-Wallet, Kartu Kredit).
- **Riwayat & Status**: Pemantauan status tagihan dan transaksi sewa secara *real-time*.
- **Notifikasi In-App**: Indikator status pesanan langsung di dalam aplikasi.

### 2. Fitur Pengelola (Admin)
- **Dashboard Ringkasan**: Statistik total pendapatan, unit aktif disewa, dan transaksi *pending*.
- **Manajemen Inventaris**: CRUD master unit kamera, *upload display image*, dan pengaturan status unit (*available* / *maintenance*).
- **Pengelolaan Transaksi**: Konfirmasi pengambilan unit (*picked_up*), konfirmasi pengembalian (*returned* / *stok auto-restore*), dan pembatalan pesanan.
- **Monitoring Jadwal (Schedule)**: Matriks jadwal ketersediaan kamera per tanggal serta *highlight overdue* (keterlambatan).

---

## 3. Skema Data Singkat

1. **`users`**: Menyimpan data pengguna (`role`: `admin` | `customer`).
   - Relasi: `hasMany(Rental)`, `hasMany(DatabaseNotification)`
2. **`cameras`**: Master unit kamera (`daily_rate`, `stock`, `status`, `image`).
   - Relasi: `hasMany(Rental)`
3. **`rentals`**: Data transaksi penyewaan (`start_date`, `end_date`, `total_price`, `status`).
   - Relasi: `belongsTo(User)`, `belongsTo(Camera)`, `hasOne(Payment)`
4. **`payments`**: Transaksi pembayaran Midtrans (`order_id`, `snap_token`, `payment_status`).
   - Relasi: `belongsTo(Rental)`
5. **`notifications`**: Tabel bawaan Laravel In-App Notification.
   - Relasi: `belongsTo(User)`

---

## 🚀 Panduan Instalasi Lokal

### Prasyarat
- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL / MariaDB

### Langkah-Langkah Instalasi

1. **Clone Repository**
```bash
git clone [https://github.com/username/camrent.git](https://github.com/username/camrent.git)
cd camrent
```


2. **Instalasi Dependensi PHP & JavaScript**
```bash
composer install
npm install && npm run build
```


3. **Konfigurasi Environment (`.env`)**
Salin berkas `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```


Atur koneksi database dan kredensial Midtrans pada berkas `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=camrent_db
DB_USERNAME=root
DB_PASSWORD=

MIDTRANS_SERVER_KEY=SB-Mid-server-YOUR_SERVER_KEY
MIDTRANS_CLIENT_KEY=SB-Mid-client-YOUR_CLIENT_KEY
MIDTRANS_IS_PRODUCTION=false
```


4. **Generate App Key & Symlink Storage**
```bash
php artisan key:generate
php artisan storage:link
```


5. **Migrasi Database & Seeder**
```bash
php artisan migrate --seed
```


6. **Jalankan Server Lokal**
```bash
php artisan serve
```


Aplikasi akan berjalan pada halaman `http://127.0.0.1:8000`.

---
