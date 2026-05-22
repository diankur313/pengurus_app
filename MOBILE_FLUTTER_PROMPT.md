Halo! Saya ingin membuat Aplikasi Mobile Scanner Absensi menggunakan **Flutter**. Backend API-nya sudah selesai dikerjakan menggunakan Laravel 11 dengan autentikasi Sanctum. 

Berikut adalah spesifikasi lengkap sistem dan API-nya. Tolong baca dengan seksama, lalu buatkan saya **Implementation Plan** langkah demi langkah untuk membuat aplikasi Flutter ini.

### 1. Konteks Aplikasi
Aplikasi ini ditujukan untuk Admin / Pengajar. Mereka akan login ke aplikasi, lalu membuka fitur "Scanner" untuk men-*scan* QR Code yang ditampilkan di layar perangkat siswa (melalui web e-sii). 
Format QR Code yang akan discan berbentuk string: `UUID_civitas|UUID_schedule`.

### 2. Spesifikasi API (Base URL: https://app2.yiscalazhar.web.id)

#### A. Endpoint Login
- **URL**: `POST /api/auth/login`
- **Payload**:
  ```json
  {
    "email": "email_pengajar@example.com",
    "password": "password123"
  }
  ```
- **Response**: Mengembalikan token otentikasi Sanctum. Token ini harus disimpan secara aman di aplikasi (contoh: `flutter_secure_storage`).

#### B. Endpoint Scanner (Butuh Bearer Token)
- **URL**: `POST /api/attendance/scan`
- **Headers**:
  - `Authorization: Bearer <TOKEN_DARI_LOGIN>`
  - `Accept: application/json`
- **Payload**:
  ```json
  {
    "qr_data": "UUID_civitas|UUID_schedule"
  }
  ```
- **Response Sukses (200 OK)**:
  ```json
  {
    "status": "success",
    "message": "Absensi berhasil dicatat!",
    "data": {
        "student_name": "Nama Siswa",
        "schedule_title": "Judul Jadwal",
        "time": "2026-05-21 15:30:00"
    }
  }
  ```
- **Response Error (Harus di-*handle* di UI)**:
  - `401 Unauthorized` (Token tidak valid/expired -> arahkan user kembali ke halaman Login)
  - `400 Bad Request` (Format QR salah)
  - `404 Not Found` (Siswa atau Jadwal tidak ditemukan)
  - `409 Conflict` (Siswa sudah absen sebelumnya / Duplikat)

### 3. Fitur Utama yang Harus Dibangun di Flutter
1. **State Management**: Gunakan Provider, Riverpod, atau GetX (pilih yang paling best practice saat ini).
2. **Network/HTTP**: Gunakan `dio` atau `http` untuk manajemen request API.
3. **QR Scanner**: Gunakan package `mobile_scanner` (atau alternatif terbaik) untuk membaca kamera.
4. **Halaman Login (Login Screen)**:
   - Form email dan password.
   - Menyimpan Bearer token jika berhasil.
5. **Halaman Utama / Scanner (Home Screen)**:
   - Menampilkan kamera langsung untuk *scan*.
   - Saat QR terbaca, jeda kamera (pause), kirim payload ke API, tampilkan loading.
   - Tampilkan *Dialog* / *Bottom Sheet* / *Snackbar* berisi pesan Sukses (berikut nama siswa) ATAU pesan Error (contoh: "Sudah absen").
   - Setelah dialog ditutup, kamera aktif kembali untuk men-*scan* siswa berikutnya.
6. **Logout**: Opsi untuk menghapus token dan kembali ke Login Screen.

### 4. Instruksi untuk AI
Tolong buatkan **Rencana Implementasi (Implementation Plan)** yang memuat:
1. Struktur folder (`lib/`) yang disarankan.
2. Daftar *dependencies* / *packages* yang harus saya tambahkan ke `pubspec.yaml`.
3. Urutan file mana dulu yang harus saya kerjakan (mulai dari model, network service, state, lalu UI).

Tolong gunakan UI/UX modern (Material 3) yang *clean* dan fungsional.
