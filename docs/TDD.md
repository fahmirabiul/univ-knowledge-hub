# TECHNICAL DESIGN DOCUMENT

**1. Deskripsi & Peran Arsitektur**
Bertindak sebagai aplikasi operasional murni. Sistem ini mendelegasikan proses *login* ke Sistem 1, dan bertindak sebagai *Subscriber/Consumer* yang mendengarkan *update* data dari Redis.

**2. Tech Stack & Infrastructure**

- **Environment:** Docker (Laravel Sail).
- **Core:** Laravel 13, PHP 8.3.
- **Authentication:** Laravel Socialite (Kustomisasi *driver* untuk membaca token dari IAM Portal).
- **Background Worker (Subscriber):** Redis Queue & Laravel Horizon (Opsional, untuk *monitoring job*).
- **File Storage:** Flysystem dengan *driver* lokal (direktori `storage/app/private`).

**3. Skema Database Utama (ERD Operasional)**

- `users` (Local Mirror): Tidak ada kolom *password*. (Kolom: `id`, `sso_id` (UUID dari Sistem 1), `nama`, `role_lokal`, `status_akademik`).
- `publications`: Data operasional jurnal. (Kolom: `id`, `user_id`, `doi`, `judul`, `nama_jurnal`, `status_pengajuan`, `file_path`).
- `audit_logs`: Mencatat jejak verifikasi Karyawan/Admin terhadap jurnal dosen.

**4. Design Patterns & Logika Inti**

- **Event-Driven Worker:** Sebuah *daemon/worker* Laravel yang berjalan di latar belakang, khusus mendengarkan *channel* Redis `university.user.profile_updated`. Jika ada pesan masuk, *worker* ini akan mencari `sso_id` di tabel `users` lokal dan memperbarui data profilnya secara otomatis tanpa membebani *traffic* antarmuka.
- **State Pattern:** Mengamankan transisi kolom `status_pengajuan` di tabel `publications`. Mencegah jurnal yang berstatus `Rejected` diubah paksa menjadi `Approved` tanpa melalui logika validasi ulang.
- **Unit-Based Admin Authorization:** Hak admin LPPM ditentukan melalui atribut `is_admin = true` dan `unit.nama = "LPPM"` pada payload IAM, atau role sivitas `dosen` untuk akses peneliti.
- **Service Pattern:** Mengelola komunikasi dengan API eksternal (misal: `CrossrefMetadataService` untuk menarik data berdasarkan DOI jurnal yang diinput dosen).

---

# RANCANGAN DATABASE

**1. Tabel Cermin (Local Mirror)**

**Tabel: `users`**
Tabel ini diisi otomatis saat *user* berhasil *login* via SSO pertama kali, dan datanya diperbarui di latar belakang oleh Redis *worker* jika ada perubahan di Sistem 1.

| **Nama Kolom** | **Tipe Data** | **Keterangan** |
| --- | --- | --- |
| `id` | Unsigned BigInt (PK) | Primary Key lokal untuk optimasi *query* (*Join*). |
| `sso_id` | UUID (Unique) | Kunci relasi absolut ke Sistem 1. |
| `nama` | String | *Cached data* dari Redis. |
| `email` | String | *Cached data* dari Redis (berguna jika Sistem 2 perlu mengirim email notifikasi lokal). |
| `role_lokal` | String | Penentu hak akses lokal (Contoh: `dosen`,`mahasiswa`,`admin`). |
| `status_akademik` | String (Nullable) | *Cached data*. Berguna untuk validasi (misal: memblokir dosen "Cuti" dari submit jurnal). |
| `created_at` | Timestamp |  |
| `updated_at` | Timestamp | Ter-update otomatis saat Redis *worker* sinkronisasi. |

**2. Tabel Operasional Inti**

**Tabel: `publications`**
Menyimpan data pengajuan publikasi. Penerapan tipe data JSON di sini adalah nilai jual teknis (*tech flex*) untuk menangani format *metadata* API eksternal yang sering berubah-ubah tanpa perlu mengubah struktur tabel.

| **Nama Kolom** | **Tipe Data** | **Keterangan** |
| --- | --- | --- |
| `id` | Unsigned BigInt (PK) | Auto-increment. |
| `pengusul_id` | Unsigned BigInt (FK) | Dosen yang melakukan pengajuan (relasi ke `users.id` lokal). |
| `doi` | String (Unique/Nullable) | Digital Object Identifier. |
| `judul` | String | Judul publikasi. |
| `nama_jurnal` | String | Nama penerbit/jurnal. |
| `status_pengajuan` | String | `pending`, `approved`, `rejected`. |
| `file_path` | String | Lokasi file PDF. |
| `metadata_api` | JSON (Nullable) | Respons mentah dari eksternal API. |
| `created_at` | Timestamp |  |
| `updated_at` | Timestamp |  |

**Tabel `publication_authors` (Pivot)**
Tabel ini memetakan siapa saja dosen yang terlibat dalam satu publikasi beserta peran spesifik mereka.

| **Nama Kolom** | **Tipe Data** | **Keterangan** |
| --- | --- | --- |
| `id` | Unsigned BigInt (PK) | Auto-increment. |
| `publication_id` | Unsigned BigInt (FK) | Relasi ke tabel `publications`. |
| `user_id` | Unsigned BigInt (FK) | Relasi ke tabel `users` (dosen pendamping/utama). |
| `peran_penulis` | String / Enum | `penulis_pertama`, `pertama_korespondensi`, `korespondensi`, `pendamping`. |

**3. Tabel Jejak Audit (Transparansi & Enterprise Standard)**

**Tabel: `audit_logs`**
Tabel ini wajib diisi menggunakan *Observer Pattern*. Saat kolom `status_pengajuan` di tabel `publications` berubah, sistem otomatis mencatat siapa yang mengubah dan alasannya. Ini adalah fitur yang memisahkan aplikasi level skripsi dengan aplikasi level industri.

| **Nama Kolom** | **Tipe Data** | **Keterangan** |
| --- | --- | --- |
| `id` | Unsigned BigInt (PK) | Auto-increment. |
| `publication_id` | Unsigned BigInt (FK) | Relasi ke tabel `publications`. |
| `actor_id` | Unsigned BigInt (FK) | ID `users` (Admin LPPM) yang melakukan aksi. |
| `status_sebelum` | String | Contoh: `pending`. |
| `status_sesudah` | String | Contoh: `rejected`. |
| `catatan` | Text (Nullable) | Alasan penolakan atau catatan *approval* dari Admin. |
| `created_at` | Timestamp | Waktu persis terjadinya eksekusi. |