@extends('layouts.app')

@section('title', 'Antrean Verifikasi LPPM')

@section('content')
<div class="container-fluid p-0">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2 mb-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">Antrean Verifikasi Publikasi Ilmiah</h4>
            <p class="text-muted small mb-0">Tinjau, verifikasi kelayakan dokumen, dan kelola status publikasi riset dosen.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill">
                <i class="icon-base ti tabler-shield-check me-1"></i>
                <span>Wewenang Admin LPPM</span>
            </span>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="d-flex align-items-center gap-2 overflow-x-auto pb-2 mb-3">
        <a href="{{ route('admin.review.index', array_merge(request()->except('status', 'page'), ['status' => 'pending'])) }}"
           class="btn btn-sm {{ $currentStatus === 'pending' ? 'btn-warning text-dark fw-semibold' : 'btn-outline-secondary bg-white text-secondary' }} d-inline-flex align-items-center gap-2 text-nowrap rounded-pill px-3 py-2">
            <i class="icon-base ti tabler-clock"></i>
            <span>Menunggu Verifikasi</span>
            <span class="badge {{ $currentStatus === 'pending' ? 'bg-dark text-white' : 'bg-warning-subtle text-warning border border-warning-subtle' }} rounded-pill">{{ $counts['pending'] ?? 0 }}</span>
        </a>

        <a href="{{ route('admin.review.index', array_merge(request()->except('status', 'page'), ['status' => 'approved'])) }}"
           class="btn btn-sm {{ $currentStatus === 'approved' ? 'btn-success text-white fw-semibold' : 'btn-outline-secondary bg-white text-secondary' }} d-inline-flex align-items-center gap-2 text-nowrap rounded-pill px-3 py-2">
            <i class="icon-base ti tabler-circle-check"></i>
            <span>Disetujui LPPM</span>
            <span class="badge {{ $currentStatus === 'approved' ? 'bg-white text-success' : 'bg-success-subtle text-success border border-success-subtle' }} rounded-pill">{{ $counts['approved'] ?? 0 }}</span>
        </a>

        <a href="{{ route('admin.review.index', array_merge(request()->except('status', 'page'), ['status' => 'rejected'])) }}"
           class="btn btn-sm {{ $currentStatus === 'rejected' ? 'btn-danger text-white fw-semibold' : 'btn-outline-secondary bg-white text-secondary' }} d-inline-flex align-items-center gap-2 text-nowrap rounded-pill px-3 py-2">
            <i class="icon-base ti tabler-alert-circle"></i>
            <span>Ditolak / Revisi</span>
            <span class="badge {{ $currentStatus === 'rejected' ? 'bg-white text-danger' : 'bg-danger-subtle text-danger border border-danger-subtle' }} rounded-pill">{{ $counts['rejected'] ?? 0 }}</span>
        </a>

        <a href="{{ route('admin.review.index', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}"
           class="btn btn-sm {{ $currentStatus === 'all' ? 'btn-dark' : 'btn-outline-secondary bg-white text-secondary' }} d-inline-flex align-items-center gap-2 text-nowrap rounded-pill px-3 py-2">
            <span>Semua Naskah</span>
            <span class="badge {{ $currentStatus === 'all' ? 'bg-white text-dark' : 'bg-light text-muted border' }} rounded-pill">{{ $counts['all'] ?? 0 }}</span>
        </a>
    </div>

    <!-- Filter & Search Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.review.index') }}" method="GET" class="row g-2 align-items-center">
                <input type="hidden" name="status" value="{{ $currentStatus }}">

                <div class="col-12 col-md-6 col-lg-5">
                    <label for="search-input" class="visually-hidden">Cari Naskah atau Penulis</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="icon-base ti tabler-search"></i></span>
                        <input type="text" id="search-input" name="q" value="{{ $currentSearch }}" class="form-control border-start-0" placeholder="Cari judul, DOI, nama jurnal, atau dosen..." aria-label="Cari Naskah">
                    </div>
                </div>

                <div class="col-12 col-md-4 col-lg-4">
                    <label for="category-filter" class="visually-hidden">Filter Kategori Riset</label>
                    <select id="category-filter" name="category_id" class="form-select">
                        <option value="">Semua Bidang Riset</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2 col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-1">
                        <i class="icon-base ti tabler-filter"></i>
                        <span>Filter</span>
                    </button>
                    @if (request('q') || request('category_id'))
                        <a href="{{ route('admin.review.index', ['status' => $currentStatus]) }}" class="btn btn-outline-secondary" title="Reset Filter" aria-label="Reset Filter">
                            <i class="icon-base ti tabler-rotate"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Alerts Feedback -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="icon-base ti tabler-circle-check fs-4"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="icon-base ti tabler-alert-circle fs-4"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <div class="fw-semibold mb-1">Terjadi kesalahan pada data yang dikirim:</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    <!-- Publication Review Queue Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase fs-7 text-muted">
                    <tr>
                        <th style="min-width: 280px;">Publikasi Ilmiah</th>
                        <th style="min-width: 180px;">Dosen Pengusul</th>
                        <th style="min-width: 140px;">Kategori Riset</th>
                        <th style="min-width: 120px;">Status</th>
                        <th style="min-width: 110px;">Dokumen</th>
                        <th style="min-width: 160px;" class="text-end">Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($publications as $pub)
                        <tr>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.72rem;">
                                            {{ $pub->registration_code ?? 'PUB-#' . $pub->id }}
                                        </span>
                                        @if ($pub->doi)
                                            <span class="badge badge-category" style="font-size: 0.72rem;">
                                                DOI: {{ $pub->doi }}
                                            </span>
                                        @endif
                                    </div>
                                    <a href="{{ route('publications.show', $pub) }}" class="fw-semibold text-dark text-decoration-none hover-primary text-wrap" style="line-height: 1.35;">
                                        {{ $pub->title }}
                                    </a>
                                    <div class="text-muted small d-flex align-items-center gap-2">
                                        <span><i class="icon-base ti tabler-book me-1"></i>{{ $pub->journal_name }}</span>
                                        <span>&bull;</span>
                                        <span>{{ $pub->publication_year }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-initials bg-light text-primary border" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                        {{ strtoupper(substr($pub->submitter->name ?? 'D', 0, 2)) }}
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="fw-semibold text-dark text-truncate" style="font-size: 0.85rem;">{{ $pub->submitter->name ?? 'Dosen' }}</div>
                                        <div class="text-muted text-truncate small" style="font-size: 0.75rem;">{{ $pub->submitter->faculty ?? 'Fakultas Riset' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($pub->category)
                                    <span class="badge bg-light text-dark border text-wrap text-start">
                                        {{ $pub->category->name }}
                                    </span>
                                @else
                                    <span class="text-muted small">(Belum dipilih)</span>
                                @endif
                            </td>
                            <td>
                                @if ($pub->isPending())
                                    <span class="badge badge-status-pending d-inline-flex align-items-center gap-1 px-2 py-1">
                                        <i class="icon-base ti tabler-clock"></i>
                                        <span>Pending</span>
                                    </span>
                                @elseif ($pub->isApproved())
                                    <span class="badge badge-status-approved d-inline-flex align-items-center gap-1 px-2 py-1">
                                        <i class="icon-base ti tabler-circle-check"></i>
                                        <span>Approved</span>
                                    </span>
                                @elseif ($pub->isRejected())
                                    <span class="badge badge-status-rejected d-inline-flex align-items-center gap-1 px-2 py-1">
                                        <i class="icon-base ti tabler-alert-circle"></i>
                                        <span>Rejected</span>
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if ($pub->file_path)
                                    <a href="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('publications.download', now()->addMinutes(15), ['publication' => $pub->id]) }}"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"
                                       title="Pratinjau Berkas PDF">
                                        <i class="icon-base ti tabler-file-type-pdf"></i>
                                        <span>PDF</span>
                                    </a>
                                @else
                                    <span class="text-muted small">Nihil</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-1">
                                    @if ($pub->isPending())
                                        <!-- Approve Button -->
                                        <button type="button"
                                                class="btn btn-sm btn-success text-white d-inline-flex align-items-center gap-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modal-approve"
                                                data-pub-id="{{ $pub->id }}"
                                                data-pub-title="{{ $pub->title }}"
                                                title="Setujui Naskah">
                                            <i class="icon-base ti tabler-check"></i>
                                            <span>Setujui</span>
                                        </button>

                                        <!-- Reject Button -->
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modal-reject"
                                                data-pub-id="{{ $pub->id }}"
                                                data-pub-title="{{ $pub->title }}"
                                                title="Tolak Naskah">
                                            <i class="icon-base ti tabler-x"></i>
                                            <span>Tolak</span>
                                        </button>
                                    @endif

                                    <!-- Audit History Button -->
                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 btn-view-audit"
                                            data-pub-id="{{ $pub->id }}"
                                            data-pub-title="{{ $pub->title }}"
                                            title="Buka Riwayat Audit">
                                        <i class="icon-base ti tabler-history"></i>
                                        <span>Audit</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="py-4">
                                    <div class="avatar-initials bg-light text-muted mx-auto mb-3" style="width: 56px; height: 56px; font-size: 1.5rem;">
                                        <i class="icon-base ti tabler-inbox"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">Tidak Ada Naskah dalam Antrean</h6>
                                    <p class="text-muted small mb-0">Semua naskah pada kriteria filter ini telah selesai ditinjau.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($publications->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $publications->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal: Approve Publication -->
<div class="modal fade" id="modal-approve" tabindex="-1" aria-labelledby="modal-approve-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="form-approve" method="POST" action="">
                @csrf
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modal-approve-label">
                        <i class="icon-base ti tabler-circle-check text-success fs-4"></i>
                        <span>Konfirmasi Persetujuan Naskah</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        Naskah yang disetujui akan resmi berstatus <strong>Terpublikasi</strong> di repositori universitas dan dapat diunduh oleh sivitas akademika.
                    </p>
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="fw-semibold text-dark mb-1" id="approve-pub-title">Judul Naskah</div>
                    </div>
                    <div class="mb-0">
                        <label for="approve-notes" class="form-label text-dark fw-semibold">Catatan Verifikasi LPPM <span class="text-muted fw-normal small">(Opsional)</span></label>
                        <textarea id="approve-notes" name="notes" rows="3" class="form-control" placeholder="Contoh: Dokumen lengkap, akreditasi jurnal terverifikasi, dan DOI valid."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success text-white d-inline-flex align-items-center gap-1">
                        <i class="icon-base ti tabler-check"></i>
                        <span>Setujui dan Publikasikan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Reject Publication -->
<div class="modal fade" id="modal-reject" tabindex="-1" aria-labelledby="modal-reject-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="form-reject" method="POST" action="">
                @csrf
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modal-reject-label">
                        <i class="icon-base ti tabler-alert-circle text-danger fs-4"></i>
                        <span>Konfirmasi Penolakan Naskah</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        Naskah akan dikembalikan ke dosen pengusul untuk perbaikan. Mohon sertakan catatan evaluasi yang jelas dan konstruktif.
                    </p>
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="fw-semibold text-dark mb-1" id="reject-pub-title">Judul Naskah</div>
                    </div>
                    <div class="mb-0">
                        <label for="reject-reason" class="form-label text-dark fw-semibold">Alasan Penolakan dan Catatan Evaluasi <span class="text-danger">*</span></label>
                        <textarea id="reject-reason" name="reason" rows="4" class="form-control" placeholder="Jelaskan alasan penolakan secara spesifik, minimal 10 karakter..." required minlength="10"></textarea>
                        <div class="form-text small">Catatan ini akan langsung terlihat oleh dosen pengusul pada lembar kerja publikasi mereka.</div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger d-inline-flex align-items-center gap-1">
                        <i class="icon-base ti tabler-x"></i>
                        <span>Tolak Naskah</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Offcanvas Drawer: Audit Trail Timeline -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="drawer-audit" aria-labelledby="drawer-audit-label" style="width: 440px;">
    <div class="offcanvas-header border-bottom py-3">
        <div>
            <h5 class="offcanvas-title fw-bold text-dark d-flex align-items-center gap-2" id="drawer-audit-label">
                <i class="icon-base ti tabler-history text-primary fs-4"></i>
                <span>Jejak Audit Verifikasi</span>
            </h5>
            <div class="text-muted small" id="audit-drawer-subtitle">Memuat riwayat...</div>
        </div>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
    </div>
    <div class="offcanvas-body p-4">
        <!-- Loading State -->
        <div id="audit-loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Memuat riwayat audit...</span>
            </div>
            <p class="text-muted small mt-2">Mengambil jejak riwayat naskah...</p>
        </div>

        <!-- Timeline Container -->
        <div id="audit-timeline" class="d-none">
            <ul class="timeline-list list-unstyled position-relative ps-4 mb-0">
                <!-- Dynamically populated via JavaScript -->
            </ul>
        </div>

        <!-- Empty State -->
        <div id="audit-empty" class="text-center py-5 d-none">
            <i class="icon-base ti tabler-file-analytics text-muted fs-1 mb-2"></i>
            <p class="text-muted small">Belum ada riwayat audit tercatat untuk naskah ini.</p>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline-list::before {
    content: '';
    position: absolute;
    top: 8px;
    bottom: 8px;
    left: 8px;
    width: 2px;
    background: #DBDADE;
}

.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -28px;
    top: 2px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 2px solid #FFFFFF;
    box-shadow: 0 0 0 2px #DBDADE;
    background: #7367F0;
}

.timeline-marker.status-approved {
    background: #28C76F;
    box-shadow: 0 0 0 2px rgba(40, 199, 111, 0.4);
}

.timeline-marker.status-rejected {
    background: #EA5455;
    box-shadow: 0 0 0 2px rgba(234, 84, 85, 0.4);
}

.timeline-marker.status-pending {
    background: #FF9F43;
    box-shadow: 0 0 0 2px rgba(255, 159, 67, 0.4);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Modal Approve Handler
    const modalApprove = document.getElementById('modal-approve');
    if (modalApprove) {
        modalApprove.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const pubId = button.getAttribute('data-pub-id');
            const pubTitle = button.getAttribute('data-pub-title');

            const form = document.getElementById('form-approve');
            form.action = '/admin/review/' + pubId + '/approve';

            document.getElementById('approve-pub-title').textContent = pubTitle;
            document.getElementById('approve-notes').value = '';
        });
    }

    // Modal Reject Handler
    const modalReject = document.getElementById('modal-reject');
    if (modalReject) {
        modalReject.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const pubId = button.getAttribute('data-pub-id');
            const pubTitle = button.getAttribute('data-pub-title');

            const form = document.getElementById('form-reject');
            form.action = '/admin/review/' + pubId + '/reject';

            document.getElementById('reject-pub-title').textContent = pubTitle;
            document.getElementById('reject-reason').value = '';
        });
    }

    // Offcanvas Audit Trail Drawer Handler
    const drawerElement = document.getElementById('drawer-audit');
    const offcanvasInstance = new bootstrap.Offcanvas(drawerElement);
    const auditButtons = document.querySelectorAll('.btn-view-audit');

    auditButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const pubId = this.getAttribute('data-pub-id');
            const pubTitle = this.getAttribute('data-pub-title');

            document.getElementById('audit-drawer-subtitle').textContent = pubTitle;
            document.getElementById('audit-loading').classList.remove('d-none');
            document.getElementById('audit-timeline').classList.add('d-none');
            document.getElementById('audit-empty').classList.add('d-none');

            offcanvasInstance.show();

            fetch('/admin/review/' + pubId + '/audit', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                document.getElementById('audit-loading').classList.add('d-none');

                if (!data.logs || data.logs.length === 0) {
                    document.getElementById('audit-empty').classList.remove('d-none');
                    return;
                }

                const listContainer = document.querySelector('.timeline-list');
                listContainer.innerHTML = '';

                data.logs.forEach(function (log) {
                    const li = document.createElement('li');
                    li.className = 'timeline-item';

                    let markerClass = 'status-pending';
                    if (log.status_after === 'approved') markerClass = 'status-approved';
                    if (log.status_after === 'rejected') markerClass = 'status-rejected';

                    const beforeText = log.status_before ? log.status_before.toUpperCase() : 'AWAL';
                    const afterText = log.status_after.toUpperCase();

                    li.innerHTML = `
                        <div class="timeline-marker ${markerClass}"></div>
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="badge ${log.status_after === 'approved' ? 'bg-success' : (log.status_after === 'rejected' ? 'bg-danger' : 'bg-warning text-dark')} px-2 py-1" style="font-size: 0.72rem;">
                                ${beforeText} &rarr; ${afterText}
                            </span>
                            <span class="text-muted small">${log.created_at_human}</span>
                        </div>
                        <div class="fw-semibold text-dark" style="font-size: 0.88rem;">${log.actor_name}</div>
                        <div class="text-muted small mb-2">${log.actor_role}</div>
                        ${log.notes ? `<div class="p-2 bg-light rounded text-dark small border">${log.notes}</div>` : ''}
                        <div class="text-muted mt-1" style="font-size: 0.72rem;">
                            <i class="icon-base ti tabler-clock me-1"></i>${log.created_at_formatted}
                            ${log.ip_address ? `&bull; IP: ${log.ip_address}` : ''}
                        </div>
                    `;

                    listContainer.appendChild(li);
                });

                document.getElementById('audit-timeline').classList.remove('d-none');
            })
            .catch(function (err) {
                document.getElementById('audit-loading').classList.add('d-none');
                document.getElementById('audit-empty').classList.remove('d-none');
                console.error('Error fetching audit logs:', err);
            });
        });
    });
});
</script>
@endpush
