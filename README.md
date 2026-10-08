# 📷 CamRent - Web Application for Camera Rental Service

**CamRent** adalah platform penyewaan kamera berbasis web yang dibangun dengan framework **Laravel 13** di sisi backend dan **Blade / Vue / React / HTML-JS** di sisi frontend. Sistem ini dilengkapi dengan otentikasi API berbasis **Sanctum**, integrasi payment gateway **Midtrans Snap**, notifikasi *in-app*, serta sistem *monitoring* jadwal dan inventaris untuk administrator.

---

## 🛠️ Stack Teknologi

- **Backend**: Laravel 13 (PHP 8.2+)
- **Database**: MySQL / MariaDB
- **Authentication**: Laravel Sanctum (Bearer Token)
- **Payment Gateway**: Midtrans Snap API (Sandbox Mode)
- **Frontend**: Blade / HTML5 / CSS3 / JavaScript (Vue/React Optional)
- **Storage**: Storage Link Public (`storage/app/public/cameras`)

---

## 🏗️ Arsitektur & Fitur Utama

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

## 🗄️ Skema Database & Relasi (5 Tabel Utama)

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

## ⚙️ Panduan Instalasi Lokal (Setup Environment)

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

## 👥 Pembagian Tugas Tim (Jobdesk Mapping)

| No | Nama | Peran | Area Kerja Utama |
| --- | --- | --- | --- |
| 1 | **Andyka** | Backend 1 | Core, Auth (Sanctum), CRUD Kamera, File Upload, In-App Notification |
| 2 | **Abbas** | Backend 2 | Booking Logic, Midtrans Integration (Snap & Webhook IPN), Admin Schedule |
| 3 | **Arifin** | Frontend 1 | Customer Portal, Auth UI, Catalog UI, Booking Form & Midtrans Pop-up |
| 4 | **Huri** | Frontend 2 | Admin Dashboard, Layout Sidebar Admin, Inventory CRUD UI, Schedule Monitoring |

> ⚠️ **Aturan Kerja Berkas Bersama (`routes/api.php`, `DatabaseSeeder.php`, `.env`)**:
> Harap selalu berkoordinasi dengan tim sebelum melakukan *commit* atau *merge* pada berkas bersama untuk menghindari *conflict*.

---

## 🔐 Ringkasan RESTful API Endpoints

| Method | Endpoint | Deskripsi | Akses |
| --- | --- | --- | --- |
| `POST` | `/api/auth/register` | Registrasi akun baru | Public |
| `POST` | `/api/auth/login` | Login & generate Bearer Token | Public |
| `GET` | `/api/cameras` | List katalog kamera (Filter/Search) | Public |
| `GET` | `/api/cameras/{id}` | Detail unit kamera | Public |
| `POST` | `/api/rentals` | Buat pesanan booking baru | Customer |
| `POST` | `/api/payments/snap-token` | Generate Snap Token Midtrans | Customer |
| `POST` | `/api/payments/midtrans-notification` | Webhook IPN Callback Midtrans | Public (Midtrans) |
| `POST` | `/api/cameras` | Tambah unit kamera baru | Admin |
| `PATCH` | `/api/rentals/{id}/status` | Update status rental (Pick Up / Return) | Admin |
| `GET` | `/api/admin/rentals/schedule` | Rekap tabel/jadwal sewa | Admin |

---

## 🎨 Skema Warna Utama (Design System)

* **Header / Navigation / Footer**: Deep Navy (`#1E293B`)
* **Active Navigation / Hover**: Darker Navy (`#0F172A`)
* **Background Utama**: Clean White (`#FFFFFF`)
* **Card & Surface Background**: Light Slate (`#F8FAFC`)
* **Tombol / Akses Utama**: Royal Blue (`#2563EB`)
* **Status Success (Paid / Available)**: Emerald Green (`#10B981`)
* **Status Warning (Pending / Rented)**: Amber Yellow (`#F59E0B`)
* **Status Danger (Cancelled / Overdue)**: Rose Red (`#EF4444`)

---

## 📄 Lisensi

Proyek ini dikembangkan untuk kebutuhan pembelajaran dan portofolio tim **CamRent** © 2026. All Rights Reserved.

```

---

<ElicitationsGroup message="Bagaimana Anda ingin melanjutkannya?">
  <Elicitation label="Buat skema DB Seeder lengkap (DatabaseSeeder.php)" query="Tolong buatkan kode PHP lengkap untuk database/seeders/DatabaseSeeder.php, UserSeeder.php, dan CameraSeeder.php sesuai spesifikasi CamRent."/>
  <Elicitation label="Buat implementasi controller backend (AuthController & CameraController)" query="Tolong buatkan implementasi kode Laravel controller lengkap untuk AuthController.php dan CameraController.php milik Backend 1."/>
  <Elicitation label="Buat alur penanganan Race Condition di RentalController" query="Tolong buatkan kode lengkap untuk RentalController.php yang menangani booking dengan lockForUpdate dan integrasi Midtrans Snap."/>
</ElicitationsGroup>

```