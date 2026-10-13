<!DOCTYPE html>
<html lang="id" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
    <meta name="description" content="Knowledge Hub - Repositori Karya Ilmiah dan Publikasi Riset Universitas">
    <title>Katalog Publikasi Riset - {{ config('app.name', 'Knowledge Hub') }}</title>

    <!-- Google Font: Public Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

    <!-- Tabler Icons -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/iconify-icons.css') }}">

    <!-- Core CSS (Vuexy Bootstrap 5) -->
    <link rel="stylesheet" href="{{ asset('assets/css/core.css') }}">

    <style>
        :root {
            --bs-primary: #7367F0;
            --bs-primary-rgb: 115, 103, 240;
            --bs-success: #28C76F;
            --bs-warning: #FF9F43;
            --bs-danger: #EA5455;
            --bs-info: #00CFE8;
            --bs-body-bg: #F8F7FA;
            --bs-body-color: #6F6B7D;
            --bs-heading-color: #4B465C;
        }

        body {
            font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
            padding: 0;
        }

        /* Top Navbar */
        .landing-navbar {
            background: #FFFFFF;
            border-bottom: 1px solid #DBDADE;
            padding: 0.85rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .brand-logo {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            background: #7367F0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            font-size: 1.25rem;
            box-shadow: 0 4px 8px rgba(115, 103, 240, 0.25);
            flex-shrink: 0;
        }

        .avatar-initials {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: rgba(115, 103, 240, 0.12);
            color: #7367F0;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* Hero Search Canvas */
        .hero-banner {
            background: linear-gradient(135deg, #7367F0 0%, #4834D4 100%);
            color: #FFFFFF;
            padding: 3.5rem 1.5rem 3rem 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .hero-banner::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            pointer-events: none;
        }

        .search-container {
            background: #FFFFFF;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(47, 43, 61, 0.12);
            padding: 0.75rem;
            margin-top: 1.5rem;
        }

        /* Publication Cards */
        .card-publication {
            border: 1px solid #DBDADE;
            border-radius: 8px;
            background-color: #FFFFFF;
            transition: transform 0.18s ease-in-out, box-shadow 0.18s ease-in-out;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .card-publication:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(47, 43, 61, 0.08);
            border-color: #C3C1C7;
        }

        .badge-category {
            background-color: rgba(0, 207, 232, 0.12);
            color: #007A8C;
            font-weight: 600;
        }

        .badge-role {
            background-color: rgba(115, 103, 240, 0.12);
            color: #7367F0;
            font-weight: 600;
        }

        .abstract-clamp {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-size: 0.85rem;
            line-height: 1.5;
            color: #6F6B7D;
        }

        /* Accessible focus ring (R-32) */
        a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible {
            outline: 2px solid #7367F0;
            outline-offset: 2px;
        }

        .landing-footer {
            background: #FFFFFF;
            border-top: 1px solid #DBDADE;
            padding: 1.25rem 1.5rem;
            color: #8C869B;
            font-size: 0.85rem;
            margin-top: auto;
        }
    </style>
</head>
<body>
    <!-- Top Navbar -->
    <header class="landing-navbar">
        <div class="container-xl d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('home') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                    <div class="brand-logo">
                        <i class="icon-base ti tabler-books"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark lh-sm" style="font-size: 1.1rem;">Knowledge Hub</div>
                        <div class="text-muted" style="font-size: 0.72rem;">Repositori Riset Sivitas</div>
                    </div>
                </a>
            </div>

            <!-- Auth Status & Controls -->
            <div class="d-flex align-items-center gap-2">
                @auth
                    <!-- Demographic Pill -->
                    <div class="d-none d-md-flex align-items-center gap-2 text-end me-1">
                        <div>
                            <div class="fw-semibold text-dark" style="font-size: 0.85rem;">{{ Auth::user()->name }}</div>
                            <div class="d-flex align-items-center justify-content-end gap-1">
                                <span class="badge badge-role" style="font-size: 0.7rem;">
                                    {{ Auth::user()->role?->label() ?? 'Pengguna' }}
                                </span>
                                @if (Auth::user()->faculty)
                                    <span class="text-muted" style="font-size: 0.7rem;">&bull; {{ Auth::user()->faculty }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="avatar-initials">
                            {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                        </div>
                    </div>

                    <!-- Role-Specific Action CTAs -->
                    @if (Auth::user()->isAdminLPPM())
                        <a href="{{ route('admin.review.index') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 px-3 py-2">
                            <i class="icon-base ti tabler-checklist fs-5"></i>
                            <span class="fw-semibold">Antrean Verifikasi LPPM</span>
                        </a>
                        <a href="{{ route('publications.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 d-none d-sm-inline-flex py-2">
                            <i class="icon-base ti tabler-files fs-5"></i>
                            <span>Semua Publikasi</span>
                        </a>
                    @elseif (Auth::user()->isDosen())
                        <a href="{{ route('publications.index') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 px-3 py-2">
                            <i class="icon-base ti tabler-files fs-5"></i>
                            <span class="fw-semibold">Workspace Naskah</span>
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 px-3 py-2">
                            <i class="icon-base ti tabler-layout-dashboard fs-5"></i>
                            <span>Dashboard</span>
                        </a>
                    @endif

                    <!-- Logout Button -->
                    <form action="{{ route('auth.logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm p-2 d-inline-flex align-items-center" title="Keluar dari Sistem" aria-label="Keluar dari Sistem">
                            <i class="icon-base ti tabler-logout fs-5"></i>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2">
                        <i class="icon-base ti tabler-key fs-5"></i>
                        <span class="fw-semibold">Masuk via SSO Kampus</span>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Flash Notifications -->
    <div class="container-xl mt-3">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 py-2 px-3 mb-0" role="alert">
                <i class="icon-base ti tabler-circle-check fs-5"></i>
                <div class="flex-grow-1 small">{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 py-2 px-3 mb-0" role="alert">
                <i class="icon-base ti tabler-alert-triangle fs-5"></i>
                <div class="flex-grow-1 small">{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        @endif

        @if (session('info'))
            <div class="alert alert-info alert-dismissible d-flex align-items-center gap-2 py-2 px-3 mb-0" role="alert">
                <i class="icon-base ti tabler-info-circle fs-5"></i>
                <div class="flex-grow-1 small">{{ session('info') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        @endif
    </div>

    <!-- Hero Search Canvas -->
    <section class="hero-banner my-3">
        <div class="container-xl position-relative">
            <div class="row justify-content-center text-center">
                <div class="col-12 col-lg-8">
                    <span class="badge bg-white bg-opacity-25 text-white border border-white border-opacity-25 px-3 py-1 rounded-pill mb-2">
                        <i class="icon-base ti tabler-certificate me-1"></i> Repositori Resmi Sivitas Akademika
                    </span>
                    <h2 class="fw-bold text-white mb-2">Eksplorasi Publikasi Ilmiah & Riset Sivitas</h2>
                    <p class="text-white text-opacity-75 small mb-0">
                        Akses naskah artikel jurnal, prosiding konferensi, dan karya ilmiah terverifikasi Lembaga Penelitian & Pengabdian kepada Masyarakat (LPPM).
                    </p>

                    <!-- Hero Search Box -->
                    <div class="search-container text-start">
                        <form action="{{ route('home') }}" method="GET" class="row g-2 align-items-center m-0">
                            <div class="col-12 col-md-7">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted">
                                        <i class="icon-base ti tabler-search"></i>
                                    </span>
                                    <input
                                        type="text"
                                        name="q"
                                        class="form-control border-start-0 ps-0"
                                        placeholder="Cari judul artikel, nama penulis/dosen, atau DOI..."
                                        value="{{ $currentSearch }}"
                                        aria-label="Kata kunci pencarian"
                                    >
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <select name="category_id" class="form-select" aria-label="Filter Kategori Bidang Riset">
                                    <option value="">Semua Bidang Riset</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ (string) $currentCategory === (string) $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-2">
                                <button type="submit" class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-1 py-2">
                                    <i class="icon-base ti tabler-search"></i>
                                    <span class="fw-semibold">Cari</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    @if ($currentSearch || $currentCategory)
                        <div class="mt-2 text-start">
                            <a href="{{ route('home') }}" class="btn btn-sm btn-link text-white text-decoration-none p-0 d-inline-flex align-items-center gap-1 opacity-75 hover-opacity-100">
                                <i class="icon-base ti tabler-rotate"></i>
                                <span>Reset Filter Pencarian</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Main Repository Catalog Content -->
    <main class="container-xl my-4 flex-grow-1">
        <!-- Results Status Header -->
        <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
            <div>
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="icon-base ti tabler-article text-primary"></i>
                    <span>Katalog Publikasi Terbit</span>
                </h5>
                <div class="text-muted small">
                    Menampilkan {{ $publications->total() }} naskah ilmiah yang telah terverifikasi dan dapat diakses.
                </div>
            </div>
        </div>

        <!-- Publication Cards Grid -->
        <div class="row g-4">
            @forelse ($publications as $pub)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card-publication p-4">
                        <!-- Top Metadata -->
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            @if ($pub->category)
                                <span class="badge badge-category text-truncate" style="max-width: 200px;">
                                    {{ $pub->category->name }}
                                </span>
                            @else
                                <span class="badge bg-light text-muted border">Karya Ilmiah</span>
                            @endif
                            <span class="text-muted font-monospace small">
                                {{ $pub->publication_year }}
                            </span>
                        </div>

                        <!-- Title -->
                        <h6 class="fw-bold text-dark mb-2 lh-base">
                            <a href="javascript:void(0)" class="text-dark text-decoration-none hover-primary" data-bs-toggle="modal" data-bs-target="#abstractModal{{ $pub->id }}">
                                {{ $pub->title }}
                            </a>
                        </h6>

                        <!-- Journal Info -->
                        <div class="text-muted small mb-2 d-flex align-items-center gap-1 text-truncate">
                            <i class="icon-base ti tabler-book-2 flex-shrink-0 text-primary"></i>
                            <span class="text-truncate">{{ $pub->journal_name }}</span>
                        </div>

                        <!-- Authors list -->
                        <div class="mb-3">
                            <div class="d-flex flex-wrap gap-1 align-items-center text-muted small">
                                <i class="icon-base ti tabler-users text-muted flex-shrink-0"></i>
                                @foreach ($pub->authors->take(2) as $author)
                                    <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.72rem;">
                                        {{ $author->author_name }}
                                    </span>
                                @endforeach
                                @if ($pub->authors->count() > 2)
                                    <span class="text-muted" style="font-size: 0.72rem;">+{{ $pub->authors->count() - 2 }} lainnya</span>
                                @endif
                            </div>
                        </div>

                        <!-- Abstract Clamp -->
                        <div class="abstract-clamp mb-4 flex-grow-1">
                            {{ $pub->abstract }}
                        </div>

                        <!-- Bottom Actions -->
                        <div class="d-flex align-items-center justify-content-between gap-2 pt-3 border-top mt-auto">
                            <button type="button" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#abstractModal{{ $pub->id }}">
                                <i class="icon-base ti tabler-file-text"></i>
                                <span>Abstrak</span>
                            </button>

                            @if ($pub->file_path)
                                @php
                                    $downloadUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                                        'publications.download',
                                        now()->addMinutes(15),
                                        ['publication' => $pub->id]
                                    );
                                @endphp
                                <a href="{{ $downloadUrl }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1" target="_blank" rel="noopener noreferrer">
                                    <i class="icon-base ti tabler-download"></i>
                                    <span>Unduh PDF</span>
                                </a>
                            @else
                                <span class="badge bg-light text-muted border" style="font-size: 0.72rem;">Naskah Fisik</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Abstract Detail Modal -->
                <div class="modal fade" id="abstractModal{{ $pub->id }}" tabindex="-1" aria-labelledby="modalLabel{{ $pub->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header border-bottom py-3">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="badge bg-light text-muted border font-monospace" style="font-size: 0.75rem;">
                                            {{ $pub->registration_code }}
                                        </span>
                                        @if ($pub->category)
                                            <span class="badge badge-category">{{ $pub->category->name }}</span>
                                        @endif
                                    </div>
                                    <h5 class="modal-title fw-bold text-dark" id="modalLabel{{ $pub->id }}">
                                        {{ $pub->title }}
                                    </h5>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="row g-3 mb-4">
                                    <div class="col-12 col-md-6">
                                        <div class="text-muted small">Jurnal / Sumber Terbit</div>
                                        <div class="fw-semibold text-dark">{{ $pub->journal_name }} ({{ $pub->publication_year }})</div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="text-muted small">Volume & Nomor</div>
                                        <div class="fw-semibold text-dark">{{ $pub->volume ?? '-' }} / {{ $pub->issue ?? '-' }}</div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="text-muted small">Halaman</div>
                                        <div class="fw-semibold text-dark">{{ $pub->pages ?? '-' }}</div>
                                    </div>
                                    @if ($pub->doi)
                                        <div class="col-12">
                                            <div class="text-muted small">Digital Object Identifier (DOI)</div>
                                            <a href="https://doi.org/{{ $pub->doi }}" target="_blank" rel="noopener noreferrer" class="text-primary text-decoration-none fw-semibold">
                                                https://doi.org/{{ $pub->doi }} <i class="icon-base ti tabler-external-link small"></i>
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                <div class="mb-4">
                                    <div class="text-muted small mb-2 fw-semibold">Tim Penulis:</div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($pub->authors as $author)
                                            <div class="p-2 bg-light rounded border small">
                                                <div class="fw-semibold text-dark">{{ $author->author_name }}</div>
                                                <div class="text-muted" style="font-size: 0.75rem;">{{ $author->affiliation ?? 'Afiliasi Kampus' }}</div>
                                                @if ($author->is_corresponding)
                                                    <span class="badge badge-role mt-1" style="font-size: 0.65rem;">Korespondensi</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div>
                                    <div class="text-muted small mb-2 fw-semibold">Abstrak:</div>
                                    <div class="p-3 bg-light rounded border text-secondary lh-lg" style="white-space: pre-line; font-size: 0.9rem;">
                                        {{ $pub->abstract }}
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-top py-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                                @if ($pub->file_path)
                                    @php
                                        $downloadUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                                            'publications.download',
                                            now()->addMinutes(15),
                                            ['publication' => $pub->id]
                                        );
                                    @endphp
                                    <a href="{{ $downloadUrl }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1" target="_blank" rel="noopener noreferrer">
                                        <i class="icon-base ti tabler-download"></i>
                                        <span>Unduh Naskah PDF</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 py-5 text-center">
                    <div class="avatar-initials bg-light text-muted rounded-circle p-3 mx-auto mb-3" style="width: 56px; height: 56px;">
                        <i class="icon-base ti tabler-books fs-2"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Tidak Ada Naskah Publikasi Ditemukan</h6>
                    <p class="text-muted small mb-3">
                        @if ($currentSearch || $currentCategory)
                            Tidak ada naskah publikasi yang sesuai dengan kriteria filter pencarian Anda.
                        @else
                            Belum ada karya ilmiah yang telah diterbitkan pada repositori kampus saat ini.
                        @endif
                    </p>
                    @if ($currentSearch || $currentCategory)
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                            <i class="icon-base ti tabler-rotate"></i>
                            <span>Reset Pencarian</span>
                        </a>
                    @endif
                </div>
            @endforelse
        </div>

        @if ($publications->hasPages())
            <div class="d-flex justify-content-center mt-5">
                {{ $publications->links() }}
            </div>
        @endif
    </main>

    <!-- Footer -->
    <footer class="landing-footer">
        <div class="container-xl d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2">
            <div>
                &copy; {{ date('Y') }} <strong>{{ config('app.institution_name', 'Universitas') }}</strong> &bull; Repositori Karya Ilmiah Sivitas Akademika
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted">Knowledge Hub v1.0</span>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS (for Modals) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
