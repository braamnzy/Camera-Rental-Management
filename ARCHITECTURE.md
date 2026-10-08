Berikut adalah pemetaan ulang arsitektur sistem **CamRent** secara detail, rapi, dan terintegrasi penuh untuk tim (2 Backend, 2 Frontend) dengan Midtrans Snap, Upload Gambar ke Server, Notifikasi In-App, dan Dashboard Admin.

---

## 1. Peta Arsitektur Sistem Utama (System Architecture)

```
                       ┌──────────────────────────────────────────┐
                       │            FRONTEND CLIENT               │
                       │     (Blade / Vue / React / HTML-JS)      │
                       └───────────────────┬──────────────────────┘
                                           │
                                     HTTP / REST API
                                     (Bearer Token)
                                           │
                                           ▼
┌──────────────────────────────────────────────────────────────────────────────────┐
│                                LARAVEL 13 BACKEND                                │
│                                                                                  │
│   ┌────────────────────┐   ┌────────────────────┐   ┌────────────────────┐       │
│   │   Sanctum Auth     │   │   Form Request     │   │  API Resources &   │       │
│   │   & Middleware     │──►│    Validation      │──►│   Error Handling   │       │
│   └────────────────────┘   └────────────────────┘   └────────────────────┘       │
│                                      │                                           │
│                                      ▼                                           │
│                          ┌──────────────────────┐                                │
│                          │  Controllers & Logic │                                │
│                          └───────────┬──────────┘                                │
│                                      │                                           │
│          ┌───────────────────────────┼───────────────────────────┐               │
│          ▼                           ▼                           ▼               │
│  ┌───────────────┐           ┌───────────────┐           ┌───────────────┐       │
│  │ Eloquent ORM  │           │ File Storage  │           │ Database Notif│       │
│  └───────┬───────┘           └───────┬───────┘           └───────┬───────┘       │
└──────────┼───────────────────────────┼───────────────────────────┼───────────────┘
           │                           │                           │
           ▼                           ▼                           ▼
┌────────────────────┐   ┌──────────────────────────┐   ┌────────────────────┐
│   MySQL / MariaDB  │   │  storage/app/public/     │   │ notifications Table│
│   (Database)       │   │  (Foto Kamera & Display) │   │ (In-App Notif)     │
└────────────────────┘   └──────────────────────────┘   └────────────────────┘
           ▲
           │ Webhook Callback (IPN)
┌──────────┴─────────┐
│ Midtrans Payment   │
│ Gateway (Snap API) │
└────────────────────┘

```

---

## 2. Skema Database & Eloquent Relationship (5 Tabel Utama)

```
 +---------------+          +---------------+          +---------------+
 |     users     | 1 ─── *  |    rentals    | * ─── 1  |    cameras    |
 +---------------+          +---------------+          +---------------+
         │                          │
         │ 1                        │ 1
         │                          │
         *                          *
 +---------------+          +---------------+
 | notifications |          |   payments    |
 +---------------+          +---------------+

```

### Detail Atribut & Relasi Tabel

1. **`users`**
* Atribut: `id`, `name`, `email`, `password`, `phone`, `role` (`'admin'`, `'customer'`), `created_at`, `updated_at`.
* Relasi: `hasMany(Rental::class)`, `hasMany(DatabaseNotification::class)`.


2. **`cameras`**
* Atribut: `id`, `name`, `brand`, `daily_rate`, `stock`, `image` *(relative path: `cameras/file.jpg`)*, `description`, `status` (`'available'`, `'maintenance'`), `created_at`, `updated_at`.
* Relasi: `hasMany(Rental::class)`.


3. **`rentals`**
* Atribut: `id`, `user_id`, `camera_id`, `start_date`, `end_date`, `total_days`, `total_price`, `status` (`'pending_payment'`, `'paid'`, `'picked_up'`, `'returned'`, `'cancelled'`), `created_at`, `updated_at`.
* Relasi: `belongsTo(User::class)`, `belongsTo(Camera::class)`, `hasOne(Payment::class)`.


4. **`payments`**
* Atribut: `id`, `rental_id`, `order_id` *(unik)*, `snap_token`, `gross_amount`, `payment_type`, `payment_status` (`'pending'`, `'settlement'`, `'deny'`, `'expire'`, `'cancel'`), `created_at`, `updated_at`.
* Relasi: `belongsTo(Rental::class)`.


5. **`notifications`** *(In-App Notification bawaan Laravel)*
* Atribut: `id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`.



---

## 3. Matriks Otorisasi & Middleware

| Endpoint Resource | Role Customer | Role Admin | Middleware & Permission |
| --- | --- | --- | --- |
| **Katalog Kamera (`GET /api/cameras`)** | Public | Public | None |
| **Detail Kamera (`GET /api/cameras/{id}`)** | Public | Public | None |
| **Auth User (`/api/auth/*`)** | ✅ | ✅ | `auth:sanctum` |
| **Booking Kamera (`POST /api/rentals`)** | ✅ | ❌ | `auth:sanctum`, `role:customer` |
| **Generate Payment Token** | ✅ (Milik Sendiri) | ❌ | `auth:sanctum`, `role:customer` |
| **Riwayat / Detail Rental** | ✅ (Milik Sendiri) | ✅ (Semua Data) | `auth:sanctum` |
| **Upload / Edit / Hapus Kamera** | ❌ | ✅ | `auth:sanctum`, `role:admin` |
| **Update Status Rental (`picked_up`, `returned`)** | ❌ | ✅ | `auth:sanctum`, `role:admin` |
| **Tabel Rekap Schedule Admin** | ❌ | ✅ | `auth:sanctum`, `role:admin` |
| **Check In-App Notification** | ✅ | ✅ | `auth:sanctum` |

---

## 4. Alur Bisnis Utama (Order, Payment & Status Flow)

```
[CUSTOMER]                    [LARAVEL BACKEND]                    [MIDTRANS]
    │                                │                                │
    │─── 1. POST /api/rentals ──────►│                                │
    │   (Cek Stok & Bentrok)         │                                │
    │                                │─── 2. Simpan Transaksi         │
    │                                │    (Status: PENDING_PAYMENT)   │
    │                                │                                │
    │─── 3. POST /api/payments ─────►│                                │
    │                                │─── 4. Request Snap Token ─────►│
    │                                │◄── 5. Return Snap Token ───────│
    │◄── 6. Kirim Snap Token ────────│                                │
    │                                │                                │
    │─── 7. Bayar via Snap UI ───────────────────────────────────────►│
    │                                │                                │
    │                                │◄── 8. Webhook Notification ────│
    │                                │    (Status: SETTLEMENT)        │
    │                                │                                │
    │                                │─── 9. Update Payment & Rental │
    │                                │    (Rental Status: PAID)       │
    │                                │                                │
    │                                │─── 10. Kirim In-App Notif ────► [DATABASE]

```

---

## 5. Pemetaan Lengkap RESTful API Endpoints

### Auth (`/api/auth`)

* `POST /api/auth/register` — Registrasi customer.
* `POST /api/auth/login` — Login user/admin & generate Bearer Token.
* `POST /api/auth/logout` — Revoke token.
* `GET /api/auth/me` — Ambil profil user login.

### Katalog Kamera (`/api/cameras`)

* `GET /api/cameras` — List katalog (Pagination + Search + Filter).
* `GET /api/cameras/{id}` — Detail informasi unit kamera.
* `POST /api/cameras` — Tambah unit + upload foto display (`multipart/form-data`) [`Admin`].
* `POST /api/cameras/{id}` — Update unit/foto (`_method=PUT`) [`Admin`].
* `DELETE /api/cameras/{id}` — Hapus unit kamera [`Admin`].

### Sewa & Booking (`/api/rentals`)

* `GET /api/rentals` — List transaksi (Customer: milik sendiri, Admin: semua).
* `GET /api/rentals/{id}` — Detail pesanan sewa.
* `POST /api/rentals` — Buat booking baru (Validasi bentrok tanggal & stok) [`Customer`].
* `PATCH /api/rentals/{id}/status` — Update status (`picked_up`, `returned`, `cancelled`) [`Admin`].

### Pembayaran / Midtrans (`/api/payments`)

* `POST /api/payments/snap-token` — Generate Snap Token Midtrans [`Customer`].
* `POST /api/payments/midtrans-notification` — Webhook IPN Callback Midtrans (Public).

### Dashboard & Schedule Admin (`/api/admin`)

* `GET /api/admin/rentals/schedule` — Rekap jadwal sewa kamera dalam format tabel/kalender [`Admin`].

### Notifikasi (`/api/notifications`)

* `GET /api/notifications` — List notifikasi in-app pengguna.
* `POST /api/notifications/{id}/read` — Tandai notifikasi dibaca.

---

## 6. Pemetaan Berkas Lengkap Per Anggota (Jobdesk File Mapping)

Agar tidak terjadi bentrok saat bekerja langsung di branch `main`:

### 🛠️ BACKEND 1 (Core, Auth, Kamera & Notifikasi)

* **`app/Http/Controllers/Api/AuthController.php`** — Handling Login, Register, Logout, Me.
* **`app/Http/Controllers/Api/CameraController.php`** — Handling CRUD Katalog & Upload Gambar ke `storage/app/public/cameras`.
* **`app/Http/Controllers/Api/NotificationController.php`** — Handling List & Read In-App Notification.
* **`app/Models/User.php`**, **`app/Models/Camera.php`** — Model & Eloquent Relationships.
* **`app/Http/Requests/StoreCameraRequest.php`** — Validasi form kamera & file gambar (Max 2MB).
* **`app/Http/Resources/CameraResource.php`** — Format JSON response & URL gambar publik (`asset('storage/...')`).
* **`app/Notifications/RentalStatusNotification.php`** — In-App Notification Class.
* **`database/migrations/xxxx_create_users_table.php`** & **`xxxx_create_cameras_table.php`**

---

### 💳 BACKEND 2 (Booking, Midtrans & Admin Schedule)

* **`app/Http/Controllers/Api/RentalController.php`** — Handling Store Booking, Update Status, & Admin Schedule Table.
* **`app/Http/Controllers/Api/PaymentController.php`** — Handling Snap Token Midtrans & Webhook Callback (IPN).
* **`app/Models/Rental.php`**, **`app/Models/Payment.php`** — Model & Eloquent Relationships.
* **`app/Http/Requests/StoreRentalRequest.php`** — Validasi bentrok tanggal & ketersediaan stok kamera.
* **`app/Http/Resources/RentalResource.php`** — Format JSON response detail rental & payment status.
* **`database/migrations/xxxx_create_rentals_table.php`** & **`xxxx_create_payments_table.php`**

---

### 🎨 FRONTEND 1 (Customer Portal & Checkout Flow)

* **`resources/views/layouts/customer.blade.php`** — Frame Layout Customer (Navbar, Footer, Badge Notif).
* **`resources/views/customer/catalog.blade.php`** — UI Grid Katalog Kamera (Search, Filter, Pagination).
* **`resources/views/customer/detail.blade.php`** — UI Detail Unit Kamera & Form Selector Tanggal Sewa.
* **`resources/views/customer/booking-form.blade.php`** — Form Konfirmasi Booking & Integrasi Pop-up Midtrans Snap JS.
* **`resources/views/customer/my-rentals.blade.php`** — UI Riwayat Sewa Customer & Status Tagihan Real-time.
* **`resources/views/auth/login.blade.php`** & **`register.blade.php`** — UI Auth Customer.

---

### 📊 FRONTEND 2 (Admin Dashboard & Schedule Monitor)

* **`resources/views/layouts/admin.blade.php`** — Frame Layout Admin (Sidebar, Topbar).
* **`resources/views/admin/dashboard.blade.php`** — Statistics Summary Card (Total Revenue, Active Rentals).
* **`resources/views/admin/cameras/index.blade.php`** & **`create.blade.php`** — Tabel Kelola Kamera & Form Upload Display Gambar.
* **`resources/views/admin/rentals/index.blade.php`** — Tabel Transaksi Sewa & Action Button (`Pick Up` / `Return`).
* **`resources/views/admin/rentals/schedule.blade.php`** — Tabel Monitoring Jadwal Sewa Kamera per Tanggal.

---

### 🤝 File Bersama (Wajib Koordinasi Sebelum Edit)

* **`routes/api.php`** — Menampung seluruh endpoint REST API.
* **`database/seeders/DatabaseSeeder.php`** — Menjalankan `UserSeeder` dan `CameraSeeder`.
* **`.env`** — Kredensial Database lokal & Server/Client Key Midtrans Sandbox.

# Saran Krusial untuk Project CamRent

Kalau harus fokus **hanya pada masalah yang benar-benar bisa bikin project gagal / rusak**, ini 5 prioritas utama kamu:

---

## 🔴 1. Race Condition saat Booking (Paling Krusial)

**Masalah:**
Dua customer booking kamera yang sama di detik yang sama → validasi stok lolos dua-duanya → **overbooking**.

**Solusi wajib:**
```php
DB::transaction(function () use ($request) {
    $camera = Camera::where('id', $request->camera_id)
                    ->lockForUpdate()   // ← kunci baris ini
                    ->first();

    $terpakai = Rental::where('camera_id', $camera->id)
        ->whereIn('status', ['paid', 'picked_up'])
        ->where(function ($q) use ($request) {
            $q->whereBetween('start_date', [$request->start_date, $request->end_date])
              ->orWhereBetween('end_date', [$request->start_date, $request->end_date]);
        })->sum('quantity');

    if ($terpakai + $request->quantity > $camera->stock) {
        throw ValidationException::withMessages(['stock' => 'Stok tidak cukup']);
    }

    return Rental::create([...]);
});
```
**Tanpa `lockForUpdate()` + transaction, project kamu akan kacau begitu ada traffic nyata.**

---

## 🔴 2. Verifikasi Signature Webhook Midtrans

**Masalah:**
Endpoint `/api/payments/midtrans-notification` publik. Kalau tidak verifikasi signature, **siapa pun bisa kirim POST palsu** dan mengubah status rental jadi `paid` tanpa bayar.

**Solusi wajib:**
```php
$signature = hash('sha512',
    $request->order_id .
    $request->status_code .
    $request->gross_amount .
    config('midtrans.server_key')
);

if ($signature !== $request->signature_key) {
    return response()->json(['message' => 'Invalid signature'], 403);
}
```
**Ini bukan opsional.** Tanpa ini, sistem pembayaran kamu bisa dibobol hanya dengan `curl`.

---

## 🔴 3. Middleware `role` Belum Ada di Laravel

**Masalah:**
Di matriks otorisasi kamu tulis `role:admin` dan `role:customer`, tapi **middleware ini tidak ada by default di Laravel**. Kalau lupa dibuat, semua endpoint admin bisa diakses customer.

**Solusi wajib:**
```php
// app/Http/Middleware/EnsureUserHasRole.php
public function handle($request, Closure $next, string $role)
{
    if (!$request->user() || $request->user()->role !== $role) {
        return response()->json(['message' => 'Forbidden'], 403);
    }
    return $next($request);
}
```
Daftarkan sebagai alias `role` di `bootstrap/app.php`. **Tanpa ini, dashboard admin kamu terbuka untuk umum.**

---

## 🔴 4. Stok Kamera Tidak Bisa Dilacak Per Unit

**Masalah:**
Kolom `stock` cuma angka. Kalau satu kamera punya 3 unit, kamu tidak tahu **unit mana** yang disewa. Saat ada kerusakan, kamu tidak bisa lacak.

**Solusi minimal (pilih salah satu):**

**Opsi A — Cepat (cukup untuk MVP):**
Tambahkan kolom `quantity` di `rentals`. Stok tersedia = `stock - sum(quantity yang aktif)`.

**Opsi B — Benar (kalau mau serius):**
Buat tabel `camera_units`:
```
id, camera_id, serial_number, condition, status ('available','rented','maintenance')
```
Lalu `rentals` merefer ke `camera_unit_id`.

**Kalau tidak diperbaiki sekarang, nanti migrasi data akan sangat menyakitkan.**

---

## 🔴 5. Upload Gambar — Hapus File Lama & Validasi Ketat

**Masalah:**
- Kalau admin update foto kamera, file lama **menumpuk di storage** dan bikin server penuh.
- Kalau tidak validasi mime, orang bisa upload `.php` dan **RCE**.

**Solusi wajib:**
```php
// StoreCameraRequest
'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',

// Saat update
if ($request->hasFile('image')) {
    Storage::disk('public')->delete($camera->image); // hapus lama
    $camera->image = $request->file('image')->store('cameras', 'public');
}
```
**Tanpa validasi mime, ini celah keamanan fatal.**

---

## 🟡 Bonus: Hal Krusial Tapi Sering Diabaikan

| # | Masalah | Dampak Kalau Dibiarkan |
|---|---------|------------------------|
| 6 | Tidak ada `'expired'` di enum status rental | Rental menggantung selamanya saat pembayaran kedaluwarsa |
| 7 | Tidak ada Policy untuk "milik sendiri" | Customer bisa lihat/ubah rental orang lain |
| 8 | Tidak ada rate limit di `/auth/login` | Brute force password |
| 9 | Tidak ada logging webhook Midtrans | Susah debug kalau ada sengketa pembayaran |
| 10 | `.env` Midtrans di-commit | Kunci server bocor |

---

## 🎯 Urutan Prioritas Pengerjaan

```
Minggu 1  →  #1 Race Condition + #3 Middleware role
Minggu 2  →  #2 Signature Webhook + #5 Upload validation
Minggu 3  →  #4 Struktur stok (pilih Opsi A dulu)
Minggu 4  →  #6–#10 (hardening)
```

---

Berikut adalah pemetaan **halaman-halaman antarmuka (UI/Views)** yang perlu dibuat oleh tim Frontend, beserta rincian **fitur, komponen, dan alur interaksi** di dalam setiap halamannya.

---

### 🎨 SISI CUSTOMER (FRONTEND 1)

#### 1. Halaman Auth (`login.blade.php` & `register.blade.php`)

* **Fitur & Komponen Utama:**
* **Form Login:** Input Email & Password dengan tombol *Submit*.
* **Form Register:** Input Nama Lengkap, Email, No. WhatsApp (aktif), Password, & Konfirmasi Password.
* **Feedback Error:** Validasi real-time (email sudah terdaftar, password kurang dari 8 karakter, kredensial salah).
* **Redirect Smart:** Setelah login sukses, redirect otomatis ke Katalog atau ke halaman Checkout jika pengguna sebelumnya terhenti saat booking.



#### 2. Halaman Katalog Utama (`customer/catalog.blade.php`)

* **Fitur & Komponen Utama:**
* **Hero Banner / Promo:** Visual header ringkas persewaan kamera.
* **Bar Pencarian & Filter:** Search bar nama kamera, filter dropdown kategori (DSLR, Mirrorless, Lens, Action Cam), dan sorting (Harga Termurah/Termahal).
* **Grid Card Kamera:** Menampilkan Foto unit, Nama Kamera, Merk, Harga Sewa/Hari, dan Badge Ketersediaan (`Tersedia` / `Disewa`).
* **Badge/Counter Notifikasi In-App:** Lonceng notifikasi di Navbar yang menampilkan jumlah pesan/status transaksi yang belum dibaca.



#### 3. Halaman Detail Kamera (`customer/detail.blade.php`)

* **Fitur & Komponen Utama:**
* **Galeri Foto Unit:** Tampilan utama foto kamera berresolusi tinggi.
* **Informasi Spesifikasi:** Deskripsi kondisi, kelengkapan unit (baterai, charger, memory card, tas), dan harga sewa.
* **Date Picker Range (Tanggal Sewa):** Widget kalender untuk memilih `Tanggal Mulai` dan `Tanggal Selesai`.
* **Kalkulator Durasi & Total Harga:** Menghitung otomatis durasi hari ($\text{Selesai} - \text{Mulai}$) $\times$ harga sewa/hari secara real-time.
* **Tombol "Sewa Sekarang":** Mengarah ke halaman *Booking Confirmation* (hanya aktif jika stok tersedia pada tanggal terpilih).



#### 4. Halaman Konfirmasi Booking (`customer/booking-form.blade.php`)

* **Fitur & Komponen Utama:**
* **Ringkasan Pesanan:** Rincian unit kamera, tanggal sewa, durasi hari, dan rincian total bayar.
* **Form Data Penyewa:** Verifikasi Nama, No. HP, dan catatan tambahan.
* **Metode Pembayaran (Midtrans Integration):** Tombol *"Lanjut ke Pembayaran"* yang memicu kemunculan **Pop-up / Modal Midtrans Snap**.
* **Snap SDK Pop-up:** Pilihan metode pembayaran (QRIS, GoPay, Bank Transfer/VA, Credit Card) tanpa beralih halaman.



#### 5. Halaman Riwayat Sewa Saya (`customer/my-rentals.blade.php`)

* **Fitur & Komponen Utama:**
* **Tab Filter Status:** Memfilter transaksi (`Semua`, `Menunggu Bayar`, `Siap Diambil`, `Sedang Disewa`, `Selesai`).
* **Card Transaksi:** Informasi ID Order, Tanggal Sewa, Total Harga, dan Status Transaksi (dilengkapi warna badge status).
* **Tombol "Bayar Sekarang":** Muncul pada transaksi status `pending_payment` jika pop-up pembayaran sebelumnya tertutup.
* **Tombol "Lihat Bukti/Nota":** Modal rincian transaksi ringkas yang siap dicetak/di-screenshot.



---

### 📊 SISI ADMIN (FRONTEND 2)

#### 1. Halaman Dashboard Ringkasan (`admin/dashboard.blade.php`)

* **Fitur & Komponen Utama:**
* **Widget Metric Cards:** Total Pendapatan Bulan Ini, Total Unit Kamera, Penyewaan Aktif (Sedang Dibawa), dan Transaksi Menunggu Konfirmasi.
* **Tabel Transaksi Terbaru:** 5-10 transaksi masuk paling akhir untuk penanganan cepat.
* **Quick Actions:** Tombol pintas ke *"Tambah Kamera Baru"* atau *"Cek Jadwal Hari Ini"*.



#### 2. Halaman Kelola Inventaris Kamera (`admin/cameras/index.blade.php`)

* **Fitur & Komponen Utama:**
* **Data Table Kamera:** Menampilkan daftar seluruh unit (Foto Thumbnail, Nama, Kategori, Stok, Harga/Hari, Status Unit).
* **Filter & Pencarian Unit:** Cari berdasarkan nama atau filter unit yang sedang *Maintenance*.
* **Action Buttons:** Tombol *Edit* unit, *Hapus* unit (dengan konfirmasi modal), dan Ubah Status Unit (`Tersedia` / `Perbaikan`).



#### 3. Halaman Form Tambah/Edit Kamera (`admin/cameras/create.blade.php` & `edit.blade.php`)

* **Fitur & Komponen Utama:**
* **Form Input Data:** Nama kamera, Merk, Kategori, Deskripsi/Spesifikasi, Harga/Hari, dan Jumlah Stok.
* **File Upload Foto Display:** Input file `image/*` dilengkapi fitur **Live Image Preview** sebelum disimpan.
* **Validasi Client-Side:** Peringatan jika format berkas bukan gambar atau ukuran berkas melebihi 2MB.



#### 4. Halaman Kelola Transaksi Penyewaan (`admin/rentals/index.blade.php`)

* **Fitur & Komponen Utama:**
* **Tabel Master Transaksi:** Daftar seluruh pesanan sewa (ID Order, Nama Customer, Unit, Tanggal Sewa, Status Bayar, Status Rental).
* **Tombol Aksi Status Rental:**
* **"Serahkan Unit (Pick Up)":** Mengubah status menjadi `sedang_disewa` saat customer mengambil unit di toko.
* **"Konfirmasi Pengembalian (Return)":** Mengubah status menjadi `selesai` dan otomatis mengembalikan stok unit.
* **"Batalkan Pesanan":** Untuk membatalkan transaksi yang bermasalah.





#### 5. Halaman Monitoring Jadwal Sewa (`admin/rentals/schedule.blade.php`)

* **Fitur & Komponen Utama:**
* **Filter Tanggal / Kalender Jadwal:** Menampilkan rekap pemakaian kamera per hari/minggu.
* **Tabel Matrix Availability:** Memetakan unit kamera mana saja yang sedang **Keluar (Disewa)**, **Tersedia di Toko**, atau **Jatuh Tempo Kembali Hari Ini**.
* **Peringatan Keterlambatan (Overdue Alert):** Highlight warna merah untuk transaksi yang belum dikembalikan melewati `end_date`.



---