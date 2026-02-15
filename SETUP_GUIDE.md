# Panduan Setup Login-Register App dengan Database

## Prerequisites
- Laragon (sudah terinstall dan berjalan)
- Database `db_restoran` sudah ada di MySQL

## Langkah 1: Setup Backend PHP

### 1.1. Copy Backend ke Laragon
1. Buka folder `d:\Projects\login-register\backend-php`
2. Copy seluruh folder `backend-php` ke `C:\laragon\www\`
3. Rename menjadi `login-api` (atau nama lain sesuai keinginan)
4. Path akhir: `C:\laragon\www\login-api`

### 1.2. Konfigurasi Database
1. Buka file `C:\laragon\www\login-api\config\config.php`
2. Pastikan konfigurasi sesuai:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // Kosong untuk Laragon default
   define('DB_NAME', 'db_restoran');
   ```

### 1.3. Test Koneksi Database
1. Pastikan Laragon sudah **Start All**
2. Buka browser dan akses: `http://localhost/login-api/test-connection.php`
3. Jika berhasil, akan muncul:
   ```json
   {
     "success": true,
     "message": "Database connected successfully!",
     "data": {
       "database": "db_restoran",
       "user_count": 0
     }
   }
   ```

### 1.4. Buat User Test (Opsional)
Jika belum ada user di tabel `user`, buat user test melalui phpMyAdmin:

```sql
INSERT INTO user (username, password, role) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Password: password (sudah di-hash)

-- Atau untuk plain text (tidak disarankan):
INSERT INTO user (username, password, role) 
VALUES ('test', 'test123', 'user');
```

## Langkah 2: Update Frontend Configuration

### 2.1. Update API URL (Jika perlu)
Jika Anda menggunakan nama folder berbeda, update file:
`d:\Projects\login-register\src\services\api.ts`

Cari baris:
```typescript
baseURL: 'http://localhost/login-api/api',
```

Ganti `login-api` dengan nama folder Anda.

### 2.2. Restart Ionic Dev Server
Jika `ionic serve` sudah berjalan, stop dan start ulang:
1. Tekan `Ctrl+C` di terminal
2. Jalankan lagi: `ionic serve`

## Langkah 3: Testing

### 3.1. Test Login di Browser
1. Buka `http://localhost:8100` (atau port yang digunakan Ionic)
2. Masukkan:
   - Username: `admin` (atau username yang Anda buat)
   - Password: `password` (atau password yang Anda buat)
3. Klik **Login**
4. Jika berhasil:
   - Muncul notifikasi "Login berhasil"
   - Redirect ke Tab 3
   - Token tersimpan di localStorage

### 3.2. Cek localStorage
1. Buka Developer Tools (F12)
2. Tab **Application** > **Local Storage** > `http://localhost:8100`
3. Periksa:
   - `auth_token`: Token JWT
   - `user_data`: Data user (id_user, username, role)

### 3.3. Test dengan CURL (Opsional)
```bash
# Test Login
curl -X POST http://localhost/login-api/api/auth.php/login \
  -H "Content-Type: application/json" \
  -d "{\"username\":\"admin\",\"password\":\"password\"}"

# Test Register
curl -X POST http://localhost/login-api/api/auth.php/register \
  -H "Content-Type: application/json" \
  -d "{\"username\":\"newuser\",\"password\":\"newpass123\",\"role\":\"user\"}"
```

## Troubleshooting

### Error: "Database connection error"
- Pastikan Laragon sudah Start All
- Cek MySQL service berjalan
- Cek konfigurasi di `config.php`
- Cek nama database `db_restoran` sudah ada

### Error: "CORS policy"
- Pastikan file `.htaccess` ada di `C:\laragon\www\login-api`
- Pastikan Apache mod_headers enabled di Laragon
- Restart Apache di Laragon

### Error: "Cannot find module '@/services/auth'"
- Pastikan file `src/services/api.ts` dan `src/services/auth.ts` sudah ada
- Restart Ionic dev server

### Login gagal: "Username atau password salah"
- Cek apakah user sudah ada di database
- Jika password sudah di-hash, gunakan `password_hash()` untuk generate
- Atau gunakan plain text untuk testing (tidak aman untuk production)

### Ionic tidak bisa connect ke backend
- Cek baseURL di `src/services/api.ts`
- Pastikan port dan folder name benar
- Buka Network tab di DevTools untuk lihat request

## Status & Next Steps

✅ Backend PHP API (Login, Register, Verify Token)
✅ Frontend Login Form dengan Username/Password
✅ JWT Token Authentication
✅ Error Handling & Validation
✅ Loading States

### Next Steps (Opsional):
- [ ] Buat halaman Register (Tab2Page.vue)
- [ ] Buat halaman Dashboard/Profile (Tab3Page.vue)
- [ ] Tambah Route Guards untuk protected pages
- [ ] Implementasi Logout functionality
- [ ] Hash password yang sudah ada di database
- [ ] Add Remember Me functionality
- [ ] Add Forgot Password feature
