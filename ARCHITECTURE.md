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