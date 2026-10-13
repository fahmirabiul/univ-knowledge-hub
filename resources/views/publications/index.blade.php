@extends('layouts.app')

@section('title', Auth::user()?->isAdminLPPM() ? 'Semua Publikasi Ilmiah' : 'Daftar Publikasi Ilmiah')

@section('content')
<div class="container-fluid p-0">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2 mb-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                {{ Auth::user()?->isAdminLPPM() ? 'Semua Publikasi Ilmiah' : 'Daftar Publikasi Ilmiah' }}
            </h4>
            <p class="text-muted small mb-0">
                {{ Auth::user()?->isAdminLPPM()
                    ? 'Kelola dan pantau seluruh naskah riset sivitas akademika universitas.'
                    : 'Kelola dan pantau status naskah riset, artikel jurnal, dan prosiding Anda.' }}
            </p>
        </div>
        <div>
            @if (Auth::user()?->isAdminLPPM())
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill">
                    <i class="icon-base ti tabler-shield-check me-1"></i>
                    <span>Hak Kelola LPPM</span>
                </span>
            @else
                <a href="{{ route('publications.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2">
                    <i class="icon-base ti tabler-plus fs-5"></i>
                    <span class="fw-semibold">Unggah Naskah Baru</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Filters & Search Component -->
    <x-publication.filters
        :counts="$counts"
        :current-status="$currentStatus"
        :current-search="$currentSearch"
        :categories="$categories"
        route-index="publications.index"
    />

    <!-- Publications List Table Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" style="min-width: 280px; width: 40%;">Judul & Informasi Naskah</th>
                        @if (Auth::user()?->isAdminLPPM())
                            <th scope="col" style="min-width: 170px;">Dosen Pengusul</th>
                        @endif
                        <th scope="col" style="min-width: 140px;">Kategori</th>
                        @if (! Auth::user()?->isAdminLPPM())
                            <th scope="col" style="min-width: 160px;">Penulis & Kolaborator</th>
                        @endif
                        <th scope="col" style="min-width: 140px;">Status</th>
                        <th scope="col" class="text-end" style="min-width: 130px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($publications as $publication)
                        <tr>
                            <td>
                                <div class="d-flex flex-column py-1">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="badge bg-light text-muted border font-monospace" style="font-size: 0.72rem;">
                                            {{ $publication->registration_code }}
                                        </span>
                                        @if ($publication->doi)
                                            <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.72rem;">
                                                DOI: {{ $publication->doi }}
                                            </span>
                                        @endif
                                    </div>
                                    <a href="{{ route('publications.show', $publication) }}" class="fw-semibold text-dark text-decoration-none hover-primary mb-1 lh-sm">
                                        {{ $publication->title }}
                                    </a>
                                    <div class="text-muted small d-flex align-items-center gap-1">
                                        <i class="icon-base ti tabler-book-2"></i>
                                        <span>{{ $publication->journal_name }} ({{ $publication->publication_year }})</span>
                                    </div>
                                </div>
                            </td>

                            @if (Auth::user()?->isAdminLPPM())
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-initials bg-light text-primary border" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                            {{ strtoupper(substr($publication->submitter->name ?? 'D', 0, 2)) }}
                                        </div>
                                        <div class="overflow-hidden">
                                            <div class="fw-semibold text-dark text-truncate" style="font-size: 0.85rem;">
                                                {{ $publication->submitter->name ?? 'Dosen Pengusul' }}
                                            </div>
                                            <div class="text-muted text-truncate small" style="font-size: 0.75rem;">
                                                {{ $publication->submitter->faculty ?? 'Fakultas Riset' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            @endif

                            <td>
                                @if ($publication->category)
                                    <span class="badge badge-category">
                                        {{ $publication->category->name }}
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>

                            @if (! Auth::user()?->isAdminLPPM())
                                <td>
                                    <div class="small text-dark">
                                        @foreach ($publication->authors->take(2) as $author)
                                            <div class="text-truncate" style="max-width: 200px;">
                                                {{ $author->author_name }}
                                                @if($author->is_corresponding)
                                                    <i class="icon-base ti tabler-mail text-primary" title="Penulis Korespondensi"></i>
                                                @endif
                                            </div>
                                        @endforeach
                                        @if ($publication->authors->count() > 2)
                                            <div class="text-muted" style="font-size: 0.75rem;">+{{ $publication->authors->count() - 2 }} lainnya</div>
                                        @endif
                                    </div>
                                </td>
                            @endif

                            <td>
                                <x-publication.status-badge :status="$publication->status" />
                            </td>

                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group" aria-label="Aksi Naskah">
                                    <a href="{{ route('publications.show', $publication) }}" class="btn btn-outline-secondary" title="Lihat Detail Naskah" aria-label="Lihat Detail Naskah">
                                        <i class="icon-base ti tabler-eye"></i>
                                    </a>

                                    @can('update', $publication)
                                        <a href="{{ route('publications.edit', $publication) }}" class="btn btn-outline-primary" title="Ubah Data Naskah" aria-label="Ubah Data Naskah">
                                            <i class="icon-base ti tabler-edit"></i>
                                        </a>
                                    @endcan

                                    @can('delete', $publication)
                                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $publication->id }}" title="Hapus Pengajuan" aria-label="Hapus Pengajuan">
                                            <i class="icon-base ti tabler-trash"></i>
                                        </button>
                                    @endcan
                                </div>

                                @can('delete', $publication)
                                    <!-- Delete Confirmation Modal -->
                                    <div class="modal fade" id="deleteModal{{ $publication->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $publication->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content text-start">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold" id="deleteModalLabel{{ $publication->id }}">Konfirmasi Hapus Naskah</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="mb-2">Apakah Anda yakin ingin menghapus naskah publikasi berikut?</p>
                                                    <div class="p-3 bg-light rounded border mb-2">
                                                        <div class="fw-semibold text-dark">{{ $publication->title }}</div>
                                                        <div class="text-muted small">Kode: {{ $publication->registration_code }}</div>
                                                    </div>
                                                    <div class="text-danger small">Tindakan ini tidak dapat dibatalkan dan file dokumen PDF akan dihapus dari server repositori.</div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <form action="{{ route('publications.destroy', $publication) }}" method="POST" class="m-0">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Hapus Naskah</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ Auth::user()?->isAdminLPPM() ? 5 : 5 }}" class="text-center py-5">
                                <x-publication.empty-state
                                    :is-admin="Auth::user()?->isAdminLPPM()"
                                    :has-filters="request()->hasAny(['q', 'status', 'category_id'])"
                                    :reset-route="route('publications.index')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($publications->hasPages())
            <div class="card-footer bg-white py-3 border-top">
                {{ $publications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
