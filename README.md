# Simple Message WhatsApp - Digital Wedding Invitation

Aplikasi undangan pernikahan digital berbasis web interaktif dengan desain tema dan pengalaman pengguna (UI/UX) yang menyerupai WhatsApp Web / Chat Room modern. Aplikasi ini dirancang untuk memberikan pengalaman yang personal, interaktif, dan mudah digunakan bagi tamu undangan serta calon pengantin.

---

## Daftar Isi
- [Fitur Utama](#fitur-utama)
- [Tech Stack & Persyaratan Sistem](#tech-stack--persyaratan-sistem)
- [Panduan Instalasi & Local Development](#panduan-instalasi--local-development)
- [Panduan Menjalankan Aplikasi](#panduan-menjalankan-aplikasi)
- [Struktur Direktori Project](#struktur-direktori-project)
- [Manajemen Hak Akses (Role, Rule & Gate)](#manajemen-hak-akses-role-rule--gate)
- [Standar Kode & Pengujian](#standar-kode--pengujian)

---

## Fitur Utama

### 1. Pengalaman Tamu Undangan (Public Room)
- **Tampilan Chat WhatsApp Web**: Suasana percakapan grup interaktif lengkap dengan header profil, nama tamu dinamis (`?to=NamaTamu`), status online, dan pesan sambutan.
- **Pesan Multimedia**: Simulasi pesan teks penyambut, pesan suara (*voice note*), video ucapan, serta pesan interaktif lainnya.
- **Drawer Profil Pengantin (Info Grup)**:
  - **Detail Mempelai & Acara**: Informasi lengkap kedua mempelai (orang tua, foto), jadwal akad nikah, resepsi, serta integrasi tautan Google Maps.
  - **Perjalanan Cinta (Love Story Timeline)**: Rangkaian cerita perjalanan cinta dengan tampilan visual 2 kolom (teks cerita di kiri, foto momen di kanan) yang responsif dan konsisten baik di desktop maupun mobile.
  - **Galeri Foto Prewedding**: Grid galeri media foto dengan preview lightbox interaktif.
  - **Hadiah Digital / Kado**: Pilihan transfer bank/e-wallet (dengan fitur salin nomor rekening instan) dan alamat pengiriman kado fisik.
- **Buku Tamu & RSVP Interaktif**: Tamu dapat mengirim ucapan selamat, doa restu, serta konfirmasi kehadiran secara langsung dari antarmuka obrolan.
- **Musik Latar (Background Music)**: Pemutar lagu MP3 latar belakang dengan kontrol putar/jeda mengambang (*floating button*).

### 2. Panel Administrasi (Filament v4 Admin Panel)
- **Multi-Step Form Wizard**: Formulir pembuatan undangan (*Page*) bertahap 4 langkah (Informasi Dasar, Detail Mempelai & Acara, Media & Timeline, Hadiah Digital) yang intuitif.
- **Manajemen Undangan (Page)**: Kustomisasi slug, prefix judul, musik latar, galeri, timeline, dan rekening donasi.
- **Manajemen Pesan & Ucapan**: Pemantauan pesan masuk dan RSVP dari para tamu.
- **Manajemen Pengguna**: Khusus untuk pengguna dengan peran Admin.

---

## Tech Stack & Persyaratan Sistem

### Kebutuhan Sistem (System Requirements)
- **PHP**: `^8.3`
- **Composer**: `v2+`
- **Node.js**: `v18+` & **NPM**: `v9+`
- **Database**: SQLite (default) atau MySQL `^8.0` / MariaDB `^10.4`
- **Web Server**: Apache / Nginx / PHP Built-in Server

### Teknologi Utama
- **Backend Framework**: [Laravel 13](https://laravel.com)
- **Admin Panel**: [Filament v4](https://filamentphp.com)
- **Frontend Reactive Component**: [Livewire 3](https://livewire.laravel.com) & [Livewire Volt](https://livewire.laravel.com/docs/volt)
- **Styling**: [Tailwind CSS](https://tailwindcss.com)
- **Interactivity**: [Alpine.js](https://alpinejs.dev)
- **Code Formatter & Testing**: [Laravel Pint](https://laravel.com/docs/pint) & [PHPUnit 12](https://phpunit.de)

---

## Panduan Instalasi & Local Development

Ikuti langkah-langkah berikut untuk memasang project di lingkungan lokal:

1. **Clone Repository**:
   ```bash
   git clone https://github.com/suhari467/simple-message-whatsapp.git
   cd simple-message-whatsapp
   ```

2. **Pasang Dependensi PHP (Composer)**:
   ```bash
   composer install
   ```

3. **Konfigurasi Environment**:
   Salin file `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   ```
   Sesuaikan konfigurasi database di file `.env`. Secara default, aplikasi siap menggunakan SQLite:
   ```env
   DB_CONNECTION=sqlite
   ```

4. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

5. **Jalankan Database Migration & Seeder**:
   ```bash
   php artisan migrate --seed
   ```

6. **Buat Symlink Storage Publik**:
   ```bash
   php artisan storage:link
   ```

7. **Pasang Dependensi Frontend (NPM) & Build Asset**:
   ```bash
   npm install
   npm run build
   ```

---

## Panduan Menjalankan Aplikasi

Anda dapat menjalankan seluruh layanan development secara bersamaan menggunakan perintah bawaan:

```bash
composer run dev
```

Perintah di atas akan mengeksekusi secara otomatis:
- `php artisan serve` (Web server pada `http://127.0.0.1:8000`)
- `php artisan queue:listen` (Pemroses antrean latar belakang)
- `php artisan pail` (Real-time application log viewer)
- `npm run dev` (Vite dev server dengan Hot Module Replacement)

### Akses Halaman
- **Admin Panel**: Kunjungi [http://127.0.0.1:8000/admin](http://127.0.0.1:8000/admin)
- **Halaman Undangan Tamu**: Kunjungi [http://127.0.0.1:8000/{slug}?to=NamaTamu](http://127.0.0.1:8000/{slug}?to=NamaTamu) (contoh: `http://127.0.0.1:8000/zuhriyani-bima?to=Budi+Santoso`)

---

## Struktur Direktori Project

Berikut adalah gambaran direktori penting pada project ini:

```
simple-message-whatsapp/
├── app/
│   ├── Filament/                  # Konfigurasi Admin Panel Filament v4
│   │   ├── Resources/
│   │   │   ├── Messages/          # Resource manajemen pesan/ucapan
│   │   │   ├── Pages/             # Resource manajemen undangan (Page)
│   │   │   │   ├── Pages/         # ListPages, CreatePage (Wizard), EditPage
│   │   │   │   ├── Schemas/       # PageForm.php (Skema modular form & wizard)
│   │   │   │   └── Tables/        # Konfigurasi tabel halaman
│   │   │   └── Users/             # Resource manajemen user (Khusus Admin)
│   │   └── Widgets/               # Dashboard widget (Countdown, ringkasan data)
│   ├── Livewire/                  # Komponen Livewire interaktif
│   │   └── ChatRoom.php           # Logika utama tampilan room chat undangan
│   ├── Models/                    # Eloquent Models
│   │   ├── User.php               # Model pengguna & otentikasi Filament
│   │   ├── Page.php               # Model undangan pernikahan
│   │   ├── Story.php              # Model timeline kisah cinta
│   │   ├── Gallery.php            # Model galeri foto prewedding
│   │   ├── Donation.php           # Model rekening donasi / alamat kado
│   │   └── Message.php            # Model pesan & konfirmasi RSVP tamu
│   └── Providers/
│       ├── AppServiceProvider.php
│       └── Filament/AdminPanelProvider.php
├── database/
│   ├── factories/                 # Model factories untuk testing
│   ├── migrations/                # Database migrations
│   └── seeders/                   # Seeder data awal
├── resources/
│   ├── views/
│   │   ├── livewire/
│   │   │   └── chatroom.blade.php # Template antarmuka chat WhatsApp undangan
│   │   └── components/            # Blade reusable components
│   └── css/                       # Entry CSS (Tailwind)
├── routes/
│   └── web.php                    # Definisi rute web
└── tests/
    ├── Feature/                   # Feature tests PHPUnit
    └── Unit/                      # Unit tests PHPUnit
```

---

## Manajemen Hak Akses (Role, Rule & Gate)

Aplikasi menerapkan sistem pembatasan akses (*access control*) berbasis Role untuk menjaga keamanan data:

| Peran (*Role*) | Akses Filament Panel | Manajemen User (`UserResource`) | Akses Data Undangan (`Page`) |
| :--- | :---: | :---: | :--- |
| **Admin (`admin`)** | Ya | Ya (Penuh) | Dapat melihat, mengedit, dan mengelola seluruh undangan milik semua pengguna. |
| **User (`user`)** | Ya | Tidak (Ditolak) | Terisolasi (*tenant-like*): Hanya dapat melihat & mengelola undangan miliknya sendiri. |
| **Guest (Tamu)** | Tidak | Tidak | Hanya dapat mengakses URL publik undangan (`/{slug}`). |

### Implementasi Rule & Gate di Kode

1. **Akses Panel Filament (`canAccessPanel`)**:
   Didefinisikan pada model `App\Models\User`:
   ```php
   public function canAccessPanel(Panel $panel): bool
   {
       return true; // Pengguna terdaftar (admin & user) berhak login ke panel
   }
   ```

2. **Proteksi Resource Pengguna (`UserResource::canAccess`)**:
   Halaman kelola akun pengguna dibatasi khusus untuk admin:
   ```php
   public static function canAccess(): bool
   {
       return auth()->user()->role === 'admin';
   }
   ```
   Pengguna dengan peran `user` tidak akan melihat menu ini di navigasi dan akan mendapatkan respons *forbidden* jika mencoba mengakses URL secara langsung.

3. **Isolasi Data Undangan Berdasarkan Kepemilikan (`PageResource::getEloquentQuery`)**:
   Data undangan yang ditampilkan di tabel difilter secara otomatis:
   ```php
   public static function getEloquentQuery(): Builder
   {
       $query = parent::getEloquentQuery();

       if (auth()->user()->role !== 'admin') {
           $query->where('user_id', auth()->id());
       }

       return $query;
   }
   ```

4. **Binding Otomatis Pembuat Undangan (`CreatePage::mutateFormDataBeforeCreate`)**:
   Saat pengguna membuat undangan baru, kolom `user_id` secara otomatis diisi dengan ID user yang sedang login untuk mencegah manipulasi data antar pengguna:
   ```php
   protected function mutateFormDataBeforeCreate(array $data): array
   {
       $data['user_id'] = auth()->id();
       return $data;
   }
   ```

---

## Standar Kode & Pengujian

### Menjalankan Unit & Feature Test
Aplikasi menggunakan **PHPUnit 12**. Untuk menjalankan seluruh suite pengujian:
```bash
php artisan test --compact
```

### Format Standar Kode (Laravel Pint)
Setiap perubahan kode PHP wajib mengikuti standar PSR-12 menggunakan Laravel Pint:
```bash
vendor/bin/pint --dirty --format agent
```

---

## Lisensi
Aplikasi ini dilisensikan di bawah [MIT License](LICENSE).
