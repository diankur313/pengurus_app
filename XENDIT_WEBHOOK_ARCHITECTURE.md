# Xendit Webhook Architecture & Fee Calculation

## Arsitektur Parent-Child Webhook System

### Konsep Dasar

Aplikasi ini (app2.yiscalazhar.web.id) berfungsi sebagai **CENTRAL WEBHOOK RECEIVER** (Parent App) yang menerima semua webhook dari Xendit, kemudian mem-forward ke child apps.

```
Xendit → app2 (Central/Parent) → Child Apps (PPAB, e-yac/Archery, e-sii)
```

### Flow Webhook

1. **Xendit** mengirim webhook ke app2: `POST /api/webhook/xendit`
2. **app2** menerima webhook dan:
   - Validasi x-callback-token
   - Deteksi app_id dari external_id prefix
   - Hitung fee berdasarkan payment method
   - **Log ke `xendit_webhook_logs`** (database ppab)
   - Forward ke internal webhook URL child app
3. **Child app** menerima forwarded webhook dan:
   - Simpan transaksi ke database mereka sendiri
   - Update status pembayaran

### App ID Detection

Berdasarkan prefix external_id:

```php
YISC/ARCH atau DISB/ARCH → app_id: 'e-yac' (archery)
DISB/SYS → app_id: 'app'
YISC/PPAB atau PPAB atau YISCAL → app_id: 'join-ppab'
SII atau ESII → app_id: 'e-sii'
```

## Lokasi Penyimpanan Transaksi

### ❌ SALAH: archery_transactions di app2
**archery_transactions TIDAK ADA di database app2 (ppab)**

### ✅ BENAR: Lokasi Transaksi per App

| App | Lokasi Transaksi | Database | Keterangan |
|-----|-----------------|----------|------------|
| **app2 (Central)** | `xendit_webhook_logs` | ppab | Log semua webhook yang masuk |
| **join-ppab** | `ppab_transactions_xendit` | ppab | Transaksi PPAB |
| **e-yac (Archery)** | `archery_transactions` | **Database e-yac** | Transaksi Archery (di child app) |
| **e-sii** | `esii_transactions` | **Database e-sii** | Transaksi e-sii (di child app) |

### Penjelasan archery_transactions

`archery_transactions` berada di **child app e-yac**, bukan di app2 ini. 

**Alur:**
1. Xendit kirim webhook ke app2
2. app2 log ke `xendit_webhook_logs`
3. app2 forward ke e-yac: `https://e-yac.domain.com/api/internal/webhook/invoice`
4. **e-yac simpan ke `archery_transactions`** di database mereka

## Struktur Database

### xendit_webhook_logs (app2 - database ppab)

**Connection:** ppab  
**Purpose:** Log semua webhook yang diterima dari Xendit

```sql
CREATE TABLE xendit_webhook_logs (
  id BIGINT UNSIGNED PRIMARY KEY,
  external_id VARCHAR(255) INDEX,
  app_id VARCHAR(255) INDEX,           -- join-ppab, e-yac, e-sii
  app_name VARCHAR(255),               -- Nama dari paymentgatewayfees
  status VARCHAR(255) INDEX,           -- PAID, SETTLED, EXPIRED
  payment_method VARCHAR(255),         -- BANK_TRANSFER, QR_CODE, RETAIL_OUTLET
  bank_code VARCHAR(255),
  amount BIGINT UNSIGNED,
  fee_pg BIGINT UNSIGNED,              -- Fee payment gateway
  fee_sysdev BIGINT UNSIGNED,          -- Fee system developer
  withdrawable BIGINT UNSIGNED,        -- Dana bersih
  forward_url VARCHAR(255),            -- URL child app
  forward_status SMALLINT,             -- HTTP status forward
  forward_response TEXT,               -- Response dari child
  raw_payload JSON,                    -- Payload asli Xendit
  paid_at TIMESTAMP,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

### ppab_transactions_xendit (PPAB - database ppab)

**Connection:** ppab  
**Purpose:** Transaksi pembayaran PPAB

**Kolom Penting:**
- `external_Id` (huruf I kapital!)
- `method` (enum: 'va', 'qris', 'merchant')
- `amount`, `fee_pg`, `fee_sysdev`, `withdrawable`

## Formula Perhitungan Fee (SUDAH DIPERBAIKI)

### 1. Virtual Account (method='va')

```
Fee PG Base = Rp 4.000 (dari config va_fee)
PPN 11% = Rp 4.000 × 1.11 = Rp 4.440
Fee Sysdev = Rp 20.000
Withdrawable = Amount - Rp 4.440 - Rp 20.000
```

**Contoh:**
- Amount: Rp 500.000
- Fee PG: Rp 4.440
- Fee Sysdev: Rp 20.000
- **Withdrawable: Rp 475.560**

### 2. QRIS (method='qris')

```
Fee PG = Amount × 0.7% (TANPA PPN)
Fee Sysdev = Rp 20.000
Withdrawable = Amount - Fee PG - Rp 20.000
```

**Contoh:**
- Amount: Rp 500.000
- Fee PG: Rp 3.500 (0.7%)
- Fee Sysdev: Rp 20.000
- **Withdrawable: Rp 476.500**

### 3. Retail Outlet / Merchant (method='merchant')

```
Fee PG = Rp 5.000 (flat, TANPA PPN)
Fee Sysdev = Rp 20.000
Withdrawable = Amount - Rp 5.000 - Rp 20.000
```

## ⚠️ Kesalahan yang Sudah Diperbaiki

### Bug #1: PPN Diterapkan ke Semua Metode

**SALAH (sebelumnya):**
```php
// PPN diterapkan ke SEMUA payment method
if ($ppn > 0) {
    $feePg = (int) round($feePg * (1 + $ppn / 100));
}
```

**BENAR (sekarang):**
```php
// PPN HANYA untuk Virtual Account
$isVirtualAccount = str_contains($method, 'VIRTUAL_ACCOUNT') 
                 || str_contains($method, 'BANK_TRANSFER');

if ($ppn > 0 && $isVirtualAccount) {
    $feePg = (int) round($feePg * (1 + $ppn / 100));
}
```

### Bug #2: Migration Salah Database Connection

**SALAH (sebelumnya):**
```php
Schema::create('xendit_webhook_logs', function (Blueprint $table) {
    // Dibuat di default connection (mysql), bukan ppab
});
```

**BENAR (sekarang):**
```php
Schema::connection('ppab')->create('xendit_webhook_logs', function (Blueprint $table) {
    // Dibuat di ppab connection
});
```

## Hasil Perbaikan

### Transaksi yang Diperbaiki

**ppab_transactions_xendit:**
- Total transaksi 2 bulan terakhir: **25 transaksi**
- Semua diperbaiki dengan kalkulasi fee yang benar
- VA transactions: Fee PG = Rp 4.440 (dengan PPN)
- QRIS transactions: Fee PG = 0.7% (tanpa PPN)

### File yang Diubah

1. **app/Services/XenditFeeCalculatorService.php**
   - Tambah validasi `$isVirtualAccount`
   - PPN hanya untuk VA

2. **database/migrations/2026_06_12_000001_create_xendit_webhook_logs_table.php**
   - Tambah `connection('ppab')`

3. **database/migrations/2026_06_13_164730_change_forward_status_to_small_integer_in_xendit_webhook_logs.php**
   - Tambah `connection('ppab')`

4. **scripts/fix_xendit_fees_corrected.php**
   - Script untuk fix transaksi lama
   - Gunakan kolom `method` bukan `payment_method`
   - Gunakan `external_Id` bukan `external_id`

## API Endpoints

### Xendit Webhook (Public)
```
POST /api/webhook/xendit
Header: x-callback-token: {XENDIT_WEBHOOK_TOKEN}
```

### Internal Webhook (Child Apps)
```
POST /api/internal/webhook/invoice
Header: Authorization: Bearer {APP2_INTERNAL_SECRET}
```

### Get Credentials (Child Apps)
```
GET /api/internal/xendit-credentials?app_id={app_id}
Header: Authorization: Bearer {APP2_INTERNAL_SECRET}
```

## Konfigurasi

### Environment Variables

```env
# Xendit Credentials
XENDIT_SECRET_KEY_LIVE=xnd_...
XENDIT_SECRET_KEY_TEST=xnd_...
XENDIT_WEBHOOK_TOKEN_LIVE=...
XENDIT_WEBHOOK_TOKEN_TEST=...

# Internal Secret
APP2_INTERNAL_SECRET=...

# Child App Webhook URLs
INTERNAL_WEBHOOK_URL_PPAB=https://join-ppab.../api/internal/webhook/invoice
INTERNAL_WEBHOOK_URL_ARCHERY=https://e-yac.../api/internal/webhook/invoice
INTERNAL_WEBHOOK_URL_ESII=https://e-sii.../api/internal/webhook/invoice
```

### Database Connection (config/database.php)

```php
'ppab' => [
    'driver' => 'mysql',
    'host' => env('DB_PPAB_HOST', env('DB_HOST', '127.0.0.1')),
    'port' => env('DB_PPAB_PORT', env('DB_PORT', '3306')),
    'database' => env('DB_PPAB_DATABASE', 'ppab'),
    'username' => env('DB_PPAB_USERNAME', env('DB_USERNAME', 'root')),
    'password' => env('DB_PPAB_PASSWORD', env('DB_PASSWORD', '')),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
],
```

## Testing

### Verify xendit_webhook_logs Table

```php
php artisan tinker --execute="
    \$count = DB::connection('ppab')->table('xendit_webhook_logs')->count();
    echo \"xendit_webhook_logs: {\$count} records\n\";
"
```

### Check Recent Transactions

```php
php artisan tinker --execute="
    \$recent = DB::connection('ppab')
        ->table('ppab_transactions_xendit')
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get(['id', 'method', 'amount', 'fee_pg', 'withdrawable']);
    print_r(\$recent);
"
```

## Kesimpulan

✅ **PPN 11% sekarang HANYA untuk Virtual Account**  
✅ **QRIS dan Merchant TANPA PPN**  
✅ **25 transaksi PPAB sudah diperbaiki**  
✅ **xendit_webhook_logs table sudah dibuat di database ppab**  
✅ **Migration files sudah menggunakan koneksi ppab yang benar**  

⚠️ **archery_transactions berada di child app e-yac, BUKAN di app2 ini**

---

**Tanggal Perbaikan:** 13 Juli 2026  
**Developer:** AI Assistant via Kiro  
**Status:** ✅ SELESAI & TERVERIFIKASI