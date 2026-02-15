# Resto Mobile App

Aplikasi restoran berbasis mobile untuk pemesanan menu dan manajemen transaksi.  
Project ini dibuat menggunakan Ionic Vue dan backend PHP dengan database MySQL.

## Fitur

- Login dan autentikasi pengguna  
- Dashboard sesuai role (Owner, Manajer, Kasir, Kitchen, Pelanggan)  
- Manajemen menu  
- Pemesanan makanan  
- Laporan transaksi  
- API backend berbasis PHP  

## Teknologi

Frontend:
- Ionic Vue
- TypeScript
- Vite

Backend:
- PHP
- MySQL
- REST API

Tools:
- XAMPP / Laragon
- Git

## Cara Menjalankan Project

1. Clone repository
  git clone https://github.com/Aldyy002-ctrl/resto-mobile-app.git

2. Masuk ke folder project
cd resto-mobile-app

3. Install dependency frontend
npm install

4. Jalankan frontend
ionic serve

5. Setup database
- Import file SQL dari folder:
backend-php/database/db_restoran.sql

- Atur koneksi database di:
backend-php/config/database.php

6. Jalankan backend di localhost (XAMPP atau Laragon)

## Struktur Project
backend-php/ -> API dan koneksi database
src/ -> Source code frontend
public/ -> Asset publik
tests/ -> Testing

<img width="539" height="896" alt="image" src="https://github.com/user-attachments/assets/2d24c660-859b-4506-8ce0-9cbfc0a20332" />
<img width="539" height="900" alt="image" src="https://github.com/user-attachments/assets/5aee55be-96b6-435f-b69d-380160270529" />

MIT License

