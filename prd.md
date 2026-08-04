# PRD: AzzaOps — Sistem Manajemen Operasional

**Project**: AzzaOps  
**Client**: PT. Azza Karunia Jaya  
**Version**: 1.0  
**Tanggal**: 22 Juli 2026  
**Status**: Draft  

---

## Daftar Isi

1. [Overview & Objectives](#1-overview--objectives)
2. [User Stories](#2-user-stories)
3. [Database Schema](#3-database-schema)
4. [Alur Kerja (Business Flow)](#4-alur-kerja-business-flow)
5. [API Endpoints](#5-api-endpoints)
6. [Fitur per Platform](#6-fitur-per-platform)
7. [Non-Functional Requirements](#7-non-functional-requirements)
8. [Referensi Spreadsheet Existing](#8-referensi-spreadsheet-existing)

---

## 1. Overview & Objectives

### 1.1 Latar Belakang

PT. Azza Karunia Jaya adalah perusahaan jasa instalasi, perawatan, dan servis AC serta elektronik. Saat ini operasional dicatat menggunakan spreadsheet manual yang rawan human error, sulit dilacak, dan tidak mendukung kolaborasi real-time antar tim.

**Skala operasional:**
- 5 staff (admin, kepala teknisi, teknisi)
- 5–10 job per hari
- Layanan: pengecekan, servis, instalasi, perawatan AC residential & komersial

### 1.2 Tujuan Bisnis

| # | Tujuan | Indikator |
|---|--------|-----------|
| 1 | Menghilangkan pencatatan manual via spreadsheet | 100% work order diinput via sistem |
| 2 | Mengurangi human error dalam tracking pekerjaan | Status pekerjaan real-time & akurat |
| 3 | Mempercepat pembuatan invoice & RAB | Generate PDF < 1 menit |
| 4 | Dokumentasi pekerjaan terstandar | Setiap job punya laporan + foto |
| 5 | Visibilitas keuangan | Neraca saldo & cost percentage real-time |
| 6 | Koordinasi tim lapangan lebih efisien | Notifikasi push ke teknisi |

### 1.3 Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel (PHP 8.1+) |
| Frontend Web | Blade + Bootstrap 5 + jQuery (tanpa Vite/build step) |
| Auth Web | Laravel Breeze |
| Auth API | Laravel Sanctum (token-based) |
| Database | MySQL 8.0 |
| Mobile | Flutter + GetX state management |
| Push Notification | Firebase Cloud Messaging (FCM) |
| PDF | DomPDF / Snappy |
| Deployment | Shared hosting atau VPS |

### 1.4 User Roles

| Role | Deskripsi | Platform |
|------|-----------|----------|
| Super Admin | Full access, manage users & konfigurasi sistem | Web |
| Admin/CS | Operasional harian: work order, invoice, RAB, keuangan | Web |
| Kepala Teknisi | Terima work order, assign teknisi, monitor progress | Web + Mobile |
| Teknisi | Terima pekerjaan, submit laporan & foto | Mobile |
| Finance *(future)* | Akses khusus modul keuangan | Web |

---

## 2. User Stories

### 2.1 Super Admin

| ID | User Story | Priority |
|----|-----------|----------|
| SA-01 | Sebagai Super Admin, saya dapat menambah, mengedit, dan menonaktifkan user staff agar hanya orang yang berwenang yang mengakses sistem | Must |
| SA-02 | Sebagai Super Admin, saya dapat mengatur role dan permission setiap user | Must |
| SA-03 | Sebagai Super Admin, saya dapat mengonfigurasi setting aplikasi (nama perusahaan, logo, alamat, nomor WA, header/footer invoice) | Must |
| SA-04 | Sebagai Super Admin, saya dapat melihat seluruh data dan dashboard yang sama dengan Admin | Must |
| SA-05 | Sebagai Super Admin, saya dapat melihat audit log aktivitas user | Should |

### 2.2 Admin/CS

**Master Data:**

| ID | User Story | Priority |
|----|-----------|----------|
| AD-01 | Sebagai Admin, saya dapat mengelola data customer (tambah, edit, lihat, hapus) termasuk data perusahaan untuk customer B2B | Must |
| AD-02 | Sebagai Admin, saya dapat mengelola kategori jasa/service (AC Residential, AC Komersial, Elektronik, dll) | Must |
| AD-03 | Sebagai Admin, saya dapat melihat daftar teknisi beserta status ketersediaannya | Must |

**Work Order:**

| ID | User Story | Priority |
|----|-----------|----------|
| AD-04 | Sebagai Admin, saya dapat membuat work order baru dengan mengisi customer, kategori, deskripsi pekerjaan, lokasi, dan tanggal rencana | Must |
| AD-05 | Sebagai Admin, saya dapat melihat daftar semua work order dengan filter berdasarkan status, tanggal, customer, dan teknisi | Must |
| AD-06 | Sebagai Admin, saya dapat melihat detail work order termasuk timeline perubahan status | Must |
| AD-07 | Sebagai Admin, saya dapat mengedit work order yang belum selesai | Must |
| AD-08 | Sebagai Admin, saya dapat membatalkan work order | Must |
| AD-09 | Sebagai Admin, saya dapat melihat laporan & foto dari teknisi setelah pekerjaan selesai | Must |

**Invoice & RAB:**

| ID | User Story | Priority |
|----|-----------|----------|
| AD-10 | Sebagai Admin, saya dapat generate invoice dari work order (termasuk item jasa & material) | Must |
| AD-11 | Sebagai Admin, saya dapat mendownload invoice dalam format PDF | Must |
| AD-12 | Sebagai Admin, saya dapat membuat RAB untuk pekerjaan instalasi | Must |
| AD-13 | Sebagai Admin, saya dapat mendownload RAB dalam format PDF | Must |
| AD-14 | Sebagai Admin, saya dapat mengubah status invoice (kirim ke customer, tandai lunas, dll) | Must |
| AD-15 | Sebagai Admin, saya dapat membuat invoice dengan nilai Rp 0 (untuk pengecekan yang dilanjutkan ke pengerjaan) | Must |

**Keuangan:**

| ID | User Story | Priority |
|----|-----------|----------|
| AD-16 | Sebagai Admin, saya dapat melihat dashboard keuangan (pemasukan, pengeluaran, neraca saldo) | Must |
| AD-17 | Sebagai Admin, saya dapat menginput pengeluaran operasional (beli material, transport, dll) | Must |
| AD-18 | Sebagai Admin, saya dapat melihat laporan keuangan per periode (harian, mingguan, bulanan) | Must |
| AD-19 | Sebagai Admin, saya dapat melihat cost percentage per pekerjaan | Should |

**Dashboard:**

| ID | User Story | Priority |
|----|-----------|----------|
| AD-20 | Sebagai Admin, saya dapat melihat ringkasan operasional hari ini: jumlah WO baru, in progress, selesai | Must |
| AD-21 | Sebagai Admin, saya dapat melihat grafik tren pekerjaan per minggu/bulan | Should |

### 2.3 Kepala Teknisi

| ID | User Story | Priority |
|----|-----------|----------|
| KT-01 | Sebagai Kepala Teknisi, saya dapat melihat daftar work order yang perlu ditugaskan | Must |
| KT-02 | Sebagai Kepala Teknisi, saya dapat assign satu atau lebih teknisi ke work order | Must |
| KT-03 | Sebagai Kepala Teknisi, saya dapat melihat ketersediaan dan beban kerja setiap teknisi | Must |
| KT-04 | Sebagai Kepala Teknisi, saya dapat memonitor progress semua pekerjaan yang sedang berjalan | Must |
| KT-05 | Sebagai Kepala Teknisi, saya dapat melihat laporan yang disubmit teknisi dan memverifikasinya | Must |
| KT-06 | Sebagai Kepala Teknisi, saya mendapat notifikasi ketika ada work order baru masuk | Must |
| KT-07 | Sebagai Kepala Teknisi, saya dapat reassign teknisi jika diperlukan | Should |

### 2.4 Teknisi

| ID | User Story | Priority |
|----|-----------|----------|
| TK-01 | Sebagai Teknisi, saya mendapat push notification ketika ada pekerjaan baru yang ditugaskan ke saya | Must |
| TK-02 | Sebagai Teknisi, saya dapat melihat daftar pekerjaan yang ditugaskan ke saya hari ini | Must |
| TK-03 | Sebagai Teknisi, saya dapat melihat detail pekerjaan (alamat, deskripsi, customer, catatan) | Must |
| TK-04 | Sebagai Teknisi, saya dapat mengupdate status pekerjaan (berangkat, tiba, mulai kerja, selesai) | Must |
| TK-05 | Sebagai Teknisi, saya dapat submit laporan pekerjaan berupa teks deskripsi dan rekomendasi | Must |
| TK-06 | Sebagai Teknisi, saya dapat upload foto dokumentasi pekerjaan (sebelum & sesudah) | Must |
| TK-07 | Sebagai Teknisi, saya dapat melihat riwayat pekerjaan yang pernah saya kerjakan | Must |
| TK-08 | Sebagai Teknisi, saya dapat melihat dan mengelola profil saya | Must |

---

## 3. Database Schema

### 3.1 ENUM Definitions

```
-- Work Order Status
ENUM work_order_status:
  'pending'        -- Baru dibuat, belum ditugaskan
  'assigned'       -- Sudah ditugaskan ke teknisi
  'in_progress'    -- Teknisi sedang mengerjakan
  'checking'       -- Khusus tipe pengecekan, sedang dicek
  'reported'       -- Teknisi sudah submit laporan
  'invoice_sent'   -- Invoice sudah dikirim ke customer
  'negotiating'    -- Sedang negosiasi harga
  'approved'       -- Customer setuju, menunggu pembayaran/pengerjaan lanjutan
  'completed'      -- Selesai (sudah dibayar)
  'cancelled'      -- Dibatalkan

-- Work Order Type
ENUM work_order_type:
  'checking'       -- Pengecekan
  'service'        -- Servis/perbaikan
  'installation'   -- Instalasi baru
  'maintenance'    -- Perawatan berkala

-- Customer Type
ENUM customer_type:
  'individual'     -- Perorangan
  'business'       -- B2B / perusahaan

-- Payment Status
ENUM payment_status:
  'unpaid'         -- Belum dibayar
  'partial'        -- Dibayar sebagian
  'paid'           -- Lunas

-- Financial Transaction Type
ENUM transaction_type:
  'income'         -- Pemasukan
  'expense'        -- Pengeluaran

-- Assignment Status
ENUM assignment_status:
  'pending'        -- Menunggu konfirmasi teknisi
  'accepted'       -- Teknisi menerima
  'rejected'       -- Teknisi menolak
  'completed'      -- Selesai dikerjakan

-- Invoice Status
ENUM invoice_status:
  'draft'          -- Draf, belum dikirim
  'sent'           -- Sudah dikirim ke customer
  'paid'           -- Sudah dibayar
  'cancelled'      -- Dibatalkan

-- RAB Status
ENUM rab_status:
  'draft'          -- Draf
  'sent'           -- Sudah dikirim ke customer
  'approved'       -- Customer setuju
  'rejected'       -- Customer tolak
  'revised'        -- Sedang direvisi

-- Notification Type
ENUM notification_type:
  'work_order_new'         -- WO baru masuk
  'work_order_assigned'    -- WO ditugaskan ke teknisi
  'work_order_updated'     -- Status WO berubah
  'report_submitted'       -- Laporan disubmit
  'invoice_created'        -- Invoice dibuat
  'payment_received'       -- Pembayaran diterima

-- Photo Type
ENUM photo_type:
  'before'         -- Foto sebelum pengerjaan
  'progress'       -- Foto saat pengerjaan
  'after'          -- Foto setelah pengerjaan
```

### 3.2 Tabel Detail

#### `users`

Staff internal perusahaan.

| Kolom | Tipe | Nullable | Default | Keterangan |
|-------|------|----------|---------|------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK |
| name | VARCHAR(255) | NO | | Nama lengkap |
| email | VARCHAR(255) | NO | | UNIQUE, untuk login |
| phone | VARCHAR(20) | YES | NULL | Nomor HP |
| password | VARCHAR(255) | NO | | Hashed password |
| role | ENUM('super_admin','admin','kepala_teknisi','teknisi') | NO | 'teknisi' | Role user |
| is_active | BOOLEAN | NO | TRUE | Status aktif/nonaktif |
| fcm_token | VARCHAR(255) | YES | NULL | Token FCM untuk push notification |
| avatar | VARCHAR(255) | YES | NULL | Path foto profil |
| email_verified_at | TIMESTAMP | YES | NULL | |
| remember_token | VARCHAR(100) | YES | NULL | |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |

**Index:** `UNIQUE(email)`

---

#### `customers`

Data customer, baik perorangan maupun perusahaan.

| Kolom | Tipe | Nullable | Default | Keterangan |
|-------|------|----------|---------|------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK |
| type | ENUM('individual','business') | NO | 'individual' | Tipe customer |
| name | VARCHAR(255) | NO | | Nama customer (perorangan) atau nama PIC |
| company_name | VARCHAR(255) | YES | NULL | Nama perusahaan (wajib jika type=business) |
| pic_name | VARCHAR(255) | YES | NULL | Person In Charge (untuk B2B) |
| phone | VARCHAR(20) | NO | | Nomor HP/WA utama |
| phone_alt | VARCHAR(20) | YES | NULL | Nomor HP alternatif |
| email | VARCHAR(255) | YES | NULL | Email customer |
| address | TEXT | YES | NULL | Alamat lengkap |
| gmaps_link | TEXT | YES | NULL | Link Google Maps alamat customer |
| city | VARCHAR(100) | YES | NULL | Kota |
| market | VARCHAR(100) | YES | NULL | Sumber/sales channel (WA, referral, Tokopedia, dll) |
| notes | TEXT | YES | NULL | Catatan tambahan |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |

**Index:** `INDEX(type)`, `INDEX(phone)`

---

#### `service_categories`

Kategori jasa layanan.

| Kolom | Tipe | Nullable | Default | Keterangan |
|-------|------|----------|---------|------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK |
| name | VARCHAR(255) | NO | | Nama kategori (AC Residential, AC Komersial, Elektronik, dll) |
| description | TEXT | YES | NULL | Deskripsi kategori |
| is_active | BOOLEAN | NO | TRUE | |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |

**Seed data:** AC Residential, AC Komersial, Elektronik, Instalasi Ducting, Cuci AC, dll.

---

#### `work_orders`

Tabel utama pekerjaan.

| Kolom | Tipe | Nullable | Default | Keterangan |
|-------|------|----------|---------|------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK |
| wo_number | VARCHAR(50) | NO | | Nomor WO unik, format: WO-YYYYMMDD-XXXX |
| type | ENUM('checking','service','installation','maintenance') | NO | | Tipe pekerjaan |
| customer_id | BIGINT UNSIGNED | NO | | FK → customers.id |
| service_category_id | BIGINT UNSIGNED | NO | | FK → service_categories.id |
| title | VARCHAR(255) | NO | | Judul singkat pekerjaan |
| description | TEXT | YES | NULL | Deskripsi/detail pekerjaan |
| location | TEXT | NO | | Alamat lokasi pengerjaan |
| gmaps_link | TEXT | YES | NULL | Link Google Maps lokasi pengerjaan |
| scheduled_date | DATE | YES | NULL | Tanggal rencana pengerjaan |
| scheduled_time | TIME | YES | NULL | Jam rencana pengerjaan |
| job_order | INT | YES | NULL | Urutan pengerjaan / urutan pekerjaan |
| started_at | TIMESTAMP | YES | NULL | Waktu mulai pengerjaan aktual |
| completed_at | TIMESTAMP | YES | NULL | Waktu selesai |
| status | ENUM('pending','assigned','in_progress','checking','reported','invoice_sent','negotiating','approved','completed','cancelled') | NO | 'pending' | Status WO |
| priority | ENUM('1','2','3','4') | NO | '3' | Prioritas (1: Urgent, 2: Tinggi, 3: Normal, 4: Rendah) |
| estimated_cost | DECIMAL(15,2) | YES | NULL | Estimasi biaya |
| total_cost | DECIMAL(15,2) | YES | NULL | Biaya aktual total |
| notes | TEXT | YES | NULL | Catatan internal |
| parent_wo_id | BIGINT UNSIGNED | YES | NULL | FK → work_orders.id, jika WO ini lanjutan dari pengecekan |
| created_by | BIGINT UNSIGNED | NO | | FK → users.id, admin yang membuat |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |

**Index:** `UNIQUE(wo_number)`, `INDEX(status)`, `INDEX(customer_id)`, `INDEX(scheduled_date)`, `INDEX(parent_wo_id)`, `INDEX(job_order)`  
**Foreign Key:** `customer_id → customers(id)`, `service_category_id → service_categories(id)`, `created_by → users(id)`, `parent_wo_id → work_orders(id) ON DELETE SET NULL`

---

#### `work_order_items`

Detail item/jasa dalam work order (material, jasa, sparepart).

| Kolom | Tipe | Nullable | Default | Keterangan |
|-------|------|----------|---------|------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK |
| work_order_id | BIGINT UNSIGNED | NO | | FK → work_orders.id |
| description | VARCHAR(255) | NO | | Deskripsi item (jasa cuci AC, freon R32, dll) |
| quantity | INT | NO | 1 | Jumlah |
| unit | VARCHAR(50) | YES | NULL | Satuan (unit, meter, set, dll) |
| unit_price | DECIMAL(15,2) | NO | 0 | Harga satuan |
| total_price | DECIMAL(15,2) | NO | 0 | quantity × unit_price |
| notes | TEXT | YES | NULL | |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |

**Foreign Key:** `work_order_id → work_orders(id) ON DELETE CASCADE`

---

#### `work_order_assignments`

Penugasan teknisi ke work order. Satu WO bisa punya banyak teknisi.

| Kolom | Tipe | Nullable | Default | Keterangan |
|-------|------|----------|---------|------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK |
| work_order_id | BIGINT UNSIGNED | NO | | FK → work_orders.id |
| technician_id | BIGINT UNSIGNED | NO | | FK → users.id (role=teknisi) |
| assigned_by | BIGINT UNSIGNED | NO | | FK → users.id (kepala teknisi yang assign) |
| status | ENUM('pending','accepted','rejected','completed') | NO | 'pending' | Status penugasan |
| assigned_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | Waktu ditugaskan |
| accepted_at | TIMESTAMP | YES | NULL | Waktu teknisi menerima |
| completed_at | TIMESTAMP | YES | NULL | Waktu teknisi menyelesaikan |
| notes | TEXT | YES | NULL | Catatan dari teknisi (misal alasan reject) |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |

**Index:** `INDEX(work_order_id, technician_id)`, `INDEX(technician_id)`  
**Foreign Key:** `work_order_id → work_orders(id) ON DELETE CASCADE`, `technician_id → users(id)`, `assigned_by → users(id)`

---

#### `work_order_reports`

Laporan dari teknisi setelah selesai mengerjakan.

| Kolom | Tipe | Nullable | Default | Keterangan |
|-------|------|----------|---------|------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK |
| work_order_id | BIGINT UNSIGNED | NO | | FK → work_orders.id |
| technician_id | BIGINT UNSIGNED | NO | | FK → users.id |
| findings | TEXT | NO | | Temuan/kondisi unit |
| work_done | TEXT | NO | | Pekerjaan yang dilakukan |
| recommendations | TEXT | YES | NULL | Rekomendasi untuk customer |
| materials_used | TEXT | YES | NULL | Material/sparepart yang dipakai |
| submitted_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | Waktu submit laporan |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |

**Foreign Key:** `work_order_id → work_orders(id) ON DELETE CASCADE`, `technician_id → users(id)`

---

#### `work_order_report_photos`

Foto dokumentasi yang dilampirkan pada laporan.

| Kolom | Tipe | Nullable | Default | Keterangan |
|-------|------|----------|---------|------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK |
| report_id | BIGINT UNSIGNED | NO | | FK → work_order_reports.id |
| photo_path | VARCHAR(255) | NO | | Path file foto di storage |
| photo_type | ENUM('before','progress','after') | NO | 'after' | Tipe foto |
| caption | VARCHAR(255) | YES | NULL | Keterangan foto |
| file_size | INT UNSIGNED | YES | NULL | Ukuran file dalam bytes |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |

**Foreign Key:** `report_id → work_order_reports(id) ON DELETE CASCADE`

---

#### `invoices`

Invoice yang digenerate dari work order.

| Kolom | Tipe | Nullable | Default | Keterangan |
|-------|------|----------|---------|------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK |
| invoice_number | VARCHAR(50) | NO | | Nomor invoice unik, format: INV-YYYYMMDD-XXXX |
| work_order_id | BIGINT UNSIGNED | NO | | FK → work_orders.id |
| customer_id | BIGINT UNSIGNED | NO | | FK → customers.id |
| subtotal | DECIMAL(15,2) | NO | 0 | Total sebelum pajak/diskon |
| discount | DECIMAL(15,2) | NO | 0 | Potongan harga |
| tax_percentage | DECIMAL(5,2) | NO | 0 | Persentase pajak (misal 11 untuk PPN 11%) |
| tax_amount | DECIMAL(15,2) | NO | 0 | Nominal pajak |
| total | DECIMAL(15,2) | NO | 0 | Grand total (subtotal - discount + tax) |
| status | ENUM('draft','sent','paid','cancelled') | NO | 'draft' | Status invoice |
| payment_status | ENUM('unpaid','partial','paid') | NO | 'unpaid' | Status pembayaran |
| paid_amount | DECIMAL(15,2) | NO | 0 | Jumlah yang sudah dibayar |
| payment_date | DATE | YES | NULL | Tanggal pembayaran (lunas) |
| payment_method | VARCHAR(100) | YES | NULL | Metode pembayaran (transfer, cash, dll) |
| due_date | DATE | YES | NULL | Jatuh tempo |
| notes | TEXT | YES | NULL | Catatan di invoice |
| issued_by | BIGINT UNSIGNED | NO | | FK → users.id |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |

**Index:** `UNIQUE(invoice_number)`, `INDEX(work_order_id)`, `INDEX(customer_id)`, `INDEX(status)`  
**Foreign Key:** `work_order_id → work_orders(id)`, `customer_id → customers(id)`, `issued_by → users(id)`

---

#### `invoice_items`

Detail baris item pada invoice.

| Kolom | Tipe | Nullable | Default | Keterangan |
|-------|------|----------|---------|------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK |
| invoice_id | BIGINT UNSIGNED | NO | | FK → invoices.id |
| description | VARCHAR(255) | NO | | Deskripsi item |
| quantity | INT | NO | 1 | |
| unit | VARCHAR(50) | YES | NULL | Satuan |
| unit_price | DECIMAL(15,2) | NO | 0 | Harga satuan |
| total_price | DECIMAL(15,2) | NO | 0 | quantity × unit_price |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |

**Foreign Key:** `invoice_id → invoices(id) ON DELETE CASCADE`

---

#### `rabs`

Rencana Anggaran Biaya untuk pekerjaan instalasi.

| Kolom | Tipe | Nullable | Default | Keterangan |
|-------|------|----------|---------|------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK |
| rab_number | VARCHAR(50) | NO | | Nomor RAB unik, format: RAB-YYYYMMDD-XXXX |
| work_order_id | BIGINT UNSIGNED | NO | | FK → work_orders.id |
| customer_id | BIGINT UNSIGNED | NO | | FK → customers.id |
| title | VARCHAR(255) | NO | | Judul RAB |
| description | TEXT | YES | NULL | Deskripsi/scope pekerjaan |
| subtotal | DECIMAL(15,2) | NO | 0 | |
| discount | DECIMAL(15,2) | NO | 0 | |
| tax_percentage | DECIMAL(5,2) | NO | 0 | |
| tax_amount | DECIMAL(15,2) | NO | 0 | |
| total | DECIMAL(15,2) | NO | 0 | |
| status | ENUM('draft','sent','approved','rejected','revised') | NO | 'draft' | |
| valid_until | DATE | YES | NULL | Masa berlaku RAB |
| notes | TEXT | YES | NULL | |
| created_by | BIGINT UNSIGNED | NO | | FK → users.id |
| approved_at | TIMESTAMP | YES | NULL | Waktu customer approve |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |

**Index:** `UNIQUE(rab_number)`, `INDEX(work_order_id)`, `INDEX(status)`  
**Foreign Key:** `work_order_id → work_orders(id)`, `customer_id → customers(id)`, `created_by → users(id)`

---

#### `rab_items`

Detail item dalam RAB.

| Kolom | Tipe | Nullable | Default | Keterangan |
|-------|------|----------|---------|------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK |
| rab_id | BIGINT UNSIGNED | NO | | FK → rabs.id |
| category | VARCHAR(100) | YES | NULL | Kategori item (Material, Jasa, Transport, dll) |
| description | VARCHAR(255) | NO | | Deskripsi item |
| quantity | INT | NO | 1 | |
| unit | VARCHAR(50) | YES | NULL | Satuan |
| unit_price | DECIMAL(15,2) | NO | 0 | |
| total_price | DECIMAL(15,2) | NO | 0 | |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |

**Foreign Key:** `rab_id → rabs(id) ON DELETE CASCADE`

---

#### `financial_categories`

Kategori untuk transaksi keuangan.

| Kolom | Tipe | Nullable | Default | Keterangan |
|-------|------|----------|---------|------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK |
| name | VARCHAR(255) | NO | | Nama kategori |
| type | ENUM('income','expense') | NO | | Berlaku untuk pemasukan/pengeluaran |
| description | TEXT | YES | NULL | |
| is_active | BOOLEAN | NO | TRUE | |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |

**Seed data income:** Pembayaran Jasa, Pembayaran Material  
**Seed data expense:** Pembelian Material, Transport, Gaji, Operasional Kantor, Lain-lain

---

#### `financial_transactions`

Catatan pemasukan dan pengeluaran.

| Kolom | Tipe | Nullable | Default | Keterangan |
|-------|------|----------|---------|------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK |
| type | ENUM('income','expense') | NO | | Pemasukan atau pengeluaran |
| category_id | BIGINT UNSIGNED | YES | NULL | FK → financial_categories.id |
| invoice_id | BIGINT UNSIGNED | YES | NULL | FK → invoices.id (jika income dari invoice) |
| expense_id | BIGINT UNSIGNED | YES | NULL | FK → expenses.id (jika expense) |
| amount | DECIMAL(15,2) | NO | | Nominal |
| transaction_date | DATE | NO | | Tanggal transaksi |
| description | TEXT | YES | NULL | Keterangan |
| reference_number | VARCHAR(100) | YES | NULL | Nomor referensi (nomor transfer, dll) |
| recorded_by | BIGINT UNSIGNED | NO | | FK → users.id |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |

**Index:** `INDEX(type)`, `INDEX(transaction_date)`, `INDEX(invoice_id)`, `INDEX(expense_id)`  
**Foreign Key:** `category_id → financial_categories(id)`, `invoice_id → invoices(id) ON DELETE SET NULL`, `expense_id → expenses(id) ON DELETE SET NULL`, `recorded_by → users(id)`

---

#### `expenses`

Pengeluaran operasional.

| Kolom | Tipe | Nullable | Default | Keterangan |
|-------|------|----------|---------|------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK |
| category_id | BIGINT UNSIGNED | YES | NULL | FK → financial_categories.id |
| work_order_id | BIGINT UNSIGNED | YES | NULL | FK → work_orders.id (jika terkait WO tertentu) |
| description | VARCHAR(255) | NO | | Keterangan pengeluaran |
| amount | DECIMAL(15,2) | NO | | Nominal |
| expense_date | DATE | NO | | Tanggal pengeluaran |
| receipt_photo | VARCHAR(255) | YES | NULL | Path foto struk/nota |
| notes | TEXT | YES | NULL | |
| recorded_by | BIGINT UNSIGNED | NO | | FK → users.id |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |

**Foreign Key:** `category_id → financial_categories(id)`, `work_order_id → work_orders(id) ON DELETE SET NULL`, `recorded_by → users(id)`

---

#### `notifications`

Notifikasi in-app.

| Kolom | Tipe | Nullable | Default | Keterangan |
|-------|------|----------|---------|------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK (UUID juga bisa) |
| user_id | BIGINT UNSIGNED | NO | | FK → users.id (penerima) |
| type | ENUM('work_order_new','work_order_assigned','work_order_updated','report_submitted','invoice_created','payment_received') | NO | | Tipe notifikasi |
| title | VARCHAR(255) | NO | | Judul notifikasi |
| body | TEXT | NO | | Isi notifikasi |
| data | JSON | YES | NULL | Payload tambahan (work_order_id, dll) |
| is_read | BOOLEAN | NO | FALSE | Sudah dibaca atau belum |
| read_at | TIMESTAMP | YES | NULL | Waktu dibaca |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |

**Index:** `INDEX(user_id, is_read)`, `INDEX(created_at)`  
**Foreign Key:** `user_id → users(id) ON DELETE CASCADE`

---

#### `settings`

Konfigurasi aplikasi (key-value store).

| Kolom | Tipe | Nullable | Default | Keterangan |
|-------|------|----------|---------|------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK |
| key | VARCHAR(255) | NO | | Key unik |
| value | TEXT | YES | NULL | Value (bisa berupa string, JSON, dll) |
| group | VARCHAR(100) | YES | 'general' | Grup setting (general, invoice, company, dll) |
| description | VARCHAR(255) | YES | NULL | Penjelasan setting |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |

**Index:** `UNIQUE(key)`

**Seed data:**

| Key | Value | Group | Deskripsi |
|-----|-------|-------|-----------|
| company_name | PT. Azza Karunia Jaya | company | Nama perusahaan |
| company_address | ... | company | Alamat |
| company_phone | ... | company | No. Telp |
| company_wa | ... | company | No. WhatsApp |
| company_email | ... | company | Email |
| company_logo | ... | company | Path logo |
| invoice_prefix | INV | invoice | Prefix nomor invoice |
| wo_prefix | WO | invoice | Prefix nomor work order |
| rab_prefix | RAB | invoice | Prefix nomor RAB |
| invoice_footer | ... | invoice | Footer text di PDF invoice |
| tax_default | 0 | invoice | Default pajak (%) |
| max_photo_size | 5242880 | upload | Max ukuran foto (bytes, 5MB) |
| max_photos_per_report | 10 | upload | Max jumlah foto per laporan |

---

#### `user_devices`

Informasi perangkat mobile yang digunakan staff.

| Kolom | Tipe | Nullable | Default | Keterangan |
|-------|------|----------|---------|------------|
| id | CHAR(36) / UUID | NO | | PK |
| device_id | VARCHAR(255) | NO | | Unique identifier device, indexed |
| platform | ENUM('android', 'ios') | NO | | OS Platform |
| app_version | VARCHAR(255) | NO | | Versi app mobile |
| build_number | INT UNSIGNED | NO | | Nomor build |
| os_version | VARCHAR(255) | YES | NULL | Versi OS device |
| device_brand | VARCHAR(255) | YES | NULL | Brand device (Samsung, Apple, dll) |
| device_model | VARCHAR(255) | YES | NULL | Model device |
| screen_resolution | VARCHAR(255) | YES | NULL | Resolusi layar |
| network_type | VARCHAR(255) | YES | NULL | Jenis jaringan (Wi-Fi, 4G, dll) |
| session_id | VARCHAR(255) | YES | NULL | ID Sesi login aktif |
| user_id | BIGINT UNSIGNED | YES | NULL | FK → users.id, indexed |
| created_at | TIMESTAMP | NO | | |
| updated_at | TIMESTAMP | NO | | |

**Index:** `INDEX(device_id)`, `INDEX(user_id)`  
**Foreign Key:** `user_id → users(id) ON DELETE SET NULL`

---

#### `app_versions`

Informasi rilis versi aplikasi Android staff.

| Kolom | Tipe | Nullable | Default | Keterangan |
|-------|------|----------|---------|------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK |
| version_code | INT UNSIGNED | NO | | Nomor versi unik build (misal 1, 2) |
| version_name | VARCHAR(255) | NO | | Nama rilis versi (misal 1.0.0) |
| release_notes | TEXT | YES | NULL | Catatan rilis pembaruan |
| apk_url | VARCHAR(255) | NO | | Link download file APK (Google Drive, dll) |
| created_at | TIMESTAMP | NO | | |
| updated_at | TIMESTAMP | NO | | |

**Index:** `UNIQUE(version_code)`

---

---

### 3.3 Entity Relationship Diagram (Tekstual)

```
users (1) ──────────── (N) work_order_assignments
users (1) ──────────── (N) work_order_reports
users (1) ──────────── (N) notifications
users (1) ──────────── (N) user_devices

customers (1) ──────── (N) work_orders
customers (1) ──────── (N) invoices
customers (1) ──────── (N) rabs

service_categories (1)─(N) work_orders

work_orders (1) ────── (N) work_order_items
work_orders (1) ────── (N) work_order_assignments
work_orders (1) ────── (N) work_order_reports
work_orders (1) ────── (1) invoices
work_orders (1) ────── (1) rabs
work_orders (1) ────── (0..1) work_orders [self-ref: parent_wo_id]
work_orders (1) ────── (N) expenses

work_order_reports (1)─(N) work_order_report_photos

invoices (1) ──────── (N) invoice_items
invoices (1) ──────── (N) financial_transactions

rabs (1) ──────────── (N) rab_items

financial_categories(1)(N) financial_transactions
financial_categories(1)(N) expenses
```

---

## 4. Alur Kerja (Business Flow)

### 4.1 Flow 1: Pengecekan AC

```
┌─────────────────────────────────────────────────────────────────────┐
│                     FLOW PENGECEKAN AC                              │
└─────────────────────────────────────────────────────────────────────┘

Customer ──WA──▶ Admin/CS
                    │
                    ▼
         ┌──────────────────────┐
         │ 1. Buat Work Order   │
         │    type = 'checking' │
         │    status = 'pending'│
         └──────────┬───────────┘
                    │
                    ▼ (notifikasi ke Kepala Teknisi)
         ┌──────────────────────┐
         │ 2. Kepala Teknisi    │
         │    assign teknisi    │
         │    status='assigned' │
         └──────────┬───────────┘
                    │
                    ▼ (push notif ke Teknisi via FCM)
         ┌──────────────────────┐
         │ 3. Teknisi menerima  │
         │    assignment_status │
         │    = 'accepted'      │
         └──────────┬───────────┘
                    │
                    ▼
         ┌──────────────────────┐
         │ 4. Teknisi berangkat │
         │    status =          │
         │    'in_progress'     │
         └──────────┬───────────┘
                    │
                    ▼
         ┌──────────────────────┐
         │ 5. Teknisi cek unit  │
         │    status='checking' │
         └──────────┬───────────┘
                    │
                    ▼
         ┌──────────────────────────────┐
         │ 6. Teknisi submit laporan    │
         │    - findings (temuan)       │
         │    - work_done               │
         │    - recommendations         │
         │    - upload foto (before/    │
         │      progress/after)         │
         │    status = 'reported'       │
         └──────────┬───────────────────┘
                    │
                    ▼ (notifikasi ke Kepala Teknisi & Admin)
         ┌──────────────────────────────┐
         │ 7. Admin review laporan      │
         │    Generate invoice:         │
         │    - Jasa pengecekan         │
         │    - Lampiran laporan        │
         │    Kirim PDF ke customer     │
         │    status = 'invoice_sent'   │
         └──────────┬───────────────────┘
                    │
              ┌─────┴─────┐
              ▼           ▼
     ┌──────────────┐ ┌─────────────────────────┐
     │ 9a. Customer │ │ 9b. Customer SETUJU     │
     │ TIDAK SETUJU │ │     harga pengerjaan    │
     │              │ │                         │
     │ Bayar jasa   │ │ Invoice cek → Rp 0     │
     │ cek saja     │ │ (free, karena dilanjut) │
     │              │ │                         │
     │ Invoice paid │ │ Buat Work Order BARU    │
     │ WO completed │ │ type='service' atau     │
     │              │ │ 'installation'          │
     │   SELESAI    │ │ parent_wo_id = WO ini   │
     └──────────────┘ │                         │
                      │ → kembali ke step 2     │
                      └─────────────────────────┘
```

**Detail Step-by-Step:**

1. **Customer menghubungi CS via WhatsApp.** CS mencatat keluhan, nama, alamat, jenis AC, dan jadwal yang diinginkan.

2. **Admin membuat Work Order baru di web app:**
   - Pilih/buat data customer
   - Tipe: `checking`
   - Isi deskripsi keluhan, lokasi, tanggal rencana
   - Status otomatis: `pending`
   - Sistem mengirim notifikasi ke Kepala Teknisi (FCM + in-app)

3. **Kepala Teknisi membuka app mobile/web**, melihat WO baru di daftar, lalu:
   - Pilih teknisi yang tersedia (bisa 1 atau lebih)
   - Klik "Assign"
   - Status WO berubah: `assigned`
   - Sistem mengirim push notification ke teknisi yang ditugaskan

4. **Teknisi menerima notifikasi di app Flutter:**
   - Lihat detail pekerjaan (alamat, deskripsi, customer)
   - Klik "Terima" → assignment_status = `accepted`
   - Saat berangkat, update status WO → `in_progress`

5. **Teknisi tiba di lokasi**, melakukan pengecekan unit AC. Status WO → `checking`.

6. **Teknisi submit laporan via app mobile:**
   - Isi temuan (findings): "Kompresor lemah, freon habis, PCB rusak"
   - Isi pekerjaan yang dilakukan (work_done): "Pengecekan unit indoor & outdoor"
   - Isi rekomendasi (recommendations): "Perlu ganti PCB dan isi freon R32"
   - Upload foto: before (kondisi awal), after (jika ada)
   - Status WO berubah → `reported`
   - Notifikasi dikirim ke Kepala Teknisi & Admin

7. **Admin melihat laporan masuk**, review temuan dan rekomendasi dari teknisi.

8. **Admin generate invoice:**
   - Isi item: Jasa Pengecekan AC — Rp XXX.XXX
   - Lampirkan laporan teknisi
   - Download PDF, kirim ke customer via WhatsApp
   - Status WO → `invoice_sent`

9. **Customer merespon:**
   - **9a. Tidak setuju harga pengerjaan:** Customer hanya bayar biaya pengecekan. Admin tandai invoice sebagai `paid`, WO status → `completed`.
   - **9b. Setuju harga pengerjaan:**
     - Admin ubah invoice pengecekan → total Rp 0 (gratis karena dilanjutkan ke pengerjaan)
     - Admin buat Work Order baru dengan tipe `service` atau `installation`
     - Set `parent_wo_id` = ID work order pengecekan
     - Alur kembali ke step 2 (Kepala Teknisi assign teknisi)

---

### 4.2 Flow 2: Instalasi AC (dengan RAB)

```
┌─────────────────────────────────────────────────────────────────────┐
│                    FLOW INSTALASI AC (RAB)                          │
└─────────────────────────────────────────────────────────────────────┘

Customer ──WA──▶ Admin/CS
                    │
                    ▼
         ┌──────────────────────────┐
         │ 1. Buat Work Order       │
         │    type = 'installation' │
         │    status = 'pending'    │
         └──────────┬───────────────┘
                    │
                    ▼
         ┌──────────────────────────┐
         │ 2. Admin buat RAB       │
         │    - Material (AC unit,  │
         │      pipa, bracket, dll) │
         │    - Jasa instalasi      │
         │    - Transport           │
         │    status = 'draft'      │
         └──────────┬───────────────┘
                    │
                    ▼
         ┌──────────────────────────┐
         │ 3. Generate PDF RAB     │
         │    Kirim ke customer    │
         │    rab_status = 'sent'  │
         │    wo_status =          │
         │    'negotiating'        │
         └──────────┬───────────────┘
                    │
              ┌─────┴─────┐
              ▼           ▼
     ┌──────────────┐ ┌──────────────────────┐
     │ 4a. Customer │ │ 4b. Customer SETUJU  │
     │ TIDAK SETUJU │ │     rab_status =     │
     │              │ │     'approved'       │
     │ Negosiasi?   │ │                      │
     │ Y: revisi    │ │ → Lanjut assign      │
     │    RAB, ulang │ │   teknisi (Flow 1    │
     │    step 3    │ │   step 2-9)          │
     │ N: cancel WO │ └──────────────────────┘
     │   wo_status= │
     │  'cancelled' │
     └──────────────┘
```

**Detail Step-by-Step:**

1. **Customer menghubungi CS** untuk minta instalasi AC baru. Admin buat Work Order tipe `installation`.

2. **Admin membuat RAB (Rencana Anggaran Biaya):**
   - Isi detail item per kategori:
     - **Material:** Unit AC 1PK Daikin (1 unit × Rp 5.500.000), Pipa tembaga (5m × Rp 150.000), Bracket outdoor (1 set × Rp 250.000), Kabel power (10m × Rp 50.000)
     - **Jasa:** Instalasi AC split (1 unit × Rp 500.000)
     - **Transport:** Biaya pengiriman (1 × Rp 100.000)
   - Hitung subtotal, tambahkan pajak jika perlu
   - Status RAB: `draft`

3. **Admin generate PDF RAB**, kirim ke customer via WhatsApp. Status RAB → `sent`, status WO → `negotiating`.

4. **Customer merespon:**
   - **4a. Tidak setuju:** Bisa negosiasi (Admin revisi RAB, ulang step 3) atau cancel (WO → `cancelled`).
   - **4b. Setuju:** RAB status → `approved`, WO status → `approved`. Selanjutnya ikut alur Flow 1 mulai dari step 2 (Kepala Teknisi assign teknisi, teknisi kerjakan, submit laporan, generate invoice final).

---

### 4.3 Flow 3: Keuangan

```
┌─────────────────────────────────────────────────────────────────────┐
│                       FLOW KEUANGAN                                 │
└─────────────────────────────────────────────────────────────────────┘

  PEMASUKAN (Otomatis)                PENGELUARAN (Manual)
  ─────────────────────               ──────────────────────
                                      
  Invoice status → 'paid'            Admin input pengeluaran:
         │                            - Beli material/sparepart
         ▼                            - Transport
  Sistem otomatis buat               - Biaya operasional
  financial_transaction:              - dll
  - type = 'income'                          │
  - amount = invoice.paid_amount             ▼
  - invoice_id = invoice.id           Sistem buat
  - transaction_date =                financial_transaction:
    invoice.payment_date              - type = 'expense'
         │                            - amount = expense.amount
         │                            - expense_id = expense.id
         ▼                                   │
  ┌──────────────────────────────────────────┘
  │
  ▼
  LAPORAN KEUANGAN
  ─────────────────
  Filter by: tanggal (harian/mingguan/bulanan)
  
  ┌────────────────────────────────────────────┐
  │ Total Pemasukan    : Rp XX.XXX.XXX         │
  │ Total Pengeluaran  : Rp XX.XXX.XXX         │
  │ ──────────────────────────────────         │
  │ Neraca Saldo       : Rp XX.XXX.XXX         │
  │                                            │
  │ Cost Percentage    : XX.XX%                │
  │ (total biaya / total pendapatan × 100%)    │
  └────────────────────────────────────────────┘
```

**Detail:**

1. **Pemasukan otomatis:** Setiap kali Admin menandai invoice sebagai `paid` (atau `partial`), sistem otomatis membuat record di `financial_transactions` dengan tipe `income`. Amount diambil dari jumlah yang dibayar. Jika pembayaran bertahap, setiap pembayaran menghasilkan 1 record transaksi.

2. **Pengeluaran manual:** Admin menginput pengeluaran melalui modul Keuangan:
   - Pilih kategori (Material, Transport, Operasional, dll)
   - Isi nominal, tanggal, deskripsi
   - Opsional: link ke Work Order tertentu (untuk tracking cost per job)
   - Opsional: upload foto struk/nota
   - Sistem otomatis membuat record di `financial_transactions` dengan tipe `expense`

3. **Neraca Saldo:**
   ```
   Neraca Saldo = SUM(income transactions) - SUM(expense transactions)
   ```
   dalam periode yang dipilih.

4. **Cost Percentage (per periode):**
   ```
   Cost % = (Total Pengeluaran / Total Pemasukan) × 100%
   ```
   Contoh: Bulan ini pemasukan Rp 50.000.000, pengeluaran Rp 20.000.000 → Cost % = 40%.

5. **Cost Percentage per Work Order (opsional):**
   ```
   Cost % per WO = (SUM expenses linked to WO / invoice total WO) × 100%
   ```

6. **Laporan keuangan per periode:** Admin bisa filter laporan berdasarkan:
   - Hari ini
   - Minggu ini
   - Bulan ini
   - Custom range (dari tanggal – sampai tanggal)

---

## 5. API Endpoints

Base URL: `/api/v1`  
Auth: Bearer Token (Laravel Sanctum)  
Format: JSON  
Header: `Accept: application/json`, `Authorization: Bearer {token}`

### 5.1 Authentication

```
POST /api/v1/auth/login
  Request:  { email: string, password: string, fcm_token?: string }
  Response: { token: string, user: User }

POST /api/v1/auth/logout
  Request:  -
  Response: { message: string }

POST /api/v1/auth/refresh
  Request:  -
  Response: { token: string }

GET /api/v1/auth/me
  Response: { user: User }
```

### 5.2 Profile

```
GET /api/v1/profile
  Response: { user: User }

PUT /api/v1/profile
  Request:  { name?: string, phone?: string, avatar?: file }
  Response: { user: User }

PUT /api/v1/profile/password
  Request:  { current_password: string, password: string, password_confirmation: string }
  Response: { message: string }

PUT /api/v1/profile/fcm-token
  Request:  { fcm_token: string }
  Response: { message: string }

POST /api/v1/devices
  Request:  { device_id: string, platform: 'android'|'ios', app_version: string, build_number: int, os_version?: string, device_brand?: string, device_model?: string, screen_resolution?: string, network_type?: string, session_id?: string }
  Response: { success: boolean, message: string, data: UserDevice }

GET /api/app-version/latest
  Request:  - (Public)
  Response: { version_code: int, version_name: string, release_notes: string|null, download_url: string }
```

### 5.3 Work Orders

```
GET /api/v1/work-orders
  Query:    { status?: string, date?: string, page?: int, per_page?: int }
  Response: { data: WorkOrder[], meta: Pagination }
  Note:     Teknisi → hanya WO yang di-assign ke dia
            Kepala Teknisi → semua WO yang status >= 'pending'

GET /api/v1/work-orders/{id}
  Response: { data: WorkOrder (with customer, items, assignments, reports) }

GET /api/v1/work-orders/today
  Query:    { page?: int, per_page?: int }
  Response: { data: WorkOrder[], meta: Pagination }
  Note:     WO hari ini yang di-assign ke user login

GET /api/v1/work-orders/{id}/timeline
  Response: { data: StatusChange[] }
  Note:     Riwayat perubahan status WO

PUT /api/v1/work-orders/{id}/status
  Request:  { status: string, notes?: string }
  Response: { data: WorkOrder }
  Note:     Teknisi update status: in_progress, checking
```

### 5.4 Assignments

```
GET /api/v1/assignments
  Query:    { status?: string, date?: string, page?: int, per_page?: int }
  Response: { data: Assignment[], meta: Pagination }
  Note:     Teknisi → assignment dia; Kepala Teknisi → semua

GET /api/v1/assignments/{id}
  Response: { data: Assignment (with work_order, technician) }

POST /api/v1/work-orders/{workOrderId}/assign
  Request:  { technician_ids: int[], notes?: string }
  Response: { data: Assignment[] }
  Note:     Khusus Kepala Teknisi

PUT /api/v1/assignments/{id}/accept
  Request:  { notes?: string }
  Response: { data: Assignment }
  Note:     Teknisi menerima assignment

PUT /api/v1/assignments/{id}/reject
  Request:  { notes: string }
  Response: { data: Assignment }
  Note:     Teknisi menolak assignment (wajib isi alasan)

PUT /api/v1/assignments/{id}/complete
  Request:  -
  Response: { data: Assignment }
  Note:     Tandai assignment selesai

POST /api/v1/work-orders/{workOrderId}/takeover
  Request:  { notes?: string }
  Response: { data: Takeover }
  Note:     Teknisi meminta pengambilalihan WO dari teknisi lain

POST /api/v1/takeovers/{takeoverId}/approve
  Request:  -
  Response: { data: Takeover }
  Note:     Persetujuan pengambilalihan (oleh teknisi awal atau manager)

POST /api/v1/takeovers/{takeoverId}/reject
  Request:  -
  Response: { data: Takeover }
  Note:     Penolakan pengambilalihan (oleh teknisi awal atau manager)

GET /api/v1/technicians/available
  Query:    { date?: string }
  Response: { data: User[] (with assignment_count) }
  Note:     Khusus Kepala Teknisi, lihat ketersediaan teknisi
```

### 5.5 Reports

```
POST /api/v1/work-orders/{workOrderId}/reports
  Request:  {
    findings: string,
    work_done: string,
    recommendations?: string,
    materials_used?: string,
    photos: [
      { file: binary, type: 'before'|'progress'|'after', caption?: string }
    ]
  }
  Content-Type: multipart/form-data
  Response: { data: Report }
  Note:     Khusus Teknisi, submit laporan + foto

GET /api/v1/work-orders/{workOrderId}/reports
  Response: { data: Report[] (with photos) }

GET /api/v1/reports/{id}
  Response: { data: Report (with photos, work_order, technician) }

GET /api/v1/reports/my
  Query:    { page?: int, per_page?: int }
  Response: { data: Report[], meta: Pagination }
  Note:     Riwayat laporan dari teknisi yang login
```

### 5.6 Notifications

```
GET /api/v1/notifications
  Query:    { is_read?: boolean, page?: int, per_page?: int }
  Response: { data: Notification[], meta: Pagination, unread_count: int }

PUT /api/v1/notifications/{id}/read
  Response: { data: Notification }

PUT /api/v1/notifications/read-all
  Response: { message: string, updated_count: int }

GET /api/v1/notifications/unread-count
  Response: { count: int }
```

### 5.7 Dashboard (Mobile)

```
GET /api/v1/dashboard
  Response: {
    today_assignments: int,
    pending_assignments: int,
    completed_today: int,
    total_completed: int,
    recent_work_orders: WorkOrder[]
  }
  Note:     Data disesuaikan per role
            Teknisi → data dia sendiri
            Kepala Teknisi → data semua teknisi
```

### 5.8 Data Types Reference

```typescript
// User
User {
  id: int
  name: string
  email: string
  phone: string | null
  role: 'super_admin' | 'admin' | 'kepala_teknisi' | 'teknisi'
  is_active: boolean
  avatar: string | null
}

// WorkOrder
WorkOrder {
  id: int
  wo_number: string
  type: 'checking' | 'service' | 'installation' | 'maintenance'
  customer: Customer
  service_category: ServiceCategory
  title: string
  description: string | null
  location: string
  scheduled_date: string | null    // YYYY-MM-DD
  started_at: string | null        // ISO 8601
  completed_at: string | null
  status: string                   // enum work_order_status
  priority: '1' | '2' | '3' | '4'  // 1: Urgent, 2: Tinggi, 3: Normal, 4: Rendah
  scheduled_time: string | null    // HH:mm:ss
  job_order: number | null         // Urutan pengerjaan
  estimated_cost: number | null
  total_cost: number | null
  notes: string | null
  parent_wo_id: int | null
  items: WorkOrderItem[]
  assignments: Assignment[]
  reports: Report[]
  created_at: string
}

// Customer
Customer {
  id: int
  type: 'individual' | 'business'
  name: string
  company_name: string | null
  pic_name: string | null
  phone: string
  address: string | null
  city: string | null
  market: string | null
}

// Assignment
Assignment {
  id: int
  work_order: WorkOrder
  technician: User
  assigned_by: User
  status: 'pending' | 'accepted' | 'rejected' | 'completed'
  assigned_at: string
  accepted_at: string | null
  completed_at: string | null
  notes: string | null
}

// Report
Report {
  id: int
  work_order_id: int
  technician: User
  findings: string
  work_done: string
  recommendations: string | null
  materials_used: string | null
  photos: ReportPhoto[]
  submitted_at: string
}

// ReportPhoto
ReportPhoto {
  id: int
  photo_url: string
  photo_type: 'before' | 'progress' | 'after'
  caption: string | null
}

// Notification
Notification {
  id: int
  type: string
  title: string
  body: string
  data: object | null
  is_read: boolean
  read_at: string | null
  created_at: string
}

// Pagination
Pagination {
  current_page: int
  last_page: int
  per_page: int
  total: int
}
```

---

## 6. Fitur per Platform

### 6.1 Web (Laravel Blade + Bootstrap 5 + jQuery)

#### Dashboard
- Statistik hari ini: jumlah WO baru, in progress, completed, cancelled
- Grafik tren pekerjaan per minggu/bulan (Chart.js via Local)
- Daftar WO terbaru yang perlu action
- Ringkasan keuangan bulan ini (pemasukan, pengeluaran, saldo)
- Quick action buttons: buat WO baru, input pengeluaran

#### Master Data

**Staff (Users):**
- List semua staff dengan filter role & status
- Tambah staff baru (nama, email, phone, role, password)
- Edit data staff
- Aktivasi / nonaktifkan staff
- Reset password staff

**Customer:**
- List customer dengan search & filter (tipe, kota)
- Tambah customer (individual / business)
- Edit data customer
- Lihat riwayat pekerjaan customer
- Import dari spreadsheet (CSV) — *nice to have*

**Kategori Jasa:**
- CRUD kategori jasa
- Aktivasi / nonaktifkan

#### Work Order Management

**List Work Order:**
- Tabel dengan filter: status, tipe, tanggal, customer, teknisi
- Search by WO number, customer name
- Sorting by tanggal, status
- Pagination
- Badge warna per status

**Create Work Order:**
- Form: customer (autocomplete search), tipe WO, kategori jasa, judul, deskripsi, lokasi, tanggal rencana, prioritas
- Tambah item (detail jasa/material + harga)
- Tombol simpan → status `pending`

**Detail Work Order:**
- Info lengkap WO
- Timeline status (log setiap perubahan status dengan timestamp & user)
- Daftar item/jasa
- Daftar teknisi yang ditugaskan + status assignment
- Laporan teknisi + foto (gallery view)
- Link ke invoice & RAB terkait
- Action buttons: edit, assign (jika role kepala teknisi), generate invoice, generate RAB, cancel

**Edit Work Order:**
- Edit semua field yang masih relevan (tidak bisa edit jika sudah completed)

#### Invoice Generator

**List Invoice:**
- Tabel dengan filter: status, tanggal, customer
- Search by invoice number, customer

**Create Invoice (dari Work Order):**
- Auto-populate dari WO items
- Bisa tambah/edit/hapus item
- Isi diskon, pajak
- Preview sebelum simpan

**Invoice PDF:**
- Header: logo + nama + alamat + kontak perusahaan
- Info customer
- Nomor invoice, tanggal, jatuh tempo
- Tabel item (deskripsi, qty, unit, harga satuan, total)
- Subtotal, diskon, pajak, grand total
- Catatan/terms
- Footer: text konfigurabel dari settings

**Update Status Invoice:**
- Tandai sent (sudah kirim ke customer)
- Tandai paid (sudah dibayar) → input payment_date, payment_method, paid_amount
- Tandai partial → input jumlah yang dibayar
- Cancel

#### RAB Generator

**Create RAB (dari Work Order instalasi):**
- Auto-link ke WO
- Isi item per kategori (Material, Jasa, Transport, dll)
- Hitung subtotal per kategori, grand total
- Isi masa berlaku (valid_until)

**RAB PDF:**
- Serupa format invoice, tapi dengan judul "Rencana Anggaran Biaya"
- Detail per kategori item

**Update Status RAB:**
- Tandai sent → kirim ke customer
- Tandai approved → customer setuju
- Tandai rejected → customer tolak
- Revisi → buat revisi RAB

#### Laporan & Dokumentasi

**Viewer Laporan Teknisi:**
- Lihat laporan per WO (temuan, pekerjaan, rekomendasi, material)
- Gallery foto (lightbox, sebelum/sesudah side-by-side)
- Download foto

**Laporan & Ekspor Multi-Fitur (Excel & CSV):**
- Modul laporan terpadu dengan penyaringan tanggal untuk semua fitur utama di halaman admin:
  * **Work Order**: Rincian status, jenis pekerjaan, prioritas, teknisi, dan nominal item.
  * **Customer**: Data tipe customer (B2B/perorangan) dan rekap jumlah WO aktif per periode.
  * **Invoice**: Laporan status tagihan, status bayar (paid/partial/unpaid), nominal, dan metode pembayaran.
  * **RAB**: Laporan estimasi biaya penawaran instalasi beserta status persetujuan.
  * **Keuangan**: Gabungan alur kas pemasukan dan pengeluaran operasional.
  * **Staff**: Laporan performa kinerja berupa jumlah tugas WO dan laporan yang disubmit.
- Dukungan ekspor data secara dinamis ke format **Excel (.xlsx)** dan **CSV** menggunakan package `maatwebsite/excel`.

#### Modul Keuangan

**Pemasukan:**
- List semua pemasukan (otomatis dari invoice yang dibayar)
- Detail: invoice number, customer, tanggal, amount
- Filter by tanggal, customer

**Pengeluaran:**
- List semua pengeluaran
- Tambah pengeluaran: kategori, deskripsi, nominal, tanggal, link ke WO (opsional), foto struk
- Edit / hapus pengeluaran

**Laporan Keuangan:**
- Filter periode: hari ini, minggu ini, bulan ini, custom range
- Ringkasan: total pemasukan, total pengeluaran, neraca saldo, cost percentage
- Tabel detail transaksi (income + expense digabung, urut tanggal)
- Grafik pemasukan vs pengeluaran per hari/minggu/bulan
- *Cetak/export PDF — nice to have*

#### Manajemen User & Role
- Dikelola oleh Super Admin
- CRUD user, assign role
- Aktivasi/nonaktifkan user

#### Settings
- Informasi perusahaan (nama, alamat, telepon, WhatsApp, email, logo)
- Konfigurasi invoice (prefix nomor, footer text, default pajak)
- Konfigurasi upload (max ukuran foto, max jumlah per laporan)

---

### 6.2 Mobile (Flutter + GetX)

#### Login
- Form email + password
- Simpan token di secure storage
- Auto-login jika token masih valid
- Register FCM token setelah login

#### Dashboard
- Greeting + nama user
- Ringkasan hari ini:
  - Teknisi: jumlah pekerjaan hari ini, pending, selesai
  - Kepala Teknisi: jumlah WO baru, total teknisi di lapangan, WO selesai hari ini
- Daftar pekerjaan hari ini (quick access)

#### List Work Order
- Tab: Hari Ini, Semua (dengan filter status)
- Card per WO: nomor WO, customer, lokasi, status badge, tanggal
- Pull-to-refresh
- Infinite scroll / pagination

#### Detail Work Order
- Info lengkap: customer, lokasi, deskripsi, kategori, tanggal, prioritas
- Daftar item/jasa
- Status timeline
- Teknisi yang ditugaskan
- Laporan (jika sudah submit)
- Action buttons (sesuai role & status):
  - Teknisi: Accept Assignment, Update Status, Submit Laporan
  - Kepala Teknisi: Assign Teknisi

#### Assign Teknisi (Kepala Teknisi Only)
- List teknisi tersedia (nama, jumlah assignment hari ini)
- Multi-select teknisi
- Tombol "Assign"
- Konfirmasi dialog

#### Submit Laporan (Teknisi Only)
- Form:
  - Temuan (text area, required)
  - Pekerjaan yang dilakukan (text area, required)
  - Rekomendasi (text area, optional)
  - Material yang dipakai (text area, optional)
- Upload foto:
  - Pilih dari kamera atau galeri
  - Pilih tipe: before / progress / after
  - Tambah caption
  - Max 10 foto, max 5MB per foto
  - Preview thumbnail sebelum submit
- Tombol "Submit Laporan"
- Konfirmasi dialog

#### Riwayat Pekerjaan
- List semua WO yang pernah dikerjakan (completed)
- Filter by bulan/tahun
- Detail per WO + laporan + foto

#### Notifikasi
- List notifikasi (read/unread)
- Badge unread count di icon
- Tap notifikasi → navigasi ke detail WO terkait
- Mark as read (per item / semua)
- Push notification handling (foreground + background)

#### Profile
- Lihat dan edit nama, phone, avatar
- Ganti password
- Logout

---

## 7. Non-Functional Requirements

### 7.1 Performance
- Halaman web load < 3 detik (mengingat shared hosting)
- API response time < 1 detik untuk operasi standar
- PDF generation < 5 detik
- Upload foto < 10 detik per foto (tergantung koneksi)

### 7.2 Security
- **Web:** Laravel Breeze auth (session-based) + CSRF protection
- **API:** Laravel Sanctum token-based authentication
- **Password:** Bcrypt hashing (Laravel default)
- **File upload:** Validasi tipe file (jpeg, png, jpg), max size 5MB
- **Authorization:** Middleware role-based (SuperAdmin, Admin, KepalaTeknisi, Teknisi)
- **SQL Injection:** Eloquent ORM (parameterized queries)
- **XSS:** Blade template escaping ({{ }})
- **Rate limiting:** API rate limit 60 request/menit per user

### 7.3 Compatibility
- **Web:** Chrome, Firefox, Safari, Edge (latest 2 versions)
- **Mobile:** Android 6.0+, iOS 12.0+
- **Responsive:** Web harus responsive (Bootstrap 5 grid), tapi bukan PWA — mobile experience lewat app Flutter

### 7.4 Deployment
- **Shared hosting compatible:**
  - Tidak pakai Vite atau Node.js build step
  - Bootstrap 5 + jQuery via Local atau file statis di `public/`
  - Chart.js via Local
  - Laravel tanpa dependency yang butuh binary khusus
- **VPS alternative:** Jika perlu performance lebih baik
- **Database:** MySQL 8.0 (tersedia di hampir semua shared hosting)

### 7.5 PDF Generation
- Library: `barryvdh/laravel-dompdf` (pure PHP, compatible shared hosting)
- Template: Blade view yang di-render ke PDF
- Paper size: A4
- Encoding: UTF-8 (support karakter Indonesia)

### 7.6 Push Notification (FCM)
- Library: `laravel-notification-channels/fcm` atau `kreait/firebase-php`
- Trigger notifikasi:
  - WO baru dibuat → ke Kepala Teknisi
  - Teknisi di-assign → ke Teknisi
  - Laporan disubmit → ke Kepala Teknisi & Admin
  - Status WO berubah → ke user terkait
  - Invoice dibayar → ke Admin
- Payload notifikasi berisi data untuk navigasi ke halaman terkait di app

### 7.7 File Storage
- Simpan di `storage/app/public/` (Laravel default)
- Symlink `public/storage` → `storage/app/public`
- Struktur folder:
  ```
  storage/app/public/
  ├── avatars/          # Foto profil user
  ├── reports/          # Foto laporan teknisi
  │   └── {report_id}/
  ├── receipts/         # Foto struk pengeluaran
  └── company/          # Logo perusahaan
  ```
- Foto laporan di-compress server-side sebelum disimpan (Intervention Image)
- Cleanup: foto dari WO yang di-cancel bisa dihapus setelah 30 hari (cron job)

### 7.8 Backup
- Database: daily MySQL dump via cron
- Files: periodic backup folder storage

---

## 8. Referensi Spreadsheet Existing

Data yang saat ini dicatat di spreadsheet dan mapping ke database schema baru:

| Field Spreadsheet | Tabel & Kolom Database | Keterangan |
|-------------------|----------------------|------------|
| WORK DESCRIPTION | `service_categories.name` | Dipecah jadi kategori: AC Residential, AC Komersial, dll |
| DETAIL PEKERJAAN | `work_orders.description` + `work_order_items.description` | Deskripsi umum di WO, detail per item di WO items |
| TANGGAL PENGERJAAN | `work_orders.scheduled_date` | Tanggal rencana pengerjaan |
| CUSTOMER | `customers.name` | Nama customer perorangan |
| COMPANY | `customers.company_name` | Nama perusahaan (customer type = business) |
| PIC | `customers.pic_name` | Person in charge di perusahaan |
| MARKET | `customers.market` | Sumber/sales channel (WA, referral, marketplace, dll) |
| LOKASI | `work_orders.location` | Alamat lokasi pengerjaan |
| TEKNISI | `work_order_assignments` (multiple rows) | Satu WO bisa banyak teknisi. Kolom "teknisi1, teknisi2" di spreadsheet → dipecah jadi multiple assignment records |
| STATUS | `work_orders.status` | Dipetakan dari status spreadsheet ke enum status baru |
| INCOME | `invoices.total` → `financial_transactions.amount` | Nominal pemasukan dari invoice |
| TANGGAL PEMBAYARAN | `invoices.payment_date` → `financial_transactions.transaction_date` | Tanggal customer membayar |

### Mapping Status Spreadsheet → Database

| Status Spreadsheet | Status Database | Keterangan |
|--------------------|-----------------|----|
| Belum dikerjakan | `pending` / `assigned` | Tergantung sudah di-assign atau belum |
| Proses | `in_progress` | Sedang dikerjakan |
| Pengecekan | `checking` | Sedang dicek |
| Selesai (belum bayar) | `completed` + payment_status `unpaid` | Pekerjaan selesai, invoice belum dibayar |
| Selesai (lunas) | `completed` + payment_status `paid` | Pekerjaan selesai & sudah dibayar |
| Cancel | `cancelled` | Dibatalkan |

### Migrasi Data

Untuk migrasi data dari spreadsheet ke sistem baru:

1. **Export spreadsheet ke CSV**
2. **Buat artisan command** `php artisan import:spreadsheet {file.csv}`
3. **Proses per baris:**
   - Cek/buat customer berdasarkan nama + company
   - Cek/buat service_category berdasarkan WORK DESCRIPTION
   - Buat work_order
   - Cek/buat user teknisi berdasarkan nama
   - Buat work_order_assignments per teknisi
   - Jika ada INCOME & TANGGAL PEMBAYARAN: buat invoice + financial_transaction
4. **Validasi:** Bandingkan total data di spreadsheet vs database
5. **Log:** Catat setiap baris yang gagal import beserta alasannya

---

## Appendix A: Wireframe Screens (Deskripsi)

### Web - Dashboard Admin
```
┌──────────────────────────────────────────────────────────────┐
│  [Logo] AzzaOps                    [Admin Name] [Logout]     │
├──────────┬───────────────────────────────────────────────────┤
│          │  Dashboard                                        │
│ Dashboard│  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐│
│ Work     │  │ WO Baru │ │Progress │ │ Selesai │ │ Income  ││
│ Orders   │  │   12    │ │    5    │ │    8    │ │ 15.5jt  ││
│ Customers│  └─────────┘ └─────────┘ └─────────┘ └─────────┘│
│ Invoice  │                                                   │
│ RAB      │  ┌─────────────────────┐ ┌───────────────────┐   │
│ Keuangan │  │ Grafik Pekerjaan    │ │ WO Perlu Action   │   │
│ Staff    │  │ (Chart.js bar chart)│ │ ┌───────────────┐ │   │
│ Kategori │  │                     │ │ │WO-001 Pending │ │   │
│ Settings │  │                     │ │ │WO-005 Reported│ │   │
│          │  └─────────────────────┘ │ │WO-008 Pending │ │   │
│          │                          │ └───────────────┘ │   │
│          │                          └───────────────────┘   │
└──────────┴───────────────────────────────────────────────────┘
```

### Mobile - Dashboard Teknisi
```
┌─────────────────────────┐
│  Halo, Ahmad! 👋        │
│  Senin, 22 Juli 2026    │
├─────────────────────────┤
│  Pekerjaan Hari Ini     │
│  ┌───────┐ ┌───────┐   │
│  │Pending│ │Selesai│   │
│  │   3   │ │   2   │   │
│  └───────┘ └───────┘   │
├─────────────────────────┤
│  ┌─────────────────────┐│
│  │ WO-20260722-0001    ││
│  │ Cuci AC - Residential│
│  │ Jl. Merdeka No. 10  ││
│  │ [ASSIGNED]           ││
│  └─────────────────────┘│
│  ┌─────────────────────┐│
│  │ WO-20260722-0003    ││
│  │ Cek AC - Komersial  ││
│  │ Ruko Blok A No. 5   ││
│  │ [IN PROGRESS]        ││
│  └─────────────────────┘│
├─────────────────────────┤
│ [Home] [Orders] [Notif] │
│                [Profile] │
└─────────────────────────┘
```

---

## Appendix B: Project Structure

### Laravel

```
azzaops/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Web/               # Web controllers (Blade)
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── WorkOrderController.php
│   │   │   │   ├── CustomerController.php
│   │   │   │   ├── InvoiceController.php
│   │   │   │   ├── RabController.php
│   │   │   │   ├── FinanceController.php
│   │   │   │   ├── UserController.php
│   │   │   │   ├── ServiceCategoryController.php
│   │   │   │   └── SettingController.php
│   │   │   └── Api/               # API controllers (JSON)
│   │   │       ├── AuthController.php
│   │   │       ├── ProfileController.php
│   │   │       ├── WorkOrderController.php
│   │   │       ├── AssignmentController.php
│   │   │       ├── ReportController.php
│   │   │       ├── NotificationController.php
│   │   │       └── DashboardController.php
│   │   ├── Middleware/
│   │   │   └── RoleMiddleware.php
│   │   └── Requests/              # Form requests (validation)
│   ├── Models/
│   │   ├── User.php
│   │   ├── Customer.php
│   │   ├── ServiceCategory.php
│   │   ├── WorkOrder.php
│   │   ├── WorkOrderItem.php
│   │   ├── WorkOrderAssignment.php
│   │   ├── WorkOrderReport.php
│   │   ├── WorkOrderReportPhoto.php
│   │   ├── Invoice.php
│   │   ├── InvoiceItem.php
│   │   ├── Rab.php
│   │   ├── RabItem.php
│   │   ├── FinancialCategory.php
│   │   ├── FinancialTransaction.php
│   │   ├── Expense.php
│   │   ├── Notification.php
│   │   └── Setting.php
│   ├── Services/
│   │   ├── WorkOrderService.php
│   │   ├── InvoiceService.php
│   │   ├── RabService.php
│   │   ├── FinanceService.php
│   │   ├── NotificationService.php
│   │   └── PdfService.php
│   └── Observers/
│       ├── InvoiceObserver.php     # Auto-create financial_transaction on payment
│       └── WorkOrderObserver.php   # Auto-notify on status change
├── database/
│   ├── migrations/
│   └── seeders/
│       ├── RoleSeeder.php
│       ├── ServiceCategorySeeder.php
│       ├── FinancialCategorySeeder.php
│       └── SettingSeeder.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php      # Bootstrap 5 layout
│       ├── dashboard/
│       ├── work-orders/
│       ├── customers/
│       ├── invoices/
│       ├── rabs/
│       ├── finance/
│       ├── users/
│       ├── settings/
│       └── pdf/
│           ├── invoice.blade.php
│           └── rab.blade.php
├── public/
│   ├── css/
│   │   └── app.css                # Custom CSS (no build step)
│   ├── js/
│   │   └── app.js                 # Custom JS (no build step)
│   └── vendor/                    # Bootstrap, jQuery, Chart.js files
│       ├── bootstrap/
│       ├── jquery/
│       └── chartjs/
└── routes/
    ├── web.php
    └── api.php
```

### Flutter

```
azzaops_mobile/
├── lib/
│   ├── main.dart
│   ├── app/
│   │   ├── routes/
│   │   │   ├── app_pages.dart
│   │   │   └── app_routes.dart
│   │   ├── bindings/
│   │   ├── data/
│   │   │   ├── models/
│   │   │   │   ├── user_model.dart
│   │   │   │   ├── work_order_model.dart
│   │   │   │   ├── assignment_model.dart
│   │   │   │   ├── report_model.dart
│   │   │   │   └── notification_model.dart
│   │   │   ├── providers/
│   │   │   │   └── api_provider.dart      # HTTP client (GetConnect)
│   │   │   └── repositories/
│   │   │       ├── auth_repository.dart
│   │   │       ├── work_order_repository.dart
│   │   │       ├── assignment_repository.dart
│   │   │       ├── report_repository.dart
│   │   │       └── notification_repository.dart
│   │   └── modules/
│   │       ├── auth/
│   │       │   ├── bindings/
│   │       │   ├── controllers/
│   │       │   └── views/
│   │       ├── dashboard/
│   │       ├── work_order/
│   │       ├── assignment/
│   │       ├── report/
│   │       ├── notification/
│   │       └── profile/
│   ├── core/
│   │   ├── theme/
│   │   ├── utils/
│   │   │   ├── constants.dart
│   │   │   └── helpers.dart
│   │   └── widgets/                # Shared widgets
│   └── services/
│       ├── auth_service.dart       # Token management
│       ├── fcm_service.dart        # Firebase Cloud Messaging
│       └── storage_service.dart    # GetStorage / secure storage
└── pubspec.yaml
```

---

## Appendix C: Milestone & Prioritas Development

### Phase 1 — MVP (4-6 minggu)
- [ ] Setup project Laravel + Breeze + Bootstrap 5
- [ ] Database migration + seeders
- [ ] CRUD Staff (User management)
- [ ] CRUD Customer
- [ ] CRUD Kategori Jasa
- [ ] Work Order: create, list, detail, update status
- [ ] Assignment: assign teknisi (web)
- [ ] API: auth, work order, assignment
- [ ] Flutter: login, dashboard, list WO, detail WO, update status
- [ ] Notifikasi: FCM basic (assignment ke teknisi)

### Phase 2 — Core Features (3-4 minggu)
- [ ] Laporan teknisi: submit + upload foto (web + API + Flutter)
- [ ] Invoice generator + PDF
- [ ] RAB generator + PDF
- [ ] Flow pengecekan lengkap (termasuk invoice Rp 0 + WO lanjutan)
- [ ] Flow instalasi lengkap (RAB → approve → assign → report → invoice)
- [ ] Push notification lengkap (semua trigger)

### Phase 3 — Keuangan & Polish (2-3 minggu)
- [ ] Modul keuangan: pemasukan otomatis, pengeluaran manual
- [ ] Laporan keuangan: neraca saldo, cost percentage, grafik
- [ ] Dashboard statistik lengkap (web + mobile)
- [ ] Settings management
- [ ] Riwayat pekerjaan (Flutter)
- [ ] Polish UI/UX, bug fixes

### Phase 4 — Future (setelah launch)
- [ ] Export laporan keuangan ke PDF/Excel
- [ ] Role Finance (akses khusus keuangan)
- [ ] Inventory management (stok material)
- [ ] Customer portal (tracking WO)
- [ ] Integrasi WhatsApp API (kirim invoice otomatis)
- [ ] Import data dari spreadsheet lama
- [ ] Audit log

---

*Dokumen ini adalah single source of truth untuk development AzzaOps. Setiap perubahan requirement harus di-update di dokumen ini.*
