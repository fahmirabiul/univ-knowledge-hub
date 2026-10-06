@extends('layouts.app')

@section('title', 'Daftar Publikasi Ilmiah')

@section('content')
<div class="container-fluid p-0">
    <!-- Page Header with Single Clear CTA Action -->
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2 mb-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">Daftar Publikasi Ilmiah</h4>
            <p class="text-muted small mb-0">Kelola dan pantau status naskah riset, artikel jurnal, dan prosiding Anda.</p>
        </div>
        <div>
            <a href="{{ route('publications.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2">
                <i class="icon-base ti tabler-plus fs-5"></i>
                <span class="fw-semibold">Unggah Naskah Baru</span>
            </a>
        </div>
    </div>

    <!-- Interactive Status Segmented Filter Tabs (Clean & Functional) -->
    <div class="d-flex align-items-center gap-2 overflow-x-auto pb-2 mb-3 status-tabs-container">
        <a href="{{ route('publications.index', array_merge(request()->except('status', 'page'))) }}"
           class="btn btn-sm {{ !request('status') ? 'btn-dark' : 'btn-outline-secondary bg-white text-secondary' }} d-inline-flex align-items-center gap-2 text-nowrap rounded-pill px-3 py-2">
            <span>Semua Naskah</span>
            <span class="badge {{ !request('status') ? 'bg-white text-dark' : 'bg-light text-muted border' }} rounded-pill">{{ $counts['all'] ?? 0 }}</span>
        </a>

        <a href="{{ route('publications.index', array_merge(request()->except('status', 'page'), ['status' => 'pending'])) }}"
           class="btn btn-sm {{ request('status') === 'pending' ? 'btn-warning text-dark fw-semibold' : 'btn-outline-secondary bg-white text-secondary' }} d-inline-flex align-items-center gap-2 text-nowrap rounded-pill px-3 py-2">
            <i class="icon-base ti tabler-clock"></i>
            <span>Menunggu Verifikasi</span>
            <span class="badge {{ request('status') === 'pending' ? 'bg-dark text-white' : 'bg-warning-subtle text-warning border border-warning-subtle' }} rounded-pill">{{ $counts['pending'] ?? 0 }}</span>
        </a>

        <a href="{{ route('publications.index', array_merge(request()->except('status', 'page'), ['status' => 'approved'])) }}"
           class="btn btn-sm {{ request('status') === 'approved' ? 'btn-success text-white' : 'btn-outline-secondary bg-white text-secondary' }} d-inline-flex align-items-center gap-2 text-nowrap rounded-pill px-3 py-2">
            <i class="icon-base ti tabler-circle-check"></i>
            <span>Disetujui LPPM</span>
            <span class="badge {{ request('status') === 'approved' ? 'bg-white text-success' : 'bg-success-subtle text-success border border-success-subtle' }} rounded-pill">{{ $counts['approved'] ?? 0 }}</span>
        </a>

        <a href="{{ route('publications.index', array_merge(request()->except('status', 'page'), ['status' => 'rejected'])) }}"
           class="btn btn-sm {{ request('status') === 'rejected' ? 'btn-danger text-white' : 'btn-outline-secondary bg-white text-secondary' }} d-inline-flex align-items-center gap-2 text-nowrap rounded-pill px-3 py-2">
            <i class="icon-base ti tabler-alert-circle"></i>
            <span>Perlu Revisi</span>
            <span class="badge {{ request('status') === 'rejected' ? 'bg-white text-danger' : 'bg-danger-subtle text-danger border border-danger-subtle' }} rounded-pill">{{ $counts['rejected'] ?? 0 }}</span>
        </a>
    </div>

    <!-- Integrated Search & Filter Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('publications.index') }}" method="GET" class="row g-2 align-items-center">
                @if (request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif

                <div class="col-12 col-md-5">
                    <label for="search-input" class="visually-hidden">Cari Naskah atau Jurnal</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="icon-base ti tabler-search"></i></span>
                        <input type="text" id="search-input" name="q" value="{{ request('q') }}" class="form-control border-start-0" placeholder="Cari judul, nomor DOI, atau jurnal..." aria-label="Cari judul, nomor DOI, atau jurnal">
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <label for="category-filter" class="visually-hidden">Kategori Riset</label>
                    <select id="category-filter" name="category_id" class="form-select" aria-label="Filter berdasarkan kategori riset">
                        <option value="">Semua Kategori Bidang</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (string)request('category_id') === (string)$cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary w-100 d-inline-flex align-items-center justify-content-center gap-1">
                        <i class="icon-base ti tabler-filter"></i>
                        <span>Terapkan</span>
                    </button>
                    @if (request()->hasAny(['q', 'category_id', 'year']))
                        <a href="{{ route('publications.index', request()->only('status')) }}" class="btn btn-outline-secondary" title="Reset Pencarian" aria-label="Reset Pencarian">
                            <i class="icon-base ti tabler-rotate"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Publications List Table Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" style="min-width: 280px; width: 45%;">Judul & Informasi Naskah</th>
                        <th scope="col" style="min-width: 140px;">Kategori</th>
                        <th scope="col" style="min-width: 160px;">Penulis & Kolaborator</th>
                        <th scope="col" style="min-width: 150px;">Status</th>
                        <th scope="col" class="text-end" style="min-width: 120px;">Aksi</th>
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
                            <td>
                                @if ($publication->category)
                                    <span class="badge badge-category">
                                        {{ $publication->category->name }}
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
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
                            <td>
                                @if ($publication->status === \App\Enums\PublicationStatus::PENDING)
                                    <span class="badge badge-status-pending px-2 py-1">
                                        <i class="icon-base ti tabler-clock me-1"></i> Menunggu
                                    </span>
                                @elseif ($publication->status === \App\Enums\PublicationStatus::APPROVED)
                                    <span class="badge badge-status-approved px-2 py-1">
                                        <i class="icon-base ti tabler-circle-check me-1"></i> Disetujui
                                    </span>
                                @elseif ($publication->status === \App\Enums\PublicationStatus::REJECTED)
                                    <span class="badge badge-status-rejected px-2 py-1" title="Perlu perbaikan sesuai catatan LPPM">
                                        <i class="icon-base ti tabler-alert-circle me-1"></i> Perlu Revisi
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group" aria-label="Aksi Naskah">
                                    <a href="{{ route('publications.show', $publication) }}" class="btn btn-outline-secondary" title="Lihat Detail Naskah" aria-label="Lihat Detail Naskah">
                                        <i class="icon-base ti tabler-eye"></i>
                                    </a>
                                    @if (! $publication->isApproved() || Auth::user()->isAdminLPPM())
                                        <a href="{{ route('publications.edit', $publication) }}" class="btn btn-outline-primary" title="Ubah Data Naskah" aria-label="Ubah Data Naskah">
                                            <i class="icon-base ti tabler-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $publication->id }}" title="Hapus Pengajuan" aria-label="Hapus Pengajuan">
                                            <i class="icon-base ti tabler-trash"></i>
                                        </button>
                                    @endif
                                </div>

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
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center justify-content-center py-4">
                                    <div class="avatar-initials bg-light text-muted rounded-circle p-3 mb-3" style="width: 56px; height: 56px;">
                                        <i class="icon-base ti tabler-books fs-2"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">Belum Ada Naskah Publikasi</h6>
                                    <p class="text-muted small mb-3" style="max-width: 440px;">
                                        @if (request()->hasAny(['q', 'status', 'category_id']))
                                            Tidak ada naskah yang cocok dengan filter pencarian saat ini. Coba ubah kata kunci atau reset filter.
                                        @else
                                            Anda belum mengajukan naskah riset atau jurnal ke repositori kampus. Mulai ajukan publikasi ilmiah pertama Anda.
                                        @endif
                                    </p>
                                    @if (request()->hasAny(['q', 'status', 'category_id']))
                                        <a href="{{ route('publications.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                                            <i class="icon-base ti tabler-rotate"></i>
                                            <span>Reset Filter Pencarian</span>
                                        </a>
                                    @else
                                        <a href="{{ route('publications.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-2 px-3 py-2">
                                            <i class="icon-base ti tabler-plus"></i>
                                            <span>Unggah Naskah Pertama</span>
                                        </a>
                                    @endif
                                </div>
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
