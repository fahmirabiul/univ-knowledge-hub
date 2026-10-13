@extends('layouts.app')

@section('title', 'Antrean Verifikasi LPPM')

@section('content')
<div class="container-fluid p-0">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2 mb-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">Antrean Verifikasi Publikasi Ilmiah</h4>
            <p class="text-muted small mb-0">Tinjau, evaluasi kelayakan dokumen, dan lakukan verifikasi pengajuan publikasi riset dosen.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill">
                <i class="icon-base ti tabler-shield-check me-1"></i>
                <span>Wewenang Admin LPPM</span>
            </span>
        </div>
    </div>

    <!-- Filters & Search Component -->
    <x-publication.filters
        :counts="$counts"
        :current-status="$currentStatus"
        :current-search="$currentSearch"
        :categories="$categories"
        route-index="admin.review.index"
    />

    <!-- Publication Review Queue Table Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase fs-7 text-muted">
                    <tr>
                        <th style="min-width: 280px; width: 35%;">Publikasi Ilmiah</th>
                        <th style="min-width: 180px;">Dosen Pengusul</th>
                        <th style="min-width: 140px;">Kategori Riset</th>
                        <th style="min-width: 130px;">Status</th>
                        <th style="min-width: 100px;">Dokumen</th>
                        <th style="min-width: 160px;" class="text-end">Aksi Tindakan</th>
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
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                <x-publication.status-badge :status="$pub->status" />
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
                                    <a href="{{ route('publications.show', $pub) }}"
                                       class="btn btn-sm {{ $pub->isPending() ? 'btn-primary' : 'btn-outline-secondary' }} d-inline-flex align-items-center gap-1"
                                       title="Buka Lembar Telaah">
                                        <i class="icon-base ti tabler-file-check"></i>
                                        <span>{{ $pub->isPending() ? 'Tinjau' : 'Detail' }}</span>
                                    </a>

                                    <!-- Audit History Button -->
                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 btn-view-audit"
                                            data-pub-id="{{ $pub->id }}"
                                            data-pub-title="{{ $pub->title }}"
                                            title="Buka Riwayat Audit">
                                        <i class="icon-base ti tabler-history"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <x-publication.empty-state
                                    :is-admin="true"
                                    :has-filters="request()->hasAny(['q', 'category_id'])"
                                    :reset-route="route('admin.review.index', ['status' => $currentStatus])"
                                />
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
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Audit Drawer handler
    const drawerElement = document.getElementById('drawer-audit');
    let auditDrawerInstance = null;

    if (window.bootstrap && drawerElement) {
        auditDrawerInstance = new bootstrap.Offcanvas(drawerElement);
    }

    document.querySelectorAll('.btn-view-audit').forEach(button => {
        button.addEventListener('click', function () {
            const pubId = this.dataset.pubId;
            const pubTitle = this.dataset.pubTitle;

            document.getElementById('audit-drawer-subtitle').textContent = pubTitle;
            document.getElementById('audit-loading').classList.remove('d-none');
            document.getElementById('audit-timeline').classList.add('d-none');
            document.getElementById('audit-empty').classList.add('d-none');

            if (auditDrawerInstance) {
                auditDrawerInstance.show();
            }

            fetch(`{{ url('admin/review') }}/${pubId}/audit`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                document.getElementById('audit-loading').classList.add('d-none');
                const listContainer = document.querySelector('#audit-timeline .timeline-list');
                listContainer.innerHTML = '';

                if (!data.logs || data.logs.length === 0) {
                    document.getElementById('audit-empty').classList.remove('d-none');
                    return;
                }

                data.logs.forEach(log => {
                    const li = document.createElement('li');
                    li.className = 'position-relative mb-4 ps-3';

                    let badgeColor = 'secondary';
                    if (log.status_after === 'approved') badgeColor = 'success';
                    if (log.status_after === 'rejected') badgeColor = 'danger';
                    if (log.status_after === 'pending') badgeColor = 'warning';

                    li.innerHTML = `
                        <div class="position-absolute start-0 top-0 translate-middle-x rounded-circle bg-${badgeColor} border border-2 border-white" style="width: 14px; height: 14px; margin-left: -8px; margin-top: 5px;"></div>
                        <div class="fw-semibold text-dark small">${log.actor_name} (${log.actor_role})</div>
                        <div class="text-muted" style="font-size: 0.75rem;">${log.created_at_formatted} &bull; ${log.created_at_human}</div>
                        <div class="mt-1">
                            <span class="badge bg-${badgeColor} text-white" style="font-size: 0.72rem;">${log.status_before} &rarr; ${log.status_after}</span>
                        </div>
                        ${log.notes ? `<div class="p-2 bg-light rounded mt-2 text-dark small border">${log.notes}</div>` : ''}
                    `;
                    listContainer.appendChild(li);
                });

                document.getElementById('audit-timeline').classList.remove('d-none');
            })
            .catch(err => {
                document.getElementById('audit-loading').classList.add('d-none');
                document.getElementById('audit-empty').classList.remove('d-none');
                document.querySelector('#audit-empty p').textContent = 'Gagal memuat log audit.';
            });
        });
    });
});
</script>
@endpush
