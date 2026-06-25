# StudyFlow

StudyFlow adalah platform web produktivitas berbasis Laravel yang dirancang untuk membantu pelajar mengelola tugas, catatan, dan waktu belajar mereka secara efektif. Aplikasi ini menggabungkan manajemen To-Do List yang dinamis dan metode Pomodoro Timer dengan sistem gamifikasi (Quests & Achievements) untuk meningkatkan motivasi pengguna. Dengan poin yang dikumpulkan dari menyelesaikan tugas dan sesi fokus, pengguna dapat membeli tema kustom di Shop untuk mempersonalisasi tampilan ruang kerja mereka sesuai selera.

---

## Fitur Utama

- **Manajemen Tugas (To-Do List)**: Pencatatan tugas dengan prioritas (High, Medium, Low), kategori kustom (Work, Study, Habit), tanggal jatuh tempo, fitur pencarian, serta penyaringan tugas.
- **Pomodoro Timer**: Sesi fokus interaktif yang dapat disesuaikan dengan statistik waktu belajar nyata dan sistem reward koin/poin.
- **Manajemen Catatan (Notes)**: Media penyimpanan cepat langsung di dashboard untuk menyimpan ide atau catatan penting saat belajar.
- **Sistem Gamifikasi (Quests & Achievements)**: Quest harian dan pencapaian yang memberikan hadiah berupa poin/koin untuk membangun konsistensi belajar.
- **Toko Tema (Shop) & Kustomisasi**: Pembelian dan perubahan tema menggunakan poin yang diperoleh untuk mengubah estetika tampilan dashboard.

---

## Cara Menjalankan Aplikasi

Ikuti langkah-langkah di bawah ini untuk menjalankan proyek ini di lingkungan lokal Anda:

### 1. Prasyarat
Pastikan komputer Anda sudah terpasang:
- PHP >= 8.3
- Composer
- Node.js & NPM
- MySQL Database

### 2. Konfigurasi Database
Buat database baru di MySQL dengan nama:
```sql
CREATE DATABASE study_flow;
```

### 3. Setup Project
Salin berkas konfigurasi `.env` dari `.env.example`, pasang semua dependensi, lakukan migrasi database, dan build frontend secara otomatis dengan perintah:
```bash
composer setup
```

### 4. Database Seeding (Penting)
Untuk mengisi data awal berupa kutipan harian (quotes), quest, achievement, serta akun uji coba, jalankan perintah seeder berikut:
```bash
php artisan db:seed
```

### 5. Menjalankan Server Pengembangan
Gunakan perintah pintas di bawah ini untuk menjalankan server Laravel, queue runner, logs, dan Vite secara bersamaan:
```bash
composer dev
```
Setelah berjalan, buka peramban Anda dan akses:
**[http://127.0.0.1:8000](http://127.0.0.1:8000)**

## Anggota Kelompok

Proyek ini dikembangkan oleh kelompok kami yang beranggotakan:

- **2472050** - Reyner Cornelius
- **2472010** - Jonathan Valent Waluya
- **2472045** - Marco Octavian
- **2472051** - Julius Santoso Setiawan
