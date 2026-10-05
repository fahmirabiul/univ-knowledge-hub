# PRODUCT REQUIREMENTS DOCUMENT

**1. Tujuan Produk**

Mengelola siklus hidup pengarsipan publikasi karya ilmiah secara terisolasi. Sistem ini tidak menangani pendaftaran akun, melainkan mendelegasikan akses ke IAM, serta memperbarui data dosen secara otonom melalui sinkronisasi pesan asinkron.

**2. User Roles**

- **Dosen:** Mengunggah dan memantau status publikasi jurnal miliknya.
- **Admin LPPM (Lembaga Penelitian):** Memverifikasi keabsahan dokumen jurnal yang diunggah dosen.
- **Mahasiswa:** Memiliki akses baca/unduh dokumen publikasi yang sudah diverifikasi.

**3. User Stories & Acceptance Criteria**

**Epic 1: Autentikasi Delegasi & Sinkronisasi**

- **Story 1.1:** Sebagai pengguna, saya ingin *login* menggunakan tombol "Masuk via SSO Kampus".
    - *Acceptance Criteria:* Tombol mengarahkan ke Sistem IAM. Setelah autentikasi berhasil, sistem menangkap token via Laravel Socialite, mengecek keberadaan `sso_id` di *database* lokal, membuat sesi *login* lokal, dan mengarahkan ke *dashboard*.
- **Story 1.2:** Sebagai sistem *backend*, saya ingin selalu mendengarkan *channel* Redis agar data profil dosen lokal selalu sinkron dengan pusat.
    - *Acceptance Criteria:* Terdapat *console command* atau *worker* (contoh: `php artisan redis:listen-users`) yang terus berjalan. Saat menerima pesan, sistem memperbarui nama/status dosen pada tabel `users` lokal berdasarkan `sso_id`.

**Epic 2: Manajemen Publikasi (Dosen & Admin)**

- **Story 2.1:** Sebagai Dosen, saya ingin menginput DOI jurnal agar sistem otomatis menarik judul dan nama jurnal.
    - *Acceptance Criteria:* Form input DOI tersedia. Saat tombol "Cari" diklik, sistem menggunakan *Service Class* untuk melakukan HTTP *request* ke API eksternal (misal: Crossref) atau *mock response*, lalu mengisi otomatis kolom form lainnya.
- **Story 2.2:** Sebagai Dosen, saya ingin mengunggah file PDF jurnal dengan aman.
    - *Acceptance Criteria:* File disimpan di direktori *private* (`storage/app/private/publications`). URL publik tidak dapat mengakses file ini.
- **Story 2.3:** Sebagai Admin LPPM, saya ingin melihat daftar jurnal "Pending" dan menyetujui atau menolaknya.
    - *Acceptance Criteria:* Terdapat *dashboard* antrean. Tombol *Approve* akan mengubah *state* jurnal menjadi terpublikasi. Perubahan ini terekam di tabel `audit_logs`.

**Epic 3: Eksplorasi Pengetahuan (Mahasiswa)**

- **Story 3.1:** Sebagai Mahasiswa, saya ingin mencari dan mengunduh jurnal dosen yang berstatus "Approved".
    - *Acceptance Criteria:* Fitur pencarian tersedia. Tombol "Unduh" akan menghasilkan *Temporary Signed URL* yang hanya berlaku selama 15 menit untuk mengakses file PDF dari *private storage*.

**4. Technical Constraints**

- **Framework:** Laravel 13, integrasi Laravel Socialite (Custom Provider).
- **File Storage:** Flysystem (Local Private).
- **Design Pattern:** State Pattern (untuk validasi transisi status jurnal) dan Service Pattern (untuk konsumsi API DOI).