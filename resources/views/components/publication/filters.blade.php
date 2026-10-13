@props([
    'counts' => [],
    'currentStatus' => 'all',
    'currentSearch' => '',
    'categories' => [],
    'routeIndex' => 'publications.index',
])

@php
    $selectedStatus = ($currentStatus === 'all' || ! $currentStatus) ? null : $currentStatus;
@endphp

<!-- Segmented Filter Tabs -->
<div class="d-flex align-items-center gap-2 overflow-x-auto pb-2 mb-3 status-tabs-container">
    <a href="{{ route($routeIndex, array_merge(request()->except('status', 'page'))) }}"
       class="btn btn-sm {{ ! $selectedStatus ? 'btn-dark' : 'btn-outline-secondary bg-white text-secondary' }} d-inline-flex align-items-center gap-2 text-nowrap rounded-pill px-3 py-2">
        <span>Semua Naskah</span>
        <span class="badge {{ ! $selectedStatus ? 'bg-white text-dark' : 'bg-light text-muted border' }} rounded-pill">{{ $counts['all'] ?? 0 }}</span>
    </a>

    <a href="{{ route($routeIndex, array_merge(request()->except('status', 'page'), ['status' => 'pending'])) }}"
       class="btn btn-sm {{ $selectedStatus === 'pending' ? 'btn-warning text-dark fw-semibold' : 'btn-outline-secondary bg-white text-secondary' }} d-inline-flex align-items-center gap-2 text-nowrap rounded-pill px-3 py-2">
        <i class="icon-base ti tabler-clock"></i>
        <span>Menunggu Verifikasi</span>
        <span class="badge {{ $selectedStatus === 'pending' ? 'bg-dark text-white' : 'bg-warning-subtle text-warning border border-warning-subtle' }} rounded-pill">{{ $counts['pending'] ?? 0 }}</span>
    </a>

    <a href="{{ route($routeIndex, array_merge(request()->except('status', 'page'), ['status' => 'approved'])) }}"
       class="btn btn-sm {{ $selectedStatus === 'approved' ? 'btn-success text-white fw-semibold' : 'btn-outline-secondary bg-white text-secondary' }} d-inline-flex align-items-center gap-2 text-nowrap rounded-pill px-3 py-2">
        <i class="icon-base ti tabler-circle-check"></i>
        <span>Disetujui LPPM</span>
        <span class="badge {{ $selectedStatus === 'approved' ? 'bg-white text-success' : 'bg-success-subtle text-success border border-success-subtle' }} rounded-pill">{{ $counts['approved'] ?? 0 }}</span>
    </a>

    <a href="{{ route($routeIndex, array_merge(request()->except('status', 'page'), ['status' => 'rejected'])) }}"
       class="btn btn-sm {{ $selectedStatus === 'rejected' ? 'btn-danger text-white fw-semibold' : 'btn-outline-secondary bg-white text-secondary' }} d-inline-flex align-items-center gap-2 text-nowrap rounded-pill px-3 py-2">
        <i class="icon-base ti tabler-alert-circle"></i>
        <span>Perlu Revisi</span>
        <span class="badge {{ $selectedStatus === 'rejected' ? 'bg-white text-danger' : 'bg-danger-subtle text-danger border border-danger-subtle' }} rounded-pill">{{ $counts['rejected'] ?? 0 }}</span>
    </a>
</div>

<!-- Integrated Search & Filter Bar -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="{{ route($routeIndex) }}" method="GET" class="row g-2 align-items-center">
            @if ($selectedStatus)
                <input type="hidden" name="status" value="{{ $selectedStatus }}">
            @endif

            <div class="col-12 col-md-5">
                <label for="search-input" class="visually-hidden">Cari Naskah atau Jurnal</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="icon-base ti tabler-search"></i></span>
                    <input type="text" id="search-input" name="q" value="{{ $currentSearch }}" class="form-control border-start-0" placeholder="Cari judul, nomor DOI, atau jurnal..." aria-label="Cari Naskah">
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
                    <a href="{{ route($routeIndex, $selectedStatus ? ['status' => $selectedStatus] : []) }}" class="btn btn-outline-secondary" title="Reset Filter" aria-label="Reset Filter">
                        <i class="icon-base ti tabler-rotate"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>
