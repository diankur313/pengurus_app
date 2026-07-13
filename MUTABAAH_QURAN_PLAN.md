# Mutabaah Quran Feature Implementation Plan

## 1. Overview
Menambahkan menu baru bernama **Mutabaah Quran** di bawah navigation group **Pendidikan** pada Filament admin panel. Menu ini berfungsi sebagai catatan tracking setoran tilawah Al-Quran para peserta (civitas).

---

## 2. Database Schema (`mutabaah_qurans` table)
Tabel baru yang akan di-migrate memiliki skema sebagai berikut:
- `id` (bigint, primary, auto_increment)
- `civitas_id` (string, foreign key ke `civitas_pendidikans.uuid`)
- `pertama_setor` (date, tanggal pertama kali setor bacaan)
- `from_surah` (string, nama surah awal)
- `from_ayat` (integer, nomor ayat awal)
- `to_surah` (string, nama surah akhir)
- `to_ayat` (integer, nomor ayat akhir)
- `total_halaman` (integer, jumlah halaman yang dibaca)
- `total_juz` (decimal(4,2), jumlah juz yang dibaca, misal 1.50 atau 30.00)
- `timestamps`

---

## 3. Model Layer (`App\Models\MutabaahQuran`)
- Representasi model `MutabaahQuran`
- Definisikan relasi `belongsTo` ke `CivitasPendidikan` dengan foreign key `civitas_id` dan owner key `uuid`

---

## 4. Filament Resource Layer (`App\Filament\Resources\MutabaahQuranResource`)
Membuat resource baru bernama `MutabaahQuranResource` dengan:
- `navigationGroup = 'Pendidikan'`
- `navigationLabel = 'Mutabaah Quran'`
- `modelLabel = 'Mutabaah Quran'`
- `pluralModelLabel = 'Mutabaah Quran'`
- `navigationIcon = 'heroicon-o-book-open'`
- Menggunakan `ManageRecords` page (Simple resource tanpa edit/create terpisah) agar pengelolaan lebih ringkas dan interaktif.

### A. Tabel Columns (Sesuai Request)
1. **Photo**: `ImageColumn` circular (60px) mengambil dari `civitas.photo` (menggunakan helper `profilePhotoUrl` yang sama dengan `AttendanceResource`).
2. **Nama**: `TextColumn` mengambil dari `civitas.name` (searchable).
3. **Pertama Setor**: `TextColumn` dengan format date `d M Y` (sortable).
4. **Progress**: `TextColumn` custom formatting menggabungkan `from_surah`, `from_ayat`, `to_surah`, dan `to_ayat`. Format: `Surah: {from_surah} Ayat: {from_ayat} => Surah: {to_surah} Ayat: {to_ayat}`.
5. **Total**: `TextColumn` custom formatting menggabungkan `total_halaman` dan `total_juz`. Format: `{total_halaman} Halaman => {total_juz} Juz`.

### B. Form Fields (Create/Edit Modal)
- `Select` `civitas_id` untuk memilih peserta. Menggunakan searchable dropdown.
- `DatePicker` `pertama_setor` (required).
- Grid layout berisi detail progress setoran:
  - `TextInput` `from_surah` (required)
  - `TextInput` `from_ayat` (numeric, required)
  - `TextInput` `to_surah` (required)
  - `TextInput` `to_ayat` (numeric, required)
- Grid layout berisi total:
  - `TextInput` `total_halaman` (numeric, required)
  - `TextInput` `total_juz` (numeric, decimal, required)

---

## 5. Langkah-Langkah Implementasi
1. Generate migration file: `database/migrations/2026_07_11_xxxxxx_create_mutabaah_qurans_table.php`.
2. Tulis skema tabel di migration dan jalankan `php artisan migrate`.
3. Buat model file `app/Models/MutabaahQuran.php`.
4. Buat Filament resource `app/Filament/Resources/MutabaahQuranResource.php`.
5. Tulis form, table schema, dan action di resource.
6. Uji coba fungsionalitas dan pastikan tidak ada cache issue.