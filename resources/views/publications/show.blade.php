@extends('layouts.app')

@section('title', 'Detail Publikasi - ' . Str::limit($publication->title, 40))

@section('content')
<div class="container-fluid p-0">
    <!-- Header & Action Navigation -->
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-light text-muted border" style="font-size: 0.8rem;">{{ $publication->registration_code }}</span>
                <x-publication.status-badge :status="$publication->status" />
            </div>
            <h4 class="fw-bold text-dark mb-0">{{ $publication->title }}</h4>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if (Auth::user()?->isAdminLPPM() && url()->previous() !== url()->current() && str_contains(url()->previous(), 'admin/review'))
                <a href="{{ route('admin.review.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                    <i class="icon-base ti tabler-arrow-left"></i>
                    <span>Kembali ke Antrean</span>
                </a>
            @else
                <a href="{{ route('publications.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                    <i class="icon-base ti tabler-arrow-left"></i>
                    <span>Kembali</span>
                </a>
            @endif

            @can('update', $publication)
                <a href="{{ route('publications.edit', $publication) }}" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1">
                    <i class="icon-base ti tabler-edit"></i>
                    <span>Ubah Data</span>
                </a>
            @endcan

            @if ($publication->file_path)
                @php
                    $storageService = app(\App\Services\Publication\PublicationStorageService::class);
                    $downloadUrl = $storageService->generateTemporaryDownloadUrl($publication);
                @endphp
                <a href="{{ $downloadUrl }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1" target="_blank" rel="noopener noreferrer">
                    <i class="icon-base ti tabler-download"></i>
                    <span>Unduh Naskah PDF</span>
                </a>
            @endif
        </div>
    </div>

    @if ($publication->isRejected() && $publication->rejection_reason)
        <div class="alert alert-danger d-flex align-items-start gap-2 mb-4 p-3 border-danger shadow-sm" role="alert">
            <i class="icon-base ti tabler-alert-circle fs-4 mt-1 flex-shrink-0"></i>
            <div>
                <div class="fw-bold">Catatan Penolakan / Evaluasi LPPM:</div>
                <div class="mt-1 small">{{ $publication->rejection_reason }}</div>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Left Column: Bibliografi & Abstrak -->
        <div class="col-12 col-lg-8">
            <!-- Bibliographic Info Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-bottom py-3">
                    <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="icon-base ti tabler-book-2 text-primary"></i>
                        <span>Informasi Bibliografi</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="text-muted small">Jurnal / Prosiding Konferensi</div>
                            <div class="fw-semibold text-dark">{{ $publication->journal_name }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Tahun Publikasi</div>
                            <div class="fw-semibold text-dark">{{ $publication->publication_year }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Kategori Bidang Riset</div>
                            <div>
                                @if ($publication->category)
                                    <span class="badge badge-category">{{ $publication->category->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Volume</div>
                            <div class="fw-semibold text-dark">{{ $publication->volume ?? '-' }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Nomor / Issue</div>
                            <div class="fw-semibold text-dark">{{ $publication->issue ?? '-' }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Halaman</div>
                            <div class="fw-semibold text-dark">{{ $publication->pages ?? '-' }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">ISBN / ISSN</div>
                            <div class="fw-semibold text-dark">{{ $publication->isbn_issn ?? '-' }}</div>
                        </div>

                        @if ($publication->doi)
                            <div class="col-12">
                                <div class="text-muted small">Digital Object Identifier (DOI)</div>
                                <div>
                                    <a href="https://doi.org/{{ $publication->doi }}" target="_blank" rel="noopener noreferrer" class="text-primary fw-semibold text-decoration-none">
                                        https://doi.org/{{ $publication->doi }} <i class="icon-base ti tabler-external-link small"></i>
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Abstract Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-bottom py-3">
                    <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="icon-base ti tabler-align-left text-primary"></i>
                        <span>Abstrak Publikasi</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-secondary lh-lg" style="white-space: pre-line;">
                        {{ $publication->abstract }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Verification Panel (LPPM), Authors & Audit Log -->
        <div class="col-12 col-lg-4">
            <!-- Verification Decision Panel (for Admin LPPM on Pending submissions) -->
            @if (Auth::user()?->isAdminLPPM() && $publication->isPending())
                <div class="card border-0 shadow-sm mb-4 border-top border-3 border-primary">
                    <div class="card-header border-bottom py-3 d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="icon-base ti tabler-shield-check text-primary"></i>
                            <span>Verifikasi LPPM</span>
                        </h5>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">Pending</span>
                    </div>
                    <div class="card-body p-3">
                        <p class="text-muted small mb-3">
                            Tinjau kelayakan berkas naskah, abstrak, dan data bibliografi sebelum memverifikasi.
                        </p>

                        <!-- Form Approve -->
                        <form action="{{ route('admin.review.approve', $publication) }}" method="POST" class="mb-3">
                            @csrf
                            <div class="mb-2">
                                <label for="approve_notes" class="form-label text-dark fw-semibold small">Catatan Persetujuan <span class="text-muted fw-normal">(Opsional)</span></label>
                                <textarea id="approve_notes" name="notes" rows="2" class="form-control form-control-sm" placeholder="Contoh: Dokumen lengkap dan memenuhi ketentuan akreditasi."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success btn-sm w-100 d-inline-flex align-items-center justify-content-center gap-1 py-2">
                                <i class="icon-base ti tabler-check"></i>
                                <span class="fw-semibold">Setujui & Publikasikan Naskah</span>
                            </button>
                        </form>

                        <hr class="my-3 text-muted">

                        <!-- Form Reject -->
                        <form action="{{ route('admin.review.reject', $publication) }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <label for="reject_reason" class="form-label text-dark fw-semibold small">Alasan Penolakan / Catatan Evaluasi <span class="text-danger">*</span></label>
                                <textarea id="reject_reason" name="reason" rows="3" class="form-control form-control-sm" placeholder="Jelaskan alasan penolakan secara jelas (minimal 10 karakter)..." required minlength="10"></textarea>
                            </div>
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100 d-inline-flex align-items-center justify-content-center gap-1 py-2">
                                <i class="icon-base ti tabler-x"></i>
                                <span class="fw-semibold">Tolak / Minta Revisi Naskah</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Authors List Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-bottom py-3">
                    <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="icon-base ti tabler-users text-primary"></i>
                        <span>Daftar Penulis ({{ $publication->authors->count() }})</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse ($publication->authors as $index => $author)
                            <li class="list-group-item p-3">
                                <div class="d-flex align-items-start gap-2">
                                    <div class="avatar-initials bg-primary-subtle text-primary rounded-circle p-2 flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                        #{{ $author->author_order ?? ($index + 1) }}
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="fw-semibold text-dark text-truncate">{{ $author->author_name }}</div>
                                        <div class="text-muted small text-truncate">{{ $author->affiliation ?? 'Afiliasi tidak dicantumkan' }}</div>
                                        <div class="mt-1">
                                            @if ($author->author_role)
                                                <span class="badge bg-light text-secondary border" style="font-size: 0.7rem;">
                                                    {{ $author->author_role->label() }}
                                                </span>
                                            @endif
                                            @if ($author->is_corresponding)
                                                <span class="badge bg-primary-subtle text-primary" style="font-size: 0.7rem;">
                                                    Korespondensi
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-muted text-center py-3">
                                Tidak ada data penulis terdaftar.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- Audit Trail / Tracking History Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-bottom py-3">
                    <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="icon-base ti tabler-history text-primary"></i>
                        <span>Riwayat Jejak Naskah</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="avatar-initials bg-success-subtle text-success rounded-circle flex-shrink-0" style="width: 28px; height: 28px; font-size: 0.75rem;">
                                <i class="icon-base ti tabler-check"></i>
                            </div>
                            <div>
                                <div class="fw-semibold text-dark small">Diajukan ke Repositori</div>
                                <div class="text-muted" style="font-size: 0.72rem;">{{ $publication->created_at->translatedFormat('d F Y, H:i') }} WIB</div>
                                <div class="text-muted small mt-1">Oleh: {{ $publication->submitter->name ?? 'Dosen Pengusul' }}</div>
                            </div>
                        </div>

                        @if ($publication->auditLogs->isNotEmpty())
                            @foreach ($publication->auditLogs as $log)
                                @php
                                    $badgeClass = 'bg-secondary';
                                    if ($log->status_after === 'approved') $badgeClass = 'bg-success';
                                    elseif ($log->status_after === 'rejected') $badgeClass = 'bg-danger';
                                    elseif ($log->status_after === 'pending') $badgeClass = 'bg-warning';
                                @endphp
                                <div class="d-flex align-items-start gap-3 mb-3">
                                    <div class="avatar-initials bg-info-subtle text-info rounded-circle flex-shrink-0" style="width: 28px; height: 28px; font-size: 0.75rem;">
                                        <i class="icon-base ti tabler-history"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="badge {{ $badgeClass }} text-white" style="font-size: 0.7rem;">
                                                {{ ucfirst($log->status_before) }} &rarr; {{ ucfirst($log->status_after) }}
                                            </span>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.72rem;">
                                            {{ $log->created_at?->translatedFormat('d F Y, H:i') }} &bull; {{ $log->actor?->name ?? 'Sistem' }}
                                        </div>
                                        @if ($log->notes)
                                            <div class="p-2 bg-light rounded text-dark small mt-1 border">
                                                {{ $log->notes }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
