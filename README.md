
# Sistem Pemesanan Makanan Online 

Sistem Pemesanan Makanan Online berbasis web yang dibangun menggunakan PHP untuk memesanan makanan secara online menggunakan sistem keranjang dan order.

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Struktur Database](#struktur-database)
- [Run Project](#run-project)
- [Authors](#authors)

## Fitur Utama

Admin
- Dashboard Pesanan
- Manage Produk (Products)
- Manage Pengguna (User)
- Manage Pesanan (Order)
- Laporan Pesanan

Customer
- Cek Produk yang tersedia
- Sistem Keranjang
- Cek Status Pesanan


## Struktur Database

| Tabel | Keterangan |
|-------|-------------|
| users | Data login (username, password, role) |
| categories | Data Kategori untuk Produk (id, nama)|
| products | Data Produk (id, category_id, name, stock, price) |
| cart | Data Keranjang untuk Customer (id, customer_id, product_id, quantity) |
| orders | Data Order untuk Customer (id, customer_id, status, payment_status) |
| order_items | Data Order untuk Admin (id, order_id, status) |
| payments | Data Pembayaran (id, order_id, payment_status) |
## Run Project


### 1. Persiapan
- Pastikan Anda memiliki **XAMPP** terinstal di pc / laptop Anda.

### 2. Copy Project
Copy folder `sistem-pemesanan-makanan-online` ke dalam folder `/xampp/htdocs` 

### 3. Import Database
- Buka `http://localhost/phpmyadmin`
- Klik **New** Lalu Pilih tab **Import**
- Pilih file `/sql/food_ordering.sql` dari folder proyek
- Klik **Go**

### 4. Konfigurasi
Buka file `includes/config.php` dan sesuaikan dengan setting server Anda:

```php
<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'food_ordering');

// Application configuration
define('SITE_NAME', 'Rumah Makan Sarwaguna');
define('BASE_URL', 'http://localhost/sistem-pemesanan-makanan-online/');
```
### 5. Jalankan Aplikasi
Akses URL ini dibrowser `http://localhost/sistem-pemesanan-makanan-online/index.php`
## Authors
Kelompok 2 PBO
- [Chendra Frendianata](https://www.github.com/ChendFrend) - 422025003
- [Adib Sarwaguna](#) - 422025006
- [Yoel Christanto Nugroho](https://github.com/yoel422025011-ui) - 422025011
- [Felix Ji Sui Thian](https://github.com/Freiiiii) - 422025025