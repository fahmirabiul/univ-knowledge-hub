@extends('layouts.app')

@section('title', 'Edit Publikasi - ' . Str::limit($publication->title, 40))

@section('content')
<div class="container-fluid p-0">
    <!-- Breadcrumb & Title -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Edit Publikasi Ilmiah</h4>
            <p class="text-muted small mb-0">Kode Registrasi: <strong>{{ $publication->registration_code }}</strong></p>
        </div>
        <a href="{{ route('publications.show', $publication) }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
            <i class="icon-base ti tabler-arrow-left"></i>
            <span>Kembali ke Detail</span>
        </a>
    </div>

    <!-- Main Edit Form -->
    <form action="{{ route('publications.update', $publication) }}" method="POST" enctype="multipart/form-data" id="form-edit-publication">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Left Column: Metadata & Authors -->
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header border-bottom py-3">
                        <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="icon-base ti tabler-file-text text-primary"></i>
                            <span>Informasi Bibliografi Naskah</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- DOI & ISBN/ISSN Row -->
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="field-doi" class="form-label">Nomor DOI <span class="text-muted small">(Opsional)</span></label>
                                <input type="text" id="field-doi" name="doi" value="{{ old('doi', $publication->doi) }}" class="form-control @error('doi') is-invalid @enderror">
                                @error('doi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="field-isbn" class="form-label">ISBN / ISSN <span class="text-muted small">(Opsional)</span></label>
                                <input type="text" id="field-isbn" name="isbn_issn" value="{{ old('isbn_issn', $publication->isbn_issn) }}" class="form-control @error('isbn_issn') is-invalid @enderror">
                                @error('isbn_issn')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Title -->
                        <div class="mb-3">
                            <label for="field-title" class="form-label">Judul Lengkap Publikasi Ilmiah <span class="text-danger">*</span></label>
                            <input type="text" id="field-title" name="title" value="{{ old('title', $publication->title) }}" class="form-control @error('title') is-invalid @enderror" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Category & Journal Name -->
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="field-category" class="form-label">Kategori Bidang Riset</label>
                                <select id="field-category" name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                    <option value="">Pilih Kategori Klasifikasi</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ (string)old('category_id', $publication->category_id) === (string)$category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="field-journal" class="form-label">Nama Jurnal / Konferensi Penerbit <span class="text-danger">*</span></label>
                                <input type="text" id="field-journal" name="journal_name" value="{{ old('journal_name', $publication->journal_name) }}" class="form-control @error('journal_name') is-invalid @enderror" required>
                                @error('journal_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Year, Volume, Issue, Pages -->
                        <div class="row g-3 mb-3">
                            <div class="col-6 col-md-3">
                                <label for="field-year" class="form-label">Tahun Terbit <span class="text-danger">*</span></label>
                                <input type="number" id="field-year" name="publication_year" value="{{ old('publication_year', $publication->publication_year) }}" class="form-control @error('publication_year') is-invalid @enderror" min="1900" max="{{ date('Y') + 1 }}" required>
                                @error('publication_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6 col-md-3">
                                <label for="field-volume" class="form-label">Volume</label>
                                <input type="text" id="field-volume" name="volume" value="{{ old('volume', $publication->volume) }}" class="form-control @error('volume') is-invalid @enderror">
                                @error('volume')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6 col-md-3">
                                <label for="field-issue" class="form-label">Nomor / Issue</label>
                                <input type="text" id="field-issue" name="issue" value="{{ old('issue', $publication->issue) }}" class="form-control @error('issue') is-invalid @enderror">
                                @error('issue')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6 col-md-3">
                                <label for="field-pages" class="form-label">Halaman</label>
                                <input type="text" id="field-pages" name="pages" value="{{ old('pages', $publication->pages) }}" class="form-control @error('pages') is-invalid @enderror">
                                @error('pages')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Abstract -->
                        <div class="mb-0">
                            <label for="field-abstract" class="form-label">Abstrak Publikasi <span class="text-danger">*</span></label>
                            <textarea id="field-abstract" name="abstract" rows="6" class="form-control @error('abstract') is-invalid @enderror" required>{{ old('abstract', $publication->abstract) }}</textarea>
                            @error('abstract')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Author Collaboration Repeater Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header border-bottom py-3 d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="icon-base ti tabler-users text-primary"></i>
                            <span>Daftar Penulis & Kolaborator</span>
                        </h5>
                        <button type="button" id="btn-add-author" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1">
                            <i class="icon-base ti tabler-user-plus"></i>
                            <span>Tambah Penulis</span>
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="author-repeater-container" class="d-flex flex-column gap-3 w-100">
                            @forelse ($publication->authors as $index => $author)
                                <div class="author-row p-3 border rounded {{ $index === 0 ? 'bg-light' : 'bg-white' }} position-relative w-100" data-index="{{ $index }}">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="badge bg-primary-subtle text-primary fw-semibold author-order-label">
                                            {{ $index === 0 ? 'Penulis #1 (Pengusul)' : 'Penulis #' . ($index + 1) }}
                                        </span>
                                        @if ($index > 0)
                                            <button type="button" class="btn btn-sm btn-icon btn-outline-danger btn-remove-author" title="Hapus Penulis" aria-label="Hapus Penulis">
                                                <i class="icon-base ti tabler-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                    <div class="row g-2">
                                        <input type="hidden" name="authors[{{ $index }}][user_id]" value="{{ $author->user_id }}">
                                        <input type="hidden" name="authors[{{ $index }}][author_order]" value="{{ $author->author_order ?? ($index + 1) }}" class="input-author-order">
                                        <input type="hidden" name="authors[{{ $index }}][is_external]" value="{{ $author->is_external ? 1 : 0 }}">

                                        <div class="col-12 col-md-5">
                                            <label class="form-label small mb-1">Nama Lengkap Penulis</label>
                                            <input type="text" name="authors[{{ $index }}][author_name]" value="{{ old("authors.{$index}.author_name", $author->author_name) }}" class="form-control form-control-sm input-author-name" required>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label small mb-1">Afiliasi / Institusi</label>
                                            <input type="text" name="authors[{{ $index }}][affiliation]" value="{{ old("authors.{$index}.affiliation", $author->affiliation) }}" class="form-control form-control-sm input-author-affiliation">
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label small mb-1">Peran Penulis</label>
                                            <select name="authors[{{ $index }}][author_role]" class="form-select form-select-sm select-author-role">
                                                <option value="pertama_korespondensi" {{ (old("authors.{$index}.author_role", $author->author_role?->value) === 'pertama_korespondensi') ? 'selected' : '' }}>Penulis Pertama & Korespondensi</option>
                                                <option value="penulis_pertama" {{ (old("authors.{$index}.author_role", $author->author_role?->value) === 'penulis_pertama') ? 'selected' : '' }}>Penulis Pertama</option>
                                                <option value="korespondensi" {{ (old("authors.{$index}.author_role", $author->author_role?->value) === 'korespondensi') ? 'selected' : '' }}>Penulis Korespondensi</option>
                                                <option value="pendamping" {{ (old("authors.{$index}.author_role", $author->author_role?->value) === 'pendamping') ? 'selected' : '' }}>Penulis Pendamping</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="author-row p-3 border rounded bg-light position-relative" data-index="0">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="badge bg-primary-subtle text-primary fw-semibold author-order-label">Penulis #1 (Pengusul)</span>
                                    </div>
                                    <div class="row g-2">
                                        <input type="hidden" name="authors[0][user_id]" value="{{ Auth::id() }}">
                                        <input type="hidden" name="authors[0][author_order]" value="1" class="input-author-order">
                                        <input type="hidden" name="authors[0][is_external]" value="0">

                                        <div class="col-12 col-md-5">
                                            <label class="form-label small mb-1">Nama Lengkap Penulis</label>
                                            <input type="text" name="authors[0][author_name]" value="{{ old('authors.0.author_name', Auth::user()->name) }}" class="form-control form-control-sm input-author-name" required>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label small mb-1">Afiliasi / Institusi</label>
                                            <input type="text" name="authors[0][affiliation]" value="{{ old('authors.0.affiliation', config('app.institution_name', 'Universitas')) }}" class="form-control form-control-sm input-author-affiliation" placeholder="Nama universitas / institusi...">
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label small mb-1">Peran Penulis</label>
                                            <select name="authors[0][author_role]" class="form-select form-select-sm select-author-role">
                                                <option value="pertama_korespondensi" selected>Penulis Pertama & Korespondensi</option>
                                                <option value="penulis_pertama">Penulis Pertama</option>
                                                <option value="korespondensi">Penulis Korespondensi</option>
                                                <option value="pendamping">Penulis Pendamping</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Document File & Actions -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header border-bottom py-3">
                        <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="icon-base ti tabler-file-upload text-primary"></i>
                            <span>Ganti File Dokumen (PDF)</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="p-3 bg-light rounded border mb-3">
                            <div class="text-muted small mb-1">Dokumen Naskah Saat Ini:</div>
                            <div class="fw-semibold text-dark text-truncate small">
                                <i class="icon-base ti tabler-file-type-pdf text-danger me-1"></i>
                                {{ basename($publication->file_path) }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="field-file" class="form-label">Unggah Dokumen Pengganti <span class="text-muted small">(Opsional)</span></label>
                            <input type="file" id="field-file" name="file" accept="application/pdf" class="form-control @error('file') is-invalid @enderror">
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text mt-2" style="font-size: 0.78rem;">
                                Biarkan kosong jika tidak ingin mengubah dokumen naskah yang sudah tersimpan.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2">
                            <i class="icon-base ti tabler-device-floppy"></i>
                            <span class="fw-semibold">Simpan Perubahan</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const repeaterContainer = document.getElementById('author-repeater-container');
    const btnAddAuthor = document.getElementById('btn-add-author');
    let authorCount = {{ $publication->authors->count() > 0 ? $publication->authors->count() : 1 }};

    btnAddAuthor.addEventListener('click', function() {
        const index = authorCount++;
        const row = document.createElement('div');
        row.className = 'author-row p-3 border rounded bg-white position-relative w-100';
        row.dataset.index = index;

        row.innerHTML = `
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="badge bg-secondary-subtle text-secondary fw-semibold author-order-label">Penulis #${index + 1}</span>
                <button type="button" class="btn btn-sm btn-icon btn-outline-danger btn-remove-author" title="Hapus Penulis" aria-label="Hapus Penulis">
                    <i class="icon-base ti tabler-trash"></i>
                </button>
            </div>
            <div class="row g-2">
                <input type="hidden" name="authors[${index}][user_id]" value="">
                <input type="hidden" name="authors[${index}][author_order]" value="${index + 1}" class="input-author-order">
                <input type="hidden" name="authors[${index}][is_external]" value="1">

                <div class="col-12 col-md-5">
                    <label class="form-label small mb-1">Nama Lengkap Penulis</label>
                    <input type="text" name="authors[${index}][author_name]" value="" class="form-control form-control-sm input-author-name" placeholder="Nama penulis..." required>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small mb-1">Afiliasi / Institusi</label>
                    <input type="text" name="authors[${index}][affiliation]" value="" class="form-control form-control-sm input-author-affiliation" placeholder="Institusi atau Universitas...">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small mb-1">Peran Penulis</label>
                    <select name="authors[${index}][author_role]" class="form-select form-select-sm select-author-role">
                        <option value="pendamping" selected>Penulis Pendamping</option>
                        <option value="korespondensi">Penulis Korespondensi</option>
                        <option value="penulis_pertama">Penulis Pertama</option>
                    </select>
                </div>
            </div>
        `;

        repeaterContainer.appendChild(row);
        bindRemoveEvents();
    });

    function bindRemoveEvents() {
        repeaterContainer.querySelectorAll('.btn-remove-author').forEach(btn => {
            btn.onclick = function() {
                btn.closest('.author-row').remove();
                reindexAuthors();
            };
        });
    }

    function reindexAuthors() {
        const rows = repeaterContainer.querySelectorAll('.author-row');
        rows.forEach((row, idx) => {
            const orderLabel = row.querySelector('.author-order-label');
            if (idx === 0) {
                orderLabel.textContent = 'Penulis #1 (Pengusul)';
            } else {
                orderLabel.textContent = `Penulis #${idx + 1}`;
            }
            const orderInput = row.querySelector('.input-author-order');
            if (orderInput) orderInput.value = idx + 1;
        });
    }

    bindRemoveEvents();
});
</script>
@endpush
