# AGENTS.md — AzzaOps Field Service Management System

> Panduan untuk AI coding assistant (OpenCode, Cursor, Copilot, dll).
> Baca file ini sebelum menulis kode apapun di project ini.

---

## 1. Project Overview

- **Nama**: AzzaOps
- **Deskripsi**: Sistem manajemen operasional lapangan untuk perusahaan jasa AC & elektronik
- **Skala**: Perusahaan kecil — 5 staff, 5–10 work order per hari
- **Tujuan**: Menggantikan spreadsheet manual. Mengelola work order, assign teknisi, generate invoice, dan modul keuangan sederhana
- **Dokumen referensi**: `azzaops-prd.md` (Product Requirements Document lengkap dengan database schema)

---

## 2. Tech Stack & Architecture

| Layer            | Teknologi                                          |
| ---------------- | -------------------------------------------------- |
| Framework        | Laravel 11 (latest stable)                         |
| PHP              | 8.2+                                               |
| Templating       | Blade                                              |
| CSS Framework    | Bootstrap 5.3 (Local)                                |
| JS Library       | jQuery 3.7 (Local)                                   |
| Admin Template   | Bootstrap 5 based (AdminLTE 4 / SB Admin 2, free)  |
| Web Auth         | Laravel Breeze (session based, email + password)    |
| API Auth         | Laravel Sanctum (token based, untuk Flutter mobile) |
| Database         | MySQL 8                                             |
| PDF              | DomPDF atau SnappyPDF                               |
| File Storage     | Local disk (`public/storage`)                       |
| Push Notification| Firebase Cloud Messaging (FCM)                     |
| DataTable        | DataTables (Local)                                    |
| Date Picker      | Flatpickr (Local)                                     |
| Chart            | Chart.js (Local)                                      |
| Text Editor      | Summernote (Local) atau textarea biasa                |

### LARANGAN KERAS

```
⛔ TIDAK MENGGUNAKAN Vite
⛔ TIDAK MENGGUNAKAN npm / yarn / pnpm
⛔ TIDAK MENGGUNAKAN Node.js build tools
⛔ TIDAK MENGGUNAKAN @vite() directive di Blade
⛔ TIDAK MENGGUNAKAN mix() atau Laravel Mix
```

Semua CSS dan JS menggunakan **Local** atau **file static di `public/`**.

---

## 3. Project Structure

```
azzaops/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/                    # Web controllers (admin panel)
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── CustomerController.php
│   │   │   │   ├── StaffController.php
│   │   │   │   ├── ServiceCategoryController.php
│   │   │   │   ├── WorkOrderTypeController.php
│   │   │   │   ├── WorkOrderController.php
│   │   │   │   ├── InvoiceController.php
│   │   │   │   ├── RabController.php
│   │   │   │   ├── ReportController.php
│   │   │   │   ├── FinanceController.php
│   │   │   │   └── SettingController.php
│   │   │   └── Api/                      # API controllers (Flutter app)
│   │   │       ├── AuthController.php
│   │   │       ├── WorkOrderController.php
│   │   │       ├── AssignmentController.php
│   │   │       ├── ReportController.php
│   │   │       ├── NotificationController.php
│   │   │       └── ProfileController.php
│   │   ├── Middleware/
│   │   │   └── CheckRole.php             # Middleware cek role user
│   │   └── Requests/                     # Form Request validation
│   │       ├── Admin/
│   │       │   ├── StoreCustomerRequest.php
│   │       │   ├── UpdateCustomerRequest.php
│   │       │   ├── StoreWorkOrderRequest.php
│   │       │   ├── UpdateWorkOrderRequest.php
│   │       │   ├── StoreInvoiceRequest.php
│   │       │   ├── StoreRabRequest.php
│   │       │   └── ...
│   │       └── Api/
│   │           ├── LoginRequest.php
│   │           ├── SubmitReportRequest.php
│   │           └── ...
│   ├── Models/
│   │   ├── User.php
│   │   ├── Customer.php
│   │   ├── ServiceCategory.php
│   │   ├── WorkOrderType.php
│   │   ├── WorkOrder.php
│   │   ├── WorkOrderItem.php
│   │   ├── WorkOrderAssignment.php
│   │   ├── WorkOrderReport.php
│   │   ├── WorkOrderReportPhoto.php
│   │   ├── Invoice.php
│   │   ├── InvoiceItem.php
│   │   ├── FinancialAccount.php
│   │   ├── Rab.php
│   │   ├── RabItem.php
│   │   ├── FinancialTransaction.php
│   │   ├── FinancialCategory.php
│   │   ├── Notification.php
│   │   └── Setting.php
│   ├── Services/                         # Business logic (thin controller pattern)
│   │   ├── WorkOrderService.php
│   │   ├── InvoiceService.php
│   │   ├── RabService.php
│   │   ├── FinanceService.php
│   │   ├── NotificationService.php
│   │   └── ReportService.php
│   ├── Notifications/
│   │   ├── WorkOrderAssigned.php
│   │   ├── ReportSubmitted.php
│   │   └── InvoiceCreated.php
│   ├── Enums/                            # PHP 8.1 backed enums
│   │   ├── UserRole.php
│   │   ├── WorkOrderStatus.php
│   │   ├── WorkOrderPriority.php
│   │   ├── WorkOrderType.php
│   │   ├── CustomerType.php
│   │   ├── PaymentStatus.php
│   │   ├── PaymentMethod.php
│   │   ├── AssignmentStatus.php
│   │   ├── TransactionType.php
│   │   └── TransactionCategory.php
│   └── Policies/
│       ├── WorkOrderPolicy.php
│       └── ...
├── database/
│   ├── migrations/
│   ├── seeders/
│   │   ├── DatabaseSeeder.php
│   │   ├── UserSeeder.php
│   │   ├── ServiceCategorySeeder.php
│   │   └── FinancialCategorySeeder.php
│   └── factories/
├── resources/
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php             # Layout utama admin (Bootstrap + Local)
│       │   ├── auth.blade.php            # Layout halaman auth
│       │   └── pdf.blade.php             # Layout untuk generate PDF
│       ├── admin/
│       │   ├── dashboard/
│       │   │   └── index.blade.php
│       │   ├── customers/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php
│       │   │   ├── edit.blade.php
│       │   │   └── show.blade.php
│       │   ├── staff/
│       │   ├── service-categories/
│       │   ├── work-orders/
│       │   ├── invoices/
│       │   ├── rab/
│       │   ├── reports/
│       │   ├── finance/
│       │   └── settings/
│       ├── auth/
│       │   ├── login.blade.php
│       │   ├── register.blade.php
│       │   └── ...
│       ├── pdf/
│       │   ├── invoice.blade.php
│       │   └── rab.blade.php
│       └── components/
│           ├── alert.blade.php
│           ├── modal-delete.blade.php
│           ├── status-badge.blade.php
│           └── ...
├── public/
│   ├── css/
│   │   └── custom.css                    # Custom styles
│   ├── js/
│   │   └── custom.js                     # Custom scripts
│   └── uploads/                          # Uploaded files (foto, dll)
├── routes/
│   ├── web.php                           # Web routes (admin panel)
│   └── api.php                           # API routes (mobile app)
├── config/
│   └── services.php                      # Config FCM, dll
├── storage/
│   └── app/public/                       # Symlink ke public/storage
└── azzaops-prd.md                        # Product Requirements Document
```

---

## 4. Layout & Asset Loading

### Layout utama (`resources/views/layouts/app.blade.php`)

Semua Local di-load di layout utama, **bukan** per halaman:

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AzzaOps')</title>

    {{-- Bootstrap 5.3 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    {{-- DataTables CSS --}}
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    {{-- Flatpickr CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    {{-- Custom CSS --}}
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body>
    {{-- Sidebar & Navbar --}}
    @include('layouts.partials.sidebar')
    @include('layouts.partials.navbar')

    <main>
        @yield('content')
    </main>

    {{-- jQuery --}}
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    {{-- Bootstrap 5.3 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    {{-- DataTables JS --}}
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    {{-- Flatpickr JS --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    {{-- Chart.js (hanya load jika dibutuhkan, atau selalu di layout) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    {{-- Custom JS --}}
    <script src="{{ asset('js/custom.js') }}"></script>

    @stack('scripts')
</body>
</html>
```

### Per-page CSS/JS

Gunakan `@push` di child view:

```blade
@extends('layouts.app')

@section('content')
    {{-- halaman content --}}
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#description').summernote({ height: 200 });
    });
</script>
@endpush
```

---

## 5. Database Schema

Referensi lengkap ada di `azzaops-prd.md`. Berikut ringkasan tabel dan relasi:

### Tabel Utama

| Tabel                      | Deskripsi                                 |
| -------------------------- | ----------------------------------------- |
| `users`                    | Admin, kepala teknisi, teknisi            |
| `customers`                | Data pelanggan (perorangan / perusahaan)  |
| `service_categories`       | Kategori layanan (AC, elektronik, dll)    |
| `work_orders`              | Work order / surat perintah kerja         |
| `work_order_items`         | Item pekerjaan dalam work order           |
| `work_order_assignments`   | Penugasan teknisi ke work order           |
| `work_order_reports`       | Laporan pengerjaan dari teknisi           |
| `work_order_report_photos` | Foto lampiran laporan                     |
| `invoices`                 | Invoice / tagihan ke customer (include discount type, value, and PPN) |
| `invoice_items`            | Item dalam invoice                        |
| `financial_accounts`       | Akun keuangan (Giro, Rekening, Cash, Direksi) |
| `rabs`                     | Rencana Anggaran Biaya (untuk instalasi)  |
| `rab_items`                | Item dalam RAB                            |
| `financial_transactions`   | Transaksi keuangan (pemasukan/pengeluaran)|
| `financial_categories`     | Kategori keuangan                         |
| `notifications`            | Notifikasi in-app & push                  |
| `settings`                 | Pengaturan aplikasi (key-value)           |

### Enum Values

```php
// app/Enums/UserRole.php
enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case KepalTeknisi = 'kepala_teknisi';
    case Teknisi = 'teknisi';
}

// app/Enums/CustomerType.php
enum CustomerType: string
{
    case Perorangan = 'perorangan';
    case Perusahaan = 'perusahaan';
}

// app/Enums/WorkOrderStatus.php
enum WorkOrderStatus: string
{
    case Pending = 'pending';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}

// app/Enums/WorkOrderPriority.php
enum WorkOrderPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';
}

// app/Enums/WorkOrderType.php
enum WorkOrderType: string
{
    case Pengecekan = 'pengecekan';
    case Perbaikan = 'perbaikan';
    case Perawatan = 'perawatan';
    case Instalasi = 'instalasi';
    case Bongkar = 'bongkar';
}

// app/Enums/PaymentStatus.php
enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Partial = 'partial';
    case Paid = 'paid';
}

// app/Enums/PaymentMethod.php
enum PaymentMethod: string
{
    case Cash = 'cash';
    case Transfer = 'transfer';
    case Qris = 'qris';
}

// app/Enums/AssignmentStatus.php
enum AssignmentStatus: string
{
    case Assigned = 'assigned';
    case Accepted = 'accepted';
    case OnTheWay = 'on_the_way';
    case Working = 'working';
    case Done = 'done';
    case Rejected = 'rejected';
}

// app/Enums/TransactionType.php
enum TransactionType: string
{
    case Income = 'income';
    case Expense = 'expense';
}
```

### Relasi Utama

```
User          1 --- * WorkOrderAssignment
Customer      1 --- * WorkOrder
WorkOrder     1 --- * WorkOrderItem
WorkOrder     1 --- * WorkOrderAssignment
WorkOrder     1 --- 1 Invoice
WorkOrder     1 --- * WorkOrderReport
WorkOrderReport 1 --- * WorkOrderReportPhoto
Invoice       1 --- * InvoiceItem
WorkOrder     1 --- 0..1 Rab
Rab           1 --- * RabItem
```

---

## 6. Coding Conventions & Rules

### PHP & Laravel

- **PHP 8.2+** — gunakan fitur modern: backed enums, named arguments, `match`, readonly properties, constructor promotion
- **Thin controllers** — controller hanya handle request/response. Business logic di `app/Services/`
- **Form Request** — semua form input WAJIB pakai Form Request, bukan validasi di controller
- **Eloquent scopes** — query yang bisa di-reuse, buat local scope di Model
- **Eloquent relationships** — selalu pakai relationship, hindari raw query dan `DB::` facade
- **Policy** — gunakan Policy untuk authorization check
- **Resource routes** — gunakan `Route::resource()` jika memungkinkan

### Naming Conventions

| Jenis                | Convention   | Contoh                           |
| -------------------- | ------------ | -------------------------------- |
| Model                | PascalCase   | `WorkOrder`, `InvoiceItem`       |
| Controller           | PascalCase   | `WorkOrderController`            |
| Method               | camelCase    | `getActiveOrders()`, `assignTechnician()` |
| DB column            | snake_case   | `work_order_id`, `created_at`    |
| DB table             | snake_case (plural) | `work_orders`, `invoice_items` |
| Route name (web)     | dot notation | `admin.work-orders.index`, `admin.customers.store` |
| Route name (api)     | dot notation | `api.work-orders.index`          |
| Blade view directory | kebab-case   | `work-orders/`, `service-categories/` |
| Enum                 | PascalCase   | `WorkOrderStatus`, `UserRole`    |
| Service class        | PascalCase   | `WorkOrderService`               |
| Form Request         | PascalCase   | `StoreWorkOrderRequest`          |

### Route Structure

```php
// routes/web.php
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('customers', CustomerController::class);
    Route::resource('staff', StaffController::class);
    Route::resource('service-categories', ServiceCategoryController::class);
    Route::resource('work-order-types', WorkOrderTypeController::class);
    Route::resource('work-orders', WorkOrderController::class);
    Route::post('work-orders/{workOrder}/assign', [WorkOrderController::class, 'assign'])->name('work-orders.assign');
    Route::resource('invoices', InvoiceController::class);
    Route::resource('rab', RabController::class);
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::resource('finance', FinanceController::class);
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
});

// routes/api.php
Route::prefix('v1')->name('api.')->group(function () {
    Route::post('login', [Api\AuthController::class, 'login'])->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [Api\AuthController::class, 'logout'])->name('logout');
        Route::get('profile', [Api\ProfileController::class, 'show'])->name('profile.show');
        Route::put('profile', [Api\ProfileController::class, 'update'])->name('profile.update');
        Route::apiResource('work-orders', Api\WorkOrderController::class)->only(['index', 'show']);
        Route::put('assignments/{assignment}/status', [Api\AssignmentController::class, 'updateStatus'])->name('assignments.update-status');
        Route::post('reports', [Api\ReportController::class, 'store'])->name('reports.store');
        Route::get('notifications', [Api\NotificationController::class, 'index'])->name('notifications.index');
        Route::put('notifications/{notification}/read', [Api\NotificationController::class, 'markAsRead'])->name('notifications.read');
    });
});
```

---

## 7. API Response Format

Semua API endpoint HARUS mengembalikan format JSON yang konsisten.

### Success Response

```json
{
    "success": true,
    "message": "Data berhasil diambil",
    "data": {
        "id": 1,
        "name": "John Doe"
    }
}
```

### Success Response (Paginated)

```json
{
    "success": true,
    "message": "Data berhasil diambil",
    "data": [
        { "id": 1, "name": "John" },
        { "id": 2, "name": "Jane" }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 72
    }
}
```

### Error Response

```json
{
    "success": false,
    "message": "Validasi gagal",
    "errors": {
        "email": ["Email wajib diisi"],
        "name": ["Nama minimal 3 karakter"]
    }
}
```

### API Trait/Helper

Gunakan trait agar format response konsisten:

```php
// app/Traits/ApiResponse.php
trait ApiResponse
{
    protected function successResponse(mixed $data = null, string $message = 'Berhasil', int $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function errorResponse(string $message = 'Gagal', int $code = 400, mixed $errors = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }
}
```

---

## 8. Authentication & Authorization

### Web Auth (Admin Panel)

- Menggunakan **Laravel Breeze** (session based)
- Login: email + password
- Hanya user dengan role `super_admin`, `admin`, `kepala_teknisi` yang bisa akses admin panel
- Teknisi TIDAK akses admin panel (hanya pakai mobile app)

### API Auth (Mobile App)

- Menggunakan **Laravel Sanctum** (token based)
- Login endpoint: `POST /api/v1/login` → return bearer token
- Logout endpoint: `POST /api/v1/logout` → revoke token
- Semua API endpoint (kecuali login) require `auth:sanctum` middleware
- Token dikirim via header: `Authorization: Bearer {token}`

### Roles

| Role             | Akses Web | Akses API | Deskripsi                    |
| ---------------- | --------- | --------- | ---------------------------- |
| `super_admin`    | Ya        | Ya        | Full access, manage semua    |
| `admin`          | Ya        | Ya        | Manage operasional & keuangan|
| `kepala_teknisi` | Ya        | Ya        | Assign teknisi, lihat laporan|
| `teknisi`        | Tidak     | Ya        | Terima WO, submit laporan    |

### Role Middleware

Implementasi sederhana tanpa package Spatie (keep it simple untuk skala kecil):

```php
// app/Http/Middleware/CheckRole.php
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!in_array($request->user()->role->value, $roles)) {
            abort(403, 'Unauthorized');
        }
        return $next($request);
    }
}

// Pemakaian di routes:
Route::middleware(['auth', 'role:super_admin,admin'])->group(function () {
    // hanya super_admin dan admin
});
```

---

## 9. Key Business Rules

### Work Order

1. Work order **HARUS** punya minimal 1 item (`work_order_items`)
2. Status flow: `pending` → `assigned` → `in_progress` → `completed` / `cancelled`
3. Work order tidak bisa diedit setelah status `completed`
4. Tipe pekerjaan: pengecekan, perbaikan, perawatan, instalasi, bongkar

### Assignment (Penugasan Teknisi)

1. Teknisi hanya bisa di-assign oleh user dengan role `kepala_teknisi` atau `admin`
2. Satu work order bisa punya lebih dari 1 teknisi
3. Status assignment: `assigned` → `accepted` → `on_the_way` → `working` → `done` / `rejected`
4. Jika teknisi reject, kepala teknisi harus re-assign

### Invoice

1. Invoice otomatis di-generate dari work order yang statusnya `completed`
2. Invoice berisi item-item dari work order + biaya jasa
3. Payment status: `unpaid` → `partial` → `paid`
4. Metode bayar: cash, transfer, QRIS

### Alur Pengecekan (Business Logic Penting)

1. Customer request pengecekan → buat WO tipe `pengecekan`
2. Teknisi cek → submit laporan
3. **Jika customer setuju perbaikan/instalasi:**
   - Invoice pengecekan = Rp 0 (gratis, jasa cek di-include ke WO berikutnya)
   - Buat WO baru (tipe `perbaikan` atau `instalasi`)
4. **Jika customer TIDAK setuju:**
   - Customer bayar biaya jasa cek
   - WO selesai (status `completed`)

### RAB (Rencana Anggaran Biaya)

1. RAB hanya dibuat untuk work order tipe `instalasi`
2. RAB berisi daftar material + biaya + jasa
3. RAB harus disetujui customer sebelum pekerjaan dimulai

### Upload Foto

1. Maksimal **5 MB** per file
2. Maksimal **10 foto** per laporan
3. Format: jpg, jpeg, png
4. Simpan di `public/uploads/reports/{work_order_id}/`

### Notifikasi

Notifikasi dikirim saat:
- Work order di-assign ke teknisi → push notif ke teknisi
- Teknisi submit laporan → notif ke admin / kepala teknisi
- Invoice dibuat → notif ke admin
- Assignment status berubah → notif ke kepala teknisi

---

## 10. Service Layer Pattern

Controller harus thin. Contoh pattern:

```php
// app/Http/Controllers/Admin/WorkOrderController.php
class WorkOrderController extends Controller
{
    public function __construct(
        private readonly WorkOrderService $workOrderService
    ) {}

    public function store(StoreWorkOrderRequest $request)
    {
        $workOrder = $this->workOrderService->create($request->validated());

        return redirect()
            ->route('admin.work-orders.show', $workOrder)
            ->with('success', 'Work order berhasil dibuat');
    }
}

// app/Services/WorkOrderService.php
class WorkOrderService
{
    public function create(array $data): WorkOrder
    {
        return DB::transaction(function () use ($data) {
            $workOrder = WorkOrder::create([
                'customer_id' => $data['customer_id'],
                'type' => $data['type'],
                'priority' => $data['priority'],
                'description' => $data['description'],
                'scheduled_date' => $data['scheduled_date'],
                'status' => WorkOrderStatus::Pending,
            ]);

            foreach ($data['items'] as $item) {
                $workOrder->items()->create($item);
            }

            return $workOrder;
        });
    }
}
```

---

## 11. Model Conventions

```php
// app/Models/WorkOrder.php
class WorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'work_order_number',
        'type',
        'priority',
        'status',
        'description',
        'scheduled_date',
        'completed_date',
        'notes',
    ];

    protected $casts = [
        'type' => WorkOrderType::class,
        'priority' => WorkOrderPriority::class,
        'status' => WorkOrderStatus::class,
        'scheduled_date' => 'date',
        'completed_date' => 'date',
    ];

    // === Relationships ===

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(WorkOrderItem::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(WorkOrderAssignment::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(WorkOrderReport::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function rab(): HasOne
    {
        return $this->hasOne(Rab::class);
    }

    // === Scopes ===

    public function scopeStatus(Builder $query, WorkOrderStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('scheduled_date', $date);
    }

    public function scopeByType(Builder $query, WorkOrderType $type): Builder
    {
        return $query->where('type', $type);
    }

    // === Accessors ===

    public function getTotalAttribute(): int
    {
        return $this->items->sum(fn ($item) => $item->quantity * $item->unit_price);
    }
}
```

---

## 12. Blade Component Examples

### Status Badge Component

```blade
{{-- resources/views/components/status-badge.blade.php --}}
@props(['status'])

@php
    $colors = match($status->value) {
        'pending' => 'warning',
        'assigned' => 'info',
        'in_progress' => 'primary',
        'completed' => 'success',
        'cancelled' => 'danger',
        default => 'secondary',
    };
@endphp

<span class="badge bg-{{ $colors }}">{{ str_replace('_', ' ', ucfirst($status->value)) }}</span>
```

### Delete Confirmation Modal

```blade
{{-- resources/views/components/modal-delete.blade.php --}}
@props(['action', 'title' => 'Hapus Data'])

<form action="{{ $action }}" method="POST" class="d-inline">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger"
        onclick="return confirm('Yakin ingin menghapus {{ $title }}?')">
        <i class="bi bi-trash"></i>
    </button>
</form>
```

---

## 13. Important Rules for AI Assistants

### WAJIB

- Selalu gunakan Local untuk library frontend (Bootstrap, jQuery, DataTables, Flatpickr, Chart.js, Summernote)
- Selalu gunakan `{{ asset('css/...') }}` atau `{{ asset('js/...') }}` untuk file static lokal
- Selalu gunakan `@stack('styles')` dan `@stack('scripts')` untuk per-page CSS/JS
- Selalu gunakan Form Request untuk validasi
- Selalu gunakan Service class untuk business logic
- Selalu gunakan Eloquent relationship
- Selalu return format API yang konsisten (lihat section 7)
- Selalu gunakan CSRF token di form: `@csrf`
- Selalu gunakan `@method('PUT')` atau `@method('DELETE')` untuk form non-GET/POST
- Gunakan Bahasa Indonesia untuk label, pesan error, dan flash message di UI

### DILARANG

```
⛔ JANGAN gunakan Vite, @vite(), npm, yarn, atau Node.js
⛔ JANGAN gunakan Laravel Mix atau mix()
⛔ JANGAN gunakan Livewire atau Inertia.js
⛔ JANGAN gunakan Tailwind CSS (project ini pakai Bootstrap 5)
⛔ JANGAN gunakan Spatie Permission package (role pakai kolom di tabel users)
⛔ JANGAN gunakan React, Vue, atau frontend framework SPA
⛔ JANGAN buat raw SQL query kalau bisa pakai Eloquent
⛔ JANGAN taruh business logic di controller
⛔ JANGAN validasi di controller (pakai Form Request)
⛔ JANGAN buat migration tanpa rollback (down method)
```

### Local References

Gunakan Local berikut (versi bisa disesuaikan ke latest stable):

```html
<!-- Bootstrap 5.3 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<!-- jQuery 3.7 -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>

<!-- DataTables -->
<link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<!-- Flatpickr -->
<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

<!-- Summernote (per-page, bukan global) -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>

<!-- SweetAlert2 (opsional, untuk konfirmasi dialog) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
```

### AJAX Pattern (jQuery)

```javascript
// Setup CSRF token untuk semua AJAX request
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Contoh AJAX call
$.ajax({
    url: '/admin/work-orders/' + id,
    method: 'DELETE',
    success: function(response) {
        location.reload();
    },
    error: function(xhr) {
        alert('Gagal menghapus data');
    }
});
```

### DataTable Pattern

```javascript
$(document).ready(function() {
    $('#work-orders-table').DataTable({
        processing: true,
        serverSide: false, // client-side untuk data kecil (< 1000 rows)
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json'
        },
        order: [[0, 'desc']]
    });
});
```

---

## 14. Testing

- Gunakan **PHPUnit** (bawaan Laravel)
- Minimal: test untuk Service class dan API endpoints
- Feature test untuk happy path flow utama (create WO → assign → report → invoice)
- Jalankan test: `php artisan test`
- Untuk factory, buat di `database/factories/`

```bash
# Run semua test
php artisan test

# Run test spesifik
php artisan test --filter=WorkOrderServiceTest
```

---

## 15. Useful Commands

```bash
# Development
php artisan serve                          # Start dev server
php artisan migrate                        # Run migrations
php artisan migrate:fresh --seed           # Reset DB + seed
php artisan db:seed                        # Run seeders
php artisan storage:link                   # Create storage symlink
php artisan make:model ModelName -mfs      # Model + migration + factory + seeder
php artisan make:controller Admin/XController --resource  # Resource controller
php artisan make:request StoreXRequest     # Form request
php artisan make:policy XPolicy --model=X  # Policy
php artisan make:enum EnumName             # Enum (manual create di app/Enums/)

# Debugging
php artisan route:list                     # List semua routes
php artisan tinker                         # REPL

# Cache (production)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 16. Environment Variables

Key `.env` variables yang perlu diset:

```env
APP_NAME=AzzaOps
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=azzaops
DB_USERNAME=root
DB_PASSWORD=

FILESYSTEM_DISK=public

# FCM
FCM_SERVER_KEY=your-fcm-server-key
FCM_SENDER_ID=your-fcm-sender-id

# PDF (jika pakai snappy)
# WKHTMLTOPDF_PATH=/usr/local/bin/wkhtmltopdf
```

---

*File ini adalah satu-satunya referensi bagi AI assistant saat coding project AzzaOps. Selalu baca file ini terlebih dahulu sebelum menulis kode.*
