# Mobile API Integration Plan
## Teacher Attendance System

---

## ✅ Implementation Status

**Status:** ✅ **COMPLETED & READY FOR MOBILE INTEGRATION**

**Last Updated:** 2026-08-04 23:40 WIB

**Backend Implementation:**
- ✅ TeacherAttendanceController created with all CRUD methods
- ✅ AuthController extended with user() method
- ✅ API routes registered with Sanctum middleware
- ✅ TeacherAttendance model updated (datetime cast)
- ✅ All PHP syntax validated
- ✅ Routes verified and registered
- ✅ Attendance history: date validation + inclusive `date_from`/`date_to` filtering
- ✅ Teacher list: photo paths automatically resolved to absolute Storage URLs
- ✅ Feature tests covering date filters, validation errors, and photo URL contract

**API Endpoints Ready:**
- ✅ `POST /api/auth/login`
- ✅ `GET /api/auth/user`
- ✅ `POST /api/auth/logout`
- ✅ `GET /api/teachers`
- ✅ `POST /api/teacher-attendance`
- ✅ `GET /api/teacher-attendance`
- ✅ `DELETE /api/teacher-attendance/{id}`

**Recent Changes (2026-08-04 23:40):**
- `TeacherAttendanceController::index()` validates `date_from`/`date_to` (`Y-m-d`, `date_from` ≤ `date_to`), returns 422 on invalid input.
- `TeacherAttendanceController::teachers()` + `index()` resolve relative `photo` paths via `Storage::disk('public')->url()`. Null stays null; absolute URLs unchanged.
- Tests: [`tests/Feature/TeacherAttendanceApiTest.php`](../tests/Feature/TeacherAttendanceApiTest.php).

---

## 📋 Executive Summary

Dokumen ini berisi plan teknis lengkap untuk API mobile Teacher Attendance System yang akan diintegrasikan dengan aplikasi mobile di workspace terpisah. Sistem ini menggunakan Laravel 11 + Sanctum untuk autentikasi.

**Base URL:** `https://app2.yiscalazhar.web.id/api`

**Implementation Files:**
- [`app/Http/Controllers/Api/TeacherAttendanceController.php`](../app/Http/Controllers/Api/TeacherAttendanceController.php)
- [`app/Http/Controllers/Api/AuthController.php`](../app/Http/Controllers/Api/AuthController.php)
- [`routes/api.php`](../routes/api.php)
- [`app/Models/TeacherAttendance.php`](../app/Models/TeacherAttendance.php)

---

## 🏗️ Architecture Overview

```
Mobile App (Workspace Terpisah)
       ↓
   HTTPS/JSON
       ↓
Laravel API (Sanctum Auth)
       ↓
MySQL Database
```

### Tech Stack
- **Backend:** Laravel 11
- **Auth:** Laravel Sanctum (Token-based)
- **Database:** MySQL
- **Response Format:** JSON

---

## 🔐 Authentication Flow

### 1. Login Endpoint

**Endpoint:** `POST /api/auth/login`

**Request Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "email": "admin@example.com",
  "password": "password123"
}
```

**Success Response (200 OK):**
```json
{
  "status": "success",
  "message": "Login berhasil",
  "data": {
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com",
      "roles": ["admin"]
    },
    "token": "1|laravel_sanctum_token_here...",
    "token_type": "Bearer"
  }
}
```

**Error Responses:**

**401 Unauthorized** (Kredensial salah):
```json
{
  "status": "error",
  "message": "Email atau password salah",
  "code": "INVALID_CREDENTIALS"
}
```

**422 Unprocessable Entity** (Validasi gagal):
```json
{
  "status": "error",
  "message": "Validasi gagal",
  "errors": {
    "email": ["Email field is required"],
    "password": ["Password field is required"]
  }
}
```

### 2. Logout Endpoint

**Endpoint:** `POST /api/auth/logout`

**Request Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Success Response (200 OK):**
```json
{
  "status": "success",
  "message": "Logout berhasil"
}
```

### 3. Get Current User

**Endpoint:** `GET /api/auth/user`

**Request Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Success Response (200 OK):**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "roles": ["admin"],
    "created_at": "2026-01-01T00:00:00.000000Z"
  }
}
```

---

## 👨🏫 Teacher Attendance API Endpoints

### 1. Get All Teachers (Dropdown)

**Endpoint:** `GET /api/teachers`

**Request Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Query Parameters:**
- `search` (optional): Filter by name
- `per_page` (optional): Items per page (default: 50)

**Success Response (200 OK):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Ustadz Ahmad",
      "photo": "https://app2.yiscalazhar.web.id/storage/teachers/photo.jpg",
      "gender": "pria",
      "tempat_lahir": "Jakarta",
      "tanggal_lahir": "1985-05-15"
    },
    {
      "id": 2,
      "name": "Ustadzah Fatimah",
      "photo": null,
      "gender": "wanita",
      "tempat_lahir": "Bandung",
      "tanggal_lahir": "1990-08-20"
    }
  ],
  "meta": {
    "total": 25,
    "per_page": 50,
    "current_page": 1,
    "last_page": 1
  }
}
```

**Photo URL contract:**
- Relative stored path (e.g. `teachers/photo.jpg`) is returned as absolute public-disk URL: `https://app2.yiscalazhar.web.id/storage/teachers/photo.jpg`.
- Missing photo (`null` or empty) is returned as `null`.
- Already absolute URL is returned unchanged.
- Same contract applies to `teacher.photo` nested in attendance-history responses.
- No new photo endpoint is needed; mobile continues using `GET /api/teachers`.

### 2. Create Teacher Attendance

**Endpoint:** `POST /api/teacher-attendance`

**Request Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "teacher_id": 1,
  "attendance_date": "2026-08-04 13:30:00"
}
```

**Validation Rules:**
- `teacher_id`: required, exists in teachers table
- `attendance_date`: required, datetime format (Y-m-d H:i:s)

**Success Response (201 Created):**
```json
{
  "status": "success",
  "message": "Absensi ustadz berhasil dicatat",
  "data": {
    "id": 15,
    "teacher_id": 1,
    "teacher_name": "Ustadz Ahmad",
    "attendance_date": "2026-08-04 13:30:00",
    "created_at": "2026-08-04T06:30:00.000000Z"
  }
}
```

**Error Responses:**

**422 Unprocessable Entity** (Validasi gagal):
```json
{
  "status": "error",
  "message": "Validasi gagal",
  "errors": {
    "teacher_id": ["Teacher not found"],
    "attendance_date": ["Format datetime tidak valid"]
  }
}
```

**409 Conflict** (Duplikat absensi):
```json
{
  "status": "error",
  "message": "Absensi ustadz sudah tercatat pada tanggal ini",
  "code": "DUPLICATE_ATTENDANCE"
}
```

### 3. Get Teacher Attendance History

**Endpoint:** `GET /api/teacher-attendance`

**Request Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Query Parameters:**
- `teacher_id` (optional, integer): Filter by teacher
- `date_from` (optional, `Y-m-d`): Start date — inclusive
- `date_to` (optional, `Y-m-d`): End date — inclusive
- `per_page` (optional, integer, 1–100): Items per page (default: 20)
- `page` (optional, integer): Page number (default: 1)

**Validation Rules:**
- `date_from` and `date_to` must use `Y-m-d` format (e.g. `2026-08-04`).
- `date_from` must not be greater (later calendar date) than `date_to`.
- Invalid query parameters return `422` with structured `errors` object.

**Success Response (200 OK):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 15,
      "teacher": {
        "id": 1,
        "name": "Ustadz Ahmad",
        "photo": "https://app2.yiscalazhar.web.id/storage/teachers/photo.jpg"
      },
      "attendance_date": "2026-08-04 13:30:00",
      "created_at": "2026-08-04T06:30:00.000000Z"
    }
  ],
  "meta": {
    "total": 150,
    "per_page": 20,
    "current_page": 1,
    "last_page": 8
  },
  "links": {
    "first": "https://app2.yiscalazhar.web.id/api/teacher-attendance?page=1",
    "last": "https://app2.yiscalazhar.web.id/api/teacher-attendance?page=8",
    "prev": null,
    "next": "https://app2.yiscalazhar.web.id/api/teacher-attendance?page=2"
  }
}
```

**Error Response (422 — invalid date format):**
```json
{
  "status": "error",
  "message": "Parameter query tidak valid.",
  "errors": {
    "date_from": ["The date from field must match the format Y-m-d."]
  }
}
```

**Error Response (422 — date range invalid):**
```json
{
  "status": "error",
  "message": "date_from tidak boleh lebih besar dari date_to."
}
```

**Error Response (401 — not authenticated):**
```json
{
  "message": "Unauthenticated."
}
```

### 4. Delete Teacher Attendance

**Endpoint:** `DELETE /api/teacher-attendance/{id}`

**Request Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Success Response (200 OK):**
```json
{
  "status": "success",
  "message": "Absensi berhasil dihapus"
}
```

**Error Response (404 Not Found):**
```json
{
  "status": "error",
  "message": "Data absensi tidak ditemukan",
  "code": "NOT_FOUND"
}
```

---

## 📊 Additional Endpoints (Already Implemented)

### 1. Student Attendance Scanner

**Endpoint:** `POST /api/attendance/scan`

**Request Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "qr_data": "uuid_civitas|uuid_schedule"
}
```

### 2. PPAB Attendance Scanner

**Endpoint:** `POST /api/ppab/attendance/scan`

**Request Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "member_uuid": "uuid_peserta_ppab"
}
```

---

## 🔨 Implementation Plan (Backend)

### Phase 1: Setup API Routes
**File:** [`routes/api.php`](routes/api.php)

```php
// Existing: Auth routes
Route::post('/auth/login', [AuthController::class, 'login']);

// NEW: Protected routes dengan Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Auth user info
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    
    // Teachers (untuk dropdown)
    Route::get('/teachers', [TeacherAttendanceController::class, 'getTeachers']);
    
    // Teacher Attendance CRUD
    Route::post('/teacher-attendance', [TeacherAttendanceController::class, 'store']);
    Route::get('/teacher-attendance', [TeacherAttendanceController::class, 'index']);
    Route::delete('/teacher-attendance/{id}', [TeacherAttendanceController::class, 'destroy']);
});
```

### Phase 2: Create Controller
**File:** `app/Http/Controllers/Api/TeacherAttendanceController.php`

**New Controller dengan methods:**
- `getTeachers()` - List semua teachers untuk dropdown
- `store()` - Create attendance record
- `index()` - List attendance history dengan filter
- `destroy()` - Delete attendance record

### Phase 3: Update Auth Controller
**File:** [`app/Http/Controllers/Api/AuthController.php`](app/Http/Controllers/Api/AuthController.php:1)

**Add methods:**
- `user()` - Get current authenticated user
- `logout()` - Revoke current token

### Phase 4: Database Migration (if needed)
**Check existing table:** `teacher_attendances`

**Required columns:**
- `id` (primary key)
- `teacher_id` (foreign key to teachers.id)
- `attendance_date` (datetime)
- `created_by` (nullable, user_id yang input)
- `created_at`, `updated_at` (timestamps)

**Add column if missing:**
```php
Schema::table('teacher_attendances', function (Blueprint $table) {
    if (!Schema::hasColumn('teacher_attendances', 'created_by')) {
        $table->unsignedBigInteger('created_by')->nullable()->after('attendance_date');
    }
});
```

### Phase 5: Update Models

**File:** [`app/Models/TeacherAttendance.php`](app/Models/TeacherAttendance.php:1)

**Add relationships:**
```php
public function teacher()
{
    return $this->belongsTo(Teacher::class);
}

public function creator()
{
    return $this->belongsTo(User::class, 'created_by');
}
```

**File:** [`app/Models/Teacher.php`](app/Models/Teacher.php:1)

**Ensure relationship:**
```php
public function attendances()
{
    return $this->hasMany(TeacherAttendance::class);
}
```

### Phase 6: Form Request Validation
**File:** `app/Http/Requests/StoreTeacherAttendanceRequest.php`

```php
public function rules()
{
    return [
        'teacher_id' => 'required|exists:teachers,id',
        'attendance_date' => 'required|date_format:Y-m-d H:i:s',
    ];
}
```

### Phase 7: API Resource
**File:** `app/Http/Resources/TeacherAttendanceResource.php`

Untuk format response yang konsisten.

---

## 🔒 Security Considerations

### 1. Rate Limiting
**File:** `app/Http/Kernel.php`

```php
'api' => [
    'throttle:60,1', // 60 requests per minute
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

### 2. Token Expiration
**File:** `config/sanctum.php`

```php
'expiration' => 60 * 24, // Token expires in 24 hours (in minutes)
```

### 3. CORS Configuration
**File:** `config/cors.php`

```php
'paths' => ['api/*'],
'allowed_origins' => [
    'http://localhost:*',  // Flutter debug
    'https://your-mobile-app.com'  // Production mobile app
],
'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => false,
```

### 4. Input Validation
- Semua input HARUS divalidasi menggunakan Form Requests
- Gunakan `exists:table,column` untuk foreign key validation
- Sanitize datetime input dengan `date_format` rule

### 5. Authorization
Tambahkan middleware/policy untuk memastikan user memiliki permission yang sesuai:

```php
Route::middleware(['auth:sanctum', 'permission:manage-teacher-attendance'])->group(function () {
    // Teacher attendance routes
});
```

---

## 📱 Mobile Integration Guide

### 1. Environment Configuration

**Mobile App `.env` atau config file:**

```dart
// lib/config/api_config.dart
class ApiConfig {
  static const String baseUrl = 'https://app2.yiscalazhar.web.id/api';
  static const int connectTimeout = 30000; // 30 seconds
  static const int receiveTimeout = 30000;
}
```

### 2. HTTP Client Setup (Flutter Example)

**Install packages:**
```yaml
dependencies:
  http: ^1.1.0
  flutter_secure_storage: ^9.0.0
```

**API Service:**
```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class ApiService {
  final String baseUrl = 'https://app2.yiscalazhar.web.id/api';
  String? _token;
  
  Future<void> setToken(String token) async {
    _token = token;
    // Simpan ke secure storage
  }
  
  Future<Map<String, String>> _getHeaders() async {
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    
    if (_token != null) {
      headers['Authorization'] = 'Bearer $_token';
    }
    
    return headers;
  }
  
  Future<http.Response> post(String endpoint, Map<String, dynamic> body) async {
    final url = Uri.parse('$baseUrl$endpoint');
    final headers = await _getHeaders();
    
    return await http.post(
      url,
      headers: headers,
      body: jsonEncode(body),
    );
  }
  
  Future<http.Response> get(String endpoint, {Map<String, String>? queryParams}) async {
    var url = Uri.parse('$baseUrl$endpoint');
    if (queryParams != null) {
      url = url.replace(queryParameters: queryParams);
    }
    
    final headers = await _getHeaders();
    return await http.get(url, headers: headers);
  }
}
```

### 3. Authentication Flow (Mobile)

```dart
class AuthService {
  final ApiService _apiService = ApiService();
  
  Future<bool> login(String email, String password) async {
    try {
      final response = await _apiService.post('/auth/login', {
        'email': email,
        'password': password,
      });
      
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final token = data['data']['token'];
        
        // Simpan token
        await _apiService.setToken(token);
        return true;
      }
      
      return false;
    } catch (e) {
      print('Login error: $e');
      return false;
    }
  }
  
  Future<void> logout() async {
    await _apiService.post('/auth/logout', {});
    // Clear token from storage
  }
}
```

### 4. Teacher Attendance Feature (Mobile)

```dart
class TeacherAttendanceService {
  final ApiService _apiService = ApiService();
  
  // Get teachers untuk dropdown
  Future<List<Teacher>> getTeachers() async {
    final response = await _apiService.get('/teachers');
    
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      final List<dynamic> teachers = data['data'];
      return teachers.map((json) => Teacher.fromJson(json)).toList();
    }
    
    throw Exception('Failed to load teachers');
  }
  
  // Submit attendance
  Future<bool> submitAttendance(int teacherId, DateTime dateTime) async {
    final formattedDate = DateFormat('yyyy-MM-dd HH:mm:ss').format(dateTime);
    
    final response = await _apiService.post('/teacher-attendance', {
      'teacher_id': teacherId,
      'attendance_date': formattedDate,
    });
    
    return response.statusCode == 201;
  }
  
  // Get attendance history
  Future<List<TeacherAttendance>> getAttendanceHistory({
    int? teacherId,
    DateTime? dateFrom,
    DateTime? dateTo,
  }) async {
    Map<String, String> queryParams = {};
    
    if (teacherId != null) {
      queryParams['teacher_id'] = teacherId.toString();
    }
    if (dateFrom != null) {
      queryParams['date_from'] = DateFormat('yyyy-MM-dd').format(dateFrom);
    }
    if (dateTo != null) {
      queryParams['date_to'] = DateFormat('yyyy-MM-dd').format(dateTo);
    }
    
    final response = await _apiService.get('/teacher-attendance', queryParams: queryParams);
    
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      final List<dynamic> attendances = data['data'];
      return attendances.map((json) => TeacherAttendance.fromJson(json)).toList();
    }
    
    throw Exception('Failed to load attendance history');
  }
}
```

### 5. Error Handling (Mobile)

```dart
class ApiException implements Exception {
  final String message;
  final int? statusCode;
  final String? code;
  
  ApiException(this.message, {this.statusCode, this.code});
  
  @override
  String toString() => message;
}

// Usage dalam service
Future<void> submitAttendance(int teacherId, DateTime dateTime) async {
  try {
    final response = await _apiService.post('/teacher-attendance', {
      'teacher_id': teacherId,
      'attendance_date': formattedDate,
    });
    
    if (response.statusCode == 201) {
      return;
    }
    
    final data = jsonDecode(response.body);
    
    if (response.statusCode == 422) {
      throw ApiException(
        'Validasi gagal: ${data['message']}',
        statusCode: 422,
      );
    }
    
    if (response.statusCode == 409) {
      throw ApiException(
        data['message'],
        statusCode: 409,
        code: data['code'],
      );
    }
    
    throw ApiException('Server error', statusCode: response.statusCode);
    
  } catch (e) {
    if (e is ApiException) rethrow;
    throw ApiException('Network error: $e');
  }
}
```

### 6. Token Management & Auto-Refresh

```dart
class TokenManager {
  final FlutterSecureStorage _storage = FlutterSecureStorage();
  
  Future<void> saveToken(String token) async {
    await _storage.write(key: 'auth_token', value: token);
  }
  
  Future<String?> getToken() async {
    return await _storage.read(key: 'auth_token');
  }
  
  Future<void> clearToken() async {
    await _storage.delete(key: 'auth_token');
  }
  
  // Interceptor untuk auto-refresh jika token expired
  Future<http.Response> requestWithRetry(Future<http.Response> Function() request) async {
    var response = await request();
    
    if (response.statusCode == 401) {
      // Token expired, redirect to login
      // atau implement refresh token mechanism
      await clearToken();
      // Navigate to login screen
    }
    
    return response;
  }
}
```

---

## 📝 Testing Guide

### Backend Testing (Laravel)

**File:** `tests/Feature/TeacherAttendanceApiTest.php`

```php
public function test_can_create_teacher_attendance()
{
    $user = User::factory()->create();
    $teacher = Teacher::factory()->create();
    
    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/teacher-attendance', [
            'teacher_id' => $teacher->id,
            'attendance_date' => '2026-08-04 13:30:00',
        ]);
    
    $response->assertStatus(201)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => ['id', 'teacher_id', 'attendance_date']
        ]);
}

public function test_cannot_create_duplicate_attendance()
{
    // Test duplicate prevention
}

public function test_requires_authentication()
{
    $response = $this->postJson('/api/teacher-attendance', []);
    $response->assertStatus(401);
}
```

### Mobile Testing (Flutter)

```dart
void main() {
  group('TeacherAttendanceService', () {
    test('should submit attendance successfully', () async {
      final service = TeacherAttendanceService();
      
      final result = await service.submitAttendance(
        1,
        DateTime.parse('2026-08-04 13:30:00'),
      );
      
      expect(result, true);
    });
    
    test('should throw error on duplicate attendance', () async {
      final service = TeacherAttendanceService();
      
      expect(
        () => service.submitAttendance(1, DateTime.now()),
        throwsA(isA<ApiException>()),
      );
    });
  });
}
```

---

## 🚀 Deployment Checklist

### Backend (Laravel)

- [ ] Update `.env` dengan database credentials production
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate `APP_KEY` baru
- [ ] Configure `SANCTUM_STATEFUL_DOMAINS`
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan migrate --force`
- [ ] Set proper file permissions (storage, cache)
- [ ] Configure SSL/HTTPS
- [ ] Enable CORS untuk mobile domain
- [ ] Set up rate limiting
- [ ] Configure queue workers (jika ada)
- [ ] Set up monitoring & logging

### Mobile App

- [ ] Update base URL ke production endpoint
- [ ] Test semua endpoints di production
- [ ] Implement error logging (Sentry, Firebase Crashlytics)
- [ ] Add network security configuration (Android)
- [ ] Configure App Transport Security (iOS)
- [ ] Test offline handling
- [ ] Implement token refresh mechanism
- [ ] Add analytics tracking
- [ ] Test on multiple devices
- [ ] Prepare for app store submission

---

## 📊 Database Schema Reference

### Table: `teachers`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `photo` | string | URL foto ustadz |
| `name` | string | Nama lengkap |
| `tempat_lahir` | string | Tempat lahir |
| `tanggal_lahir` | date | Tanggal lahir |
| `gender` | enum | 'pria' atau 'wanita' |
| `education_history` | text | Riwayat pendidikan |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### Table: `teacher_attendances`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `teacher_id` | bigint | FK to teachers.id |
| `attendance_date` | datetime | Waktu kehadiran |
| `created_by` | bigint | FK to users.id (nullable) |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

**Indexes:**
- `teacher_id` (foreign key)
- `attendance_date` (untuk query by date)
- Composite unique: (`teacher_id`, `attendance_date`) untuk prevent duplicate

---

## 🐛 Common Issues & Solutions

### Issue 1: CORS Error
**Symptom:** Mobile app tidak bisa hit API, error "CORS policy"

**Solution:**
```php
// config/cors.php
'paths' => ['api/*'],
'allowed_origins' => ['*'], // or specify mobile app domain
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
```

### Issue 2: 401 Unauthorized setelah login
**Symptom:** Login sukses tapi endpoint lain return 401

**Solution:**
- Pastikan token disimpan dengan benar di mobile
- Cek header Authorization: `Bearer {token}` (perhatikan spasi)
- Verify token di database table `personal_access_tokens`

### Issue 3: 419 CSRF Token Mismatch
**Symptom:** API return 419 error

**Solution:**
- API routes tidak memerlukan CSRF protection
- Pastikan routes ada di `routes/api.php`, bukan `routes/web.php`

### Issue 4: Datetime Format Error
**Symptom:** Validation error pada attendance_date

**Solution:**
```dart
// Mobile: gunakan format yang tepat
final formattedDate = DateFormat('yyyy-MM-dd HH:mm:ss').format(dateTime);

// Backend validation:
'attendance_date' => 'required|date_format:Y-m-d H:i:s'
```

### Issue 5: Token Expired
**Symptom:** App tiba-tiba logout atau error 401

**Solution:**
- Implement token refresh mechanism
- Atau set token expiration lebih panjang di `config/sanctum.php`
- Handle 401 error dan redirect ke login

---

## 📚 API Response Standard

### Success Response Format

```json
{
  "status": "success",
  "message": "Optional success message",
  "data": {
    // Response data here
  },
  "meta": {
    // Optional pagination/metadata
  }
}
```

### Error Response Format

```json
{
  "status": "error",
  "message": "Human-readable error message",
  "code": "ERROR_CODE", // Optional machine-readable code
  "errors": {
    // Optional validation errors
    "field_name": ["Error message"]
  }
}
```

### HTTP Status Codes

| Code | Meaning | Usage |
|------|---------|-------|
| 200 | OK | GET, DELETE success |
| 201 | Created | POST success (new resource) |
| 400 | Bad Request | Malformed request |
| 401 | Unauthorized | Missing/invalid token |
| 403 | Forbidden | Valid token, insufficient permission |
| 404 | Not Found | Resource not found |
| 409 | Conflict | Duplicate data |
| 422 | Unprocessable Entity | Validation failed |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server-side error |

---

## 🎯 Next Steps

### Immediate Actions

1. **Review existing codebase**
   - Check [`routes/api.php`](routes/api.php) untuk routes yang sudah ada
   - Review [`app/Http/Controllers/Api/AuthController.php`](app/Http/Controllers/Api/AuthController.php:1)
   - Check [`app/Models/TeacherAttendance.php`](app/Models/TeacherAttendance.php:1) dan [`app/Models/Teacher.php`](app/Models/Teacher.php:1)

2. **Create new controller**
   - Buat `app/Http/Controllers/Api/TeacherAttendanceController.php`
   - Implement 4 methods utama (getTeachers, store, index, destroy)

3. **Add API routes**
   - Tambahkan routes baru ke [`routes/api.php`](routes/api.php) dengan Sanctum middleware

4. **Update AuthController**
   - Tambah method `user()` dan `logout()` ke [`AuthController`](app/Http/Controllers/Api/AuthController.php:1)

5. **Database migration (if needed)**
   - Check apakah perlu tambah column `created_by` di table `teacher_attendances`

6. **Testing**
   - Buat Feature tests untuk API endpoints baru
   - Test menggunakan Postman/Insomnia

7. **Documentation**
   - Generate API documentation (Swagger/Postman Collection)
   - Share dengan mobile development team

### Mobile Team Actions

1. Setup Flutter project di workspace terpisah
2. Implement ApiService dengan base URL production
3. Implement authentication flow & token management
4. Build Teacher Attendance UI (dropdown + datetime picker)
5. Implement error handling & loading states
6. Test integration dengan API staging
7. Deploy ke test environment

---

## 📞 Support & Contact

**Backend Developer:** Your Team
**Mobile Developer:** Mobile Team (separate workspace)

**API Base URL:** `https://app2.yiscalazhar.web.id/api`
**Documentation:** This file + Postman Collection (to be created)

---

## 📄 Document Version

- **Version:** 1.0
- **Last Updated:** 2026-08-04
- **Author:** System Architect
- **Status:** Draft - Ready for Review

---

**Note:** Dokumen ini adalah PLAN teknis untuk implementasi API. Belum ada eksekusi kode. Setelah plan disetujui, implementasi akan dilakukan di mode `code`.
