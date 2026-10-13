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
            --bs-body-color: #5D596C;
            --bs-heading-color: #2F2B3D;
            --border-color: #DBDADE;
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

        /* Top Header */
        .catalog-header {
            background-color: #FFFFFF;
            border-bottom: 1px solid var(--border-color);
            padding: 0.85rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .brand-logo {
            width: 36px;
            height: 36px;
            border-radius: 6px;
            background-color: var(--bs-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .avatar-initials {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background-color: rgba(115, 103, 240, 0.1);
            color: var(--bs-primary);
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* Scholarly Hero Banner (Flat, Clean, Institutional) */
        .scholarly-banner {
            background-color: #FFFFFF;
            border-bottom: 1px solid var(--border-color);
            padding: 2.75rem 1.5rem 2.25rem 1.5rem;
        }

        .scholarly-search-box {
            background-color: #F8F7FA;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 0.5rem;
        }

        /* Publication Cards */
        .card-publication {
            border: 1px solid var(--border-color);
            border-radius: 6px;
            background-color: #FFFFFF;
            height: 100%;
            display: flex;
            flex-direction: column;
            padding: 1.25rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .card-publication:hover {
            border-color: #B5B2C0;
            box-shadow: 0 2px 6px rgba(47, 43, 61, 0.06);
        }

        .badge-category {
            background-color: rgba(0, 207, 232, 0.1);
            color: #007A8C;
            font-weight: 600;
            border-radius: 4px;
            padding: 0.3rem 0.55rem;
        }

        .badge-role {
            background-color: rgba(115, 103, 240, 0.1);
            color: var(--bs-primary);
            font-weight: 600;
            border-radius: 4px;
            padding: 0.25rem 0.5rem;
        }

        .abstract-clamp {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-size: 0.85rem;
            line-height: 1.55;
            color: #6F6B7D;
        }

        /* Accessible Focus Ring (R-32) */
        a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible {
            outline: 2px solid var(--bs-primary);
            outline-offset: 2px;
        }

        .catalog-footer {
            background-color: #FFFFFF;
            border-top: 1px solid var(--border-color);
            padding: 1.25rem 1.5rem;
            color: #8C869B;
            font-size: 0.85rem;
            margin-top: auto;
        }
    </style>
</head>
<body>
    <!-- Top Header Navigation -->
    <header class="catalog-header">
        <div class="container-xl d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('home') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                    <div class="brand-logo" aria-hidden="true">
                        <i class="icon-base ti tabler-books"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark lh-sm" style="font-size: 1.05rem;">Knowledge Hub</div>
                        <div class="text-muted" style="font-size: 0.72rem;">Repositori Riset Sivitas Akademika</div>
                    </div>
                </a>
            </div>

            <!-- User Auth Status & Action CTAs -->
            <div class="d-flex align-items-center gap-2">
                @auth
                    <!-- User Demographic Info -->
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
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-2 px-3 py-2">
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

    <!-- Scholarly Catalog Header & Search Section -->
    <section class="scholarly-banner">
        <div class="container-xl">
            <div class="row justify-content-center text-center">
                <div class="col-12 col-lg-9">
                    <h3 class="fw-bold text-dark mb-1">Repositori Publikasi Ilmiah & Riset Sivitas</h3>
                    <p class="text-secondary small mb-3">
                        Katalog naskah artikel jurnal, prosiding konferensi, dan karya ilmiah terverifikasi Lembaga Penelitian & Pengabdian kepada Masyarakat (LPPM).
                    </p>

                    <!-- Search & Filter Controls -->
                    <div class="scholarly-search-box text-start">
                        <form action="{{ route('home') }}" method="GET" class="row g-2 align-items-center m-0">
                            <div class="col-12 col-md-7">
                                <label for="search-input" class="visually-hidden">Kata kunci pencarian publikasi</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted">
                                        <i class="icon-base ti tabler-search"></i>
                                    </span>
                                    <input
                                        type="text"
                                        id="search-input"
                                        name="q"
                                        class="form-control border-start-0 ps-0"
                                        placeholder="Cari judul artikel, nama penulis/dosen, atau DOI..."
                                        value="{{ $currentSearch }}"
                                    >
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <label for="category-select" class="visually-hidden">Filter Bidang Riset</label>
                                <select id="category-select" name="category_id" class="form-select">
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
                            <a href="{{ route('home') }}" class="btn btn-sm btn-link text-secondary text-decoration-none p-0 d-inline-flex align-items-center gap-1">
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
        <!-- Results Summary Bar -->
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
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
        <div class="row g-3">
            @forelse ($publications as $pub)
                <div class="col-12 col-md-6 col-lg-4">
                    <article class="card-publication">
                        <!-- Card Header Metadata -->
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            @if ($pub->category)
                                <span class="badge badge-category text-truncate" style="max-width: 200px;">
                                    {{ $pub->category->name }}
                                </span>
                            @else
                                <span class="badge bg-light text-muted border" style="border-radius: 4px;">Karya Ilmiah</span>
                            @endif
                            <span class="text-muted font-monospace small">
                                {{ $pub->publication_year }}
                            </span>
                        </div>

                        <!-- Publication Title -->
                        <h6 class="fw-bold text-dark mb-2 lh-base">
                            <a href="javascript:void(0)" class="text-dark text-decoration-none hover-primary" data-bs-toggle="modal" data-bs-target="#abstractModal{{ $pub->id }}">
                                {{ $pub->title }}
                            </a>
                        </h6>

                        <!-- Journal / Source Info -->
                        <div class="text-muted small mb-2 d-flex align-items-center gap-1 text-truncate">
                            <i class="icon-base ti tabler-book-2 flex-shrink-0 text-primary"></i>
                            <span class="text-truncate">{{ $pub->journal_name }}</span>
                        </div>

                        <!-- Authors List -->
                        <div class="mb-3">
                            <div class="d-flex flex-wrap gap-1 align-items-center text-muted small">
                                <i class="icon-base ti tabler-users text-muted flex-shrink-0"></i>
                                @foreach ($pub->authors->take(2) as $author)
                                    <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.72rem; border-radius: 4px;">
                                        {{ $author->author_name }}
                                    </span>
                                @endforeach
                                @if ($pub->authors->count() > 2)
                                    <span class="text-muted" style="font-size: 0.72rem;">+{{ $pub->authors->count() - 2 }} lainnya</span>
                                @endif
                            </div>
                        </div>

                        <!-- Abstract Excerpt -->
                        <div class="abstract-clamp mb-3 flex-grow-1">
                            {{ $pub->abstract }}
                        </div>

                        <!-- Bottom Action Buttons -->
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
                                <span class="badge bg-light text-muted border" style="font-size: 0.72rem; border-radius: 4px;">Naskah Fisik</span>
                            @endif
                        </div>
                    </article>
                </div>

                <!-- Abstract Detail Modal -->
                <div class="modal fade" id="abstractModal{{ $pub->id }}" tabindex="-1" aria-labelledby="modalLabel{{ $pub->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header border-bottom py-3">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="badge bg-light text-muted border font-monospace" style="font-size: 0.75rem; border-radius: 4px;">
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
                    <div class="avatar-initials bg-light text-muted rounded-circle p-3 mx-auto mb-3" style="width: 52px; height: 52px;">
                        <i class="icon-base ti tabler-search fs-3"></i>
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
            <div class="d-flex justify-content-center mt-4">
                {{ $publications->links() }}
            </div>
        @endif
    </main>

    <!-- Institutional Footer -->
    <footer class="catalog-footer">
        <div class="container-xl d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2">
            <div>
                &copy; {{ date('Y') }} <strong>{{ config('app.institution_name', 'Universitas') }}</strong> &bull; Repositori Karya Ilmiah Sivitas Akademika
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted">Knowledge Hub v1.0</span>
            </div>
        </div>
    </footer>

    <!-- Local Bootstrap JS & Popper -->
    <script src="{{ asset('assets/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.js') }}"></script>
</body>
</html>
