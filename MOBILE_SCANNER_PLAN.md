# Rencana Implementasi: Aplikasi Mobile Scanner Absensi (Updated)

Ide Anda **sangat brilian dan relevan!** Memindahkan pembuatan Backend API ke dalam project `app2.yiscalazhar.web.id` adalah keputusan arsitektur yang paling tepat. Karena semua data utama (Siswa, Jadwal, User, Role Shield) aslinya berada di `app2`, maka membuat API-nya di sana akan membuat sistem jauh lebih rapi, terpusat, dan otentikasinya bisa berjalan secara *native* tanpa perlu manipulasi *cross-database*.

## 1. Strategi Direktori & Pengembangan Mobile App

- Aplikasi mobile (Flutter) **WAJIB** berada di direktori project yang benar-benar baru dan terpisah dari Laravel. 
- Disarankan agar pengembangan Flutter (penulisan kode UI dan logika scanner) dilakukan di **Laptop/PC lokal Anda**, dan nantinya menembak API ke `https://app2.yiscalazhar.web.id/api/...`.

---

## 2. Arsitektur Autentikasi API (Native di app2)

Karena API akan dibuat di dalam `app2`, kita bisa memanfaatkan ekosistem Laravel yang sudah ada di sana secara langsung:
- **Kredensial Login**: Langsung menggunakan tabel `users` utama di `app2`.
- **Hak Akses (Roles)**: Langsung menggunakan `Spatie Shield` yang sudah terinstall di `app2`.
- **Mekanisme**: Kita akan membuat endpoint login (`/api/login`) yang menghasilkan **Sanctum Token**. Token ini akan dipakai oleh aplikasi Flutter.

---

## 3. Persiapan Backend API (Di dalam project `app2`)

Berikut adalah langkah-langkah **Fase 1** yang akan kita eksekusi di dalam folder `/www/wwwroot/app2.yiscalazhar.web.id`:

### A. Migrasi Tabel `attendances`
Membuat migration baru di `app2` dengan struktur:
- `id` (Primary Key)
- `civitas_id` (UUID - Relasi ke tabel `civitas_pendidikans`)
- `schedule_id` (UUID - Relasi ke tabel `education_schedules`)
- `scanned_by_user_id` (Integer - ID Pengajar/Admin dari tabel `users` yang melakukan scan)
- `status` (String - Contoh: 'hadir', 'terlambat')
- `created_at` & `updated_at` (Timestamps)

### B. Endpoint API Otentikasi
- `POST /api/auth/login`: Menerima email/username dan password, memvalidasi user, dan mereturn Sanctum API Token.

### C. Endpoint API Scanner
- `POST /api/attendance/scan` (Membutuhkan Bearer Token).
- **Logika**:
  1. Menerima string `UUID_civitas|UUID_schedule`.
  2. Mengecek apakah `civitas_id` dan `schedule_id` ada di database.
  3. Mengecek apakah siswa sudah discan sebelumnya di jadwal ini (menghindari duplikasi absen).
  4. Melakukan `INSERT` ke tabel `attendances`.
  5. Mengembalikan response HTTP 200 beserta detail siswa untuk ditampilkan sebagai *success message* di layar HP pengajar.
