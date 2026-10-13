@props([
    'isAdmin' => false,
    'hasFilters' => false,
    'resetRoute' => null,
])

<div class="d-flex flex-column align-items-center justify-content-center py-5">
    <div class="avatar-initials bg-light text-muted rounded-circle p-3 mb-3" style="width: 56px; height: 56px;">
        <i class="icon-base ti tabler-books fs-2"></i>
    </div>
    <h6 class="fw-bold text-dark mb-1">
        {{ $hasFilters ? 'Naskah Tidak Ditemukan' : ($isAdmin ? 'Tidak Ada Naskah dalam Antrean' : 'Belum Ada Naskah Publikasi') }}
    </h6>
    <p class="text-muted small mb-3 text-center" style="max-width: 440px;">
        @if ($hasFilters)
            Tidak ada naskah yang cocok dengan filter pencarian saat ini. Coba ubah kata kunci atau reset filter.
        @elseif ($isAdmin)
            Saat ini tidak ada naskah publikasi dosen yang memerlukan tindakan pada kriteria ini.
        @else
            Anda belum mengajukan naskah riset atau jurnal ke repositori kampus. Mulai ajukan publikasi ilmiah pertama Anda.
        @endif
    </p>
    @if ($hasFilters && $resetRoute)
        <a href="{{ $resetRoute }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
            <i class="icon-base ti tabler-rotate"></i>
            <span>Reset Filter Pencarian</span>
        </a>
    @elseif (! $isAdmin)
        <a href="{{ route('publications.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-2 px-3 py-2">
            <i class="icon-base ti tabler-plus"></i>
            <span>Unggah Naskah Pertama</span>
        </a>
    @endif
</div>
